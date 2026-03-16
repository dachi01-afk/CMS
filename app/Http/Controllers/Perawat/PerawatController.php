<?php

namespace App\Http\Controllers\Perawat;

use App\Http\Controllers\Controller;
use App\Models\Dokter;
use App\Models\DokterPoli;
use App\Models\EMR;
use App\Models\JadwalDokter;
use App\Models\Kunjungan;
use App\Models\Perawat;
use App\Models\Poli;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class PerawatController extends Controller
{
    private const ACTIVE_KUNJUNGAN_STATUSES = ['Pending', 'Waiting', 'Engaged', 'Payment'];

    private function getPerawatLogin(): ?Perawat
    {
        return Perawat::where('user_id', Auth::id())->first();
    }

    private function getNamaPerawat(): string
    {
        $perawat = $this->getPerawatLogin();
        return $perawat->nama_perawat ?? 'Perawat';
    }

    private function normalizeChartFilter(?string $filter): string
    {
        $allowed = ['harian', 'mingguan', 'bulanan', 'tahunan'];
        return in_array($filter, $allowed, true) ? $filter : 'bulanan';
    }

    private function getChartFilterLabel(string $filter): string
    {
        return match ($filter) {
            'harian' => 'Harian',
            'mingguan' => 'Mingguan',
            'tahunan' => 'Tahunan',
            default => 'Bulanan',
        };
    }

    private function emptyChartPayload(string $filter = 'bulanan'): array
    {
        return [
            'filter' => $filter,
            'filter_label' => $this->getChartFilterLabel($filter),
            'labels' => [],
            'range_text' => '-',

            'assigned_total' => [],
            'handled_total' => [],

            'summary_assigned_total' => 0,
            'summary_handled_total' => 0,

            'rows' => [],
        ];
    }

    private function assignedVisitBaseQuery(int $perawatId, ?string $date = null)
    {
        $query = Kunjungan::query()
            ->whereExists(function ($q) use ($perawatId) {
                $q->select(DB::raw(1))
                    ->from('perawat_dokter_poli as pdp')
                    ->join('dokter_poli as dp', 'dp.id', '=', 'pdp.dokter_poli_id')
                    ->whereColumn('dp.dokter_id', 'kunjungan.dokter_id')
                    ->whereColumn('dp.poli_id', 'kunjungan.poli_id')
                    ->where('pdp.perawat_id', $perawatId);
            });

        if ($date) {
            $query->whereDate('kunjungan.tanggal_kunjungan', $date);
        }

        return $query;
    }

    public function dashboard()
    {
        Carbon::setLocale('id');

        $tz = config('app.timezone', 'Asia/Jakarta');
        $today = Carbon::today($tz)->toDateString();
        $namaPerawat = $this->getNamaPerawat();
        $serverStatus = 'Online';
        $chartFilter = 'bulanan';

        $perawat = $this->getPerawatLogin();

        // default data
        $statPasienAreaTugasHariIni = 0;
        $statMenungguTindakan = 0;
        $statSedangKonsultasi = 0;
        $statSudahDitanganiHariIni = 0;
        $statTotalEmrSaya = 0;
        $statTotalPasienUnikSaya = 0;
        $statTotalKunjunganAreaTugas = 0;
        $statDokterAktif = 0;

        $persenPenangananHariIni = 0;

        $listSiapTriage = collect();
        $listPasienTerbaruDitangani = collect();
        $poliTeratas = collect();

        $chartData = $this->emptyChartPayload($chartFilter);

        $dayName = strtolower(Carbon::today($tz)->locale('id')->dayName);
        $statDokterAktif = JadwalDokter::where('hari', $dayName)->count();

        if ($perawat) {
            $perawatId = $perawat->id;

            /*
            |--------------------------------------------------------------------------
            | A. DATA OTOMATIS DARI KUNJUNGAN (AREA TUGAS PERAWAT)
            |--------------------------------------------------------------------------
            */
            $assignedTodayQuery = $this->assignedVisitBaseQuery($perawatId, $today);

            $statPasienAreaTugasHariIni = (clone $assignedTodayQuery)
                ->whereNotNull('kunjungan.pasien_id')
                ->distinct('kunjungan.pasien_id')
                ->count('kunjungan.pasien_id');

            $statMenungguTindakan = (clone $assignedTodayQuery)
                ->where('kunjungan.status', 'Waiting')
                ->count();

            $statSedangKonsultasi = (clone $assignedTodayQuery)
                ->where('kunjungan.status', 'Engaged')
                ->count();

            $statTotalKunjunganAreaTugas = (clone $assignedTodayQuery)->count();

            /*
            |--------------------------------------------------------------------------
            | B. DATA REAL DARI EMR (YANG BENAR-BENAR DIINPUT PERAWAT)
            |--------------------------------------------------------------------------
            */
            $emrBaseQuery = Emr::query()->where('perawat_id', $perawatId);

            $statTotalEmrSaya = (clone $emrBaseQuery)->count();

            $statTotalPasienUnikSaya = (clone $emrBaseQuery)
                ->whereNotNull('pasien_id')
                ->distinct('pasien_id')
                ->count('pasien_id');

            $statSudahDitanganiHariIni = (clone $emrBaseQuery)
                ->whereDate('created_at', $today)
                ->whereNotNull('pasien_id')
                ->distinct('pasien_id')
                ->count('pasien_id');

            $persenPenangananHariIni = $statPasienAreaTugasHariIni > 0
                ? round(($statSudahDitanganiHariIni / $statPasienAreaTugasHariIni) * 100)
                : 0;

            /*
            |--------------------------------------------------------------------------
            | C. LIST PASIEN AREA TUGAS YANG MENUNGGU
            |--------------------------------------------------------------------------
            */
            $listSiapTriage = (clone $assignedTodayQuery)
                ->with(['pasien', 'poli', 'dokter'])
                ->where('kunjungan.status', 'Waiting')
                ->orderByRaw('CAST(kunjungan.no_antrian AS UNSIGNED)')
                ->limit(5)
                ->get()
                ->map(function ($row) {
                    return (object) [
                        'id' => $row->id,
                        'no_antrian' => $row->no_antrian,
                        'nama_pasien' => $row->pasien->nama_pasien ?? '-',
                        'nama_poli' => $row->poli->nama_poli ?? '-',
                        'nama_dokter' => $row->dokter->nama_dokter ?? '-',
                        'status' => $row->status ?? '-',
                    ];
                });

            /*
            |--------------------------------------------------------------------------
            | D. LIST EMR TERBARU YANG DIINPUT PERAWAT
            |--------------------------------------------------------------------------
            */
            $listPasienTerbaruDitangani = Emr::query()
                ->leftJoin('pasien', 'emr.pasien_id', '=', 'pasien.id')
                ->leftJoin('poli', 'emr.poli_id', '=', 'poli.id')
                ->leftJoin('dokter', 'emr.dokter_id', '=', 'dokter.id')
                ->where('emr.perawat_id', $perawatId)
                ->orderByDesc('emr.created_at')
                ->select([
                    'emr.id',
                    'emr.created_at',
                    'pasien.nama_pasien',
                    'poli.nama_poli',
                    'dokter.nama_dokter',
                    'emr.keluhan_utama',
                    'emr.tekanan_darah',
                    'emr.suhu_tubuh',
                    'emr.nadi',
                    'emr.pernapasan',
                    'emr.saturasi_oksigen',
                ])
                ->limit(5)
                ->get();

            /*
            |--------------------------------------------------------------------------
            | E. POLI TERATAS BERDASARKAN INPUT EMR PERAWAT
            |--------------------------------------------------------------------------
            */
            $poliTeratas = Emr::query()
                ->leftJoin('poli', 'emr.poli_id', '=', 'poli.id')
                ->where('emr.perawat_id', $perawatId)
                ->select(
                    'emr.poli_id',
                    'poli.nama_poli',
                    DB::raw('COUNT(emr.id) as total_emr'),
                    DB::raw('COUNT(DISTINCT emr.pasien_id) as total_pasien')
                )
                ->groupBy('emr.poli_id', 'poli.nama_poli')
                ->orderByDesc('total_emr')
                ->limit(5)
                ->get();

            /*
            |--------------------------------------------------------------------------
            | F. CHART HYBRID
            |--------------------------------------------------------------------------
            */
            $chartData = $this->buildHybridPerawatChart($perawatId, $chartFilter);
        }

        return view('perawat.dashboard', [
            'namaPerawat' => $namaPerawat,
            'serverStatus' => $serverStatus,

            // KPI kunjungan otomatis
            'statPasienAreaTugasHariIni' => $statPasienAreaTugasHariIni,
            'statMenungguTindakan' => $statMenungguTindakan,
            'statSedangKonsultasi' => $statSedangKonsultasi,
            'statTotalKunjunganAreaTugas' => $statTotalKunjunganAreaTugas,

            // KPI EMR real
            'statSudahDitanganiHariIni' => $statSudahDitanganiHariIni,
            'statTotalEmrSaya' => $statTotalEmrSaya,
            'statTotalPasienUnikSaya' => $statTotalPasienUnikSaya,

            // tambahan
            'statDokterAktif' => $statDokterAktif,
            'persenPenangananHariIni' => $persenPenangananHariIni,

            // list
            'listSiapTriage' => $listSiapTriage,
            'listPasienTerbaruDitangani' => $listPasienTerbaruDitangani,
            'poliTeratas' => $poliTeratas,

            // chart
            'chartFilter' => $chartFilter,
            'chartData' => $chartData,
        ]);
    }

    public function chartDashboard(Request $request)
    {
        $perawat = $this->getPerawatLogin();

        if (!$perawat) {
            return response()->json($this->emptyChartPayload());
        }

        $filter = $this->normalizeChartFilter($request->get('filter'));

        return response()->json(
            $this->buildHybridPerawatChart($perawat->id, $filter)
        );
    }

    private function buildHybridPerawatChart(int $perawatId, string $filter): array
    {
        Carbon::setLocale('id');
        $now = now();

        switch ($filter) {
            case 'harian':
                $start = $now->copy()->startOfMonth();
                $end = $now->copy()->endOfMonth();
                $bucketType = 'day';
                break;

            case 'mingguan':
                $start = $now->copy()->subWeeks(11)->startOfWeek(Carbon::MONDAY);
                $end = $now->copy()->endOfWeek(Carbon::SUNDAY);
                $bucketType = 'week';
                break;

            case 'tahunan':
                $start = $now->copy()->subYears(4)->startOfYear();
                $end = $now->copy()->endOfYear();
                $bucketType = 'year';
                break;

            case 'bulanan':
            default:
                $start = $now->copy()->startOfYear();
                $end = $now->copy()->endOfYear();
                $bucketType = 'month';
                break;
        }

        $labels = [];
        $keys = [];
        $cursor = $start->copy();

        if ($bucketType === 'day') {
            while ($cursor->lte($end)) {
                $keys[] = $cursor->format('Y-m-d');
                $labels[] = $cursor->translatedFormat('d M');
                $cursor->addDay();
            }
        } elseif ($bucketType === 'week') {
            while ($cursor->lte($end)) {
                $weekStart = $cursor->copy()->startOfWeek(Carbon::MONDAY);
                $weekEnd = $cursor->copy()->endOfWeek(Carbon::SUNDAY);

                $keys[] = $weekStart->format('Y-m-d');
                $labels[] = $weekStart->translatedFormat('d M') . ' - ' . $weekEnd->translatedFormat('d M');
                $cursor->addWeek();
            }
        } elseif ($bucketType === 'month') {
            while ($cursor->lte($end)) {
                $keys[] = $cursor->format('Y-m');
                $labels[] = $cursor->translatedFormat('M Y');
                $cursor->addMonth();
            }
        } else {
            while ($cursor->lte($end)) {
                $keys[] = $cursor->format('Y');
                $labels[] = $cursor->format('Y');
                $cursor->addYear();
            }
        }

        $assignedMap = array_fill_keys($keys, 0);
        $handledMap = array_fill_keys($keys, []);

        /*
        |--------------------------------------------------------------------------
        | 1. WORKLOAD OTOMATIS DARI KUNJUNGAN
        |--------------------------------------------------------------------------
        */
        $kunjungans = Kunjungan::query()
            ->select('kunjungan.tanggal_kunjungan', 'kunjungan.pasien_id')
            ->whereDate('kunjungan.tanggal_kunjungan', '>=', $start->toDateString())
            ->whereDate('kunjungan.tanggal_kunjungan', '<=', $end->toDateString())
            ->whereExists(function ($q) use ($perawatId) {
                $q->select(DB::raw(1))
                    ->from('perawat_dokter_poli as pdp')
                    ->join('dokter_poli as dp', 'dp.id', '=', 'pdp.dokter_poli_id')
                    ->whereColumn('dp.dokter_id', 'kunjungan.dokter_id')
                    ->whereColumn('dp.poli_id', 'kunjungan.poli_id')
                    ->where('pdp.perawat_id', $perawatId);
            })
            ->get();

        foreach ($kunjungans as $kunjungan) {
            $tanggal = Carbon::parse($kunjungan->tanggal_kunjungan);

            if ($bucketType === 'day') {
                $key = $tanggal->format('Y-m-d');
            } elseif ($bucketType === 'week') {
                $key = $tanggal->copy()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
            } elseif ($bucketType === 'month') {
                $key = $tanggal->format('Y-m');
            } else {
                $key = $tanggal->format('Y');
            }

            if (!array_key_exists($key, $assignedMap)) {
                continue;
            }

            $assignedMap[$key]++;
        }

        /*
        |--------------------------------------------------------------------------
        | 2. REAL HANDLED DARI EMR
        |--------------------------------------------------------------------------
        */
        $emrRows = Emr::query()
            ->where('perawat_id', $perawatId)
            ->whereDate('created_at', '>=', $start->toDateString())
            ->whereDate('created_at', '<=', $end->toDateString())
            ->select('pasien_id', 'created_at')
            ->get();

        foreach ($emrRows as $row) {
            $tanggal = Carbon::parse($row->created_at);

            if ($bucketType === 'day') {
                $key = $tanggal->format('Y-m-d');
            } elseif ($bucketType === 'week') {
                $key = $tanggal->copy()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
            } elseif ($bucketType === 'month') {
                $key = $tanggal->format('Y-m');
            } else {
                $key = $tanggal->format('Y');
            }

            if (!array_key_exists($key, $handledMap)) {
                continue;
            }

            if (!empty($row->pasien_id)) {
                $handledMap[$key][$row->pasien_id] = true;
            }
        }

        $handledCountMap = [];
        foreach ($handledMap as $key => $patientSet) {
            $handledCountMap[$key] = count($patientSet);
        }

        $rows = [];
        foreach ($keys as $index => $key) {
            $rows[] = [
                'label' => $labels[$index],
                'assigned_total' => $assignedMap[$key] ?? 0,
                'handled_total' => $handledCountMap[$key] ?? 0,
            ];
        }

        return [
            'filter' => $filter,
            'filter_label' => $this->getChartFilterLabel($filter),
            'labels' => $labels,
            'range_text' => $start->translatedFormat('d M Y') . ' - ' . $end->translatedFormat('d M Y'),

            'assigned_total' => array_values($assignedMap),
            'handled_total' => array_values($handledCountMap),

            'summary_assigned_total' => array_sum($assignedMap),
            'summary_handled_total' => array_sum($handledCountMap),

            'rows' => $rows,
        ];
    }

    public function createPerawat(Request $request)
    {
        try {
            // 🧩 Validasi input
            $validated = $request->validate([
                'foto_perawat' => 'required|file|mimes:jpeg,jpg,png,gif,webp,svg,jfif|max:5120',
                'username_perawat' => 'required|string|max:255',
                'nama_perawat' => 'required|string|max:255',
                'email_perawat' => 'required|email',
                'no_hp_perawat' => 'nullable|string|max:20',
                'password_perawat' => 'required|string|min:8|confirmed',

                // relasi banyak dokter_poli
                'dokter_poli_id' => 'nullable|array',
                'dokter_poli_id.*' => 'exists:dokter_poli,id',
            ]);

            DB::beginTransaction();

            // 🧑‍💻 Buat user baru
            $user = User::create([
                'username' => $validated['username_perawat'],
                'email' => $validated['email_perawat'],
                'password' => Hash::make($validated['password_perawat']),
                'role' => 'Perawat',
            ]);

            // 📸 Upload + Kompres Foto
            $fotoPath = null;
            if ($request->hasFile('foto_perawat')) {
                $file = $request->file('foto_perawat');

                $extension = strtolower($file->getClientOriginalExtension());
                if ($extension === 'jfif') {
                    $extension = 'jpg';
                }

                $fileName = 'perawat_' . time() . '.' . $extension;
                $path = 'perawat/' . $fileName;

                if ($extension === 'svg') {
                    Storage::disk('public')->put($path, file_get_contents($file));
                } else {
                    $image = Image::read($file);
                    $image->scale(width: 800);
                    Storage::disk('public')->put(
                        $path,
                        (string) $image->encodeByExtension($extension, quality: 80)
                    );
                }

                $fotoPath = $path;
            }

            // 🏥 Buat data perawat (tanpa dokter_id & poli_id)
            $perawat = Perawat::create([
                'user_id' => $user->id,
                'nama_perawat' => $validated['nama_perawat'],
                'foto_perawat' => $fotoPath,
                'no_hp_perawat' => $validated['no_hp_perawat'] ?? null,
            ]);

            // 🔗 Simpan relasi ke pivot perawat_dokter_poli (boleh banyak)
            if (! empty($validated['dokter_poli_id'])) {
                $perawat->perawatDokterPoli()->attach($validated['dokter_poli_id']);
            }

            DB::commit();

            return response()->json(['message' => 'Data perawat berhasil ditambahkan.']);
        } catch (\Illuminate\Http\Exceptions\PostTooLargeException $e) {
            DB::rollBack();

            return response()->json(['message' => 'Ukuran file terlalu besar! Maksimal 5 MB.'], 413);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Validasi gagal.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Tidak ada respon dari server.',
                'error_detail' => $e->getMessage(),
            ], 500);
        }
    }

    public function getPerawatById($id)
    {
        $data = Perawat::with('user', 'perawatDokterPoli.poli', 'perawatDokterPoli.dokter')->findOrFail($id);

        return response()->json(['data' => $data]);
    }

    public function listPoli(Request $request)
    {
        $q = $request->input('q', '');

        $data = Poli::select('id', 'nama_poli')
            ->when($q, fn($w) => $w->where('nama_poli', 'like', "%{$q}%"))
            ->orderBy('nama_poli')
            ->get();

        return response()->json(['data' => $data]);
    }

    // List dokter berdasarkan poli (ambil dari tabel dokter_poli)
    public function listDokterByPoli(Request $request, $poliId)
    {
        $q = $request->input('q', '');

        $dokterPolis = DokterPoli::with('dokter:id,nama_dokter')
            ->where('poli_id', $poliId)
            ->when($q, function ($w) use ($q) {
                $w->whereHas('dokter', function ($qq) use ($q) {
                    $qq->where('nama_dokter', 'like', "%{$q}%");
                });
            })
            ->get()
            ->sortBy('dokter.nama_dokter')
            ->values();

        $data = $dokterPolis->map(function ($dp) {
            return [
                'dokter_poli_id' => $dp->id,
                'dokter_id' => $dp->dokter_id,
                'nama_dokter' => $dp->dokter->nama_dokter ?? 'Tanpa Nama',
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function updatePerawat(Request $request, $id)
    {
        try {
            $perawat = Perawat::with('user')->findOrFail($id);
            $user = $perawat->user;

            $validated = $request->validate([
                'edit_username_perawat' => 'required|string|max:255',
                'edit_nama_perawat' => 'required|string|max:255',
                'edit_email_perawat' => 'required|email',
                'edit_foto_perawat' => 'nullable|file|mimes:jpeg,jpg,png,gif,webp,svg,jfif|max:5120',
                'edit_no_hp_perawat' => 'nullable|string|max:20',
                'edit_password_perawat' => 'nullable|string|min:8|confirmed',

                // multi penugasan
                'dokter_poli_id' => 'nullable|array',
                'dokter_poli_id.*' => 'integer|exists:dokter_poli,id',
            ]);

            $dokterPoliIds = $request->input('dokter_poli_id', []);

            DB::beginTransaction();

            // --- update user ---
            $user->username = $validated['edit_username_perawat'];
            $user->email = $validated['edit_email_perawat'];

            if (! empty($validated['edit_password_perawat'])) {
                $user->password = Hash::make($validated['edit_password_perawat']);
            }
            $user->save();

            // --- handle foto ---
            $fotoPath = null;
            if ($request->hasFile('edit_foto_perawat')) {
                $file = $request->file('edit_foto_perawat');

                $extension = strtolower($file->getClientOriginalExtension());
                if ($extension === 'jfif') {
                    $extension = 'jpg';
                }

                $fileName = 'perawat_' . time() . '.' . $extension;
                $path = 'perawat/' . $fileName;

                if ($extension === 'svg') {
                    Storage::disk('public')->put($path, file_get_contents($file));
                } else {
                    $image = Image::read($file);
                    $image->scale(width: 800);
                    Storage::disk('public')->put($path, (string) $image->encodeByExtension($extension, quality: 80));
                }

                if ($perawat->foto_perawat && Storage::disk('public')->exists($perawat->foto_perawat)) {
                    Storage::disk('public')->delete($perawat->foto_perawat);
                }

                $fotoPath = $path;
            }

            // --- update perawat ---
            $updateData = [
                'nama_perawat' => $validated['edit_nama_perawat'],
                'no_hp_perawat' => $validated['edit_no_hp_perawat'] ?? $perawat->no_hp_perawat,
            ];
            if ($fotoPath) {
                $updateData['foto_perawat'] = $fotoPath;
            }

            $perawat->update($updateData);

            // --- sync pivot penugasan ---
            $perawat->perawatDokterPoli()->sync($dokterPoliIds);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data perawat berhasil diperbarui.',
            ]);
        } catch (\Illuminate\Http\Exceptions\PostTooLargeException $e) {
            return response()->json([
                'message' => 'Ukuran file terlalu besar! Maksimal 5 MB.',
            ], 413);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Tidak ada respon dari server.',
                'error_detail' => $e->getMessage(),
            ], 500);
        }
    }

    public function deletePerawat($id)
    {
        try {
            $perawat = Perawat::with('user')->findOrFail($id);

            DB::beginTransaction();

            // Hapus foto jika ada
            if ($perawat->foto_perawat && Storage::disk('public')->exists($perawat->foto_perawat)) {
                Storage::disk('public')->delete($perawat->foto_perawat);
            }

            // Jika FK user_id sudah cascadeOnDelete di migrasi perawat,
            // cukup hapus $perawat saja. Tapi untuk pasti, kita hapus berurutan:
            if ($perawat->user) {
                $perawat->user->delete(); // hapus akun user
            }
            $perawat->delete(); // hapus record perawat

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data perawat berhasil dihapus.',
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();

            // FK constraint (contoh: perawat dipakai di tabel lain)
            if ((int) ($e->errorInfo[1] ?? 0) === 1451) { // MySQL: Cannot delete or update a parent row: a foreign key constraint fails
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat menghapus perawat karena masih terkait dengan data lain (kunjungan/EMR/…).
Silakan hapus/lepaskan keterkaitannya terlebih dahulu.',
                ], 409);
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus perawat.',
                'error_detail' => $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server.',
                'error_detail' => $e->getMessage(),
            ], 500);
        }
    }
}

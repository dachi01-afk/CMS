<?php

namespace App\Http\Controllers\Farmasi;

use App\Http\Controllers\Controller;
use App\Models\Farmasi;
use App\Models\Kasir;
use App\Models\Obat;
use App\Models\PenjualanObat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class FarmasiController extends Controller
{
    public function index()
    {
        return view('farmasi.dashboard');
    }

    public function chartPenjualanObat(Request $request)
    {
        $tz   = 'Asia/Jakarta';
        $mode = $request->query('range', 'harian'); // harian | mingguan | bulanan | tahunan
        $now  = Carbon::now($tz);

        $label    = [];
        $seriesA  = []; // jumlah transaksi
        $seriesB  = []; // total pemasukan (Rp)
        $mapTrans = [];
        $mapTotal = [];

        if ($mode === 'harian') {
            $hari = $now->toDateString();

            $rows = PenjualanObat::query()
                ->selectRaw('HOUR(tanggal_transaksi) as idx')
                ->selectRaw('COUNT(*) as jumlah_transaksi')
                ->selectRaw('COALESCE(SUM(sub_total),0) as total')
                ->whereDate('tanggal_transaksi', $hari)
                ->groupBy('idx')
                ->orderBy('idx')
                ->get();

            foreach ($rows as $r) {
                $mapTrans[(int)$r->idx] = (int)$r->jumlah_transaksi;
                $mapTotal[(int)$r->idx] = (float)$r->total;
            }
            for ($h = 0; $h <= 23; $h++) {
                $label[]   = sprintf('%02d:00', $h);
                $seriesA[] = $mapTrans[$h] ?? 0;
                $seriesB[] = $mapTotal[$h] ?? 0.0;
            }

            $meta = [
                'range'    => 'harian',
                'tanggal'  => $hari,
                'timezone' => $tz,
                'x_title'  => 'Jam',
            ];
        } elseif ($mode === 'mingguan') {
            $start = $now->copy()->startOfWeek(Carbon::MONDAY);
            $end   = $now->copy()->endOfWeek(Carbon::SUNDAY);

            $rows = PenjualanObat::query()
                ->selectRaw('WEEKDAY(tanggal_transaksi) as idx') // 0=Mon..6=Sun
                ->selectRaw('COUNT(*) as jumlah_transaksi')
                ->selectRaw('COALESCE(SUM(sub_total),0) as total')
                ->whereBetween(DB::raw('DATE(tanggal_transaksi)'), [$start->toDateString(), $end->toDateString()])
                ->groupBy('idx')
                ->orderBy('idx')
                ->get();

            foreach ($rows as $r) {
                $mapTrans[(int)$r->idx] = (int)$r->jumlah_transaksi;
                $mapTotal[(int)$r->idx] = (float)$r->total;
            }

            $hariIndo = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
            for ($d = 0; $d <= 6; $d++) {
                $label[]   = $hariIndo[$d];
                $seriesA[] = $mapTrans[$d] ?? 0;
                $seriesB[] = $mapTotal[$d] ?? 0.0;
            }

            $meta = [
                'range'    => 'mingguan',
                'start'    => $start->toDateString(),
                'end'      => $end->toDateString(),
                'timezone' => $tz,
                'x_title'  => 'Hari (Minggu ini)',
            ];
        } elseif ($mode === 'bulanan') {
            $start = $now->copy()->startOfMonth();
            $end   = $now->copy()->endOfMonth();
            $days  = $now->daysInMonth;

            $rows = PenjualanObat::query()
                ->selectRaw('DAY(tanggal_transaksi) as idx') // 1..31
                ->selectRaw('COUNT(*) as jumlah_transaksi')
                ->selectRaw('COALESCE(SUM(sub_total),0) as total')
                ->whereBetween(DB::raw('DATE(tanggal_transaksi)'), [$start->toDateString(), $end->toDateString()])
                ->groupBy('idx')
                ->orderBy('idx')
                ->get();

            foreach ($rows as $r) {
                $mapTrans[(int)$r->idx] = (int)$r->jumlah_transaksi;
                $mapTotal[(int)$r->idx] = (float)$r->total;
            }

            for ($d = 1; $d <= $days; $d++) {
                $label[]   = sprintf('%02d', $d);
                $seriesA[] = $mapTrans[$d] ?? 0;
                $seriesB[] = $mapTotal[$d] ?? 0.0;
            }

            $meta = [
                'range'    => 'bulanan',
                'bulan'    => $now->format('Y-m'),
                'timezone' => $tz,
                'x_title'  => 'Tanggal (Bulan ini)',
            ];
        } else { // tahunan
            $start = $now->copy()->startOfYear();
            $end   = $now->copy()->endOfYear();

            $rows = PenjualanObat::query()
                ->selectRaw('MONTH(tanggal_transaksi) as idx') // 1..12
                ->selectRaw('COUNT(*) as jumlah_transaksi')
                ->selectRaw('COALESCE(SUM(sub_total),0) as total')
                ->whereBetween(DB::raw('DATE(tanggal_transaksi)'), [$start->toDateString(), $end->toDateString()])
                ->groupBy('idx')
                ->orderBy('idx')
                ->get();

            foreach ($rows as $r) {
                $mapTrans[(int)$r->idx] = (int)$r->jumlah_transaksi;
                $mapTotal[(int)$r->idx] = (float)$r->total;
            }

            $bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            for ($m = 1; $m <= 12; $m++) {
                $label[]   = $bulan[$m - 1];
                $seriesA[] = $mapTrans[$m] ?? 0;
                $seriesB[] = $mapTotal[$m] ?? 0.0;
            }

            $meta = [
                'range'    => 'tahunan',
                'tahun'    => $now->year,
                'timezone' => $tz,
                'x_title'  => 'Bulan (Tahun ini)',
            ];
        }

        return response()->json([
            'label'   => $label,
            'dataset' => [
                [
                    'label'           => 'Jumlah Transaksi',
                    'type'            => 'bar',
                    'data'            => $seriesA,
                    'borderWidth'     => 1,
                    'borderRadius'    => 6,
                    'backgroundColor' => 'rgba(37,99,235,0.7)',
                    'borderColor'     => 'rgba(37,99,235,1)',
                ],
                [
                    'label'       => 'Total Pemasukan (Rp)',
                    'type'        => 'line',
                    'yAxisID'     => 'y1',
                    'data'        => $seriesB,
                    'borderWidth' => 2,
                    'tension'     => 0.3,
                    'pointRadius' => 3,
                    'borderColor' => 'rgba(16,185,129,1)',
                ],
            ],
            'meta' => $meta,
        ]);
    }

    public function getJumlahPenjualanObatHariIni(Request $request)
    {
        $tz     = 'Asia/Jakarta';
        $today  = Carbon::now($tz)->toDateString();

        // Secara default hanya hitung transaksi yang Sudah Bayar.
        // Bisa override dengan query ?paid=0 kalau ingin termasuk "Belum Bayar".
        $onlyPaid = filter_var($request->query('paid', '1'), FILTER_VALIDATE_BOOLEAN);

        $q = PenjualanObat::query()
            ->whereDate('tanggal_transaksi', $today);

        if ($onlyPaid) {
            $q->where('status', 'Sudah Bayar');
        }

        // Hitung jumlah transaksi unik berdasarkan kode_transaksi
        $totalTransaksi = $q->distinct('kode_transaksi')->count('kode_transaksi');

        return response()->json([
            'total' => (int) $totalTransaksi,
            'meta'  => [
                'tanggal'  => $today,
                'timezone' => $tz,
                'onlyPaid' => $onlyPaid,
            ],
        ]);
    }

    public function getJumlahKeseluruhanTransaksiObat()
    {
        // Hitung semua transaksi unik berdasarkan kode_transaksi
        $totalTransaksi = PenjualanObat::distinct('kode_transaksi')->count('kode_transaksi');

        return response()->json([
            'total' => (int) $totalTransaksi,
        ]);
    }

    public function getTotalObat()
    {
        // Hitung total stok obat dari kolom 'jumlah'
        $jumlahObat = Obat::sum('jumlah');

        // Kembalikan hasil dalam format JSON
        return response()->json([
            'total' => $jumlahObat,
        ]);
    }

    public function createFarmasi(Request $request)
    {
        try {
            // 🧩 Validasi input
            $request->validate([
                'foto_apoteker'     => 'required|file|mimes:jpeg,jpg,png,gif,webp,svg,jfif|max:5120',
                'username_apoteker' => 'required|string|max:255',
                'nama_apoteker'     => 'required|string|max:255',
                'email_apoteker'    => 'required|email',
                'no_hp_apoteker'    => 'nullable|string|max:20',
                'password_apoteker' => 'required|string|min:8|confirmed',
            ]);

            // 🧑‍💻 Buat user baru
            $user = User::create([
                'username' => $request->username_apoteker,
                'email'    => $request->email_apoteker,
                'password' => Hash::make($request->password_apoteker),
                'role'     => 'Farmasi',
            ]);

            // 📸 Upload + Kompres Foto
            $fotoPath = null;
            if ($request->hasFile('foto_apoteker')) {
                $file = $request->file('foto_apoteker');

                $extension = strtolower($file->getClientOriginalExtension());
                if ($extension === 'jfif') {
                    $extension = 'jpg';
                }

                $fileName = 'farmasi_' . time() . '.' . $extension;
                $path = 'farmasi/' . $fileName;

                if ($extension === 'svg') {
                    Storage::disk('public')->put($path, file_get_contents($file));
                } else {
                    $image = Image::read($file);
                    $image->scale(width: 800);
                    Storage::disk('public')->put($path, (string) $image->encodeByExtension($extension, quality: 80));
                }

                $fotoPath = $path;
            }

            // 🏥 Buat data apoteker
            Farmasi::create([
                'user_id'        => $user->id,
                'nama_farmasi'  => $request->nama_apoteker,
                'foto_farmasi'  => $fotoPath,
                'no_hp_farmasi' => $request->no_hp_apoteker,
            ]);

            return response()->json(['message' => 'Data farmasi berhasil ditambahkan.']);
        } catch (\Illuminate\Http\Exceptions\PostTooLargeException $e) {
            // 🚫 File terlalu besar
            return response()->json([
                'message' => 'Ukuran file terlalu besar! Maksimal 5 MB.'
            ], 413);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // ⚠️ Validasi gagal
            return response()->json([
                'message' => 'Validasi gagal.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            // 💥 Error umum
            return response()->json([
                'message' => 'Tidak ada respon dari server.', // 🔥 ini pesan yang kamu mau
                'error_detail' => $e->getMessage(), // opsional, untuk debugging (bisa kamu hapus kalau gak mau tampil)
            ], 500);
        }
    }

    public function getFarmasiById($id)
    {
        $data = Farmasi::with('user')->findOrFail($id);
        return response()->json(['data' => $data]);
    }

    public function updateFarmasi(Request $request, $id)
    {
        try {
            $apoteker = Farmasi::findOrFail($id);
            $user = $apoteker->user;

            $request->validate([
                'edit_username_apoteker'    => 'required|string|max:255',
                'edit_nama_apoteker'        => 'required|string|max:255',
                'edit_email_apoteker'       => 'required|email',
                'edit_foto_apoteker'        => 'nullable|file|mimes:jpeg,jpg,png,gif,webp,svg,jfif|max:5120',
                'edit_no_hp_apoteker'       => 'nullable|string|max:20',
                'edit_password_apoteker'    => 'nullable|string|min:8|confirmed',
            ]);

            // Update user
            $user->username = $request->input('edit_username_apoteker');
            $user->email    = $request->input('edit_email_apoteker');

            if ($request->filled('edit_password_apoteker')) {
                $user->password = Hash::make($request->input('edit_password_apoteker'));
            }

            // Handle foto upload
            $fotoPath = null;
            if ($request->hasFile('edit_foto_apoteker')) {
                $file = $request->file('edit_foto_apoteker');

                $extension = strtolower($file->getClientOriginalExtension());
                if ($extension === 'jfif') {
                    $extension = 'jpg';
                }

                $fileName = 'apoteker_' . time() . '.' . $extension;
                $path = 'apoteker/' . $fileName;

                if ($extension === 'svg') {
                    Storage::disk('public')->put($path, file_get_contents($file));
                } else {
                    $image = Image::read($file);
                    $image->scale(width: 800);
                    Storage::disk('public')->put($path, (string) $image->encodeByExtension($extension, quality: 80));
                }

                $fotoPath = $path;

                if ($apoteker->foto_apoteker && Storage::disk('public')->exists($apoteker->foto_apoteker)) {
                    Storage::disk('public')->delete($apoteker->foto_apoteker);
                }
            }

            // Update apoteker
            $updateData = [
                'nama_farmasi'  => $request->edit_nama_apoteker,
                'no_hp_farmasi' => $request->edit_no_hp_apoteker,
            ];

            $updateDataUser = ([
                'username' => $request->edit_username_apoteker,
            ]);

            if ($fotoPath) {
                $updateData['foto_farmasi'] = $fotoPath;
            }

            $apoteker->update($updateData);
            $user->update($updateDataUser);

            return response()->json(['message' => 'Data farmasi berhasil diperbarui.']);
        } catch (\Illuminate\Http\Exceptions\PostTooLargeException $e) {
            // 📛 Jika file melebihi batas upload
            return response()->json([
                'message' => 'Ukuran file terlalu besar! Maksimal 5 MB.'
            ], 413);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // 📛 Jika validasi gagal
            return response()->json([
                'message' => 'Validasi gagal.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            // 💥 Error umum
            return response()->json([
                'message' => 'Tidak ada respon dari server.', // 🔥 ini pesan yang kamu mau
                'error_detail' => $e->getMessage(), // opsional, untuk debugging (bisa kamu hapus kalau gak mau tampil)
            ], 500);
        }
    }

    public function deleteFarmasi($id)
    {
        $farmasi = Farmasi::findOrFail($id);

        $farmasi->user->delete();
        $farmasi->delete();
        // Hapus foto jika ada
        if ($farmasi->foto_farmasi && Storage::disk('public')->exists($farmasi->foto_farmasi)) {
            Storage::disk('public')->delete($farmasi->foto_farmasi);
        }

        return response()->json(['success' => 'Data farmasi berhasil dihapus.']);
    }
}

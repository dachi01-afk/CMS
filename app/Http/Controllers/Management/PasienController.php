<?php

namespace App\Http\Controllers\Management;

use App\Models\User;
use App\Models\Pasien;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Drivers\Gd\Driver;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Intervention\Image\Laravel\Facades\Image;

class PasienController extends Controller
{

    public function createPasien(Request $request)
    {
        $request->validate([
            // Foto
            'foto_pasien'               => 'nullable|file|mimes:jpeg,jpg,png,gif,webp,svg,jfif|max:5120',

            // Data akun
            'username_pasien'           => 'required|string|max:255',
            'email_pasien'              => 'required|email',
            'password_pasien'           => 'required|string|min:8|confirmed',

            // Identitas dasar
            'nama_pasien'               => 'required|string|max:255',
            'alamat_pasien'             => 'nullable|string|max:255',
            'no_hp_pasien'              => 'nullable|string|max:20',
            'tanggal_lahir_pasien'      => 'nullable|date',
            'jenis_kelamin_pasien'      => 'nullable|in:Laki-laki,Perempuan',

            // Identitas tambahan
            'nik'                       => 'nullable|string|max:20',
            'no_bpjs'                   => 'nullable|string|max:50',
            'golongan_darah'            => 'nullable|string|max:3',
            'status_perkawinan'         => 'nullable|string|max:50',
            'pekerjaan'                 => 'nullable|string|max:100',

            // Penanggung jawab
            'nama_penanggung_jawab'     => 'nullable|string|max:255',
            'no_hp_penanggung_jawab'    => 'nullable|string|max:20',

            // Medis
            'alergi'                    => 'nullable|string',
        ]);

        // ==== Generate Nomor EMR (RM-00000001) ====
        $lastPasien = Pasien::orderBy('id', 'desc')->first();
        $lastNumber = 0;

        if ($lastPasien && preg_match('/RM-(\d+)/', $lastPasien->no_emr, $matches)) {
            $lastNumber = (int) $matches[1];
        }

        $nextNumber = $lastNumber + 1;
        $no_emr = 'RM-' . str_pad($nextNumber, 8, '0', STR_PAD_LEFT);

        // ==== Buat User ====
        $user = User::create([
            'username' => $request->username_pasien,
            'email'    => $request->email_pasien,
            'password' => Hash::make($request->password_pasien),
            'role'     => 'Pasien',
        ]);

        // ==== Upload Foto ====
        $fotoPath = null;
        if ($request->hasFile('foto_pasien')) {
            $file = $request->file('foto_pasien');
            $extension = strtolower($file->getClientOriginalExtension());
            if ($extension === 'jfif') {
                $extension = 'jpg';
            }

            $fileName = 'pasien_' . time() . '.' . $extension;
            $path = 'pasien/' . $fileName;

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

        // ==== Simpan Pasien (no_emr == barcode_pasien) ====
        Pasien::create([
            'user_id'                => $user->id,
            'no_emr'                 => $no_emr,
            'barcode_pasien'         => $no_emr,                   // ⬅️ SAMA dengan no_emr
            'foto_pasien'            => $fotoPath,

            'nama_pasien'            => $request->nama_pasien,
            'alamat'                 => $request->alamat_pasien,
            'tanggal_lahir'          => $request->tanggal_lahir_pasien,
            'jenis_kelamin'          => $request->jenis_kelamin_pasien,
            'no_hp_pasien'           => $request->no_hp_pasien,

            'nik'                    => $request->nik,
            'no_bpjs'                => $request->no_bpjs,
            'golongan_darah'         => $request->golongan_darah,
            'status_perkawinan'      => $request->status_perkawinan,
            'pekerjaan'              => $request->pekerjaan,

            'nama_penanggung_jawab'  => $request->nama_penanggung_jawab,
            'no_hp_penanggung_jawab' => $request->no_hp_penanggung_jawab,

            'alergi'                 => $request->alergi,
        ]);

        return response()->json([
            'message' => 'Data pasien berhasil ditambahkan.',
            'no_emr'  => $no_emr,
        ]);
    }


    public function getPasienById($id)
    {
        // dd($id);
        $data = Pasien::with('user')->findOrFail($id);
        return response()->json(['data' => $data]);
    }

    public function updatePasien(Request $request, $id)
    {
        $pasien = Pasien::with('user')->findOrFail($id);
        $user   = $pasien->user;

        $request->validate([
            'foto_pasien'               => 'nullable|file|mimes:jpeg,jpg,png,gif,webp,svg,jfif|max:5120',

            'username_pasien'           => 'required|string|max:255',
            'email_pasien'              => 'required|email',
            'password_pasien'           => 'nullable|string|min:8|confirmed',

            'nama_pasien'               => 'required|string|max:255',
            'alamat_pasien'             => 'nullable|string|max:255',
            'no_hp_pasien'              => 'nullable|string|max:20',
            'tanggal_lahir_pasien'      => 'nullable|date',
            'jenis_kelamin_pasien'      => 'nullable|in:Laki-laki,Perempuan',

            'nik'                       => 'nullable|string|max:20',
            'no_bpjs'                   => 'nullable|string|max:50',
            'golongan_darah'            => 'nullable|string|max:3',
            'status_perkawinan'         => 'nullable|string|max:50',
            'pekerjaan'                 => 'nullable|string|max:100',

            'nama_penanggung_jawab'     => 'nullable|string|max:255',
            'no_hp_penanggung_jawab'    => 'nullable|string|max:20',

            'alergi'                    => 'nullable|string',
            // kalau suatu saat no_emr boleh diedit:
            'no_emr'                    => 'nullable|string|max:50',
        ]);

        // ==== Update User ====
        $user->username = $request->username_pasien;
        $user->email    = $request->email_pasien;

        if ($request->filled('password_pasien')) {
            $user->password = Hash::make($request->password_pasien);
        }
        $user->save();

        // ==== Upload Foto (jika diganti) ====
        $fotoPath = $pasien->foto_pasien;

        if ($request->hasFile('foto_pasien')) {
            // hapus foto lama
            if ($fotoPath && Storage::disk('public')->exists($fotoPath)) {
                Storage::disk('public')->delete($fotoPath);
            }

            $file = $request->file('foto_pasien');
            $extension = strtolower($file->getClientOriginalExtension());
            if ($extension === 'jfif') {
                $extension = 'jpg';
            }

            $fileName = 'pasien_' . time() . '.' . $extension;
            $path = 'pasien/' . $fileName;

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

        // ==== Pastikan no_emr dan barcode_pasien selalu sama ====
        $no_emr = $request->input('no_emr', $pasien->no_emr); // readonly di form, tapi siap kalau nanti boleh diedit

        $pasien->update([
            'no_emr'                 => $no_emr,
            'barcode_pasien'         => $no_emr,                 // ⬅️ sync dengan no_emr
            'foto_pasien'            => $fotoPath,

            'nama_pasien'            => $request->nama_pasien,
            'alamat'                 => $request->alamat_pasien,
            'tanggal_lahir'          => $request->tanggal_lahir_pasien,
            'jenis_kelamin'          => $request->jenis_kelamin_pasien,
            'no_hp_pasien'           => $request->no_hp_pasien,

            'nik'                    => $request->nik,
            'no_bpjs'                => $request->no_bpjs,
            'golongan_darah'         => $request->golongan_darah,
            'status_perkawinan'      => $request->status_perkawinan,
            'pekerjaan'              => $request->pekerjaan,

            'nama_penanggung_jawab'  => $request->nama_penanggung_jawab,
            'no_hp_penanggung_jawab' => $request->no_hp_penanggung_jawab,

            'alergi'                 => $request->alergi,
        ]);

        return response()->json([
            'message' => 'Data pasien berhasil diperbarui.',
        ]);
    }


    public function deletePasien($id)
    {
        $pasien = Pasien::findOrFail($id);
        // Hapus foto jika ada
        if ($pasien->foto_pasien && Storage::disk('public')->exists($pasien->foto_pasien)) {
            Storage::disk('public')->delete($pasien->foto_pasien);
        }

        $pasien->user->delete();
        $pasien->delete();

        return response()->json(['success' => 'Data pasien berhasil dihapus.']);
    }

    public function generateNoEmrPasien()
    {
        try {
            DB::beginTransaction();

            // 🔹 Cari pasien terakhir yang sudah punya no_emr
            $lastPasien = \App\Models\Pasien::whereNotNull('no_emr')
                ->orderByDesc('id')
                ->first();

            $lastNumber = 0;

            // 🔹 Ambil angka terakhir dari format RM-00000001
            if ($lastPasien && preg_match('/RM-(\d+)/', $lastPasien->no_emr, $matches)) {
                $lastNumber = (int)$matches[1];
            }

            // 🔹 Ambil semua pasien yang belum punya no_emr
            $pasienList = \App\Models\Pasien::whereNull('no_emr')
                ->orderBy('id')
                ->get();

            $counter = $lastNumber;

            foreach ($pasienList as $pasien) {
                $counter++;

                // 🔹 Format sama dengan createPasien(): RM-00000001
                $newNoEmr = 'RM-' . str_pad($counter, 8, '0', STR_PAD_LEFT);

                // 🔹 Pastikan unik (antisipasi edge case)
                $exists = \App\Models\Pasien::where('no_emr', $newNoEmr)->exists();
                if ($exists) {
                    $newNoEmr .= '-' . substr(uniqid(), -3);
                }

                // 🔹 Update ke pasien
                $pasien->update(['no_emr' => $newNoEmr]);
            }

            DB::commit();

            // 🔹 Ambil ulang data untuk ditampilkan
            $pasienList = \App\Models\Pasien::orderBy('id')->get();

            return view('testing-emr', compact('pasienList'))
                ->with('success', 'Generate no_emr selesai dan sinkron dengan format RM-00000001.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal generate no_emr: ' . $e->getMessage());
        }
    }

    public function showPasien($noEMR)
    {
        $pasien = Pasien::with('user')->where('no_emr', $noEMR)->firstOrFail();

        $umur = null;
        if ($pasien->tanggal_lahir) {
            $tgl = Carbon::parse($pasien->tanggal_lahir);
            $diff = $tgl->diff(Carbon::now());
            $umur = sprintf('%d Tahun %d Bulan %d Hari', $diff->y, $diff->m, $diff->d);
        }

        return view('admin.manajemenPengguna.detail-data-pasien', [
            'pasien' => $pasien,
            'umur'   => $umur,
        ]);
    }

    public function cetakStiker($noEMR)
    {
        $pasien = Pasien::where('no_emr', $noEMR)->firstOrFail();

        // umur lengkap: tahun – bulan – hari
        $umur = null;
        if ($pasien->tanggal_lahir) {
            $tglLahir = Carbon::parse($pasien->tanggal_lahir);
            $diff     = $tglLahir->diff(Carbon::now());

            $umur = sprintf(
                '%d Tahun %d Bulan %d Hari',
                $diff->y,
                $diff->m,
                $diff->d
            );
        }

        return view('admin.manajemenPengguna.cetak-stiker-pasien', [
            'pasien' => $pasien,
            'umur'   => $umur,
        ]);
    }
}

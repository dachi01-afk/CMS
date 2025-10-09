

public function getDataDokterSpesialisasi()
    {
        $dataDokterSpesialisasi = Dokter::where('spesialisasi', 'Psikiatri')->get();
        return response()->json([
            'Data Dokter Spesialisasi' => $dataDokterSpesialisasi,
        ]);
    }

   public function getDataJadwalDokter()
{
    $dataJadwalDokter = JadwalDokter::with('dokter')->get();

    $dataDokter = [];

    foreach ($dataJadwalDokter as $jadwal) {
        // Pastikan relasi dokter tidak null
        if ($jadwal->dokter) {
            // FIXED: Clean the day name by removing quotes
            $cleanHari = trim($jadwal->hari, '"\'');
            
            $dataDokter[] = [
                'dokter_id' => $jadwal->dokter->id,
                'nama_dokter' => $jadwal->dokter->nama_dokter,
                'hari' => $cleanHari, 
                'spesialisasi' => $jadwal->dokter->spesialisasi,
                'jam_awal' => $jadwal->jam_awal,
                'jam_selesai' => $jadwal->jam_selesai,
                'foto' => $jadwal->dokter->foto,
                'pengalaman' => $jadwal->dokter->pengalaman,
                'deskripsi_dokter' => $jadwal->dokter->deskripsi_dokter,
            ];
        }
    }

    return response()->json([
        'Data Jadwal Dokter' => $dataDokter,
    ]);
}

    public function getDataKunjungan() {}



    public function getDataTestimoni()
    {
        $dataTestimoni = Testimoni::get();

        return response()->json([
            'Data Testimoni' => $dataTestimoni,
        ]);
    }
    public function getDataPasien()
    {
        $dataPasien = Pasien::get();

        return response()->json([
            'Data Pasien' => $dataPasien,
        ]);
    }

    public function getDataDokter()
    {
        $dataDokter = Dokter::all();

        return response()->json([
            'Data Dokter' => $dataDokter,
        ]);
    }

    public function storeDataTestimoni(Request $request)
    {
        $request->validate([
            'pasien_id' => ['required', 'exists:pasien,id'],
            'nama_testimoni' => ['required', 'exists:pasien,nama_pasien'],
            'umur' => ['required', 'integer'],
            'pekerjaan' => ['required', 'string'],
            'isi_testimoni' => ['required', 'string'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'], // 2MB
            'video' => ['nullable', 'mimetypes:video/mp4,video/avi,video/mpeg,video/quicktime', 'max:20480'], // 20MB
        ]);

        // Inisialisasi path null
        $pathFoto = null;
        $pathVideo = null;

        // Upload foto jika ada
        if ($request->hasFile('foto')) {
            $pathFoto = $request->file('foto')->store('testimoni/foto', 'public');
        }

        // Upload video jika ada
        if ($request->hasFile('video')) {
            $pathVideo = $request->file('video')->store('testimoni/video', 'public');
        }

        // Simpan ke database
        $dataTestimoni = Testimoni::create([
            'pasien_id' => $request->pasien_id,
            'nama_testimoni' => $request->nama_testimoni,
            'umur' => $request->umur,
            'pekerjaan' => $request->pekerjaan,
            'isi_testimoni' => $request->isi_testimoni,
            'foto' => $pathFoto,
            'video' => $pathVideo,
        ]);

        return response()->json(['Data Testimoni' => $dataTestimoni]);
    }

    public function getDataKunjunganDokter()
    {
        $idUser = Auth::user()->id;
        $dataDokter = Dokter::where('user_id', $idUser)->get();

        $dataKunjungan = Kunjungan::with('dokter', 'pasien')->where('dokter_id', $dataDokter)->get();

        return response()->json([
            'Data Orderan Dokter' => $dataKunjungan,
        ]);
    }

    public function createKunjungan(Request $request)
{
    $request->validate([
        'pasien_id' => ['required', 'exists:pasien,id'],
        'dokter_id' => ['required', 'exists:dokter,id'],
        'tanggal_kunjungan' => ['required', 'date'],
        'keluhan_awal' => ['required'],
    ]);

    // Check authentication
    $user = $request->user();
    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'User tidak terautentikasi'
        ], 401);
    }

    $tanggalKunjungan = $request->tanggal_kunjungan;

    try {
        // Use transaction for safe queue number handling
        $result = DB::transaction(function () use ($tanggalKunjungan, $request) {

            // Find last visit on the same date
            $lastKunjungan = Kunjungan::where('tanggal_kunjungan', 'LIKE', date('Y-m-d', strtotime($tanggalKunjungan)) . '%')
                ->orderByDesc('id')
                ->lockForUpdate() // Lock to prevent duplicates
                ->first();

            // Determine next queue number
            if ($lastKunjungan && $lastKunjungan->no_antrian) {
                $nextNumber = (int)$lastKunjungan->no_antrian + 1;
            } else {
                $nextNumber = 1;
            }

            // Format as 3 digits: 001, 002, 010, 123
            $formattedNumber = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

            // Create new visit record
            $kunjungan = Kunjungan::create([
                'pasien_id' => $request->pasien_id,
                'dokter_id' => $request->dokter_id,
                'tanggal_kunjungan' => $tanggalKunjungan,
                'no_antrian' => $formattedNumber,
                'keluhan_awal' => $request->keluhan_awal,
                'status' => 'menunggu', // Changed from 'Pending'
            ]);

            // Return data, NOT response
            return [
                'kunjungan' => $kunjungan,
                'no_antrian' => $formattedNumber
            ];
        });

        // Get doctor info
        $dokter = Dokter::find($request->dokter_id);

        // Return the final response
        return response()->json([
            'success' => true,
            'status' => 'success',
            'message' => 'Kunjungan berhasil dibuat',
            'data' => [
                'kunjungan' => [
                    'id' => $result['kunjungan']->id,
                    'pasien_id' => $result['kunjungan']->pasien_id,
                    'dokter_id' => $result['kunjungan']->dokter_id,
                    'tanggal_kunjungan' => $result['kunjungan']->tanggal_kunjungan,
                    'keluhan_awal' => $result['kunjungan']->keluhan_awal,
                    'status' => $result['kunjungan']->status,
                    'no_antrian' => $result['no_antrian'],
                    'dokter_nama' => $dokter->nama_dokter ?? 'Unknown',
                ],
                'emr' => [
                    'pasien_id' => $result['kunjungan']->pasien_id,
                    'kunjungan_id' => $result['kunjungan']->id,
                ]
            ],
            'no_antrian' => $result['no_antrian']
        ], 201);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'status' => 'error',
            'message' => 'Gagal membuat kunjungan: ' . $e->getMessage()
        ], 500);
    }
}
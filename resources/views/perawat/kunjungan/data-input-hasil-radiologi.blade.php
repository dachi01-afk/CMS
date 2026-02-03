<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <title>Input Hasil Radiologi</title>
</head>

<body class="bg-slate-50">
    <div class="max-w-full mx-auto px-12 py-8">

        {{-- Top Bar --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <div class="text-sm text-slate-500">
                    Perawat / Order Radiologi / <span class="text-slate-700 font-medium">Input Hasil</span>
                </div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 mt-1">
                    Input Hasil Radiologi
                </h1>
            </div>

            <div class="flex items-center gap-2">
                <span
                    class="inline-flex items-center gap-2 rounded-full bg-indigo-50 text-indigo-700 px-3 py-1 text-xs font-semibold border border-indigo-100">
                    <span class="h-2 w-2 rounded-full bg-indigo-600"></span>
                    No. Order: {{ $order->no_order_radiologi }}
                </span>
            </div>
        </div>

        {{-- Main Card --}}
        <div class="bg-white/80 backdrop-blur rounded-2xl shadow-xl border border-slate-200 overflow-hidden">

            {{-- Header Card --}}
            <div class="px-6 py-5 bg-gradient-to-r from-indigo-600 to-violet-600">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                    <div>
                        <h2 class="text-white text-lg font-bold">Form Upload Hasil Radiologi</h2>
                        <p class="text-indigo-100 text-sm">
                            Pastikan foto & keterangan diisi untuk setiap pemeriksaan.
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <span
                            class="inline-flex items-center gap-2 rounded-lg bg-white/10 text-white px-3 py-2 text-xs border border-white/20">
                            <span class="opacity-80">Tanggal</span>
                            <span class="font-semibold">{{ now()->format('d M Y') }}</span>
                        </span>
                        <span
                            class="inline-flex items-center gap-2 rounded-lg bg-white/10 text-white px-3 py-2 text-xs border border-white/20">
                            <span class="opacity-80">Jam</span>
                            <span class="font-semibold">{{ now()->format('H:i') }}</span>
                        </span>
                    </div>
                </div>
            </div>

            {{-- Patient/Dokter Summary --}}
            <div class="px-6 py-5 bg-white border-b border-slate-100">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <div class="text-[11px] uppercase tracking-wide text-slate-500 font-semibold">Pasien</div>
                        <div class="text-slate-900 font-bold mt-1">{{ $order->pasien->nama_pasien }}</div>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <div class="text-[11px] uppercase tracking-wide text-slate-500 font-semibold">Dokter Pengirim
                        </div>
                        <div class="text-slate-900 font-bold mt-1">{{ $order->dokter->nama_dokter }}</div>
                    </div>

                    <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3">
                        <div class="text-[11px] uppercase tracking-wide text-amber-700 font-semibold">Aturan Input</div>
                        <div class="text-amber-900 mt-1 text-sm">
                            Setiap pemeriksaan <b>wajib</b> upload foto + isi keterangan.
                        </div>
                    </div>
                </div>
            </div>

            {{-- Form --}}
            <form id="formInputHasil" enctype="multipart/form-data" class="px-6 py-6">
                @csrf
                <input type="hidden" name="order_radiologi_id" value="{{ $order->id }}">

                {{-- List Pemeriksaan (Cards) --}}
                {{-- NOTE: mb-10 kamu bikin keliatan “gempet” sama action. Kita rapihin jadi mb-6 --}}
                <div class="space-y-5 mb-6">
                    @foreach ($order->orderRadiologiDetail as $detail)
                        <div class="group rounded-2xl border border-slate-200 bg-white shadow-sm hover:shadow-md transition overflow-hidden"
                            data-card="radiologi" data-detail-id="{{ $detail->id }}">

                            {{-- Header per card --}}
                            <div class="px-5 py-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                                <div class="flex items-start gap-3">
                                    <div
                                        class="h-10 w-10 rounded-xl bg-indigo-50 text-indigo-700 flex items-center justify-center font-bold border border-indigo-100">
                                        {{ $loop->iteration }}
                                    </div>

                                    <div>
                                        <div class="text-slate-900 font-bold">
                                            {{ $detail->jenisPemeriksaanRadiologi->nama_pemeriksaan }}
                                        </div>
                                        <div class="text-slate-500 text-sm">
                                            Upload foto hasil + isi keterangan untuk pemeriksaan ini.
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    {{-- Badge status --}}
                                    <span id="badge-{{ $detail->id }}"
                                        class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold border
                                               bg-amber-50 text-amber-700 border-amber-200">
                                        <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                                        ⚠️ Belum lengkap
                                    </span>

                                    <div class="text-xs text-slate-500">
                                        ID Detail: <span
                                            class="font-semibold text-slate-700">{{ $detail->id }}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Body per card --}}
                            {{-- Kunci tinggi sama: row stretch + kolom h-full + wrapper flex --}}
                            <div class="px-5 pb-5 pt-1 grid grid-cols-1 lg:grid-cols-3 gap-4 items-stretch">

                                {{-- Upload + Preview --}}
                                <div class="lg:col-span-1 flex flex-col">
                                    <label class="block text-xs font-semibold text-slate-700 mb-2">
                                        Foto Hasil (wajib)
                                    </label>

                                    <div
                                        class="rounded-xl border border-slate-200 bg-slate-50 p-3 h-full flex flex-col">
                                        <input type="file" name="foto_hasil_radiologi[{{ $detail->id }}]"
                                            accept="image/*" required
                                            class="w-full text-sm file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0
                                                   file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 cursor-pointer
                                                   radiologi-file"
                                            data-detail="{{ $detail->id }}"
                                            data-preview="#preview-{{ $detail->id }}">

                                        {{-- Preview box: flex-1 biar tinggi ikut pasangan textarea --}}
                                        <div
                                            class="mt-3 rounded-lg border border-slate-200 bg-white p-2 flex-1 flex flex-col">
                                            <img id="preview-{{ $detail->id }}" src="" alt="Preview"
                                                class="hidden w-full flex-1 object-contain rounded-md">

                                            <div id="placeholder-{{ $detail->id }}"
                                                class="text-xs text-slate-500 text-center flex-1 flex items-center justify-center">
                                                Preview akan muncul di sini
                                            </div>
                                        </div>

                                        <p class="text-[11px] text-slate-500 mt-2">
                                            Format: JPG/PNG/WebP • Max 4MB
                                        </p>
                                    </div>
                                </div>

                                {{-- Keterangan --}}
                                <div class="lg:col-span-2 flex flex-col">
                                    <label class="block text-xs font-semibold text-slate-700 mb-2">
                                        Keterangan (wajib)
                                    </label>

                                    <div
                                        class="rounded-xl border border-slate-200 bg-slate-50 p-3 h-full flex flex-col">
                                        {{-- Textarea: flex-1 supaya ngisi sisa ruang, min height supaya enak --}}
                                        <textarea name="keterangan[{{ $detail->id }}]" required
                                            placeholder="Contoh: temuan, sisi kiri/kanan, kualitas gambar, catatan tambahan..."
                                            class="w-full flex-1 min-h-[220px] resize-none px-3 py-2 border border-slate-200 rounded-lg bg-white
                                                   focus:ring-2 focus:ring-indigo-500 focus:outline-none text-slate-800 radiologi-note"
                                            data-detail="{{ $detail->id }}"></textarea>

                                        <div class="flex items-center justify-between mt-2 text-[11px] text-slate-500">
                                            <span>Jelaskan hasil singkat & jelas.</span>
                                            <span class="font-medium">Wajib diisi</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Divider bawah biar ga “nempel” --}}
                            <div class="h-1 bg-gradient-to-r from-transparent via-slate-100 to-transparent"></div>
                        </div>
                    @endforeach
                </div>

                {{-- Actions --}}
                <div
                    class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 pt-4 border-t border-slate-100">
                    <div class="text-sm text-slate-600">
                        <span class="font-semibold text-slate-900">{{ $order->orderRadiologiDetail->count() }}</span>
                        pemeriksaan harus lengkap.
                        <span class="ml-2 text-xs text-slate-400">(cek badge di tiap card ya)</span>
                    </div>

                    <div class="flex justify-end gap-3">
                        <a href="{{ url()->previous() }}"
                            class="px-5 py-2.5 rounded-xl bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 transition">
                            Batal
                        </a>

                        <button type="submit" id="btnSimpan"
                            class="px-5 py-2.5 rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 shadow-md hover:shadow-lg transition">
                            Simpan Hasil
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Footer hint --}}
        <div class="text-center text-xs text-slate-400 mt-6">
            Sistem Radiologi • Input hasil oleh Perawat • Simpan dengan benar biar hidup tenang 🙃
        </div>
    </div>

    {{-- CDN --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(function() {
            const $form = $('#formInputHasil');
            const $btn = $('#btnSimpan');

            // CSRF setup
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            function setLoading(state) {
                $btn.prop('disabled', state);
                $btn.text(state ? 'Menyimpan...' : 'Simpan Hasil');
            }

            // ============================
            // 1) Preview Image
            // ============================
            $(document).on('change', '.radiologi-file', function() {
                const detailId = $(this).data('detail');
                const previewSel = $(this).data('preview');
                const $img = $(previewSel);
                const $placeholder = $('#placeholder-' + detailId);

                const file = this.files && this.files[0] ? this.files[0] : null;

                if (!file) {
                    $img.addClass('hidden').attr('src', '');
                    $placeholder.removeClass('hidden');
                    updateBadge(detailId);
                    return;
                }

                // optional: validasi size max 4MB
                const max = 4 * 1024 * 1024;
                if (file.size > max) {
                    this.value = '';
                    $img.addClass('hidden').attr('src', '');
                    $placeholder.removeClass('hidden');

                    Swal.fire('Ukuran terlalu besar', 'Maksimal 4MB ya. Kompres dulu fotonya.', 'warning');
                    updateBadge(detailId);
                    return;
                }

                const reader = new FileReader();
                reader.onload = (e) => {
                    $img.attr('src', e.target.result).removeClass('hidden');
                    $placeholder.addClass('hidden');
                    updateBadge(detailId);
                };
                reader.readAsDataURL(file);
            });

            // ============================
            // 2) Badge ✅ Lengkap / ⚠️ Belum (realtime)
            // ============================
            function isFilled(detailId) {
                const fileInput = document.querySelector(`input[name="foto_hasil_radiologi[${detailId}]"]`);
                const noteInput = document.querySelector(`textarea[name="keterangan[${detailId}]"]`);

                const hasFile = fileInput && fileInput.files && fileInput.files.length > 0;
                const hasNote = noteInput && noteInput.value.trim().length > 0;

                return hasFile && hasNote;
            }

            function updateBadge(detailId) {
                const $badge = $('#badge-' + detailId);

                if (isFilled(detailId)) {
                    $badge
                        .removeClass('bg-amber-50 text-amber-700 border-amber-200')
                        .addClass('bg-emerald-50 text-emerald-700 border-emerald-200')
                        .html('<span class="h-2 w-2 rounded-full bg-emerald-500"></span> ✅ Lengkap');
                } else {
                    $badge
                        .removeClass('bg-emerald-50 text-emerald-700 border-emerald-200')
                        .addClass('bg-amber-50 text-amber-700 border-amber-200')
                        .html('<span class="h-2 w-2 rounded-full bg-amber-500"></span> ⚠️ Belum lengkap');
                }
            }

            // update badge saat keterangan diketik
            $(document).on('input', '.radiologi-note', function() {
                updateBadge($(this).data('detail'));
            });

            // init badge semua card saat load
            $('[data-card="radiologi"]').each(function() {
                const detailId = $(this).data('detail-id');
                updateBadge(detailId);
            });

            // ============================
            // 3) Submit form (validasi semua lengkap)
            // ============================
            function validateAll() {
                let total = 0;
                let complete = 0;

                $('[data-card="radiologi"]').each(function() {
                    total++;
                    const detailId = $(this).data('detail-id');
                    if (isFilled(detailId)) complete++;
                });

                if (complete !== total) {
                    Swal.fire(
                        'Belum lengkap!',
                        `Yang lengkap baru ${complete} dari ${total}. Cek badge tiap card.`,
                        'warning'
                    );
                    return false;
                }
                return true;
            }

            $form.on('submit', function(e) {
                e.preventDefault();

                if (!validateAll()) return;

                setLoading(true);

                const formData = new FormData(this);

                // GANTI route sesuai punyamu:
                // contoh: route('simpan-hasil') atau route('perawat.order-radiologi.simpan-hasil')
                $.ajax({
                    url: "{{ route('simpan-hasil') }}",
                    method: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        Swal.fire('Berhasil!', res.message ||
                                'Hasil radiologi berhasil disimpan.', 'success')
                            .then(() => {
                                window.location.href = "{{ route('perawat.kunjungan') }}";
                            });
                    },
                    error: function(xhr) {
                        setLoading(false);

                        let msg = 'Terjadi kesalahan saat menyimpan data.';
                        if (xhr.responseJSON?.message) msg = xhr.responseJSON.message;

                        const errors = xhr.responseJSON?.errors;
                        if (errors) {
                            const list = Object.values(errors).flat().map(e => `• ${e}`).join(
                                '<br>');
                            Swal.fire({
                                icon: 'error',
                                title: 'Validasi gagal',
                                html: list,
                            });
                            return;
                        }

                        Swal.fire('Gagal!', msg, 'error');
                    }
                });
            });
        });
    </script>
</body>

</html>

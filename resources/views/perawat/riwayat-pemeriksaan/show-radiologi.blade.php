<x-mycomponents.layout>
    <div class="p-4 md:p-6 space-y-6" x-data="imageViewer()" @keydown.window.escape="close()">

        {{-- ========================= HEADER ========================== --}}
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div class="flex items-start gap-3">
                <a href="{{ route('riwayat-pemeriksaan.index') }}"
                    class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-white shadow-sm hover:shadow transition text-sm font-semibold text-slate-700">
                    <i class="fa-solid fa-arrow-left"></i>
                    Kembali
                </a>

                <div class="space-y-1">
                    <h1 class="text-xl md:text-2xl font-extrabold text-slate-900 tracking-tight">
                        Detail Hasil Radiologi
                    </h1>

                    <div class="text-sm text-slate-600 flex flex-wrap items-center gap-2">
                        <span class="font-semibold">No. Order:</span>
                        <span class="font-extrabold text-slate-900">{{ $order->no_order_radiologi ?? '-' }}</span>
                        <span class="text-slate-300">•</span>

                        @php
                            $tgl = $order->tanggal_pemeriksaan ?? ($order->tanggal_order ?? null);
                        @endphp

                        <span class="inline-flex items-center gap-2">
                            <i class="fa-regular fa-calendar text-slate-400"></i>
                            <span class="font-semibold">
                                {{ $tgl ? \Carbon\Carbon::parse($tgl)->format('d M Y') : '-' }}
                            </span>
                        </span>
                    </div>
                </div>
            </div>

            @php
                $totalHasil =
                    $order->orderRadiologiDetail?->flatMap(fn($d) => $d->hasilRadiologi ?? collect())->count() ?? 0;

                $totalDetailWithHasil =
                    $order->orderRadiologiDetail?->filter(fn($d) => ($d->hasilRadiologi?->count() ?? 0) > 0)->count() ??
                    0;
            @endphp

            <div class="flex items-center gap-2">
                <span
                    class="inline-flex items-center gap-2 px-3 py-2 rounded-2xl bg-emerald-50 text-emerald-700 text-sm font-semibold">
                    <i class="fa-solid fa-circle-check"></i>
                    {{ $totalDetailWithHasil }} pemeriksaan • {{ $totalHasil }} hasil
                </span>
            </div>
        </div>

        {{-- ========================= SUMMARY ========================== --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="rounded-2xl bg-white shadow-sm p-4">
                <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">Pasien</div>
                <div class="mt-1 text-base font-extrabold text-slate-900 truncate">
                    {{ $order->pasien->nama_pasien ?? '-' }}
                </div>
            </div>

            <div class="rounded-2xl bg-white shadow-sm p-4">
                <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">Dokter</div>
                <div class="mt-1 text-base font-extrabold text-slate-900 truncate">
                    {{ $order->dokter->nama_dokter ?? '-' }}
                </div>
            </div>

            <div class="rounded-2xl bg-white shadow-sm p-4">
                <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">Catatan</div>
                <div class="mt-1 text-sm text-slate-700">
                    Yang tampil di sini <span class="font-semibold">hanya hasil yang kamu input</span>.
                    Klik gambar untuk preview (zoom & open tab).
                </div>
            </div>
        </div>

        {{-- ========================= CONTENT ========================== --}}
        <div class="rounded-2xl bg-white shadow-sm overflow-hidden">
            <div class="px-4 md:px-6 py-4 bg-slate-50/70">
                <div>
                    <div class="text-base font-extrabold text-slate-900">Daftar Hasil</div>
                    <div class="text-sm text-slate-600">
                        Klik tombol <b>Lihat</b> atau klik gambarnya untuk preview.
                    </div>
                </div>
            </div>

            <div class="p-4 md:p-6 space-y-6">
                @if ($totalHasil === 0)
                    <div class="rounded-2xl bg-slate-50 p-6 text-center">
                        <div class="text-slate-900 font-extrabold">Belum ada hasil</div>
                        <div class="text-sm text-slate-600 mt-1">
                            Untuk order ini, belum ada hasil radiologi yang kamu input.
                        </div>
                    </div>
                @endif

                {{-- GRID daftar pemeriksaan (2 kolom) --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach ($order->orderRadiologiDetail ?? collect() as $detail)
                        @php
                            $hasilList = $detail->hasilRadiologi ?? collect();
                        @endphp

                        @if ($hasilList->count() === 0)
                            @continue
                        @endif

                        <div class="rounded-2xl bg-slate-50/60 p-4 md:p-5">
                            <div class="flex flex-col gap-2">
                                <div class="text-lg font-extrabold text-slate-900">
                                    {{ $detail->jenisPemeriksaanRadiologi->nama_pemeriksaan ?? 'Pemeriksaan' }}
                                </div>

                                <div class="text-sm text-slate-600 flex items-center justify-between">
                                    <span>ID Detail: <span class="font-semibold">{{ $detail->id }}</span></span>
                                    <span
                                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-100/70 text-emerald-800 text-xs font-extrabold">
                                        <i class="fa-solid fa-check"></i> Tersimpan
                                    </span>
                                </div>
                            </div>

                            {{-- GRID hasil (2 kolom) --}}
                            <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                                @foreach ($hasilList as $hasil)
                                    @php
                                        $hasilTgl = $hasil->tanggal_pemeriksaan ?? null;
                                        $hasilJam = $hasil->jam_pemeriksaan ?? null;
                                        $img = !empty($hasil->foto_hasil_radiologi)
                                            ? asset('storage/' . $hasil->foto_hasil_radiologi)
                                            : null;
                                    @endphp

                                    <div class="rounded-2xl bg-white shadow-sm overflow-hidden">
                                        <div
                                            class="px-4 py-3 text-xs text-slate-600 flex items-center justify-between gap-3">
                                            <div class="font-semibold truncate">
                                                {{ $hasilTgl ? \Carbon\Carbon::parse($hasilTgl)->format('d M Y') : '-' }}
                                                @if ($hasilJam)
                                                    • {{ $hasilJam }}
                                                @endif
                                            </div>

                                            @if ($img)
                                                <button type="button"
                                                    class="shrink-0 inline-flex items-center gap-2 px-2.5 py-1.5 rounded-full bg-slate-100 text-slate-700 font-semibold hover:bg-slate-200 transition"
                                                    @click="open('{{ $img }}')" title="Lihat gambar">
                                                    <i class="fa-solid fa-magnifying-glass"></i> Lihat
                                                </button>
                                            @endif
                                        </div>

                                        <div class="px-4 pb-4 space-y-3">
                                            <div class="rounded-xl bg-slate-50 overflow-hidden">
                                                @if ($img)
                                                    <button type="button" class="block w-full"
                                                        @click="open('{{ $img }}')" title="Klik untuk zoom">
                                                        <img src="{{ $img }}"
                                                            class="w-full h-56 object-contain bg-white"
                                                            alt="Foto Hasil Radiologi">
                                                    </button>
                                                @else
                                                    <div
                                                        class="h-56 flex items-center justify-center text-sm text-slate-500">
                                                        Tidak ada foto
                                                    </div>
                                                @endif
                                            </div>

                                            <div>
                                                <div
                                                    class="text-xs font-extrabold text-slate-700 uppercase tracking-wider">
                                                    Keterangan
                                                </div>
                                                <div class="text-sm text-slate-700 mt-1 whitespace-pre-line">
                                                    {{ $hasil->keterangan ?? '-' }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                        </div>
                    @endforeach
                </div>

            </div>
        </div>

        {{-- ========================= IMAGE VIEWER MODAL ========================== --}}
        <div x-show="isOpen" x-transition.opacity
            class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/70" style="display:none;"
            @click.self="close()">

            <div class="w-full max-w-6xl bg-white rounded-2xl shadow-xl overflow-hidden">

                {{-- Top bar --}}
                <div class="px-4 py-3 bg-slate-50 flex items-center justify-between gap-3">
                    <div class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                        <i class="fa-regular fa-image text-slate-500"></i>
                        Preview Gambar
                    </div>

                    <div class="flex items-center gap-2">
                        <a :href="src" target="_blank"
                            class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-white shadow-sm hover:shadow transition text-sm font-semibold text-slate-700">
                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                            New Tab
                        </a>

                        <button type="button"
                            class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-white shadow-sm hover:shadow transition text-sm font-semibold text-slate-700"
                            @click="zoomOut()">
                            <i class="fa-solid fa-minus"></i>
                        </button>

                        <button type="button"
                            class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-white shadow-sm hover:shadow transition text-sm font-semibold text-slate-700"
                            @click="zoomIn()">
                            <i class="fa-solid fa-plus"></i>
                        </button>

                        <button type="button"
                            class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-white shadow-sm hover:shadow transition text-sm font-semibold text-slate-700"
                            @click="fit()">
                            <i class="fa-solid fa-down-left-and-up-right-to-center"></i>
                            Fit
                        </button>

                        <button type="button"
                            class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-slate-900 text-white hover:bg-slate-800 transition text-sm font-semibold"
                            @click="close()">
                            <i class="fa-solid fa-xmark"></i>
                            Close
                        </button>
                    </div>
                </div>

                {{-- Body --}}
                <div class="bg-black/5">
                    <div class="h-[75vh] overflow-auto p-4" x-ref="viewport" style="overscroll-behavior: contain;">
                        <div class="w-full flex justify-center">
                            <div class="relative inline-block min-w-max" :style="wrapStyle">
                                <div x-show="!isReady"
                                    class="absolute inset-0 rounded-xl bg-slate-200/60 animate-pulse"></div>

                                <img x-ref="img" :src="src" x-on:load="onImgLoad($event)"
                                    x-on:error="onImgError()"
                                    class="block bg-white rounded-lg shadow-sm select-none transition-opacity duration-200"
                                    :class="isReady ? 'opacity-100' : 'opacity-0'" :style="imgStyle"
                                    alt="Preview" draggable="false">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="px-4 py-3 bg-white flex items-center justify-between text-xs text-slate-600">
                    <div>Tips: Zoom in dulu kalau mau geser kanan/kiri/atas/bawah. (Esc untuk close)</div>
                    <div class="font-semibold">Zoom: <span x-text="Math.round(zoom * 100) + '%'"></span></div>
                </div>
            </div>
        </div>

        {{-- ========================= SCRIPT ========================== --}}
        <script>
            function imageViewer() {
                return {
                    isOpen: false,
                    isReady: false,
                    src: '',

                    zoom: 1,
                    step: 0.10,
                    minZoom: 0.10,
                    maxZoom: 6,

                    baseW: 0,
                    baseH: 0,

                    get imgStyle() {
                        if (!this.src) return '';
                        return `
                            width:${this.baseW}px;
                            height:${this.baseH}px;
                            object-fit:contain;
                            transform: scale(${this.zoom});
                            transform-origin: top left;
                        `;
                    },

                    get wrapStyle() {
                        if (!this.baseW || !this.baseH) {
                            return `width:900px; height:520px;`;
                        }
                        const w = Math.round(this.baseW * this.zoom);
                        const h = Math.round(this.baseH * this.zoom);
                        return `width:${w}px; height:${h}px;`;
                    },

                    open(url) {
                        this.isOpen = true;
                        this.isReady = false;

                        this.zoom = 1;
                        this.baseW = 0;
                        this.baseH = 0;

                        this.src = url;

                        document.documentElement.classList.add('overflow-hidden');
                        document.body.classList.add('overflow-hidden');

                        this.$nextTick(() => {
                            const vp = this.$refs.viewport;
                            if (vp) {
                                vp.scrollTop = 0;
                                vp.scrollLeft = 0;
                            }
                        });
                    },

                    close() {
                        this.isOpen = false;
                        this.isReady = false;
                        this.src = '';
                        this.zoom = 1;

                        document.documentElement.classList.remove('overflow-hidden');
                        document.body.classList.remove('overflow-hidden');
                    },

                    onImgLoad(e) {
                        const img = e.target;
                        const vp = this.$refs.viewport;
                        if (!vp) return;

                        const nw = img.naturalWidth || 1;
                        const nh = img.naturalHeight || 1;

                        const padding = 32;
                        const vw = Math.max(1, vp.clientWidth - padding);
                        const vh = Math.max(1, vp.clientHeight - padding);

                        const fitScale = Math.min(vw / nw, vh / nh, 1);

                        this.baseW = Math.round(nw * fitScale);
                        this.baseH = Math.round(nh * fitScale);

                        this.zoom = 1;
                        this.isReady = true;

                        this.$nextTick(() => {
                            vp.scrollTop = 0;
                            vp.scrollLeft = 0;
                        });
                    },

                    onImgError() {
                        this.isReady = false;
                    },

                    zoomIn() {
                        if (!this.isReady) return;
                        this.zoom = Math.min(Number((this.zoom + this.step).toFixed(2)), this.maxZoom);
                    },

                    zoomOut() {
                        if (!this.isReady) return;
                        this.zoom = Math.max(Number((this.zoom - this.step).toFixed(2)), this.minZoom);

                        if (this.zoom <= 1) {
                            this.zoom = 1;
                            this.$nextTick(() => {
                                const vp = this.$refs.viewport;
                                if (vp) {
                                    vp.scrollTop = 0;
                                    vp.scrollLeft = 0;
                                }
                            });
                        }
                    },

                    fit() {
                        if (!this.isReady) return;
                        this.zoom = 1;
                        this.$nextTick(() => {
                            const vp = this.$refs.viewport;
                            if (vp) {
                                vp.scrollTop = 0;
                                vp.scrollLeft = 0;
                            }
                        });
                    },
                }
            }
        </script>

    </div>
</x-mycomponents.layout>

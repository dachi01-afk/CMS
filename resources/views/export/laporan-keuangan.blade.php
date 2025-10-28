@php
    use Carbon\Carbon;

    $bulanInt = is_numeric($bulan) ? (int) $bulan : null;
    $namaBulan = $bulanInt ? Carbon::create()->month($bulanInt)->translatedFormat('F') : null;
@endphp

<table style="width: 100%; border-collapse: collapse; font-family: 'Segoe UI', sans-serif; font-size: 12px;">
    <thead>
        <!-- Judul -->
        <tr>
            <th colspan="14"
                style="text-align: center; font-weight: 700; font-size: 18px; padding: 10px; background-color: #f9fafb; border-bottom: 2px solid #e5e7eb;">
                LAPORAN KEUANGAN
                @if ($filter === 'bulanan' && $bulan)
                    BULAN {{ strtoupper($namaBulan) }}
                @elseif ($filter === 'tahunan')
                    TAHUN {{ $tahun }}
                @else
                    MINGGUAN ({{ $tahun }})
                @endif
            </th>
        </tr>

        <!-- Tanggal export -->
        <tr>
            <th colspan="14" style="text-align: center; font-size: 11px; padding-bottom: 12px; color: #6b7280;">
                Tanggal Export: {{ Carbon::now()->translatedFormat('d F Y H:i') }}
            </th>
        </tr>

        <!-- Header tabel -->
        <tr style="background-color: #dcfce7; color: #064e3b;">
            @php
                $headers = [
                    'No',
                    'Nama Pasien',
                    'Tanggal Kunjungan',
                    'Nomor Antrian',
                    'Nama Obat',
                    'Dosis',
                    'Jumlah Obat',
                    'Nama Layanan',
                    'Jumlah Layanan',
                    'Metode Pembayaran',
                    'Status',
                    'Bukti Pembayaran',
                    'Tanggal Pembayaran',
                    'Total Pembayaran (Rp)',
                ];
            @endphp

            @foreach ($headers as $header)
                <th
                    style="border: 1px solid #9ca3af; padding: 6px; text-align: center; font-weight: 600; background-color: #d1fae5;">
                    {{ $header }}
                </th>
            @endforeach
        </tr>
    </thead>

    <tbody>
        @php $totalKeseluruhan = 0; @endphp

        @forelse ($data as $index => $item)
            <tr style="border-bottom: 1px solid #e5e7eb;">
                <td style="border: 1px solid #d1d5db; text-align: center; padding: 6px;">{{ $index + 1 }}</td>

                <td style="border: 1px solid #d1d5db; padding: 6px;">
                    {{ $item->emr->kunjungan->pasien->nama_pasien ?? '-' }}</td>

                <td style="border: 1px solid #d1d5db; text-align: center; padding: 6px;">
                    {{ Carbon::parse($item->emr->kunjungan->tanggal_kunjungan)->timezone('Asia/Jakarta')->translatedFormat('d F Y') }}
                </td>

                <td style="border: 1px solid #d1d5db; text-align: center; padding: 6px;">
                    {{ $item->emr->kunjungan->no_antrian ?? '-' }}</td>

                <td style="border: 1px solid #d1d5db; padding: 6px;">
                    @foreach ($item->emr->resep->obat ?? [] as $obat)
                        <div>{{ $obat->nama_obat ?? '-' }}</div>
                    @endforeach
                </td>

                <td style="border: 1px solid #d1d5db; padding: 6px;">
                    @foreach ($item->emr->resep->obat ?? [] as $obat)
                        <div>{{ $obat->dosis ?? '-' }}</div>
                    @endforeach
                </td>

                <td style="border: 1px solid #d1d5db; padding: 6px;">
                    @foreach ($item->emr->resep->obat ?? [] as $obat)
                        <div>{{ $obat->pivot->jumlah ?? '-' }}</div>
                    @endforeach
                </td>

                <td style="border: 1px solid #000; text-align: left; padding-left: 5px;">
                    @foreach ($item->emr->kunjungan->layanan ?? [] as $layanan)
                        {{ $layanan->nama_layanan ?? '-' }}<br>
                    @endforeach
                </td>
                
                <td style="border: 1px solid #000; text-align: left; padding-left: 5px;">
                    @foreach ($item->emr->kunjungan->layanan ?? [] as $layanan)
                        {{ $layanan->pivot->jumlah ?? '-' }}<br>
                    @endforeach
                </td>

                <td style="border: 1px solid #d1d5db; text-align: center; padding: 6px;">
                    {{ $item->metodePembayaran->nama_metode ?? '-' }}</td>
                <td style="border: 1px solid #d1d5db; text-align: center; padding: 6px;">{{ $item->status ?? '-' }}
                </td>

                <td style="border: 1px solid #d1d5db; text-align: center; padding: 6px;">
                    @if ($item->bukti_pembayaran && file_exists(public_path('storage/' . $item->bukti_pembayaran)))
                        <img src="{{ public_path('storage/' . $item->bukti_pembayaran) }}" alt="Bukti Pembayaran"
                            width="80" height="80" style="object-fit: cover; border-radius: 6px;">
                    @else
                        <span style="color: #9ca3af;">-</span>
                    @endif
                </td>

                <td style="border: 1px solid #d1d5db; text-align: center; padding: 6px;">
                    {{ Carbon::parse($item->tanggal_pembayaran)->timezone('Asia/Jakarta')->translatedFormat('d F Y') }}
                </td>

                <td
                    style="border: 1px solid #d1d5db; text-align: right; padding: 6px; font-weight: 600; color: #065f46;">
                    {{ number_format($item->total_tagihan, 0, ',', '.') }}
                </td>
            </tr>
            @php $totalKeseluruhan += $item->total_tagihan; @endphp
        @empty
            <tr>
                <td colspan="14" style="text-align: center; padding: 12px; color: #6b7280;">
                    Tidak ada data untuk periode ini.
                </td>
            </tr>
        @endforelse

        <!-- Total keseluruhan -->
        <tr style="background-color: #f0fdf4;">
            <td colspan="13"
                style="text-align: right; font-weight: 700; border: 1px solid #86efac; padding: 8px; color: #065f46;">
                TOTAL KESELURUHAN
            </td>
            <td style="border: 1px solid #86efac; text-align: right; font-weight: 700; color: #065f46; padding: 8px;">
                {{ number_format($totalKeseluruhan, 0, ',', '.') }}
            </td>
        </tr>
    </tbody>
</table>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Transaksi</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; vertical-align: top; }
        th { background-color: #eee; }
    </style>
</head>
<body>
    <h2>Laporan Transaksi Penyewaan</h2>

    <table>
        <thead>
            <tr>
                <th>NIK</th>
                <th>ID Billing</th>
                <th>Tanggal Booking</th>
                <th>Detail Penyewaan</th>
                <th>Tarif</th>
                <th>Subtotal</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($transaksis as $transaksi)
                @php
                    $penyewaan = $transaksi->penyewaan;
                    $perHari = is_string($penyewaan->penyewaan_per_hari ?? '') 
                                ? json_decode($penyewaan->penyewaan_per_hari, true) 
                                : ($penyewaan->penyewaan_per_hari ?? []);
                    $perJam = is_string($penyewaan->penyewaan_per_jam ?? '') 
                                ? json_decode($penyewaan->penyewaan_per_jam, true) 
                                : ($penyewaan->penyewaan_per_jam ?? []);
                @endphp
                <tr>
                    <td>{{ $transaksi->nik }}</td>
                    <td>{{ $transaksi->id_billing }}</td>
                    <td>{{ \Carbon\Carbon::parse($transaksi->tgl_booking)->format('d-m-Y') }}</td>
                    <td>
                        <strong>{{ $penyewaan->kategori_sewa ?? '-' }}</strong><br>

                        @if (($penyewaan->kategori_sewa ?? '') === 'per hari' && count($perHari) > 0)
                            @foreach ($perHari as $item)
                                {{ \Carbon\Carbon::parse($item['tgl_mulai'])->format('d M Y') }} 
                                - 
                                {{ \Carbon\Carbon::parse($item['tgl_selesai'])->format('d M Y') }}<br>
                            @endforeach
                        @elseif (($penyewaan->kategori_sewa ?? '') === 'per jam' && count($perJam) > 0)
                            @foreach ($perJam as $item)
                                {{ $item['jam_mulai'] ?? '-' }} - {{ $item['jam_selesai'] ?? '-' }}<br>
                            @endforeach
                        @else
                            -
                        @endif
                    </td>
                    <td>Rp{{ number_format($transaksi->tarif, 0, ',', '.') }}</td>
                    <td>Rp{{ number_format($transaksi->sub_total, 0, ',', '.') }}</td>
                    <td>{{ $transaksi->status }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

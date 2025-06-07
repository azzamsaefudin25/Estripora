<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Transaksi</title>
    <style>
        body { 
            font-family: DejaVu Sans, sans-serif; 
            font-size: 11px; 
            margin: 20px;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        
        .header h2 {
            margin: 0;
            font-size: 18px;
            color: #2c3e50;
        }
        
        .info {
            margin-bottom: 20px;
            font-size: 10px;
            color: #666;
        }
        
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 10px;
            font-size: 10px;
        }
        
        th, td { 
            border: 1px solid #ddd; 
            padding: 6px; 
            vertical-align: top; 
            text-align: left;
        }
        
        th { 
            background-color: #f8f9fa; 
            font-weight: bold;
            color: #2c3e50;
        }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        
        .status-pending { color: #f39c12; font-weight: bold; }
        .status-confirmed { color: #27ae60; font-weight: bold; }
        .status-canceled { color: #e74c3c; font-weight: bold; }
        
        .total-row {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        
        .footer {
            margin-top: 30px;
            font-size: 9px;
            color: #666;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Laporan Transaksi Penyewaan</h2>
    </div>

    <div class="info">
        <strong>Tanggal Cetak:</strong> {{ date('d F Y, H:i') }} WIB<br>
        <strong>Total Transaksi:</strong> {{ count($transaksis) }} transaksi
    </div>

    <table>
        <thead>
            <tr>
                <th width="8%" class="text-center">No</th>
                <th width="12%">NIK</th>
                <th width="15%">ID Billing</th>
                <th width="12%">Tgl Booking</th>
                <th width="25%">Detail Penyewaan</th>
                <th width="12%" class="text-right">Tarif</th>
                <th width="12%" class="text-right">Subtotal</th>
                <th width="8%" class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @php $totalKeseluruhan = 0; @endphp
            @foreach ($transaksis as $index => $transaksi)
                @php
                    $penyewaan = $transaksi->penyewaan;
                    $perHari = is_string($penyewaan->penyewaan_per_hari ?? '') 
                                ? json_decode($penyewaan->penyewaan_per_hari, true) 
                                : ($penyewaan->penyewaan_per_hari ?? []);
                    $perJam = is_string($penyewaan->penyewaan_per_jam ?? '') 
                                ? json_decode($penyewaan->penyewaan_per_jam, true) 
                                : ($penyewaan->penyewaan_per_jam ?? []);
                    $totalKeseluruhan += $transaksi->sub_total;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $transaksi->nik }}</td>
                    <td style="font-family: monospace;">{{ $transaksi->id_billing }}</td>
                    <td>{{ \Carbon\Carbon::parse($transaksi->tgl_booking)->format('d/m/Y') }}</td>
                    <td>
                        <strong>{{ $penyewaan->kategori_sewa ?? '-' }}</strong><br>

                        @if (($penyewaan->kategori_sewa ?? '') === 'per hari' && !empty($perHari) && is_array($perHari))
                            @if (count($perHari) > 0)
                                {{ \Carbon\Carbon::parse($perHari[0]['tgl_mulai'])->format('d M Y') }} 
                                - 
                                {{ \Carbon\Carbon::parse($perHari[0]['tgl_selesai'])->format('d M Y') }}
                                    <br><small>({{ count($perHari) }} hari)</small>
                             @endif

                        @elseif (($penyewaan->kategori_sewa ?? '') === 'per jam' && !empty($perJam) && is_array($perJam))
                            @foreach ($perJam as $item)
                                {{ \Carbon\Carbon::parse($item['tgl_mulai'] ?? '')->format('d M Y') }}<br>
                                <small>{{ $item['jam_mulai'] ?? '-' }} - {{ $item['jam_selesai'] ?? '-' }}</small><br>
                            @endforeach
                        @else
                            <small>Data tidak tersedia</small>
                        @endif
                    </td>
                    <td class="text-right">Rp {{ number_format($transaksi->tarif, 0, ',', '.') }}</td>
                    <td class="text-right"><strong>Rp {{ number_format($transaksi->sub_total, 0, ',', '.') }}</strong></td>
                    <td class="text-center">
                        @if($transaksi->status === 'Pending')
                            <span class="status-pending">{{ $transaksi->status }}</span>
                        @elseif($transaksi->status === 'Confirmed')
                            <span class="status-confirmed">{{ $transaksi->status }}</span>
                        @elseif($transaksi->status === 'Canceled')
                            <span class="status-canceled">{{ $transaksi->status }}</span>
                        @else
                            {{ $transaksi->status }}
                        @endif
                    </td>
                </tr>
            @endforeach
            
            {{-- Baris Total --}}
            <tr class="total-row">
                <td colspan="6" class="text-right"><strong>TOTAL KESELURUHAN:</strong></td>
                <td class="text-right"><strong>Rp {{ number_format($totalKeseluruhan, 0, ',', '.') }}</strong></td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>Dokumen ini digenerate secara otomatis pada {{ date('d F Y, H:i:s') }} WIB</p>
    </div>
</body>
</html>
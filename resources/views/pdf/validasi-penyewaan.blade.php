<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tiket Validasi Penyewaan</title>
    <style>
        body { 
            font-family: DejaVu Sans, sans-serif; 
            font-size: 12px; 
            margin: 15px;
            color: #2c3e50;
            background: #f8f9fa;
        }
        
        .container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background: #667eea;
            color: white;
            text-align: center;
            padding: 25px 20px;
            position: relative;
        }
        
        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHZpZXdCb3g9IjAgMCAyMCAyMCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMTAiIGN5PSIxMCIgcj0iMSIgZmlsbD0iI2ZmZmZmZiIgZmlsbC1vcGFjaXR5PSIwLjEiLz4KPHN2Zz4=') repeat;
            opacity: 0.1;
        }
        
        @keyframes float {
            0% { transform: translate(0, 0); }
            100% { transform: translate(-50px, -50px); }
        }
        
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            position: relative;
            z-index: 2;
        }
        
        .header .subtitle {
            font-size: 14px;
            opacity: 0.9;
            margin-top: 5px;
            position: relative;
            z-index: 2;
        }
        
        .validation-badge {
            background: #27ae60;
            color: white;
            padding: 8px 20px;
            border-radius: 25px;
            font-weight: bold;
            font-size: 14px;
            margin: 20px auto;
            text-align: center;
            width: fit-content;
            box-shadow: 0 4px 15px rgba(39, 174, 96, 0.3);
        }
        
        .main-content {
            padding: 30px;
        }
        
        .booking-card {
            background: #f8f9ff;
            border-left: 5px solid #667eea;
            padding: 20px;
            margin-bottom: 25px;
            border-radius: 0 10px 10px 0;
        }
        
        .booking-id {
            font-family: 'Courier New', monospace;
            font-size: 20px;
            font-weight: bold;
            color: #667eea;
            text-align: center;
            background: white;
            padding: 12px;
            border-radius: 8px;
            border: 2px dashed #667eea;
            margin-bottom: 20px;
        }

        /* Section untuk informasi tempat yang menonjol */
        .venue-highlight {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            text-align: center;
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.3);
        }

        .venue-name {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 8px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }

        .venue-category {
            font-size: 14px;
            opacity: 0.9;
            background: rgba(255,255,255,0.2);
            padding: 6px 12px;
            border-radius: 20px;
            display: inline-block;
        }
        
        .info-grid {
            width: 100%;
            margin-bottom: 25px;
        }
        
        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }
        
        .info-item {
            display: table-cell;
            width: 48%;
            background: white;
            padding: 15px;
            border-radius: 10px;
            border: 1px solid #e1e8ed;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            margin-right: 4%;
        }
        
        .info-item:last-child {
            margin-right: 0;
        }
        
        .info-label {
            font-size: 11px;
            color: #7f8c8d;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 5px;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
        }
        
        .info-value {
            font-size: 14px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .schedule-section {
            background: #ff9a9e;
            color: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 25px;
        }
        
        .schedule-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .schedule-details {
            background: rgba(255,255,255,0.2);
            padding: 15px;
            border-radius: 8px;
        }
        
        .payment-section {
            background: #a8edea;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 25px;
        }
        
        .payment-amount {
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 12px;
            text-transform: uppercase;
        }
        
        .status-paid { 
            background: #d4edda; 
            color: #155724; 
            border: 1px solid #c3e6cb;
        }
        
        .status-pending { 
            background: #fff3cd; 
            color: #856404; 
            border: 1px solid #ffeaa7;
        }
        
        .status-failed { 
            background: #f8d7da; 
            color: #721c24; 
            border: 1px solid #f5c6cb;
        }
        
        .qr-section {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            margin-bottom: 25px;
        }
        
        .qr-placeholder {
            width: 120px;
            height: 120px;
            background: white;
            border: 2px dashed #667eea;
            margin: 0 auto 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            font-size: 12px;
            color: #667eea;
        }
        
        .footer {
            background: #34495e;
            color: white;
            text-align: center;
            padding: 20px;
            font-size: 11px;
        }
        
        /* SVG Icons menggunakan style Heroicon */
        .icon {
            width: 16px;
            height: 16px;
            margin-right: 8px;
            vertical-align: middle;
            fill: currentColor;
        }
        
        .footer-note {
            opacity: 0.8;
            margin-bottom: 10px;
        }
        
        .print-time {
            font-weight: bold;
            color: #ecf0f1;
        }
        
        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .warning-title {
            font-weight: bold;
            color: #856404;
            margin-bottom: 8px;
        }
        
        .warning-text {
            color: #856404;
            font-size: 11px;
            line-height: 1.4;
        }
        @media print {
            body { 
                background: none;
                margin: 0;
            }
            .container {
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>BUKTI PENYEWAAN ESTRIPORA</h1>
            <div class="subtitle">Dokumen Resmi Pemesanan Lokasi</div>
        </div>

        <div class="validation-badge">
            TUNJUKKAN FILE BUKTI PENYEWAAN INI KE PENGELOLA TEMPAT
        </div>

        <div class="main-content">
            <!-- ID Booking yang mencolok -->
            <div class="booking-id">
                {{ $transaksi->id_billing }}
            </div>

        @php
            $tempat = $transaksi->penyewaan?->lokasi?->tempat;
        @endphp

        <div class="venue-name">
            {{ $tempat?->nama ?? 'Nama Tempat' }}
        </div>
        <div class="venue-category">
            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c1.1045695 0 2 .8954305 2 2v1M9 21v-6a2 2 0 012-2h2a2 2 0 012 2v6M21 21H3M3 21V8a2 2 0 012-2h4M7 21v-3"></path>
            </svg>
            {{ ucfirst($tempat?->kategori ?? 'Kategori Tempat') }}
        </div>
            <!-- Informasi Utama -->
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-item">
                        <div class="info-label">
                            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            NIK Penyewa
                        </div>
                        <div class="info-value">{{ $transaksi->nik }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">
                            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 0V6a2 2 0 012-2h4a2 2 0 012 2v1M8 7h8m-8 0H6a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V9a2 2 0 00-2-2h-2"></path>
                            </svg>
                            Tanggal Booking
                        </div>
                        <div class="info-value">{{ \Carbon\Carbon::parse($transaksi->tgl_booking)->format('d F Y') }}</div>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-item">
                        <div class="info-label">
                            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            Lokasi
                        </div>
                        <div class="info-value">{{ $transaksi->penyewaan->lokasi->nama_lokasi ?? 'Lokasi Penyewaan' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">
                            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Kategori Sewa
                        </div>
                        <div class="info-value">{{ ucfirst($transaksi->penyewaan->kategori_sewa ?? 'per hari') }}</div>
                    </div>
                </div>
            </div>

            <!-- Detail Jadwal Penyewaan -->
            <div class="schedule-section">
                <div class="schedule-title">JADWAL PENYEWAAN</div>
                <div class="schedule-details">
                    @php
                        $penyewaan = $transaksi->penyewaan;
                        $perHari = is_string($penyewaan->penyewaan_per_hari ?? '') 
                                    ? json_decode($penyewaan->penyewaan_per_hari, true) 
                                    : ($penyewaan->penyewaan_per_hari ?? []);
                        $perJam = is_string($penyewaan->penyewaan_per_jam ?? '') 
                                    ? json_decode($penyewaan->penyewaan_per_jam, true) 
                                    : ($penyewaan->penyewaan_per_jam ?? []);
                    @endphp

                    @if (($penyewaan->kategori_sewa ?? '') === 'per hari' && !empty($perHari) && is_array($perHari))
                        @if (count($perHari) > 0)
                            <strong>Periode:</strong> 
                            {{ \Carbon\Carbon::parse($perHari[0]['tgl_mulai'])->format('d F Y') }} 
                            s/d 
                            {{ \Carbon\Carbon::parse($perHari[0]['tgl_selesai'])->format('d F Y') }}
                            <br><strong>Durasi:</strong> {{ $transaksi->total_durasi }} hari
                        @endif
                    @elseif (($penyewaan->kategori_sewa ?? '') === 'per jam' && !empty($perJam) && is_array($perJam))
                        @foreach ($perJam as $index => $item)
                            <strong>Sesi {{ $index + 1 }}:</strong> {{ \Carbon\Carbon::parse($item['tgl_mulai'] ?? '')->format('d F Y') }}<br>
                            <strong>Waktu:</strong> {{ $item['jam_mulai'] ?? '-' }} - {{ $item['jam_selesai'] ?? '-' }}<br>
                            @if (!$loop->last) <hr style="margin: 10px 0; border: 1px solid rgba(255,255,255,0.3);"> @endif
                        @endforeach
                        <br><strong>Total Durasi:</strong> {{ $transaksi->total_durasi }} jam
                    @endif
                </div>
            </div>

            <!-- Informasi Pembayaran -->
            <div class="payment-section">
                <div class="payment-amount">
                    Rp {{ number_format($transaksi->sub_total, 0, ',', '.') }}
                </div>
                <div style="text-align: center;">
                    <span class="status-badge status-{{ strtolower($transaksi->status) }}">
                        @if($transaksi->status === 'Paid')
                            LUNAS
                        @elseif($transaksi->status === 'Pending')
                            MENUNGGU PEMBAYARAN
                        @else
                            {{ $transaksi->status }}
                        @endif
                    </span>
                </div>
                @if($transaksi->metode_pembayaran)
                    <div style="text-align: center; margin-top: 10px; font-size: 12px;">
                        <strong>Metode:</strong> {{ $transaksi->metode_pembayaran }}
                    </div>
                @endif
            </div>

            <!-- Peringatan -->
            <div class="warning-box">
                <div class="warning-title">⚠️ PENTING - HARAP DIBACA</div>
                <div class="warning-text">
                    • Tunjukkan tiket ini kepada pengelola lokasi sebelum masuk<br>
                    • Pastikan status pembayaran "LUNAS" sebelum menggunakan fasilitas<br>
                    • Bawa identitas (KTP) yang sesuai dengan NIK di tiket<br>
                    • Tiket ini berlaku sesuai jadwal yang tertera<br>
                    • Hubungi customer service jika ada kendala
                </div>
            </div>
        </div>

        <div class="footer">
            <div class="footer-note">
                Dokumen ini digenerate secara otomatis dan sah sebagai bukti pemesanan
            </div>
            <div class="print-time">
                Dicetak pada: {{ date('d F Y, H:i:s') }} WIB
            </div>
        </div>
    </div>
</body>
</html>
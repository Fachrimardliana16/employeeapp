<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan {{ $title }}</title>
    <style>
        body { font-family: 'Times New Roman', serif; font-size: 10pt; color: #333; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; text-align: center; }
        .header { text-align: center; margin-bottom: 30px; }
        .footer { margin-top: 30px; text-align: right; }
        
        /* Kop Surat Styles */
        .kop-table { width: 100%; border-collapse: collapse; margin-bottom: 5px; border: none; }
        .kop-table td { border: none; padding: 0; }
        .line-thick { border-bottom: 3pt solid #000; margin-top: 2pt; }
        .line-thin { border-bottom: 1pt solid #000; margin-top: 2pt; margin-bottom: 15pt; }
    </style>
</head>
<body>
    <!-- Header Resmi PERUMDA -->
    <table class="kop-table">
        <tr>
            <td style="width: 100px; vertical-align: middle; text-align: left;">
                @php
                    $logoPath = public_path('assets/images/logo-pdam.png');
                    $logoData = "";
                    if (file_exists($logoPath)) {
                        $logoData = base64_encode(file_get_contents($logoPath));
                    }
                @endphp
                @if($logoData)
                    <img src="data:image/png;base64,{{ $logoData }}" style="height: 80px; width: auto;">
                @endif
            </td>
            <td style="text-align: center; vertical-align: middle; padding-right: 50px;">
                <h1 style="margin: 0; font-size: 14pt; font-weight: bold; text-transform: uppercase; line-height: 1.1;">PERUSAHAAN UMUM DAERAH AIR MINUM</h1>
                <h1 style="margin: 0; font-size: 20pt; font-weight: bold; text-transform: uppercase; line-height: 1.1;">TIRTA PERWIRA</h1>
                <h2 style="margin: 0; font-size: 14pt; font-weight: bold; text-transform: uppercase; line-height: 1.1;">KABUPATEN PURBALINGGA</h2>
                <p style="margin: 5pt 0 0 0; font-size: 9pt; font-style: italic; line-height: 1.2;">Jl. Let. Jend. S.Parman No. 62 Kedung Menjangan. Purbalingga (53316).</p>
            </td>
        </tr>
    </table>

    <div class="line-thick"></div>
    <div class="line-thin"></div>

    <div class="header">
        <h2 style="text-decoration: underline; margin-bottom: 10px;">{{ strtoupper($title) }}</h2>
        <p>Nama Pengguna: {{ $userName }}</p>
        <p>Periode: {{ $startDate ? \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') : 'Semua' }} s/d {{ $endDate ? \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') : 'Semua' }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 15%;">Kategori</th>
                <th style="width: 50%;">Deskripsi</th>
                <th style="width: 30%;">Tanggal & Waktu</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $index => $item)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ $item->log_name }}</td>
                    <td>{{ $item->description }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y H:i:s') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center;">Tidak ada data ditemukan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak pada: {{ date('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>

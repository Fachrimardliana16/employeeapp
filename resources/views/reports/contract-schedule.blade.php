<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Jadwal Habis Kontrak Kerja {{ $year }}</title>
    <style>
        @page {
            margin: 1cm 1.5cm;
        }
        body {
            font-family: 'Times New Roman', serif;
            font-size: 10pt;
            line-height: 1.3;
            color: #000;
            margin: 0;
            padding: 0;
        }
        /* Kop Surat Styles */
        .kop-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        .logo-cell {
            width: 80px;
            vertical-align: middle;
            text-align: left;
        }
        .info-cell {
            text-align: center;
            vertical-align: middle;
            padding-right: 50px;
        }
        .info-cell h1.line-1 {
            margin: 0;
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            line-height: 1.1;
        }
        .info-cell h1.line-2 {
            margin: 0;
            font-size: 20pt;
            font-weight: bold;
            text-transform: uppercase;
            line-height: 1.1;
        }
        .info-cell h2.line-3 {
            margin: 0;
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            line-height: 1.1;
        }
        .info-cell p.address {
            margin: 5pt 0 0 0;
            font-size: 9pt;
            font-style: italic;
            line-height: 1.2;
        }
        .line-thick {
            border-bottom: 2.5pt solid #000;
            margin-top: 2pt;
        }
        .line-thin {
            border-bottom: 1pt solid #000;
            margin-top: 1.5pt;
            margin-bottom: 15pt;
        }

        /* Content Styles */
        .report-title {
            text-align: center;
            margin-top: 10pt;
            margin-bottom: 20pt;
        }
        .report-title h2 {
            margin: 0;
            font-size: 14pt;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
        }
        .report-title p {
            margin: 5pt 0 0 0;
            font-size: 11pt;
            font-weight: bold;
        }

        .month-header {
            background-color: #eee;
            padding: 5pt 10pt;
            font-weight: bold;
            font-size: 11pt;
            border: 1pt solid #000;
            margin-top: 15pt;
            margin-bottom: 5pt;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10pt;
        }
        table.data-table th {
            border: 1pt solid #000;
            padding: 6pt 4pt;
            font-size: 9pt;
            background-color: #f2f2f2;
            text-transform: uppercase;
        }
        table.data-table td {
            border: 1pt solid #000;
            padding: 5pt 4pt;
            font-size: 9pt;
            vertical-align: middle;
        }
        
        .footer-note {
            margin-top: 15pt;
            font-size: 8pt;
            font-style: italic;
            color: #444;
        }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    @php
        $monthNames = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
            '04' => 'April', '05' => 'Mei', '06' => 'Juni',
            '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
            '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
        ];
    @endphp

    <table class="kop-table">
        <tr>
            <td class="logo-cell">
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
            <td class="info-cell">
                <h1 class="line-1">PERUSAHAAN UMUM DAERAH AIR MINUM</h1>
                <h1 class="line-2">TIRTA PERWIRA</h1>
                <h2 class="line-3">KABUPATEN PURBALINGGA</h2>
                <p class="address">Jl. Let. Jend. S.Parman No. 62 Kedung Menjangan. Purbalingga (53316).</p>
            </td>
        </tr>
    </table>
    <div class="line-thick"></div>
    <div class="line-thin"></div>

    <div class="report-title">
        <h2>JADWAL PEGAWAI HABIS KONTRAK KERJA</h2>
        <p>TAHUN ANGGARAN {{ $year }}</p>
    </div>

    @forelse($contractData as $month => $agreements)
        <div class="month-header">
            BULAN: {{ strtoupper($monthNames[$month] ?? 'Bulan Lainnya') }} {{ $year }}
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 30px;">NO</th>
                    <th style="width: 100px;">NIPPAM</th>
                    <th>NAMA PEGAWAI</th>
                    <th>JENIS KONTRAK</th>
                    <th>UNIT KERJA / BAGIAN</th>
                    <th style="width: 100px;">TGL BERAKHIR</th>
                </tr>
            </thead>
            <tbody>
                @foreach($agreements as $index => $agreement)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td class="text-center">{{ $agreement->employee->nippam ?? '-' }}</td>
                        <td><strong>{{ $agreement->name }}</strong></td>
                        <td class="text-center">{{ $agreement->masterAgreement->name ?? '-' }}</td>
                        <td>{{ $agreement->department->name ?? '-' }}</td>
                        <td class="text-center">{{ $agreement->agreement_date_end->format('d/m/Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @empty
        <div style="text-align: center; border: 1pt solid #000; padding: 20pt; margin-top: 20pt;">
            TIDAK ADA JADWAL KONTRAK BERAKHIR PADA TAHUN {{ $year }}
        </div>
    @endforelse

    <div class="footer-note">
        * Dicetak pada: {{ $generated_at }}
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Hasil Interview - {{ $record->name }}</title>
    <style>
        body {
            font-family: 'Times New Roman', serif;
            font-size: 11pt;
            line-height: 1.5;
            margin: 0.5in 0.8in;
            color: #000;
        }
        .header table {
            width: 100%;
            border-collapse: collapse;
        }
        .kop-border-thick {
            border-bottom: 2.5pt solid #000;
            margin-top: 2pt;
        }
        .kop-border-thin {
            border-bottom: 1pt solid #000;
            margin-top: 2pt;
            margin-bottom: 20pt;
        }
        .title {
            text-align: center;
            margin: 20pt 0 10pt 0;
        }
        .title h1 {
            margin: 0;
            font-size: 14pt;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
        }
        .content {
            margin-top: 20pt;
            text-align: justify;
        }
        .detail-table {
            width: 100%;
            margin: 15pt 0;
            border-collapse: collapse;
        }
        .detail-table td {
            padding: 4pt 0;
            vertical-align: top;
        }
        .detail-table .label {
            width: 150pt;
        }
        .detail-table .colon {
            width: 20pt;
            text-align: center;
        }
        .decision-box {
            margin: 20pt 0;
            padding: 10pt;
            border: 1pt solid #000;
            text-align: center;
            font-weight: bold;
            font-size: 12pt;
            background-color: #f9f9f9;
        }
        .signature-wrapper {
            margin-top: 40pt;
            width: 100%;
        }
        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }
        .signature-table td {
            width: 50%;
            vertical-align: top;
        }
        @media print {
            .no-print { display: none; }
            body { margin: 0.5in 0.8in; }
        }
    </style>
</head>
<body onload="@if(!request()->has('noprint')) window.print() @endif">
    
    <!-- Header Resmi PERUMDA (Identical to Assignment Letter) -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 5pt;">
        <tr>
            <td style="width: 80pt; vertical-align: middle; text-align: left;">
                @php
                    $logoPath = public_path('assets/images/logo-pdam.png');
                    $logoData = "";
                    if (file_exists($logoPath)) {
                        $logoData = base64_encode(file_get_contents($logoPath));
                    }
                @endphp
                @if($logoData)
                    <img src="data:image/png;base64,{{ $logoData }}" style="height: 70pt; width: auto;">
                @endif
            </td>
            <td style="text-align: center; vertical-align: middle; padding-right: 40pt;">
                <h1 style="margin: 0; font-size: 14pt; font-weight: bold; text-transform: uppercase; line-height: 1.1;">PERUSAHAAN UMUM DAERAH AIR MINUM</h1>
                <h1 style="margin: 0; font-size: 18pt; font-weight: bold; text-transform: uppercase; line-height: 1.1;">TIRTA PERWIRA</h1>
                <h2 style="margin: 0; font-size: 14pt; font-weight: bold; text-transform: uppercase; line-height: 1.1;">KABUPATEN PURBALINGGA</h2>
                <p style="margin: 5pt 0 0 0; font-size: 9pt; font-style: italic; line-height: 1.2;">Jl. Let. Jend. S.Parman No. 62 Kedung Menjangan. Purbalingga (53316).</p>
            </td>
        </tr>
    </table>

    <div class="kop-border-thick"></div>
    <div class="kop-border-thin"></div>

    <div class="title">
        <h1>HASIL INTERVIEW</h1>
    </div>

    <div class="content">
        <p>Berdasarkan hasil interview yang telah dilaksanakan, disampaikan data pelamar sebagai berikut:</p>

        <table class="detail-table">
            <tr>
                <td class="label">Nama Lengkap</td>
                <td class="colon">:</td>
                <td style="font-weight: bold;">{{ strtoupper($record->name) }}</td>
            </tr>
            <tr>
                <td class="label">Nomor Lamaran</td>
                <td class="colon">:</td>
                <td>{{ $record->application_number }}</td>
            </tr>
            <tr>
                <td class="label">Posisi yang Dilamar</td>
                <td class="colon">:</td>
                <td>{{ $record->appliedPosition->name }}</td>
            </tr>
            <tr>
                <td class="label">Bagian</td>
                <td class="colon">:</td>
                <td>{{ $record->appliedDepartment->name }}</td>
            </tr>
            <tr>
                <td class="label">Email</td>
                <td class="colon">:</td>
                <td>{{ $record->email }}</td>
            </tr>
            <tr>
                <td class="label">Nomor Telepon</td>
                <td class="colon">:</td>
                <td>{{ $record->phone_number }}</td>
            </tr>
        </table>

        <p>Memutuskan bahwa pelamar tersebut dinyatakan:</p>

        <div class="decision-box" style="border: 2px solid #000;">
            <span style="font-size: 16pt; margin-top: 5pt; display: block; color: #000">
                @if($record->status === 'accepted')
                    DITERIMA
                @elseif($record->status === 'rejected')
                    TIDAK DITERIMA
                @elseif($record->status === 'interviewed')
                    SUDAH INTERVIEW
                @else
                    {{ strtoupper($record->status_label) }}
                @endif
            </span>
        </div>

        @if($record->archive && $record->archive->decision_reason)
            <p><strong>Keterangan:</strong> {{ $record->archive->decision_reason }}</p>
        @elseif($record->notes)
            <p><strong>Catatan HR:</strong> {{ $record->notes }}</p>
        @endif

        <p>Demikian hasil interview ini disampaikan.</p>
    </div>

    <div class="signature-wrapper">
        <table class="signature-table">
            <tr>
                <td></td>
                <td style="text-align: center;">
                    @php
                        $lastProcess = $record->interviewProcesses->where('status', 'completed')->sortByDesc('interview_stage')->first();
                        $interviewerName = $lastProcess->interviewer_name ?? '............................................';
                    @endphp
                    <p>Purbalingga, {{ now()->translatedFormat('d F Y') }}</p>
                    <p style="font-weight: bold; margin-bottom: 60pt;">Pewawancara / Tim Seleksi,</p>
                    
                    <p style="font-weight: bold; text-decoration: underline;">( {{ $interviewerName }} )</p>
                    <p style="font-size: 9pt; margin-top: 2pt;">Bagian Kepegawaian & SDM</p>
                </td>
            </tr>
        </table>
    </div>

    <!-- No Print Toggle for Testing -->
    <div class="no-print" style="margin-top: 50pt; text-align: center; border-top: 1px dashed #ccc; padding-top: 10pt;">
        <button onclick="window.print()" style="padding: 10pt 20pt; cursor: pointer;">Cetak Sekarang</button>
    </div>

</body>
</html>

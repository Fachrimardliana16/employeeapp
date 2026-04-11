<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Tugas - {{ $assignment->registration_number }}</title>
    <style>
        body {
            font-family: 'Times New Roman', serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 20px;
        }
        .header {
            width: 100%;
            margin-bottom: 20px;
        }
        .header table {
            width: 100%;
            border-collapse: collapse;
        }
        .header td {
            vertical-align: top;
            padding: 0;
        }
        .logo {
            width: 80px;
            text-align: center;
        }
        .kop-surat {
            text-align: center;
            padding-left: 20px;
        }
        .kop-surat h2 {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .kop-surat h3 {
            margin: 2px 0;
            font-size: 14px;
            font-weight: bold;
        }
        .kop-surat p {
            margin: 1px 0;
            font-size: 11px;
        }
        .divider {
            border-bottom: 2px solid #000;
            margin: 15px 0;
        }
        .title {
            text-align: center;
            margin: 20px 0;
        }
        .title h1 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
            text-decoration: underline;
        }
        .nomor {
            text-align: center;
            margin: 10px 0 30px 0;
            font-weight: bold;
        }
        .content {
            margin: 20px 0;
            text-align: justify;
        }
        .pemberi-tugas {
            margin: 20px 0;
        }
        .penerima-tugas {
            margin: 20px 0;
        }
        .detail-table {
            width: 100%;
            margin: 10px 0;
        }
        .detail-table td {
            padding: 3px 0;
            vertical-align: top;
        }
        .detail-table .label {
            width: 120px;
        }
        .detail-table .colon {
            width: 20px;
        }
        .signature {
            margin-top: 40px;
            width: 100%;
        }
        .signature table {
            width: 100%;
        }
        .signature td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 0 20px;
        }
        .signature-box {
            margin-top: 60px;
        }
        .signature-name {
            font-weight: bold;
            text-decoration: underline;
        }
        .employee-list {
            margin: 5px 0;
            padding-left: 120px;
        }
    </style>
</head>
<body>
    <!-- Header Resmi PERUMDA menggunakan Table (PDF Compatible) -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 5px;">
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
                    <img src="data:image/png;base64,{{ $logoData }}" style="height: 90px; width: auto;">
                @endif
            </td>
            <td style="text-align: center; vertical-align: middle; padding-right: 50px;">
                <h1 style="margin: 0; font-size: 14pt; font-family: 'Times New Roman', serif; font-weight: bold; text-transform: uppercase; line-height: 1.1;">PERUSAHAAN UMUM DAERAH AIR MINUM</h1>
                <h1 style="margin: 0; font-size: 20pt; font-family: 'Times New Roman', serif; font-weight: bold; text-transform: uppercase; line-height: 1.1;">TIRTA PERWIRA</h1>
                <h2 style="margin: 0; font-size: 14pt; font-family: 'Times New Roman', serif; font-weight: bold; text-transform: uppercase; line-height: 1.1;">KABUPATEN PURBALINGGA</h2>
                <p style="margin: 5pt 0 0 0; font-size: 9pt; font-style: italic; font-family: 'Times New Roman', serif; line-height: 1.2;">Jl. Let. Jend. S.Parman No. 62 Kedung Menjangan. Purbalingga (53316).</p>
            </td>
        </tr>
    </table>

    <!-- Garis Kop Tebal & Tipis -->
    <div style="border-bottom: 3pt solid #000; margin-top: 2pt;"></div>
    <div style="border-bottom: 1pt solid #000; margin-top: 2pt; margin-bottom: 20pt;"></div>

    <!-- Judul Surat -->
    <div class="title" style="margin-top: 20pt;">
        <h1>SURAT TUGAS</h1>
    </div>

    <!-- Nomor Surat -->
    <div class="nomor">
        Nomor: {{ $assignment->registration_number }}
    </div>

    <!-- Konten Surat -->
    <div class="content">
        <p>Yang bertanda tangan di bawah ini:</p>
        
        <div class="pemberi-tugas">
            <table class="detail-table">
                <tr>
                    <td class="label">Nama</td>
                    <td class="colon">:</td>
                    <td>{{ $signatory_name ?? 'Direktur Utama' }}</td>
                </tr>
                <tr>
                    <td class="label">Jabatan</td>
                    <td class="colon">:</td>
                    <td>{{ $signatory_position ?? 'Direktur Utama PERUMDA Air Minum Tirta Pewira' }}</td>
                </tr>
            </table>
        </div>

        <p>Dengan ini menugaskan kepada:</p>

        <div class="penerima-tugas">
            @php
                $employees = collect();
                
                // Tambahkan pegawai utama
                if($assignment->assigningEmployee) {
                    $employees->push($assignment->assigningEmployee);
                }
                
                // Tambahkan pegawai tambahan
                if(!empty($assignment->additional_employee_ids)) {
                    $additionalEmployees = $assignment->additionalEmployees();
                    $employees = $employees->merge($additionalEmployees);
                }
            @endphp
            
            @if($employees->count() > 1)
                <ol style="margin: 10px 0; padding-left: 140px;">
                    @foreach($employees as $employee)
                        <li style="margin: 5px 0;">{{ $employee->name }}</li>
                    @endforeach
                </ol>
            @else
                <table class="detail-table">
                    <tr>
                        <td class="label">Nama</td>
                        <td class="colon">:</td>
                        <td>{{ $assignment->assigningEmployee->name ?? 'Tidak ada pegawai' }}</td>
                    </tr>
                </table>
            @endif
        </div>

        <p>
            Untuk {{ $assignment->task }} 
            @if($assignment->start_date && $assignment->end_date)
                yang dilaksanakan mulai tanggal {{ \Carbon\Carbon::parse($assignment->start_date)->format('d F Y') }} 
                sampai dengan {{ \Carbon\Carbon::parse($assignment->end_date)->format('d F Y') }}.
            @endif
        </p>

        @if($assignment->description)
        <p>{{ $assignment->description }}</p>
        @endif

        <p>Demikian untuk menjadikan periksa dan dilaksanakan dengan sebaik-baiknya.</p>
    </div>

    <!-- Tanda Tangan -->
    <div class="signature">
        <table style="width: 100%;">
            <tr>
                <td style="width: 50%;"></td>
                <td style="width: 50%; text-align: center;">
                    <p style="margin-bottom: 5pt;">Purbalingga, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                    <p style="font-weight: bold; text-transform: uppercase; margin: 0;">Mengetahui/Menyetujui,</p>
                    <p style="font-weight: bold; text-transform: uppercase; font-style: italic; margin: 0;">Pejabat Berwenang</p>
                    
                    <div style="height: 60pt;"></div> <!-- Signature Space -->
                    
                    <div style="border-bottom: 1pt solid #000; width: 180pt; margin: 0 auto;">
                        <p style="font-weight: bold; text-decoration: none; margin: 0; padding-bottom: 2pt;">{{ $signatory_name ?? 'Direktur Utama' }}</p>
                    </div>
                    <p style="font-size: 8pt; color: #666; text-transform: uppercase; margin-top: 2pt;">{{ $signatory_position ?? 'Direktur Utama' }}</p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>

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
    <!-- Header dengan Logo dan Kop Surat -->
    <div class="header">
        <table>
            <tr>
                <td class="logo">
                    <div style="border: 1px solid #000; width: 60px; height: 60px; margin: 0 auto; display: flex; align-items: center; justify-content: center; font-size: 10px;">
                        LOGO
                    </div>
                </td>
                <td class="kop-surat">
                    <h2>PERUMDA Air Minum Tirta Pewira</h2>
                    <h3>Kabupaten Purbalingga</h3>
                    <p>Jl. Jenderal Sudirman No. 123, Purbalingga, Jawa Tengah</p>
                    <p>Telp: (0281) 123456, Email: info@tirtapewira.co.id</p>
                </td>
            </tr>
        </table>
    </div>

    <div class="divider"></div>

    <!-- Judul Surat -->
    <div class="title">
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
        <table>
            <tr>
                <td></td>
                <td>
                    <div>
                        <p>Purbalingga, {{ \Carbon\Carbon::now()->format('d F Y') }}</p>
                        <p>Mengetahui,</p>
                        <p><strong>PERUMDA Air Minum Tirta Pewira</strong></p>
                        <p><strong>Kabupaten Purbalingga</strong></p>
                        <p>{{ $signatory_position ?? 'Direktur Utama' }}</p>
                        
                        <div class="signature-box">
                            <div class="signature-name">{{ $signatory_name ?? 'Direktur Utama' }}</div>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Surat Perintah Perjalanan Dinas - {{ $travel->registration_number }}</title>
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
            text-align: left;
        }

        .detail-table {
            width: 100%;
            margin: 10px 0;
        }

        .detail-table td {
            padding: 5px 0;
            vertical-align: top;
        }

        .detail-table .number {
            width: 30px;
        }

        .detail-table .label {
            width: 200px;
        }

        .detail-table .colon {
            width: 20px;
        }

        .bordered-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }

        .bordered-table th,
        .bordered-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
        }

        .bordered-table th {
            background-color: #f0f0f0;
            font-weight: bold;
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

        .pengikut-list {
            margin: 5px 0;
            padding-left: 20px;
        }
    </style>
</head>

<body>
    <!-- Header dengan Logo dan Kop Surat -->
    <div class="header">
        <table>
            <tr>
                <td class="logo">
                    <div
                        style="border: 1px solid #000; width: 60px; height: 60px; margin: 0 auto; display: flex; align-items: center; justify-content: center; font-size: 10px;">
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
        <h1>SURAT PERINTAH PERJALANAN DINAS</h1>
    </div>

    <!-- Nomor Surat -->
    <div class="nomor">
        Nomor: {{ $travel->registration_number }}
    </div>

    <!-- Konten Surat -->
    <div class="content">
        <p><strong>DIPERINTAHKAN KEPADA:</strong></p>

        <table class="detail-table">
            @php
                $employees = collect();

                // Tambahkan pegawai utama jika ada
                if ($travel->employee) {
                    $employees->push($travel->employee);
                }

                // Tambahkan pegawai tambahan
                if (!empty($travel->additional_employee_ids)) {
                    $additionalEmployees = $travel->additionalEmployees();
                    $employees = $employees->merge($additionalEmployees);
                }

                $mainEmployee = $employees->first();
            @endphp

            <tr>
                <td class="number">1.</td>
                <td class="label">NAMA</td>
                <td class="colon">:</td>
                <td>{{ $mainEmployee->name ?? 'Tim Perjalanan Dinas' }}</td>
            </tr>
            <tr>
                <td class="number">2.</td>
                <td class="label">JABATAN DAN PANGKAT</td>
                <td class="colon">:</td>
                <td>{{ $mainEmployee->position->name ?? '-' }}</td>
            </tr>
            <tr>
                <td class="number">3.</td>
                <td class="label">NAMA TEMPAT YANG DITUJU</td>
                <td class="colon">:</td>
                <td>{{ $travel->destination }}</td>
            </tr>
        </table>

        <!-- Tabel Tempat Tujuan dan Maksud -->
        <table class="bordered-table">
            <thead>
                <tr>
                    <th style="width: 50%;">TEMPAT TUJUAN</th>
                    <th style="width: 50%;">MAKSUD PERJALANAN</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $travel->destination_detail ?: $travel->destination }}</td>
                    <td>{{ $travel->purpose_of_trip }}</td>
                </tr>
            </tbody>
        </table>

        @php
            $startDate = \Carbon\Carbon::parse($travel->start_date);
            $endDate = \Carbon\Carbon::parse($travel->end_date);
            $duration = $startDate->diffInDays($endDate) + 1;
        @endphp

        <table class="detail-table">
            <tr>
                <td class="number">4.</td>
                <td class="label">UNTUK SELAMA WAKTU</td>
                <td class="colon">:</td>
                <td>{{ $duration }} hari</td>
            </tr>
        </table>

        <!-- Tabel Berangkat dan Kembali -->
        <table class="bordered-table">
            <thead>
                <tr>
                    <th style="width: 50%;">BERANGKAT DAN KEMBALI TANGGAL</th>
                    <th style="width: 50%;">CAP DAN TANDA TANGAN</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $startDate->format('d F Y') }} s/d {{ $endDate->format('d F Y') }}</td>
                    <td style="height: 60px;">&nbsp;</td>
                </tr>
            </tbody>
        </table>

        <table class="detail-table">
            <tr>
                <td class="number">5.</td>
                <td class="label">PERJALANAN DINAS DIBIAYAI</td>
                <td class="colon">:</td>
                <td>
                    @if ($travel->business_trip_expenses > 0)
                        Rp {{ number_format($travel->business_trip_expenses, 0, ',', '.') }}
                    @else
                        Beban PERUMDA Air Minum Tirta Pewira
                    @endif
                </td>
            </tr>
            <tr>
                <td class="number">6.</td>
                <td class="label">PASAL</td>
                <td class="colon">:</td>
                <td>{{ $travel->pasal ?: '-' }}</td>
            </tr>
            <tr>
                <td class="number">7.</td>
                <td class="label">PENGIKUT</td>
                <td class="colon">:</td>
                <td>
                    @if ($employees->count() > 1)
                        <ol class="pengikut-list">
                            @foreach ($employees->skip(1) as $employee)
                                <li>{{ $employee->name }}</li>
                            @endforeach
                        </ol>
                    @else
                        -
                    @endif
                </td>
            </tr>
            <tr>
                <td class="number">8.</td>
                <td class="label">KETERANGAN LAIN-LAIN</td>
                <td class="colon">:</td>
                <td>
                    {{ $travel->description ?: 'Kepada instansi yang bersangkutan maklum adanya.' }}
                </td>
            </tr>
        </table>
    </div>

    <!-- Tanda Tangan -->
    <div class="signature">
        <table>
            <tr>
                <td style="text-align: center; width: 50%; vertical-align: top;">
                    <div>
                        <p>&nbsp;</p> <!-- Baris kosong untuk menyejajarkan dengan baris ke-2 sebelah kanan -->
                        <p>&nbsp;</p>
                        <p>&nbsp;</p>
                        <p>Tanda tangan pemegang</p>

                        <div class="signature-box">
                            <div class="signature-name">{{ $mainEmployee->name ?? '________________' }}</div>
                        </div>
                    </div>
                </td>
                <td style="text-align: center; width: 50%; vertical-align: top;">
                    <div>
                        <p>Purbalingga, {{ \Carbon\Carbon::now()->format('d F Y') }}</p>
                        <p><strong>PERUMDA Air Minum Tirta Pewira</strong></p>
                        <p><strong>Kabupaten Purbalingga</strong></p>
                        <p><strong>{{ $signatory_position ?? 'Direktur Utama' }}</strong></p>

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

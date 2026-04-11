<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Kontrak Pegawai</title>
    <style>
        @page {
            margin: 15mm;
            size: A4 landscape;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 11px;
            line-height: 1.3;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }

        .company-info {
            margin-bottom: 12px;
        }

        .company-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .company-subtitle {
            font-size: 14px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 4px;
        }

        .company-address {
            font-size: 10px;
            color: #666;
            line-height: 1.3;
        }

        .report-title {
            font-size: 14px;
            font-weight: bold;
            margin-top: 15px;
            text-transform: uppercase;
        }

        .report-subtitle {
            font-size: 12px;
            font-weight: bold;
            color: #2563eb;
            margin-top: 4px;
        }

        .stats-title {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 8px;
            background-color: #f3f4f6;
            padding: 6px;
            border-left: 4px solid #2563eb;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 8px;
            table-layout: fixed;
        }

        .data-table th,
        .data-table td {
            border: 1px solid #ddd;
            padding: 3px;
            text-align: left;
            word-wrap: break-word;
            overflow-wrap: break-word;
            vertical-align: top;
        }

        .data-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            text-align: center;
            font-size: 9px;
            padding: 4px 3px;
        }

        .data-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        /* Landscape column width specifications */
        .data-table th:nth-child(1),
        .data-table td:nth-child(1) {
            width: 3%;
        }

        /* No */
        .data-table th:nth-child(2),
        .data-table td:nth-child(2) {
            width: 9%;
        }

        /* Nomor Kontrak */
        .data-table th:nth-child(3),
        .data-table td:nth-child(3) {
            width: 12%;
        }

        /* Nama */
        .data-table th:nth-child(4),
        .data-table td:nth-child(4) {
            width: 7%;
        }

        /* Jenis Kontrak */
        .data-table th:nth-child(5),
        .data-table td:nth-child(5) {
            width: 9%;
        }

        /* Posisi */
        .data-table th:nth-child(6),
        .data-table td:nth-child(6) {
            width: 8%;
        }

        /* Status Kepegawaian */
        .data-table th:nth-child(7),
        .data-table td:nth-child(7) {
            width: 6%;
        }

        /* Pendidikan */
        .data-table th:nth-child(8),
        .data-table td:nth-child(8) {
            width: 5%;
        }

        /* Grade */
        .data-table th:nth-child(9),
        .data-table td:nth-child(9) {
            width: 9%;
        }

        /* Gaji */
        .data-table th:nth-child(10),
        .data-table td:nth-child(10) {
            width: 7%;
        }

        /* Tanggal Mulai */
        .data-table th:nth-child(11),
        .data-table td:nth-child(11) {
            width: 7%;
        }

        /* Tanggal Berakhir */
        .data-table th:nth-child(12),
        .data-table td:nth-child(12) {
            width: 8%;
        }

        /* Departemen */
        .data-table th:nth-child(13),
        .data-table td:nth-child(13) {
            width: 8%;
        }

        /* Sub Departemen */
        .data-table th:nth-child(14),
        .data-table td:nth-child(14) {
            width: 6%;
        }

        /* Status Kontrak */

        .footer {
            margin-top: 25px;
            text-align: center;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 12px;
        }

        .generated-info {
            font-weight: bold;
            color: #333;
        }

        .page-break {
            page-break-before: always;
        }

        .money {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .badge {
            padding: 1px 3px;
            border-radius: 2px;
            font-size: 7px;
            font-weight: bold;
            display: inline-block;
        }

        .badge-success {
            background-color: #dcf4e4;
            color: #166534;
        }

        .badge-info {
            background-color: #dbeafe;
            color: #1e40af;
        }

        /* Responsive text sizing for landscape */
        @media print {
            .data-table {
                font-size: 7px;
            }

            .data-table th {
                font-size: 8px;
            }

            .badge {
                font-size: 6px;
                padding: 1px 2px;
            }
        }
    </style>
</head>

<body>
    <!-- Header dengan Kop Surat -->
    <div class="header">
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">
            <tr>
                <td style="width: 80px; text-align: left;">
                    <img src="{{ public_path('assets/images/logo-pdam.png') }}" style="height: 60px; width: auto;">
                </td>
                <td style="text-align: center;">
                    <div style="font-size: 14px; font-weight: bold; text-transform: uppercase;">PERUSAHAAN UMUM DAERAH AIR MINUM</div>
                    <div style="font-size: 18px; font-weight: 800; text-transform: uppercase; color: #000;">TIRTA PERWIRA</div>
                    <div style="font-size: 14px; font-weight: bold; text-transform: uppercase;">KABUPATEN PURBALINGGA</div>
                    <div style="font-size: 9px; font-style: italic; margin-top: 2px;">Jl. Let. Jend. S.Parman No. 62 Kedung Menjangan. Purbalingga (53316).</div>
                </td>
                <td style="width: 80px;"></td>
            </tr>
        </table>
        <div style="border-bottom: 2px solid #000; margin-top: 5px;"></div>
        <div style="border-bottom: 0.5px solid #000; margin-top: 1px; margin-bottom: 15px;"></div>

        <div class="report-title">Report Penandatanganan Kontrak Pegawai</div>
        <div class="report-subtitle">Perumda Air Minum Tirta Perwira</div>
    </div>

    <!-- Tabel Data Lengkap Landscape -->
    <div>
        <div class="stats-title">Data Lengkap Kontrak Pegawai</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nomor Kontrak</th>
                    <th>Nama Pegawai</th>
                    <th>Jenis Kontrak</th>
                    <th>Posisi/Jabatan</th>
                    <th>Status Kepegawaian</th>
                    <th>Pendidikan</th>
                    <th>Golongan</th>
                    <th>Gaji Pokok</th>
                    <th>Tanggal Mulai</th>
                    <th>Tanggal Berakhir</th>
                    <th>Bagian</th>
                    <th>Sub Bagian</th>
                    <th>Status Kontrak</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data['agreements'] as $index => $agreement)
                    <tr>
                        <td class="center">{{ $index + 1 }}</td>
                        <td>{{ $agreement->agreement_number }}</td>
                        <td>{{ $agreement->name }}</td>
                        <td>{{ $agreement->masterAgreement->name ?? '-' }}</td>
                        <td>{{ $agreement->employeePosition->name ?? '-' }}</td>
                        <td>{{ $agreement->employmentStatus->name ?? '-' }}</td>
                        <td class="center">
                            @if ($agreement->education)
                                <span class="badge badge-success">{{ $agreement->education->name }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="center">
                            @if ($agreement->basicSalaryGrade)
                                <span class="badge badge-info">{{ $agreement->basicSalaryGrade->name }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="money">
                            @if ($agreement->basic_salary)
                                Rp {{ number_format($agreement->basic_salary, 0, ',', '.') }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="center">
                            {{ $agreement->agreement_date_start ? \Carbon\Carbon::parse($agreement->agreement_date_start)->format('d/m/Y') : '-' }}
                        </td>
                        <td class="center">
                            {{ $agreement->agreement_date_end ? \Carbon\Carbon::parse($agreement->agreement_date_end)->format('d/m/Y') : '-' }}
                        </td>
                        <td>{{ $agreement->department->name ?? '-' }}</td>
                        <td>{{ $agreement->subDepartment->name ?? '-' }}</td>
                        <td class="center">
                            @if ($agreement->is_active)
                                <span class="badge badge-success">Aktif</span>
                            @else
                                <span class="badge" style="background-color: #fee2e2; color: #dc2626;">Tidak
                                    Aktif</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="generated-info">
            Dokumen ini dicetak melalui Web ERP Tirta Perwira pada tanggal: {{ $generated_at }} WIB
        </div>
        <div style="margin-top: 10px; color: #888;">
            Report ini berisi informasi lengkap tentang kontrak pegawai yang telah ditandatangani.<br>
            Data yang ditampilkan merupakan data real-time dari sistem pada saat report digenerate.<br>
            Untuk informasi lebih lanjut, silakan hubungi bagian HRD Perumda Air Minum Tirta Perwira.
        </div>
    </div>
</body>

</html>

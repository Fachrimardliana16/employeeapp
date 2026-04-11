<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Data Pegawai</title>
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

        /* Landscape column width specifications for Employee */
        .data-table th:nth-child(1),
        .data-table td:nth-child(1) {
            width: 2%;
        }

        /* No */
        .data-table th:nth-child(2),
        .data-table td:nth-child(2) {
            width: 6%;
        }

        /* NIPPAM */
        .data-table th:nth-child(3),
        .data-table td:nth-child(3) {
            width: 10%;
        }

        /* Nama */
        .data-table th:nth-child(4),
        .data-table td:nth-child(4) {
            width: 6%;
        }

        /* Username */
        .data-table th:nth-child(5),
        .data-table td:nth-child(5) {
            width: 4%;
        }

        /* Jenis Kelamin */
        .data-table th:nth-child(6),
        .data-table td:nth-child(6) {
            width: 5%;
        }

        /* Tempat, Tanggal Lahir */
        .data-table th:nth-child(7),
        .data-table td:nth-child(7) {
            width: 8%;
        }

        /* Email */
        .data-table th:nth-child(8),
        .data-table td:nth-child(8) {
            width: 6%;
        }

        /* No. Telepon */
        .data-table th:nth-child(9),
        .data-table td:nth-child(9) {
            width: 8%;
        }

        /* Departemen */
        .data-table th:nth-child(10),
        .data-table td:nth-child(10) {
            width: 8%;
        }

        /* Posisi */
        .data-table th:nth-child(11),
        .data-table td:nth-child(11) {
            width: 6%;
        }

        /* Status Kepegawaian */
        .data-table th:nth-child(12),
        .data-table td:nth-child(12) {
            width: 5%;
        }

        /* Pendidikan */
        .data-table th:nth-child(13),
        .data-table td:nth-child(13) {
            width: 4%;
        }

        /* Grade */
        .data-table th:nth-child(14),
        .data-table td:nth-child(14) {
            width: 5%;
        }

        /* Tanggal Masuk */
        .data-table th:nth-child(15),
        .data-table td:nth-child(15) {
            width: 5%;
        }

        /* Masa Kerja */
        .data-table th:nth-child(16),
        .data-table td:nth-child(16) {
            width: 4%;
        }

        /* Kelengkapan Data */

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

        .badge-warning {
            background-color: #fef3c7;
            color: #92400e;
        }

        .badge-danger {
            background-color: #fee2e2;
            color: #dc2626;
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

        <div class="report-title">Report Data Pegawai</div>
        <div class="report-subtitle">Perumda Air Minum Tirta Perwira</div>

        @if (isset($filters))
            <div style="margin-top: 10px; font-size: 10px; color: #666;">
                <strong>Filter:</strong>
                Departemen: {{ $filters['department'] }} |
                Posisi: {{ $filters['position'] }} |
                Status: {{ $filters['status'] }} |
                Pendidikan: {{ $filters['education'] }}
            </div>
        @endif
    </div>

    <!-- Tabel Data Lengkap Landscape -->
    <div>
        <div class="stats-title">Data Lengkap Pegawai ({{ $data['total_employees'] }} Orang)</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>NIPPAM</th>
                    <th>Nama Pegawai</th>
                    <th>Username</th>
                    <th>Jenis Kelamin</th>
                    <th>Tempat, Tgl Lahir</th>
                    <th>Email</th>
                    <th>No. Telepon</th>
                    <th>Departemen</th>
                    <th>Posisi/Jabatan</th>
                    <th>Status Kepegawaian</th>
                    <th>Pendidikan</th>
                    <th>Grade</th>
                    <th>Tanggal Masuk</th>
                    <th>Masa Kerja</th>
                    <th>Kelengkapan Data</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data['employees'] as $index => $employee)
                    <tr>
                        <td class="center">{{ $index + 1 }}</td>
                        <td>{{ $employee->nippam ?? '-' }}</td>
                        <td>{{ $employee->name }}</td>
                        <td>{{ $employee->username ?? '-' }}</td>
                        <td class="center">
                            @if ($employee->gender == 'male')
                                L
                            @elseif($employee->gender == 'female')
                                P
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            {{ $employee->place_birth ?? '-' }}
                            @if ($employee->date_birth)
                                , {{ \Carbon\Carbon::parse($employee->date_birth)->format('d/m/Y') }}
                            @endif
                        </td>
                        <td>{{ $employee->email ?? '-' }}</td>
                        <td>{{ $employee->phone_number ?? '-' }}</td>
                        <td>{{ $employee->department->name ?? '-' }}</td>
                        <td>{{ $employee->position->name ?? '-' }}</td>
                        <td class="center">
                            @if ($employee->employmentStatus)
                                <span class="badge badge-info">{{ $employee->employmentStatus->name }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="center">
                            @if ($employee->education)
                                <span class="badge badge-success">{{ $employee->education->name }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="center">
                            @if ($employee->grade)
                                <span class="badge badge-info">{{ $employee->grade->name }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="center">
                            {{ $employee->entry_date ? \Carbon\Carbon::parse($employee->entry_date)->format('d/m/Y') : '-' }}
                        </td>
                        <td class="center">
                            {{ $employee->formatted_length_service ?? '-' }}
                        </td>
                        <td class="center">
                            @php
                                $completeness = $employee->getDataCompletenessPercentage();
                                $badgeClass =
                                    $completeness >= 90
                                        ? 'badge-success'
                                        : ($completeness >= 70
                                            ? 'badge-warning'
                                            : 'badge-danger');
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ $completeness }}%</span>
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
            Report ini berisi informasi lengkap tentang data Pegawai yang terdaftar dalam sistem.<br>
            Data yang ditampilkan merupakan data real-time dari sistem pada saat report digenerate.<br>
            Untuk informasi lebih lanjut, silakan hubungi bagian HRD Perumda Air Minum Tirta Perwira.
        </div>
    </div>
</body>

</html>

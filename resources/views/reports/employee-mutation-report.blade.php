<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Mutasi Pegawai</title>
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
            font-size: 18px;
            font-weight: bold;
            color: #1f2937;
            margin: 15px 0 8px 0;
        }

        .report-subtitle {
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 20px;
        }

        .stats-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 25px;
            gap: 15px;
        }

        .stat-box {
            flex: 1;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px;
            text-align: center;
            min-height: 70px;
        }

        .stat-number {
            font-size: 20px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 4px;
        }

        .stat-label {
            font-size: 10px;
            color: #64748b;
            font-weight: 500;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #374151;
            margin: 20px 0 10px 0;
            padding-bottom: 5px;
            border-bottom: 1px solid #d1d5db;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 10px;
        }

        .table th,
        .table td {
            border: 1px solid #d1d5db;
            padding: 6px;
            text-align: left;
            vertical-align: top;
        }

        .table th {
            background-color: #f3f4f6;
            font-weight: bold;
            color: #374151;
            text-align: center;
        }

        .table tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .table tbody tr:hover {
            background-color: #f3f4f6;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: 500;
            color: white;
        }

        .badge-success {
            background-color: #059669;
        }

        .badge-warning {
            background-color: #d97706;
        }

        .badge-info {
            background-color: #0284c7;
        }

        .badge-gray {
            background-color: #6b7280;
        }

        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 10px;
            color: #6b7280;
        }

        .mutation-arrow {
            font-weight: bold;
            color: #0284c7;
            margin: 0 5px;
        }

        .highlight {
            background-color: #fef3c7;
            padding: 1px 3px;
            border-radius: 3px;
        }

        .no-data {
            text-align: center;
            color: #9ca3af;
            font-style: italic;
            padding: 20px;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }

        .summary-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
        }

        .summary-title {
            font-size: 12px;
            font-weight: bold;
            color: #374151;
            margin-bottom: 10px;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 10px;
        }

        .summary-label {
            color: #6b7280;
        }

        .summary-value {
            font-weight: 500;
            color: #374151;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="company-info">
            <div class="company-name">SISTEM Manajemen Pegawai</div>
            <div class="company-subtitle">LAPORAN MUTASI Pegawai</div>
            <div class="company-address">
                Jl. Contoh No. 123, Jakarta 12345<br>
                Telp: (021) 1234-5678 | Email: hr@company.com
            </div>
        </div>
        <div class="report-title">LAPORAN MUTASI Pegawai</div>
        <div class="report-subtitle">
            Periode: Semua Data | Dibuat pada: {{ $generated_at }}
        </div>
    </div>

    <!-- Statistics Overview -->
    <div class="stats-container">
        <div class="stat-box">
            <div class="stat-number">{{ $data['total_mutations'] }}</div>
            <div class="stat-label">Total Mutasi</div>
        </div>
        <div class="stat-box">
            <div class="stat-number">{{ $data['mutation_stats']['department_change'] }}</div>
            <div class="stat-label">Mutasi Departemen</div>
        </div>
        <div class="stat-box">
            <div class="stat-number">{{ $data['mutation_stats']['position_change'] }}</div>
            <div class="stat-label">Mutasi Jabatan</div>
        </div>
        <div class="stat-box">
            <div class="stat-number">{{ $data['mutation_stats']['both_change'] }}</div>
            <div class="stat-label">Mutasi Lengkap</div>
        </div>
    </div>

    <!-- Summary Information -->
    <div class="summary-grid">
        <div class="summary-box">
            <div class="summary-title">Top 5 Departemen Tujuan</div>
            @if ($data['department_stats']->count() > 0)
                @foreach ($data['department_stats'] as $department => $count)
                    <div class="summary-item">
                        <span class="summary-label">{{ $department ?: 'Tidak Diketahui' }}</span>
                        <span class="summary-value">{{ $count }} mutasi</span>
                    </div>
                @endforeach
            @else
                <div class="no-data">Belum ada data</div>
            @endif
        </div>

        <div class="summary-box">
            <div class="summary-title">Mutasi Per Bulan (6 Bulan Terakhir)</div>
            @if ($data['monthly_stats']->count() > 0)
                @foreach ($data['monthly_stats'] as $month => $count)
                    <div class="summary-item">
                        <span
                            class="summary-label">{{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y') }}</span>
                        <span class="summary-value">{{ $count }} mutasi</span>
                    </div>
                @endforeach
            @else
                <div class="no-data">Belum ada data</div>
            @endif
        </div>
    </div>

    <!-- Detailed Mutation Table -->
    <div class="section-title">Detail Mutasi Pegawai</div>

    @if ($data['mutations']->count() > 0)
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 8%;">No.</th>
                    <th style="width: 12%;">Tgl Mutasi</th>
                    <th style="width: 15%;">Nama Pegawai</th>
                    <th style="width: 12%;">Nomor SK</th>
                    <th style="width: 25%;">Dari</th>
                    <th style="width: 25%;">Ke</th>
                    <th style="width: 8%;">Dokumen</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data['mutations'] as $index => $mutation)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-center">{{ $mutation->mutation_date->format('d/m/Y') }}</td>
                        <td>
                            <strong>{{ $mutation->employee?->name ?? 'N/A' }}</strong>
                            @if ($mutation->employee?->nippam)
                                <br><small style="color: #6b7280;">{{ $mutation->employee->nippam }}</small>
                            @endif
                        </td>
                        <td>{{ $mutation->decision_letter_number }}</td>
                        <td>
                            <div>
                                <strong>{{ $mutation->oldDepartment?->name ?? 'N/A' }}</strong>
                                @if ($mutation->oldSubDepartment)
                                    <br><small>{{ $mutation->oldSubDepartment->name }}</small>
                                @endif
                                <br><span class="badge badge-info">{{ $mutation->oldPosition?->name ?? 'N/A' }}</span>
                            </div>
                        </td>
                        <td>
                            <div>
                                <strong>{{ $mutation->newDepartment?->name ?? 'N/A' }}</strong>
                                @if ($mutation->newSubDepartment)
                                    <br><small>{{ $mutation->newSubDepartment->name }}</small>
                                @endif
                                <br><span
                                    class="badge badge-success">{{ $mutation->newPosition?->name ?? 'N/A' }}</span>
                            </div>
                        </td>
                        <td class="text-center">
                            @if ($mutation->docs)
                                <span class="badge badge-success">Ada</span>
                            @else
                                <span class="badge badge-gray">Tidak</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="no-data">
            Belum ada data mutasi Pegawai yang tersedia.
        </div>
    @endif

    <!-- Mutation Type Analysis -->
    @if ($data['mutations']->count() > 0)
        <div class="section-title">Analisis Jenis Mutasi</div>
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 40%;">Jenis Mutasi</th>
                    <th style="width: 20%;">Jumlah</th>
                    <th style="width: 20%;">Persentase</th>
                    <th style="width: 20%;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Mutasi Departemen Saja</td>
                    <td class="text-center">
                        {{ $data['mutation_stats']['department_change'] - $data['mutation_stats']['both_change'] }}
                    </td>
                    <td class="text-center">
                        {{ $data['total_mutations'] > 0 ? round((($data['mutation_stats']['department_change'] - $data['mutation_stats']['both_change']) / $data['total_mutations']) * 100, 1) : 0 }}%
                    </td>
                    <td>Pindah departemen, jabatan tetap</td>
                </tr>
                <tr>
                    <td>Mutasi Jabatan Saja</td>
                    <td class="text-center">
                        {{ $data['mutation_stats']['position_change'] - $data['mutation_stats']['both_change'] }}</td>
                    <td class="text-center">
                        {{ $data['total_mutations'] > 0 ? round((($data['mutation_stats']['position_change'] - $data['mutation_stats']['both_change']) / $data['total_mutations']) * 100, 1) : 0 }}%
                    </td>
                    <td>Promosi/demosi dalam departemen</td>
                </tr>
                <tr>
                    <td>Mutasi Lengkap</td>
                    <td class="text-center">{{ $data['mutation_stats']['both_change'] }}</td>
                    <td class="text-center">
                        {{ $data['total_mutations'] > 0 ? round(($data['mutation_stats']['both_change'] / $data['total_mutations']) * 100, 1) : 0 }}%
                    </td>
                    <td>Pindah departemen dan jabatan</td>
                </tr>
                <tr style="background-color: #f3f4f6; font-weight: bold;">
                    <td>Total</td>
                    <td class="text-center">{{ $data['total_mutations'] }}</td>
                    <td class="text-center">100%</td>
                    <td>Semua jenis mutasi</td>
                </tr>
            </tbody>
        </table>
    @endif

    <div class="footer">
        <div>
            <strong>Total Data:</strong> {{ $data['total_mutations'] }} mutasi Pegawai
        </div>
        <div>
            <strong>Digenerate:</strong> {{ $generated_at }}
        </div>
        <div>
            Halaman 1 dari 1
        </div>
    </div>
</body>

</html>

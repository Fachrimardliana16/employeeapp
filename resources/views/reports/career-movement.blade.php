<!DOCTYPE html>
<html>
<head>
    <title>{{ $title }}</title>
    <style>
        body { font-family: 'Arial', sans-serif; font-size: 11px; color: #333; margin: 0; padding: 0; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h1 { margin: 0; text-transform: uppercase; font-size: 18px; }
        .header p { margin: 5px 0 0; font-size: 12px; color: #666; }
        .info { margin-bottom: 15px; }
        .info table { width: 100%; }
        .info td { vertical-align: top; }
        table.main { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.main th, table.main td { border: 1px solid #999; padding: 8px; text-align: left; }
        table.main th { background-color: #f2f2f2; font-weight: bold; text-transform: uppercase; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: right; font-size: 9px; color: #888; border-top: 1px solid #ccc; padding-top: 5px; }
        .badge { padding: 3px 6px; border-radius: 4px; font-size: 9px; font-weight: bold; color: #fff; }
        .bg-success { background-color: #28a745; }
        .bg-warning { background-color: #ffc107; color: #000; }
        .text-right { text-align: right; }
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
    <div style="border-bottom: 1pt solid #000; margin-top: 2pt; margin-bottom: 15pt;"></div>

    <div class="info">
        <table>
            <tr>
                <td width="15%">Periode</td>
                <td width="2%">:</td>
                <td>{{ $startDate }} s.d {{ $endDate }}</td>
                <td width="15%" class="text-right">Dicetak Pada</td>
                <td width="2%">:</td>
                <td width="20%">{{ now()->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <td>Pegawai</td>
                <td>:</td>
                <td>{{ $employeeName }}</td>
            </tr>
        </table>
    </div>

    <table class="main">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Tanggal</th>
                <th width="20%">Pegawai / NIPPAM</th>
                @if($type === 'promotion')
                    <th>No. SK</th>
                    <th>Golongan Baru</th>
                @elseif($type === 'mutation')
                    <th>Asal Bagian</th>
                    <th>Tujuan Bagian</th>
                    <th>Jabatan Baru</th>
                @elseif($type === 'retirement')
                    <th>Jenis / Alasan</th>
                    <th>Status</th>
                @elseif($type === 'appointment')
                    <th>No. SK</th>
                    <th>Status Baru</th>
                @elseif($type === 'psi')
                    <th>No. SK</th>
                    <th>MKG Baru</th>
                    <th>Gaji Baru</th>
                @elseif($type === 'career_movement')
                    <th>Jenis</th>
                    <th>No. SK</th>
                    <th>Jabatan Baru</th>
                @endif
                <th width="10%">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $index => $row)
                <tr>
                    <td align="center">{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($row->$dateColumn)->format('d/m/Y') }}</td>
                    <td>
                        <strong>{{ $row->employee->name ?? '-' }}</strong><br>
                        <small>{{ $row->employee->nippam ?? '-' }}</small>
                    </td>
                    
                    @if($type === 'promotion')
                        <td>{{ $row->decision_letter_number ?? '-' }}</td>
                        <td>{{ $row->newGrade->name ?? '-' }}</td>
                    @elseif($type === 'mutation')
                        <td>{{ $row->oldDepartment->name ?? '-' }}</td>
                        <td>{{ $row->newDepartment->name ?? '-' }}</td>
                        <td>{{ $row->newPosition->name ?? '-' }}</td>
                    @elseif($type === 'retirement')
                        <td>{{ $row->reason ?? '-' }}</td>
                        <td>{{ $row->retirement_type ?? 'Pensiun' }}</td>
                    @elseif($type === 'appointment')
                        <td>{{ $row->decision_letter_number ?? '-' }}</td>
                        <td>{{ $row->newEmploymentStatus->name ?? '-' }}</td>
                    @elseif($type === 'psi')
                        <td>{{ $row->number_psi ?? '-' }}</td>
                        <td>{{ $row->newServiceGrade->service_grade ?? '-' }} Thn</td>
                        <td>Rp {{ number_format($row->total_basic_salary, 0, ',', '.') }}</td>
                    @elseif($type === 'career_movement')
                        <td>{{ $row->type === 'promotion' ? 'PROMOSI' : 'DEMOSI' }}</td>
                        <td>{{ $row->decision_letter_number ?? '-' }}</td>
                        <td>{{ $row->newPosition->name ?? '-' }}</td>
                    @endif

                    <td align="center">
                        @if($row->is_applied)
                            <span>Realisasi</span>
                        @else
                            <span>Usulan</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" align="center">Tidak ada data untuk periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Halaman 1 dari 1 | SIP - Sistem Informasi Pegawai
    </div>
</body>
</html>

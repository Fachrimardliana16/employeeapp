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

        .page-break {
            page-break-after: always;
        }

        .budget-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .budget-table th,
        .budget-table td {
            border: 1px solid #000;
            padding: 8px;
            font-size: 11px;
        }

        .budget-table th {
            background-color: #f5f5f5;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .signature-cell {
            height: 40px;
            width: 100px;
            position: relative;
        }
        
        .signature-line {
            position: absolute;
            bottom: 5px;
            left: 5px;
            right: 5px;
            border-bottom: 1px dotted #000;
        }
    </style>
</head>

<body>
    @php
        $startDate = \Carbon\Carbon::parse($travel->start_date);
        $endDate = \Carbon\Carbon::parse($travel->end_date);
        $duration = $startDate->diffInDays($endDate) + 1;

        $participants = collect();
        
        // 1. Ambil Pegawai Utama
        if ($travel->employee) {
            $participants->push([
                'id' => $travel->employee_id,
                'name' => $travel->employee->name,
                'position' => $travel->employee->position->name ?? '-',
                'pocket_money' => (float)($travel->pocket_money_cost ?: 0),
                'per_day' => (float)($travel->pocket_money_cost ?: 0) * $duration,
                'is_main' => true
            ]);
        }

        // 2. Ambil Pegawai Tambahan dari Detail Array
        if (!empty($travel->additional_employees_detail)) {
            $additionalEmployeeModels = \App\Models\Employee::whereIn('id', collect($travel->additional_employees_detail)->pluck('employee_id'))->with('position')->get();
            
            foreach ($travel->additional_employees_detail as $detail) {
                $empId = $detail['employee_id'] ?? null;
                if (!$empId) continue;
                
                $empModel = $additionalEmployeeModels->firstWhere('id', $empId);
                $participants->push([
                    'id' => $empId,
                    'name' => $empModel ? $empModel->name : 'Pegawai',
                    'position' => $empModel ? ($empModel->position->name ?? '-') : ($detail['position'] ?? '-'),
                    'pocket_money' => (float)($detail['pocket_money_cost'] ?? 0),
                    'per_day' => (float)($detail['pocket_money_cost'] ?? 0) * $duration,
                    'is_main' => false
                ]);
            }
        }
        
        $mainParticipant = $participants->firstWhere('is_main', true) ?? $participants->first();
    @endphp

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
            <tr>
                <td class="number">1.</td>
                <td class="label">NAMA</td>
                <td class="colon">:</td>
                <td>{{ $mainParticipant['name'] ?? 'Tim Perjalanan Dinas' }}</td>
            </tr>
            <tr>
                <td class="number">2.</td>
                <td class="label">JABATAN DAN PANGKAT</td>
                <td class="colon">:</td>
                <td>{{ $mainParticipant['position'] ?? '-' }}</td>
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
                    <td>{{ $startDate->translatedFormat('d F Y') }} s/d {{ $endDate->translatedFormat('d F Y') }}</td>
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
                    PERUMDAM AIR MINUM TIRTA PERWIRA
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
                    @php
                        $followers = $participants->filter(fn($p) => !$p['is_main']);
                    @endphp
                    @if ($followers->count() > 0)
                        <ol class="pengikut-list">
                            @foreach ($followers as $follower)
                                <li>{{ $follower['name'] }} ({{ $follower['position'] }})</li>
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

    <!-- Page Break -->
    <div class="page-break"></div>

    <!-- Halaman 2: Rincian Anggaran -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 5px;">
        <tr>
            <td style="width: 100px; vertical-align: middle; text-align: left;">
                @if($logoData)
                    <img src="data:image/png;base64,{{ $logoData }}" style="height: 60px; width: auto;">
                @endif
            </td>
            <td style="text-align: center; vertical-align: middle; padding-right: 50px;">
                <h2 style="margin: 0; font-size: 11pt; text-transform: uppercase; font-weight: bold;">RINCIAN ANGGARAN BIAYA PERJALANAN DINAS</h2>
                <p style="margin: 2pt 0; font-size: 9pt;">Nomor: {{ $travel->registration_number }}</p>
            </td>
        </tr>
    </table>
    <div style="border-bottom: 2pt solid #000; margin-top: 2pt; margin-bottom: 10pt;"></div>

    <table class="detail-table" style="font-size: 10pt; margin-bottom: 15px;">
        <tr>
            <td style="width: 120px;">Maksud Perjalanan</td>
            <td style="width: 15px;">:</td>
            <td>{{ $travel->purpose_of_trip }}</td>
        </tr>
        <tr>
            <td>Tujuan</td>
            <td>:</td>
            <td>{{ $travel->destination }}</td>
        </tr>
        <tr>
            <td>Waktu</td>
            <td>:</td>
            <td>{{ $duration }} hari ({{ $startDate->translatedFormat('d F Y') }} s/d {{ $endDate->translatedFormat('d F Y') }})</td>
        </tr>
    </table>

    <table class="budget-table">
        <thead>
            <tr>
                <th style="width: 5%;">NO</th>
                <th style="width: 35%;">URAIAN / JENIS BIAYA</th>
                <th style="width: 20%;">PERHITUNGAN</th>
                <th style="width: 20%;">JUMLAH (Rp)</th>
                <th style="width: 20%;">TANDA TANGAN</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="5" style="background-color: #f9f9f9; font-weight: bold;">1. UANG SAKU</td>
            </tr>
            @php $no = 1; $totalPocket = 0; @endphp
            @foreach($participants as $index => $participant)
                @php $totalPocket += $participant['per_day']; @endphp
                <tr>
                    <td class="text-center">{{ $no++ }}</td>
                    <td>{{ $participant['name'] }}<br><small>({{ $participant['position'] }})</small></td>
                    <td class="text-center">{{ number_format($participant['pocket_money'], 0, ',', '.') }} x {{ $duration }} hr</td>
                    <td class="text-right">{{ number_format($participant['per_day'], 0, ',', '.') }}</td>
                    <td class="signature-cell">
                        <div style="font-size: 8pt; margin-bottom: 15px;">{{ $no - 1 }}.</div>
                        <div class="signature-line"></div>
                    </td>
                </tr>
            @endforeach

            <tr>
                <td colspan="5">&nbsp;</td> <!-- Baris Kosong Pemisah -->
            </tr>

            <tr>
                <td class="text-center">{{ $no++ }}</td>
                <td colspan="2">2. BIAYA AKOMODASI / PENGINAPAN (TOTAL)</td>
                <td class="text-right">{{ number_format($travel->accommodation_cost, 0, ',', '.') }}</td>
                <td></td>
            </tr>

            <tr>
                <td class="text-center">{{ $no++ }}</td>
                <td colspan="2">3. UANG CADANGAN (TOTAL)</td>
                <td class="text-right">{{ number_format($travel->reserve_cost, 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" class="text-right" style="font-size: 12pt;">JUMLAH TOTAL</th>
                <th class="text-right" style="font-size: 12pt;">Rp {{ number_format($travel->total_cost, 0, ',', '.') }}</th>
                <th></th>
            </tr>
        </tfoot>
    </table>

    <p style="font-size: 10pt; font-style: italic; margin-top: 10px;">Terbilang: <strong>{{ \App\Support\NumberToWords::convert($travel->total_cost) }} Rupiah</strong></p>

    <!-- Signature for Page 2 -->
    <div class="signature" style="margin-top: 30px;">
        <table style="width: 100%;">
            <tr>
                <td style="width: 50%;"></td>
                <td style="width: 50%; text-align: center;">
                    <p style="margin-bottom: 5pt;">Purbalingga, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                    <p style="font-weight: bold; text-transform: uppercase; margin: 0;">Mengetahui/Menyetujui,</p>
                    
                    <div style="height: 50pt;"></div>
                    
                    <div style="border-bottom: 1pt solid #000; width: 180pt; margin: 0 auto;">
                        <p style="font-weight: bold; margin: 0; padding-bottom: 2pt;">{{ $signatory_name ?? 'Direktur Utama' }}</p>
                    </div>
                    <p style="font-size: 8pt; text-transform: uppercase; margin-top: 2pt;">{{ $signatory_position ?? 'Direktur Utama' }}</p>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>

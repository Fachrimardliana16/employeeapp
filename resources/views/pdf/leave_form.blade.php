<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>FORMULIR PERMOHONAN DAN PEMBERIAN CUTI - {{ $employee->name }}</title>
    <style>
        @page {
            margin: 0.5cm 1.3cm; /* Atas/bawah 0.5cm, Kiri/Kanan 1.3cm (Sesuai Permintaan) */
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9pt;
            line-height: 1.15;
            color: #000;
            margin: 0;
            padding: 0;
        }
        .watermark {
            position: fixed;
            top: 25%;
            left: 0;
            width: 100%;
            text-align: center;
            opacity: 0.15;
            z-index: -1000;
        }
        .watermark img {
            width: 70%;
        }
        .header-right {
            float: right;
            width: 210px;
            text-align: left;
            margin-bottom: 5px; /* Margin diperkecil */
            font-size: 9pt;
            line-height: 1.15;
        }
        .title {
            clear: both;
            text-align: center;
            font-weight: bold;
            font-size: 10.5pt;
            margin-top: 5px;
            margin-bottom: 8px;
            text-decoration: underline;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid black;
            padding: 3px 5px; /* Sedikit dikecilkan agar memastikan 1 halaman */
            vertical-align: middle;
        }
        .section-title {
            font-weight: bold;
            text-align: left;
            padding: 3px 5px;
        }
        /* No border bottom for consecutive sections */
        .bb-none { border-bottom: none !important; }
        .bt-none { border-top: none !important; }
        .bl-none { border-left: none !important; }
        .br-none { border-right: none !important; }
        
        .text-center { text-align: center; }
        .bold { font-weight: bold; }
        .small { font-size: 8pt; line-height: 1.1; }
        
        /* Checkbox */
        .check-td {
            width: 5%;
            text-align: center;
            font-weight: bold;
            font-family: 'DejaVu Sans', sans-serif;
            line-height: 1;
            padding: 0;
            font-size: 11pt;
        }
    </style>
</head>
<body>
    @php
        $logoPath = base_path('01KCHAZQ0KD21RMDA3D9BDKMHC.png');
        $logoData = "";
        if (file_exists($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
        }
        $checkEmoji = "&#10003;"; 
        
        $permissionName = strtolower($permission->permission->name ?? '');
        $isTahunan = str_contains($permissionName, 'tahunan');
        $isBesar = str_contains($permissionName, 'besar');
        $isSakit = str_contains($permissionName, 'sakit');
        $isMelahirkan = str_contains($permissionName, 'lahir');
        $isPenting = str_contains($permissionName, 'penting');
        $isLuarNegara = str_contains($permissionName, 'luar tanggungan');

        $start = \Carbon\Carbon::parse($permission->start_permission_date);
        $end = \Carbon\Carbon::parse($permission->end_permission_date);
        $diff = $start->diffInDays($end) + 1;
    @endphp
    
    @if($logoData)
        <div class="watermark">
            <img src="data:image/png;base64,{{ $logoData }}">
        </div>
    @endif

    <!-- Header Right -->
    <div class="header-right">
        Purbalingga, {{ $date_generated }}<br>
        Kepada Yth. Direksi Perumda Air Minum<br>
        Tirta Perwira Purbalingga<br>
        di-<br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Purbalingga,-
    </div>

    <div class="title">FORMULIR PERMOHONAN DAN PEMBERIAN CUTI</div>

    <!-- MAIN WRAPPER TABLE to fix border doubling and column widths -->
    <table style="border: 2px solid black;">
        <tr>
            <td style="padding: 0; border: none;">
                
                <!-- I. DATA PEGAWAI -->
                <table class="bb-none" style="border: none;">
                    <tr><td colspan="4" class="section-title">I. DATA PEGAWAI</td></tr>
                    <tr>
                        <td style="width: 15%; border-left: none;">Nama</td>
                        <td style="width: 35%;">{{ $employee->name }}</td>
                        <td style="width: 15%;">NIPPAM</td>
                        <td style="width: 35%; border-right: none;">{{ $employee->nippam }}</td>
                    </tr>
                    <tr>
                        <td style="border-left: none;">Jabatan</td>
                        <td>{{ $employee->position->name ?? '-' }}</td>
                        <td>Masa Kerja</td>
                        <td style="border-right: none;">{{ $employee->formatted_length_service }}</td>
                    </tr>
                    <tr>
                        <td style="border-left: none; border-bottom: none;">Unit Kerja</td>
                        <td colspan="3" style="border-right: none; border-bottom: none;">{{ optional($employee->active_organizational_unit)->name ?? '-' }}</td>
                    </tr>
                </table>

            </td>
        </tr>
        <tr>
            <td style="padding: 0; border: none; border-top: 1px solid black;">

                <!-- II. JENIS CUTI -->
                <table class="bb-none bt-none" style="border: none;">
                    <tr><td colspan="4" class="section-title" style="border-top: none; border-left: none; border-right: none;">II. JENIS CUTI YANG DIAMBIL**</td></tr>
                    <tr>
                        <td style="width: 45%; border-left: none;">1. Cuti Tahunan</td>
                        <td class="check-td">{!! $isTahunan ? $checkEmoji : '' !!}</td>
                        <td style="width: 42%;">2. Cuti Besar</td>
                        <td class="check-td" style="border-right: none;">{!! $isBesar ? $checkEmoji : '' !!}</td>
                    </tr>
                    <tr>
                        <td style="border-left: none;">3. Cuti Sakit</td>
                        <td class="check-td">{!! $isSakit ? $checkEmoji : '' !!}</td>
                        <td>4. Cuti Melahirkan</td>
                        <td class="check-td" style="border-right: none;">{!! $isMelahirkan ? $checkEmoji : '' !!}</td>
                    </tr>
                    <tr>
                        <td style="border-left: none; border-bottom: none;">5. Cuti Karena Alasan Penting</td>
                        <td class="check-td" style="border-bottom: none;">{!! $isPenting ? $checkEmoji : '' !!}</td>
                        <td style="border-bottom: none;">6. Cuti di Luar Tanggungan Negara</td>
                        <td class="check-td" style="border-right: none; border-bottom: none;">{!! $isLuarNegara ? $checkEmoji : '' !!}</td>
                    </tr>
                </table>

            </td>
        </tr>
        <tr>
            <td style="padding: 0; border: none; border-top: 1px solid black;">

                <!-- III. ALASAN CUTI -->
                <table class="bb-none bt-none" style="border: none;">
                    <tr><td class="section-title" style="border-top: none; border-left: none; border-right: none;">III. ALASAN CUTI</td></tr>
                    <tr><td style="height: 30px; border-bottom: none; border-left: none; border-right: none; vertical-align: top;">{{ $permission->permission_desc }}</td></tr>
                </table>

            </td>
        </tr>
        <tr>
            <td style="padding: 0; border: none; border-top: 1px solid black;">

                <!-- IV. LAMANYA CUTI -->
                <table class="bb-none bt-none" style="border: none;">
                    <tr><td colspan="6" class="section-title" style="border-top: none; border-left: none; border-right: none;">IV. LAMANYA CUTI</td></tr>
                    <tr>
                        <td style="width: 10%; border-bottom: none; border-left: none;">Selama</td>
                        <td style="width: 15%; border-bottom: none;" class="text-center bold">{{ $diff }} hari kerja</td>
                        <td style="width: 15%; border-bottom: none;">Mulai tanggal</td>
                        <td style="width: 25%; border-bottom: none;" class="text-center bold">{{ $start->translatedFormat('d M Y') }}</td>
                        <td style="width: 10%; border-bottom: none;">s/d</td>
                        <td style="width: 25%; border-bottom: none; border-right: none;" class="text-center bold">{{ $end->translatedFormat('d M Y') }}</td>
                    </tr>
                </table>

            </td>
        </tr>
        <tr>
            <td style="padding: 0; border: none; border-top: 1px solid black;">

                <!-- V. CATATAN CUTI & SIGNATURE ROW -->
                <table class="bb-none bt-none" style="border: none;">
                    <tr><td colspan="6" class="section-title" style="border-top: none; border-left: none; border-right: none;">V. CATATAN CUTI***</td></tr>
                    <tr class="text-center bold">
                        <td colspan="3" style="border-left: none; text-align: left;">1. CUTI TAHUNAN</td>
                        <td style="text-align: left; width: 35%;">2. CUTI BESAR</td>
                        <td colspan="2" style="border-right: none; width: 25%;"></td>
                    </tr>
                    <tr class="text-center small bold">
                        <td style="width: 10%; border-left: none;">Tahun</td>
                        <td style="width: 10%;">Sisa</td>
                        <td style="width: 20%;">Keterangan</td>
                        <td style="text-align: left;">3. CUTI SAKIT</td>
                        <td colspan="2" style="border-right: none;"></td>
                    </tr>
                    <tr class="text-center">
                        <td style="border-left: none;">{{ date('Y') }}</td>
                        <td>{{ $employee->remaining_leave_balance }}</td>
                        <td></td>
                        <td style="text-align: left;">4. CUTI MELAHIRKAN</td>
                        <td colspan="2" style="border-right: none;"></td>
                    </tr>
                    <tr class="text-center">
                        <td style="border-left: none;">{{ date('Y') - 1 }}</td>
                        <td>-</td>
                        <td></td>
                        <td style="text-align: left;">5. CUTI KARENA ALASAN PENTING</td>
                        <td colspan="2" style="border-right: none;"></td>
                    </tr>
                    <tr class="text-center">
                        <td style="border-left: none;">{{ date('Y') - 2 }}</td>
                        <td>-</td>
                        <td></td>
                        <td style="text-align: left;">6. CUTI DI LUAR TANGGUNGAN NEGARA</td>
                        <td colspan="2" style="border-right: none;"></td>
                    </tr>
                    
                    <!-- ROW TELP -->
                    <tr class="text-center bold">
                        <td style="border-left: none;"></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td style="width: 10%;">TELP</td>
                        <td style="width: 15%; border-right: none;">{{ $employee->phone_number ?? '-' }}</td>
                    </tr>

                    <!-- Signature Row -->
                    <tr>
                        <td colspan="4" style="border-left: none; border-bottom: none; border-top: none; vertical-align: bottom; padding: 10px; text-align: left;">
                            {{ $employee->address ?? 'XXXXXX' }}
                        </td>
                        <td colspan="2" style="border-right: none; border-bottom: none; border-top: none; text-align: center; vertical-align: top; padding: 10px;">
                            Hormat saya,<br><br><br>
                            <span class="bold" style="text-decoration: underline;">{{ $employee->name }}</span><br>
                            NIPPAM. {{ $employee->nippam }}
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
        <tr>
            <td style="padding: 0; border: none; border-top: 2px solid black;">

                <!-- VII. PERTIMBANGAN ATASAN -->
                <table class="bb-none bt-none" style="border: none;">
                    <tr><td colspan="4" class="section-title" style="border-top: none; border-left: none; border-right: none;">VII. PERTIMBANGAN ATASAN LANGSUNG DAN BAGIAN UMUM</td></tr>
                    <tr class="text-center small bold">
                        <td style="width: 25%; border-left: none;">DISETUJUI</td>
                        <td style="width: 25%;">PERUBAHAN****</td>
                        <td style="width: 25%;">DITANGGUHKAN****</td>
                        <td style="width: 25%; border-right: none;">TIDAK DISETUJUI****</td>
                    </tr>
                    
                    <!-- Checklist Row -->
                    <tr>
                        <td style="height: 15px; border-left: none;"></td>
                        <td></td>
                        <td></td>
                        <td style="border-right: none;"></td>
                    </tr>

                    <!-- Signatures -->
                    <tr>
                        <td style="height: 45px; border-left: none;"></td>
                        <td></td>
                        <td></td>
                        <td style="border-right: none; text-align: center; vertical-align: top; padding-top: 5px;">
                            Kepala. .........................<br><br><br>
                            <span class="bold"><u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u></span><br>
                            NIPPAM. .........................
                        </td>
                    </tr>
                    <tr>
                        <td style="height: 45px; border-bottom: none; border-left: none;"></td>
                        <td style="border-bottom: none;"></td>
                        <td style="border-bottom: none;"></td>
                        <td style="border-bottom: none; border-right: none; text-align: center; vertical-align: top; padding-top: 5px;">
                            Kepala Bagian/Bagian Umum<br><br><br>
                            <span class="bold"><u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u></span><br>
                            NIPPAM. .........................
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
        <tr>
            <td style="padding: 0; border: none; border-top: 2px solid black;">

                <!-- VIII. KEPUTUSAN PEJABAT -->
                <table class="bb-none bt-none" style="border: none;">
                    <tr><td colspan="4" class="section-title" style="border-top: none; border-left: none; border-right: none;">VIII. KEPUTUSAN PEJABAT YANG BERWENANG MEMBERIKAN CUTI</td></tr>
                    <tr class="text-center small bold">
                        <td style="width: 25%; border-left: none;">DISETUJUI</td>
                        <td style="width: 25%;">PERUBAHAN****</td>
                        <td style="width: 25%;">DITANGGUHKAN****</td>
                        <td style="width: 25%; border-right: none;">TIDAK DISETUJUI****</td>
                    </tr>
                    
                    <!-- Checklist Row -->
                    <tr>
                        <td style="height: 15px; border-left: none;"></td>
                        <td></td>
                        <td></td>
                        <td style="border-right: none;"></td>
                    </tr>

                    <!-- Signatures & Catatan -->
                    <tr>
                        <!-- Catatan : No left border, no bottom border, no right border (merged) -->
                        <td colspan="3" style="vertical-align: top; border-bottom: none; border-left: none; border-right: none; padding-right: 15px;">
                            <br>
                            <span class="bold">Catatan :</span><br>
                            Atasan langsung Bertanggung Jawab/<br>
                            Mengkoordinasikan Pekerjaan yang ditinggalkan<br>
                            permohonan cuti
                        </td>
                        <td style="text-align: center; vertical-align: top; padding-top: 10px; border-bottom: none; border-right: none; border-left: 1px solid black;">
                            Direksi<br><br><br>
                            <span class="bold" style="text-decoration: underline;">Sugeng, S.T.</span><br>
                            NIPPAM. 201200021
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>

    <!-- Footer Notes -->
    <div class="small" style="margin-top: 8px; line-height: 1.2;">
        * Coret yang tidak perlu<br>
        ** Pilih salah satu dengan memberikan tanda centang (v)<br>
        *** Diisi oleh pejabat yang menangani bidang kepegawaian sebelum Karyawan/ti mengajukan cuti<br>
        **** Diberikan catatan alasannya.
    </div>

</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ANALISA KEHADIRAN PEGAWAI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            @page { size: landscape; margin: 0.5cm; }
            .no-print { display: none !important; }
            body { background: white; padding: 0; }
            .report-container { box-shadow: none !important; border: none !important; padding: 0 !important; max-width: none !important; width: 100% !important; }
            table { border-collapse: collapse; width: 100%; border: 1.5px solid black; }
            th, td { border: 1px solid black !important; padding: 4px 2px !important; font-size: 9px !important; line-height: 1.1; }
            th { background-color: #f0f0f0 !important; color: black !important; font-weight: bold; text-transform: uppercase; }
        }
        .signature-space { height: 60px; }
        table { border-collapse: collapse; }
        th, td { border: 1px solid black; }
    </style>
</head>
<body class="bg-slate-50 p-4 md:p-6 antialiased font-sans">
    <div class="report-container max-w-[297mm] mx-auto bg-white p-6 md:p-10 shadow-sm border border-slate-200 text-black">
        
        <!-- Professional KOP (Letterhead) -->
        <div class="flex items-center justify-center gap-6 mb-2">
            <div class="flex-shrink-0">
                <img src="{{ asset('assets/images/logo-pdam.png') }}" class="h-20 w-auto object-contain">
            </div>
            <div class="text-center">
                <h1 class="text-lg font-bold uppercase leading-tight">PERUSAHAAN UMUM DAERAH AIR MINUM</h1>
                <h1 class="text-2xl font-black uppercase leading-none tracking-tight">TIRTA PERWIRA</h1>
                <h2 class="text-lg font-bold uppercase leading-tight tracking-wide">KABUPATEN PURBALINGGA</h2>
                <p class="text-[10px] mt-1 italic">Jl. Let. Jend. S.Parman No. 62 Kedung Menjangan. Purbalingga (53316).</p>
            </div>
        </div>

        <!-- Heavy Divider Line -->
        <div class="border-b-[3px] border-black mb-0.5"></div>
        <div class="border-b border-black mb-6"></div>

        <!-- Document Title -->
        <div class="text-center mb-6">
            <h3 class="text-base font-bold uppercase decoration-1 underline underline-offset-4">LAPORAN ANALISA & PERSENTASE KEHADIRAN PEGAWAI</h3>
            <p class="text-xs mt-1">Periode: {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}</p>
        </div>

        <!-- Summary Context -->
        <div class="mb-4 flex justify-between text-[10px]">
            <div class="flex flex-col gap-1">
                <div class="flex gap-1">
                    <span class="font-bold w-32">Pegawai</span>
                    <span>: {{ $singleEmployee ?: 'Semua Pegawai' }}</span>
                </div>
                <div class="flex gap-1">
                    <span class="font-bold w-32">Total Hari Kalender</span>
                    <span>: {{ \Carbon\Carbon::parse($startDate)->diffInDays(\Carbon\Carbon::parse($endDate)) + 1 }} Hari</span>
                </div>
            </div>
            <div class="flex flex-col gap-1 text-right">
                <div class="flex gap-1 justify-end">
                    <span class="font-bold">Total Hari Kerja Aktif</span>
                    <span class="font-bold">: {{ $totalWorkingDays }} Hari</span>
                </div>
                <div class="text-[9px] italic text-gray-500">* Persentase dihitung berdasarkan Hari Kerja Efektif (Hari Kerja - Izin/Cuti)</div>
            </div>
        </div>

        <!-- Attendance Summary Table -->
        <!-- Summary Table -->
        <div class="overflow-x-auto mb-8">
            <table class="w-full text-center border-collapse">
                <thead>
                    <tr class="bg-gray-100 text-[9px] font-bold">
                        <th class="py-2 px-1 w-8 border" rowspan="2">NO</th>
                        <th class="py-2 px-2 text-left border" rowspan="2">NAMA PEGAWAI</th>
                        <th class="py-2 px-1 w-16 bg-blue-50 border" rowspan="2">KERJA<br>EFEKTIF</th>
                        <th class="py-2 px-1 border" colspan="4">REKAPITULASI KEHADIRAN (JUMLAH HARI)</th>
                    </tr>
                    <tr class="bg-gray-50 text-[8px] font-bold">
                        <th class="py-1 w-16 border">1. HADIR</th>
                        <th class="py-1 w-16 border">2. ABSEN</th>
                        <th class="py-1 w-16 border">3. TERLAMBAT</th>
                        <th class="py-1 w-16 border text-orange-700">4. PULANG CEPAT</th>
                    </tr>
                </thead>
                <tbody class="text-[9px]">
                    @foreach($summaries as $index => $summary)
                    <tr class="hover:bg-slate-50 border">
                        <td class="py-1.5 px-1 border">{{ $index + 1 }}</td>
                        <td class="py-1.5 px-2 text-left border">
                            <div class="font-bold leading-none text-slate-900">{{ $summary->employee->name }}</div>
                            <div class="text-[7px] text-slate-500 font-normal mt-1 uppercase tracking-tighter leading-tight">
                                <span class="font-bold text-slate-700">PIN:</span> {{ $summary->employee->pin ?: '-' }} | 
                                <span class="font-bold text-slate-700">JABATAN:</span> {{ $summary->employee->position->name ?? '-' }} | 
                                <span class="font-bold text-slate-700">STATUS:</span> {{ $summary->employee->employmentStatus->name ?? '-' }} | 
                                <span class="font-bold text-slate-700">BAGIAN:</span> {{ $summary->employee->department->name ?? '-' }} | 
                                <span class="font-bold text-slate-700">SUB BAGIAN:</span> {{ $summary->employee->subDepartment->name ?? '-' }}
                            </div>
                        </td>
                        <td class="py-1.5 px-1 bg-blue-50/30 border">{{ $summary->effective_working_days }}</td>
                        <td class="py-1.5 px-1 border">{{ $summary->present }}</td>
                        <td class="py-1.5 px-1 border text-red-600 font-bold">{{ $summary->absent }}</td>
                        <td class="py-1.5 px-1 border text-amber-600 font-bold">{{ $summary->late }}</td>
                        <td class="py-1.5 px-1 border text-orange-600 font-bold">{{ $summary->early }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Summary Footer & Signature -->
        <div class="flex justify-between items-start mt-8">
            <div class="text-[8px] italic text-gray-400 space-y-0.5">
                <p>Keterangan:</p>
                <ul class="list-disc pl-3">
                    <li>Hadir: Wajib memiliki data scan MASUK dan KELUAR lengkap</li>
                    <li>Absen: Tidak ada data absensi pada hari kerja aktif / libur tidak terjadwal</li>
                    <li>Terlambat: Absen masuk melewati batas waktu toleransi (Late Threshold)</li>
                    <li>Pulang Cepat: Absen keluar sebelum waktu yang ditentukan dalam jadwal</li>
                </ul>
                <p class="mt-2 text-[7px]">Dicetak pada: {{ now()->translatedFormat('l, d F Y | H:i:s') }} WIB</p>
            </div>
            <div class="w-56 text-center">
                <p class="text-[10px] mb-0.5">Purbalingga, {{ now()->translatedFormat('d F Y') }}</p>
                <p class="text-[10px] font-bold uppercase mb-0.5">Mengetahui/Menyetujui,</p>
                <p class="text-[10px] font-bold uppercase italic border-b border-black inline-block px-3">Pejabat Berwenang</p>
                <div class="signature-space"></div>
                <div class="border-b border-black w-40 mx-auto mb-0.5"></div>
                <p class="text-[8px] text-gray-400 uppercase tracking-widest leading-none">NIP / Tanda Tangan</p>
            </div>
        </div>

        <!-- Floating Controls (No-Print) -->
        <div class="fixed bottom-6 right-6 flex gap-2 no-print">
            <button onclick="window.print()" class="bg-black text-white px-6 py-2.5 rounded shadow-xl font-bold flex items-center gap-2 hover:bg-slate-800 transition transform hover:-translate-y-0.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                CETAK PDF
            </button>
            <button onclick="window.close()" class="bg-white text-slate-500 px-4 py-2.5 rounded shadow font-bold border border-slate-200 hover:bg-slate-50 transition">
                TUTUP
            </button>
        </div>
    </div>
    <script>
        window.onload = function() {
            // Uncomment line below if you want automated print on load
            // window.print();
        };
    </script>
</body>
</html>

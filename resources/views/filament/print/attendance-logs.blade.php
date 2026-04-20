<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REKAP LOG ABSENSI PEGAWAI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            @page { size: landscape; margin: 0.5cm; }
            .no-print { display: none !important; }
            body { background: white; padding: 0; }
            .report-container { box-shadow: none !important; border: none !important; padding: 0 !important; max-width: none !important; width: 100% !important; }
            table { border-collapse: collapse; width: 100%; border: 1.5px solid black; }
            th, td { border: 1px solid black !important; padding: 4px 6px !important; font-size: 10px !important; line-height: 1.2; }
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
                <img src="{{ asset('assets/images/logo-pterm.png') }}" class="h-20 w-auto object-contain" onerror="this.src='{{ asset('assets/images/logo-pdam.png') }}'">
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
            <h3 class="text-base font-bold uppercase decoration-1 underline underline-offset-4">REKAPITULASI LOG ABSENSI MESIN (DATA MENTAH)</h3>
            <p class="text-xs mt-1">Periode: {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}</p>
        </div>

        <!-- Summary Context -->
        <div class="mb-4 grid grid-cols-2 text-[10px]">
            <div class="space-y-1">
                <div class="flex gap-1">
                    <span class="font-bold w-24">Filter Pegawai</span>
                    <span>: {{ $singleEmployee ?: 'Semua Pegawai' }}</span>
                </div>
                <div class="flex gap-1">
                    <span class="font-bold w-24">Filter Mesin</span>
                    <span>: {{ $singleMachine ?: 'Semua Mesin' }}</span>
                </div>
            </div>
            <div class="text-right space-y-1">
                <div class="flex gap-1 justify-end">
                    <span class="font-bold">Total Rekaman</span>
                    <span>: {{ count($records) }} Log</span>
                </div>
                <div class="text-[9px] italic text-gray-500">Dicetak melalui Sistem Informasi Kepegawaian (SIP)</div>
            </div>
        </div>

        <!-- Log Table -->
        <div class="overflow-x-auto mb-8">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 text-[10px] font-bold">
                        <th class="px-2 py-1 border border-slate-300 w-12">NO</th>
                        <th class="px-2 py-1 border border-slate-300">TANGGAL</th>
                        <th class="px-2 py-1 border border-slate-300">HARI</th>
                        <th class="px-2 py-1 border border-slate-300">PIN</th>
                        <th class="px-2 py-1 border border-slate-300 text-left">NAMA PEGAWAI</th>
                        <th class="px-2 py-1 border border-slate-300">JAM</th>
                        <th class="px-2 py-1 border border-slate-300">TIPE LOG</th>
                        <th class="px-2 py-1 border border-slate-300">LOKASI/MESIN</th>
                        <th class="px-2 py-1 border border-slate-300">KET.</th>
                    </tr>
                </thead>
                <tbody class="text-[10px]">
                            <div class="text-[8px] text-gray-400 capitalize">{{ $record->machine?->officeLocation?->name ?? '-' }}</div>
                        </td>
                        <td class="py-2 px-2 text-center">
                            @php
                                $typeLabel = match ($record->type) {
                                    '0' => 'Masuk',
                                    '1' => 'Keluar',
                                    '2' => 'Break Out',
                                    '3' => 'Break In',
                                    '4' => 'Overtime In',
                                    '5' => 'Overtime Out',
                                    default => 'Tipe ' . $record->type,
                                };
                                $colorClass = match ($record->type) {
                                    '0', '3', '4' => 'text-emerald-700 font-bold',
                                    '1', '2', '5' => 'text-red-700 font-bold',
                                    default => 'text-gray-600',
                                };
                            @endphp
                            <span class="{{ $colorClass }} uppercase text-[8px] border border-current px-1 rounded-sm">
                                {{ $typeLabel }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center italic text-gray-400">Tidak ada data log absensi yang ditemukan untuk kriteria ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Summary Footer & Signature -->
        <div class="flex justify-between items-start mt-8">
            <div class="text-[9px] italic text-gray-400">
                <p>Keterangan:</p>
                <ul class="list-disc pl-3">
                    <li>Data di atas adalah rekaman mentah (Raw Log) dari mesin absensi.</li>
                    <li>Waktu yang ditampilkan adalah waktu saat jari/kartu ditempelkan ke mesin.</li>
                </ul>
                <p class="mt-4 text-[8px]">Dicetak oleh: {{ auth()->user()->name }} | {{ now()->translatedFormat('d/m/Y H:i:s') }}</p>
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
            // window.print();
        };
    </script>
</body>
</html>

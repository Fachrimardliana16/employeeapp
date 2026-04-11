<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REKAPITULASI KEHADIRAN PEGAWAI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white; padding: 0; }
            .report-container { box-shadow: none !important; border: none !important; padding: 0 !important; max-width: none !important; }
            table { border-collapse: collapse; width: 100%; border: 1px solid black; }
            th, td { border: 1px solid black !important; padding: 6px !important; font-size: 10px !important; }
            th { background-color: #f3f4f6 !important; color: black !important; font-weight: bold; text-transform: uppercase; }
        }
        .signature-space { height: 80px; }
    </style>
</head>
<body class="bg-slate-100 p-4 md:p-10 antialiased font-sans">
    <div class="report-container max-w-5xl mx-auto bg-white p-8 md:p-12 shadow-sm border border-slate-200 relative overflow-hidden text-black">
        
        <!-- Professional KOP (Letterhead) -->
        <div class="flex items-center justify-center gap-8 mb-2">
            <div class="flex-shrink-0">
                <img src="{{ asset('assets/images/logo-pdam.png') }}" class="h-28 w-auto object-contain">
            </div>
            <div class="text-center">
                <h1 class="text-xl font-bold uppercase leading-tight">PERUSAHAAN UMUM DAERAH AIR MINUM</h1>
                <h1 class="text-3xl font-black uppercase leading-none tracking-tighter">TIRTA PERWIRA</h1>
                <h2 class="text-xl font-bold uppercase leading-tight tracking-wide">KABUPATEN PURBALINGGA</h2>
                <p class="text-sm mt-2 italic">Jl. Let. Jend. S.Parman No. 62 Kedung Menjangan. Purbalingga (53316).</p>
            </div>
        </div>

        <!-- Heavy Divider Line -->
        <div class="border-b-4 border-black mb-1"></div>
        <div class="border-b border-black mb-8"></div>

        <!-- Document Title -->
        <div class="text-center mb-8">
            <h3 class="text-lg font-bold uppercase decoration-1 underline underline-offset-4">LAPORAN REKAPITULASI KEHADIRAN PEGAWAI</h3>
            <p class="text-sm mt-1">Periode: {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}</p>
        </div>

        <!-- Summary Context -->
        <div class="mb-6 grid grid-cols-2 gap-4 text-xs">
            <div class="flex gap-2">
                <span class="font-bold w-24">Pegawai</span>
                <span>: {{ $employeeName ?? 'Semua Pegawai' }}</span>
            </div>
            <div class="flex gap-2 text-right justify-end">
                <span class="font-bold w-24 text-right">Lokasi Kantor</span>
                <span class="text-right">: {{ $locationName ?? 'Semua Lokasi' }}</span>
            </div>
        </div>

        <!-- Attendance Table -->
        <div class="overflow-x-auto mb-10">
            <table class="w-full text-left border-collapse border border-black">
                <thead>
                    <tr class="bg-gray-100 text-[10px] font-bold">
                        <th class="py-2 px-2 border border-black text-center w-10">NO</th>
                        <th class="py-2 px-2 border border-black">TANGGAL & WAKTU</th>
                        <th class="py-2 px-2 border border-black">PEGAWAI (PIN)</th>
                        <th class="py-2 px-2 border border-black text-center">STATUS</th>
                        <th class="py-2 px-2 border border-black text-center">KETEPATAN</th>
                        <th class="py-2 px-2 border border-black">LOKASI</th>
                        <th class="py-2 px-2 border border-black text-center">JARAK (M)</th>
                    </tr>
                </thead>
                <tbody class="text-[10px]">
                    @forelse($records as $index => $record)
                    <tr class="border-b border-black">
                        <td class="py-2 px-2 border-x border-black text-center">{{ $index + 1 }}</td>
                        <td class="py-2 px-2 border-x border-black font-semibold">
                            {{ \Carbon\Carbon::parse($record->attendance_time)->translatedFormat('d/m/Y') }}
                            <span class="block font-normal text-gray-500">{{ \Carbon\Carbon::parse($record->attendance_time)->format('H:i') }} WIB</span>
                        </td>
                        <td class="py-2 px-2 border-x border-black">
                            <span class="font-bold border-b border-dotted border-gray-300">{{ $record->employee_name }}</span>
                            <span class="block text-gray-400">PIN: {{ $record->pin }}</span>
                        </td>
                        <td class="py-2 px-2 border-x border-black text-center uppercase font-bold">
                            {{ in_array($record->state, ['in', 'check_in', 'ot_in']) ? 'Masuk' : 'Keluar' }}
                        </td>
                        <td class="py-2 px-2 border-x border-black text-center font-bold">
                            @if($record->attendance_status === 'late')
                                <span class="text-red-700">TERLAMBAT</span>
                            @elseif($record->attendance_status === 'early')
                                <span class="text-amber-600">PLG CEPAT</span>
                            @else
                                <span class="text-emerald-700">TEPAT WAKTU</span>
                            @endif
                        </td>
                        <td class="py-2 px-2 border-x border-black">{{ $record->officeLocation->name ?? '-' }}</td>
                        <td class="py-2 px-2 border-x border-black text-center">{{ $record->distance_from_office ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-10 text-center italic text-gray-400">Pencarian data tidak ditemukan untuk kriteria ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Summary Footer & Signature -->
        <div class="flex justify-between items-start mt-12">
            <div class="text-[10px] italic text-gray-400">
                <p>Dokumen ini diterbitkan secara otomatis secara digital.</p>
                <p>Dicetak pada: {{ now()->translatedFormat('l, d F Y | H:i') }} WIB</p>
            </div>
            <div class="w-64 text-center">
                <p class="text-xs mb-1">Purbalingga, {{ now()->translatedFormat('d F Y') }}</p>
                <p class="text-xs font-bold uppercase mb-1">Mengetahui/Menyetujui,</p>
                <p class="text-xs font-bold uppercase italic border-b border-black inline-block px-4">Pejabat Berwenang</p>
                <div class="signature-space"></div>
                <div class="border-b border-black w-48 mx-auto mb-1"></div>
                <p class="text-[10px] text-gray-400 uppercase tracking-widest leading-none">NIP / Tanda Tangan</p>
            </div>
        </div>

        <!-- Floating Controls (No-Print) -->
        <div class="fixed bottom-8 right-8 flex gap-3 no-print">
            <button onclick="window.print()" class="bg-black text-white px-8 py-3 rounded shadow-2xl font-bold flex items-center gap-3 hover:bg-slate-800 transition transform hover:-translate-y-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                CETAK DOKUMEN
            </button>
            <button onclick="window.close()" class="bg-white text-slate-600 px-6 py-3 rounded shadow-xl font-bold border border-slate-200 hover:bg-slate-50 transition">
                TUTUP
            </button>
        </div>
    </div>
</body>
</html>

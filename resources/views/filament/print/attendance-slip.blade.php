<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slip Kehadiran Resmi - {{ $record->employee_name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body { background: white; }
            .no-print { display: none !important; }
            .print-shadow { shadow: none !important; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        }
        @page {
            size: A4;
            margin: 20mm;
        }
    </style>
</head>
<body class="bg-slate-50 antialiased font-sans">

    <div class="min-h-screen py-10 px-4">
        <!-- Main Document Container -->
        <div class="max-w-3xl mx-auto bg-white shadow-xl rounded-sm border border-slate-200 overflow-hidden relative">
            
            <!-- Professional Kop Instansi -->
            <div class="px-8 py-6 bg-white border-b border-gray-100 flex items-center justify-center gap-6">
                <img src="{{ asset('assets/images/logo-pdam.png') }}" class="h-20 w-auto object-contain">
                <div class="text-center">
                    <h1 class="text-lg font-bold uppercase leading-tight">PERUSAHAAN UMUM DAERAH AIR MINUM</h1>
                    <h1 class="text-2xl font-black uppercase leading-none tracking-tighter">TIRTA PERWIRA</h1>
                    <h2 class="text-lg font-bold uppercase leading-tight tracking-wide">KABUPATEN PURBALINGGA</h2>
                    <p class="text-[10px] mt-1 italic text-gray-500">Jl. Let. Jend. S.Parman No. 62 Kedung Menjangan. Purbalingga (53316).</p>
                </div>
            </div>

            <!-- Professional Header Bar -->
            <div class="bg-slate-900 px-8 py-6 flex justify-between items-center text-white">
                <div>
                    <h1 class="text-xl font-bold tracking-tight uppercase">Slip Bukti Kehadiran</h1>
                    <p class="text-xs text-slate-400 mt-1 uppercase tracking-widest font-semibold">Tervalidasi Sistem Digital HRIS</p>
                </div>
                <div class="text-right">
                    <p class="text-2xl font-extrabold text-blue-500 italic uppercase">PORTAL</p>
                </div>
            </div>

            <!-- Content Area -->
            <div class="p-8">
                <!-- Summary Section -->
                <div class="flex justify-between items-start mb-8 border-b border-slate-100 pb-6">
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Nama Lengkap Pegawai</p>
                        <h2 class="text-3xl font-bold text-slate-800 tracking-tight">{{ $record->employee_name }}</h2>
                        <div class="flex gap-4 mt-2">
                             <span class="text-sm text-slate-500 font-medium bg-slate-100 px-2 py-0.5 rounded">PIN: {{ $record->pin }}</span>
                             <div class="inline-block px-4 py-2 bg-black text-white text-sm font-black uppercase italic shadow-lg">
                                {{ $record->state === 'check_in' || $record->state === 'in' ? 'MASUK / CHECK-IN' : 'KELUAR / CHECK-OUT' }}
                                @if($record->attendance_status === 'late')
                                    <span class="ml-2 bg-red-600 px-1 text-[10px] not-italic">(TERLAMBAT)</span>
                                @elseif($record->attendance_status === 'early')
                                    <span class="ml-2 bg-yellow-500 px-1 text-[10px] text-black not-italic">(TERLALU CEPAT)</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">ID Transaksi</p>
                        <p class="text-sm font-mono font-bold text-slate-700">#{{ str_pad($record->id, 10, '0', STR_PAD_LEFT) }}</p>
                    </div>
                </div>

                <!-- Data Grid -->
                <div class="grid grid-cols-2 gap-8 mb-10">
                    <div class="space-y-4">
                        <div class="group">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Waktu Presensi</p>
                            <div class="p-3 bg-slate-50 rounded-lg border border-slate-100">
                                <p class="text-sm font-semibold text-slate-800">{{ \Carbon\Carbon::parse($record->attendance_time)->translatedFormat('l, d F Y') }}</p>
                                <p class="text-2xl font-bold text-slate-900 mt-1">{{ \Carbon\Carbon::parse($record->attendance_time)->format('H:i:s') }} <span class="text-xs font-normal text-slate-400 ml-1">WIB</span></p>
                            </div>
                        </div>

                        <div class="group">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Lokasi & Verifikasi</p>
                            <div class="p-3 bg-slate-50 rounded-lg border border-slate-100">
                                <p class="text-sm font-semibold text-slate-800">{{ $record->officeLocation->name ?? 'Lokasi Terdaftar' }}</p>
                                <p class="text-xs text-slate-500 mt-1 flex items-center gap-1">
                                    <svg class="w-3 h-3 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path></svg>
                                    {{ $record->check_latitude ?? $record->latitude }}, {{ $record->check_longitude ?? $record->longitude }}
                                </p>
                                <p class="text-[10px] text-slate-400 mt-2 italic border-t border-slate-200/50 pt-2">Akurasi Radius: {{ $record->is_within_radius ? '✓ Terverifikasi' : '✗ Luar Radius' }} ({{ $record->distance_from_office ?? $record->distance_meters }}m)</p>
                            </div>
                        </div>
                    </div>

                    <!-- Photo Portrait -->
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Dokumentasi Visual</p>
                        <div class="relative p-1 bg-white border border-slate-200 rounded shadow-sm overflow-hidden">
                            @php 
                                $photo = $record->photo_checkin ?? $record->photo_checkout ?? $record->picture; 
                            @endphp
                            @if($photo)
                                <img src="{{ asset('storage/' . $photo) }}" class="w-full h-56 object-cover rounded-sm">
                            @else
                                <div class="w-full h-56 bg-slate-50 flex flex-col items-center justify-center text-slate-300">
                                    <svg class="w-12 h-12 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    <p class="text-[10px] uppercase font-bold tracking-tighter">No Image Data</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Disclaimer & Authentication -->
                <div class="grid grid-cols-2 gap-10 mt-12 pt-8 border-t border-slate-100">
                    <div>
                        <p class="text-[10px] text-slate-400 leading-relaxed italic">
                            Dokumen ini adalah bukti kehadiran yang sah yang dihasilkan secara otomatis oleh sistem. Tidak memerlukan tanda tangan basah untuk validasi internal. Data telah terverifikasi melalui sinkronisasi GPS dan identifikasi perangkat.
                        </p>
                    </div>
                    <div class="flex flex-col items-end justify-end">
                        <div class="text-right">
                             <div class="p-2 border border-slate-200 inline-block mb-1">
                                 <!-- QR Code Placeholder Simulation -->
                                 <div class="grid grid-cols-3 gap-0.5 w-8 h-8 opacity-20">
                                     @for ($i = 0; $i < 9; $i++) <div class="bg-black {{ rand(0,1) ? 'opacity-100' : 'opacity-0' }}"></div> @endfor
                                 </div>
                             </div>
                             <p class="text-[9px] font-mono text-slate-400 uppercase tracking-tighter">Verified Digital Hash</p>
                             <p class="text-[8px] font-mono text-slate-300 truncate max-w-[150px]">{{ md5($record->id . $record->created_at) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Decorative Bar -->
            <div class="h-1.5 bg-blue-600 w-full"></div>

            <!-- Control Box (Hidden on Print) -->
            <div class="absolute top-4 right-4 no-print flex gap-2">
                <button onclick="window.print()" class="bg-slate-800 hover:bg-slate-700 text-white px-4 py-2 text-xs font-bold rounded shadow-lg transition flex items-center gap-2">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    CETAK LAPORAN
                </button>
                <button onclick="window.close()" class="bg-white border border-slate-200 text-slate-600 px-4 py-2 text-xs font-bold rounded shadow-lg hover:bg-slate-50 transition">
                    TUTUP
                </button>
            </div>
        </div>
        
        <div class="max-w-3xl mx-auto mt-4 text-center no-print">
            <p class="text-xs text-slate-400 italic">Disarankan mencetak menggunakan printer warna untuk hasil dokumentasi terbaik.</p>
        </div>
    </div>

</body>
</html>

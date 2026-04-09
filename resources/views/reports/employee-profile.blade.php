<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Pegawai - {{ $record->name }}</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        @page {
            size: A4 portrait;
            margin: 0;
        }
        @media print {
            .no-print { display: none !important; }
            body { 
                padding: 0 !important; 
                margin: 0 !important;
                background-color: white !important; 
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .print-container { 
                width: 210mm !important; 
                padding: 10mm !important;
                margin: 0 !important;
                box-shadow: none !important;
                border: none !important;
                border-radius: 0 !important;
            }
            .print-border { border-color: #e5e7eb !important; }
            .print-bg-gray { background-color: #f9fafb !important; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            .print-grid-2 { display: grid !important; grid-template-columns: repeat(2, minmax(0, 1fr)) !important; gap: 2.5rem !important; }
            
            /* ULTRA-STABLE NAVY HEADER */
            .print-navy-header { 
                background-color: #0f172a !important; 
                display: block !important;
                width: calc(210mm - 20mm) !important;
                min-height: 200px !important;
                padding: 40px !important;
                overflow: visible !important;
                position: static !important;
            }
            .print-header-content {
                display: flex !important;
                flex-direction: row !important;
                align-items: center !important;
                gap: 40px !important;
                position: static !important;
            }
            .print-photo-wrapper {
                width: 160px !important;
                height: 210px !important;
                flex-shrink: 0 !important;
                position: static !important;
            }
            .print-photo-wrapper div {
                height: 100% !important;
                border: 2px solid #ffffff !important;
                background-color: transparent !important;
            }
            .print-text-pane {
                flex-grow: 1 !important;
                text-align: left !important;
                position: static !important;
            }
            .print-white { color: #ffffff !important; font-weight: bold !important; }
            .print-hidden { display: none !important; }
            
            /* Hard-force text visibility */
            .print-navy-header * { 
                color: #ffffff !important; 
                text-shadow: none !important;
                position: static !important;
            }
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 p-8 antialiased" onload="window.print()">
    <!-- Utility Bar -->
    <div class="no-print max-w-4xl mx-auto mb-8 flex justify-between items-center bg-white p-4 rounded-xl shadow-sm border border-gray-100">
        <div>
            <h2 class="font-bold text-gray-900">Preview Cetak Profil Pegawai</h2>
            <p class="text-xs text-gray-500">Gunakan tombol cetak untuk mencetak atau simpan sebagai PDF.</p>
        </div>
        <button onclick="window.print()" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-all shadow-md flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Cetak Sekarang
        </button>
    </div>

    <!-- Main Profile Card -->
    <div class="max-w-4xl mx-auto bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100 print-container">
        
        <!-- Header Section -->
        <div class="bg-slate-900 p-10 flex flex-col md:flex-row items-center gap-8 relative overflow-hidden print-navy-header">
            <div class="absolute top-0 right-0 w-64 h-64 bg-blue-500/10 rounded-full -mr-32 -mt-32 print-hidden"></div>
            <div class="absolute bottom-0 left-0 w-32 h-32 bg-white/5 rounded-full -ml-16 -mb-16 print-hidden"></div>
            
            <div class="print-header-content">
                <div class="relative z-10 print-photo-wrapper">
                    <div class="w-40 h-52 bg-white/10 rounded-xl overflow-hidden p-1 backdrop-blur-sm border border-white/20">
                        @if($record->image)
                            <img src="{{ asset('storage/' . $record->image) }}" alt="Foto Pegawai" class="w-full h-full object-cover rounded-lg">
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-slate-800 text-slate-500 italic text-xs">No Photo</div>
                        @endif
                    </div>
                </div>

                <div class="relative z-10 flex-1 text-center md:text-left print-text-pane">
                    <span class="inline-block px-3 py-1 bg-blue-500/20 text-blue-400 text-[10px] font-bold tracking-wider uppercase rounded-full mb-3 print-white">Profil Pegawai</span>
                    <h1 class="text-3xl md:text-4xl font-extrabold text-white mb-2 leading-tight tracking-tight print-white">{{ strtoupper($record->name) }}</h1>
                    <div class="flex flex-wrap justify-center md:justify-start gap-4 items-center text-slate-400 text-sm print-white">
                        <span class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 bg-blue-400 rounded-full"></span> NIPPAM: {{ $record->nippam ?? '-' }}</span>
                        <span class="text-slate-600 hidden md:inline print:inline">|</span>
                        <span class="print-white">{{ $record->position->name ?? 'Jabatan tidak diset' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-10">
            <!-- Grid Layout -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-10 print-grid-2">
                
                <!-- Left Column -->
                <div class="space-y-10">
                    <!-- Basic Info -->
                    <div>
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center text-blue-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" stroke-width="2"/></svg>
                            </div>
                            <h3 class="font-bold text-gray-900 border-b border-gray-100 pb-1 flex-1">Data Identitas</h3>
                        </div>
                        <div class="grid gap-5 text-sm">
                            <div class="flex flex-col">
                                <span class="text-[10px] uppercase tracking-wider text-gray-400 font-bold mb-1">NIK (Nomor KTP)</span>
                                <span class="text-gray-900 font-medium">{{ $record->id_number ?: '-' }}</span>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-[10px] uppercase tracking-wider text-gray-400 font-bold mb-1">No. Kartu Keluarga</span>
                                <span class="text-gray-900 font-medium">{{ $record->familycard_number ?: '-' }}</span>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="flex flex-col">
                                    <span class="text-[10px] uppercase tracking-wider text-gray-400 font-bold mb-1">Jenis Kelamin</span>
                                    <span class="text-gray-900 font-medium uppercase">{{ $record->gender === 'male' ? 'Laki-laki' : 'Perempuan' }}</span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-[10px] uppercase tracking-wider text-gray-400 font-bold mb-1">Status Perkawinan</span>
                                    <span class="text-gray-900 font-medium capitalize">{{ $record->marital_status }}</span>
                                </div>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-[10px] uppercase tracking-wider text-gray-400 font-bold mb-1">Tempat, Tanggal Lahir</span>
                                <span class="text-gray-900 font-medium">{{ $record->place_birth }}, {{ $record->date_birth?->format('d F Y') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Info -->
                    <div>
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-8 h-8 bg-emerald-100 rounded-full flex items-center justify-center text-emerald-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" stroke-width="2"/></svg>
                            </div>
                            <h3 class="font-bold text-gray-900 border-b border-gray-100 pb-1 flex-1">Kontak</h3>
                        </div>
                        <div class="grid gap-5 text-sm">
                            <div class="flex flex-col">
                                <span class="text-[10px] uppercase tracking-wider text-gray-400 font-bold mb-1">Email</span>
                                <span class="text-gray-900 font-medium">{{ $record->email ?: '-' }}</span>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-[10px] uppercase tracking-wider text-gray-400 font-bold mb-1">No. Telepon</span>
                                <span class="text-gray-900 font-medium">{{ $record->phone_number ?: '-' }}</span>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-[10px] uppercase tracking-wider text-gray-400 font-bold mb-1">Alamat Lengkap</span>
                                <span class="text-gray-900 font-medium leading-relaxed">{{ $record->address ?: '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-10">
                    <!-- Employment Details -->
                    <div>
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" stroke-width="2"/></svg>
                            </div>
                            <h3 class="font-bold text-gray-900 border-b border-gray-100 pb-1 flex-1">Data Kepegawaian</h3>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-6 border border-gray-100 space-y-6 text-sm print-bg-gray print-border">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="flex flex-col">
                                    <span class="text-[10px] uppercase tracking-wider text-gray-400 font-bold mb-1">Departemen</span>
                                    <span class="text-gray-900 font-bold">{{ $record->department->name ?? '-' }}</span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-[10px] uppercase tracking-wider text-gray-400 font-bold mb-1">Sub-Departemen</span>
                                    <span class="text-gray-900 font-bold">{{ $record->subDepartment->name ?? '-' }}</span>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="flex flex-col">
                                    <span class="text-[10px] uppercase tracking-wider text-gray-400 font-bold mb-1">Status Karyawan</span>
                                    <span class="text-indigo-600 font-extrabold">{{ $record->employmentStatus->name ?? '-' }}</span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-[10px] uppercase tracking-wider text-gray-400 font-bold mb-1">Pendidikan Terakhir</span>
                                    <span class="text-gray-900 font-bold uppercase">{{ $record->education->name ?? '-' }}</span>
                                </div>
                            </div>
                            <div class="pt-4 border-t border-gray-200 print-border grid grid-cols-2 gap-4">
                                <div class="flex flex-col">
                                    <span class="text-[10px] uppercase tracking-wider text-gray-400 font-bold mb-1">Tanggal Masuk</span>
                                    <span class="text-gray-900 font-bold">{{ $record->entry_date?->format('d/m/Y') ?: '-' }}</span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-[10px] uppercase tracking-wider text-gray-400 font-bold mb-1">Masa Kerja</span>
                                    <span class="text-gray-900 font-bold">{{ $record->formatted_length_service }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Financial & Insurance -->
                    <div>
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-8 h-8 bg-amber-100 rounded-full flex items-center justify-center text-amber-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke-width="2"/></svg>
                            </div>
                            <h3 class="font-bold text-gray-900 border-b border-gray-100 pb-1 flex-1">Keuangan & Asuransi</h3>
                        </div>
                        <div class="grid gap-5 text-sm">
                            <div class="flex flex-row justify-between items-center bg-gray-50 p-3 rounded-lg print-bg-gray">
                                <span class="text-[10px] uppercase tracking-wider text-gray-400 font-bold">No. Rekening Bank</span>
                                <span class="text-gray-900 font-mono font-bold">{{ $record->bank_account_number ?: '-' }}</span>
                            </div>
                            <div class="flex flex-row justify-between items-center bg-gray-50 p-3 rounded-lg print-bg-gray">
                                <span class="text-[10px] uppercase tracking-wider text-gray-400 font-bold">BPJS Ketenagakerjaan</span>
                                <span class="text-gray-900 font-mono font-bold">{{ $record->bpjs_tk_number ?: '-' }}</span>
                            </div>
                            <div class="flex flex-row justify-between items-center bg-gray-50 p-3 rounded-lg print-bg-gray">
                                <span class="text-[10px] uppercase tracking-wider text-gray-400 font-bold">BPJS Kesehatan</span>
                                <span class="text-gray-900 font-mono font-bold">{{ $record->bpjs_kes_number ?: '-' }}</span>
                            </div>
                            <div class="flex flex-row justify-between items-center bg-gray-50 p-3 rounded-lg print-bg-gray">
                                <span class="text-[10px] uppercase tracking-wider text-gray-400 font-bold">NPWP</span>
                                <span class="text-gray-900 font-mono font-bold">{{ $record->npwp_number ?: '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-12 pt-6 border-t border-gray-100 flex justify-between items-center text-[9px] font-bold uppercase tracking-widest text-gray-400">
                <span class="flex items-center gap-2">
                    <span class="w-1 h-1 bg-gray-400 rounded-full"></span>
                    Dokumen Internal - HRIS PEGADAIAN
                </span>
                <span>Dicetak: {{ now()->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</span>
            </div>
        </div>
    </div>
</body>
</html>

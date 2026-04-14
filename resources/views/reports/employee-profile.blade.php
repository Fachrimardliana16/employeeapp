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
        
        /* GENERAL PRINT CONFIG */
        @page {
            size: A4 portrait;
            margin: 15mm 10mm 15mm 10mm;
        }

        @media print {
            .no-print { display: none !important; }
            body { 
                padding: 0 !important; 
                margin: 0 !important;
                background-color: white !important; 
            }
            .print-container { 
                width: 100% !important;
                max-width: none !important;
                margin: 0 !important;
                box-shadow: none !important;
                border: none !important;
                border-radius: 0 !important;
            }

            /* FORCE BACKGROUND COLORS */
            * { 
                -webkit-print-color-adjust: exact !important; 
                print-color-adjust: exact !important; 
                color-adjust: exact !important;
            }

            .print-navy-header { 
                background-color: #0f172a !important; 
                color: white !important;
                display: flex !important;
                flex-direction: row !important;
                padding: 30px !important;
                align-items: center !important;
                min-height: auto !important;
                gap: 30px !important;
            }

            .print-navy-header * {
                color: white !important;
            }

            .print-photo-container {
                width: 140px !important;
                height: 190px !important;
                flex-shrink: 0 !important;
                border: 2px solid white !important;
                border-radius: 12px !important;
                overflow: hidden !important;
            }

            .print-photo-container img {
                width: 100% !important;
                height: 100% !important;
                object-fit: cover !important;
            }

            .print-text-pane {
                text-align: left !important;
                flex: 1 !important;
            }

            .avoid-break {
                page-break-inside: avoid !important;
                margin-bottom: 20px !important;
            }

            .print-grid {
                display: grid !important;
                grid-template-columns: 1fr 1fr !important;
                gap: 30px !important;
            }

            .print-border { border-color: #f3f4f6 !important; }
            .print-bg-gray { background-color: #f9fafb !important; }
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 p-8 antialiased" onload="window.print()">
    <!-- Utility Bar -->
    <div class="no-print max-w-4xl mx-auto mb-8 flex justify-between items-center bg-white p-4 rounded-xl shadow-sm border border-gray-100">
        <div>
            <h2 class="font-bold text-gray-900 text-lg">Preview Cetak Profil Pegawai</h2>
            <p class="text-xs text-gray-500 italic">Pastikan opsi "Background Graphics" dicentang pada jendela print.</p>
        </div>
        <div class="flex gap-3">
            <a href="javascript:history.back()" class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 transition-all flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M10 19l-7-7m0 0l7-7m-7 7h18" stroke-width="2"/></svg>
                Kembali
            </a>
            <button onclick="window.print()" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-all shadow-md flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak Cetak
            </button>
        </div>
    </div>

    <!-- Main Profile Card -->
    <div class="max-w-4xl mx-auto bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100 print-container">
        
        <!-- Header Section -->
        <div class="bg-slate-900 p-10 flex flex-col md:flex-row items-center gap-8 relative overflow-hidden print-navy-header">
            <!-- Decorative elements (hidden on print usually, but kept for web view) -->
            <div class="absolute top-0 right-0 w-64 h-64 bg-blue-500/10 rounded-full -mr-32 -mt-32 no-print"></div>
            
            <div class="relative z-20 print-photo-container">
                @if($record->image)
                    <img src="{{ Storage::url($record->image) }}" alt="Foto Pegawai" class="w-44 h-56 object-cover rounded-xl shadow-2xl border-2 border-white/20">
                @else
                    <div class="w-44 h-56 flex items-center justify-center bg-slate-800 text-slate-500 italic text-xs rounded-xl border border-white/10">No Photo</div>
                @endif
            </div>

            <div class="relative z-20 flex-1 text-left print-text-pane text-white">
                <span class="inline-block px-4 py-1.5 bg-blue-500/20 text-blue-300 text-[10px] font-bold tracking-[0.2em] uppercase rounded-lg mb-4 border border-blue-500/30 print-white">Profil Pegawai</span>
                
                <h1 class="text-4xl md:text-5xl font-black mb-3 leading-tight tracking-tight print-white drop-shadow-sm">{{ strtoupper($record->name) }}</h1>
                
                <div class="flex flex-wrap justify-start gap-x-4 gap-y-2 items-center mb-4">
                    <span class="px-3 py-1 bg-white/10 rounded-md text-sm font-bold flex items-center gap-2">
                        NIPPAM: {{ $record->nippam ?? '-' }}
                    </span>
                    <span class="text-lg font-medium text-blue-200">{{ $record->position->name ?? 'Jabatan tidak diset' }}</span>
                </div>

                <div class="flex flex-wrap justify-start gap-2 items-center opacity-90">
                    <div class="flex items-center gap-2 px-3 py-1 bg-slate-800/50 rounded-lg border border-slate-700/50 text-xs text-left">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" stroke-width="2"/></svg>
                        <span class="font-semibold text-slate-300 uppercase tracking-wider">{{ $record->department->name ?? '-' }}</span>
                    </div>
                    @if($record->subDepartment)
                        <svg class="w-3 h-3 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-width="2"/></svg>
                        <div class="flex items-center gap-2 px-3 py-1 bg-slate-800/50 rounded-lg border border-slate-700/50 text-xs">
                            <span class="font-medium text-slate-400">{{ $record->subDepartment->name }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="p-10">
            <!-- Grid Layout -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-10 print-grid">
                
                <!-- Left Column -->
                <div class="space-y-10 avoid-break">
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
                    <div class="pt-4">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-8 h-8 bg-emerald-100 rounded-full flex items-center justify-center text-emerald-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" stroke-width="2"/></svg>
                            </div>
                            <h3 class="font-bold text-gray-900 border-b border-gray-100 pb-1 flex-1">Kontak</h3>
                        </div>
                        <div class="grid gap-5 text-sm">
                            <div class="flex flex-col">
                                <span class="text-[10px] uppercase tracking-wider text-gray-400 font-bold mb-1">Email Pribadi</span>
                                <span class="text-gray-900 font-medium">{{ $record->email ?: '-' }}</span>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-[10px] uppercase tracking-wider text-gray-400 font-bold mb-1">Email Kantor</span>
                                <span class="text-blue-600 font-bold">{{ $record->office_email ?: '-' }}</span>
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
                <div class="space-y-10 avoid-break">
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
                    <div class="pt-4">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-8 h-8 bg-amber-100 rounded-full flex items-center justify-center text-amber-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke-width="2"/></svg>
                            </div>
                            <h3 class="font-bold text-gray-900 border-b border-gray-100 pb-1 flex-1">Keuangan & Asuransi</h3>
                        </div>
                        <div class="grid gap-4 text-sm">
                            <div class="flex flex-row justify-between items-center bg-gray-50 px-4 py-2 rounded-lg print-bg-gray border border-gray-100 print-border">
                                <span class="text-[10px] uppercase tracking-wider text-gray-400 font-bold">No. Rekening Bank</span>
                                <span class="text-gray-900 font-mono font-bold">{{ $record->bank_account_number ?: '-' }}</span>
                            </div>
                            <div class="flex flex-row justify-between items-center bg-gray-50 px-4 py-2 rounded-lg print-bg-gray border border-gray-100 print-border">
                                <span class="text-[10px] uppercase tracking-wider text-gray-400 font-bold">BPJS Ketenagakerjaan</span>
                                <span class="text-gray-900 font-mono font-bold">{{ $record->bpjs_tk_number ?: '-' }}</span>
                            </div>
                            <div class="flex flex-row justify-between items-center bg-gray-50 px-4 py-2 rounded-lg print-bg-gray border border-gray-100 print-border">
                                <span class="text-[10px] uppercase tracking-wider text-gray-400 font-bold">BPJS Kesehatan</span>
                                <span class="text-gray-900 font-mono font-bold">{{ $record->bpjs_kes_number ?: '-' }}</span>
                            </div>
                            <div class="flex flex-row justify-between items-center bg-gray-50 px-4 py-2 rounded-lg print-bg-gray border border-gray-100 print-border">
                                <span class="text-[10px] uppercase tracking-wider text-gray-400 font-bold">NPWP</span>
                                <span class="text-gray-900 font-mono font-bold">{{ $record->npwp_number ?: '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CAREER HISTORY SECTIONS -->
            <div class="mt-12 space-y-12">
                
                <!-- 1. Riwayat Mutasi -->
                @if($record->mutations->isNotEmpty())
                <div class="avoid-break">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center text-orange-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" stroke-width="2"/></svg>
                        </div>
                        <h3 class="font-bold text-gray-900 border-b border-gray-100 pb-1 flex-1 uppercase tracking-wider text-xs">Riwayat Mutasi Pegawai</h3>
                    </div>
                    <div class="overflow-hidden border border-gray-200 rounded-xl">
                        <table class="w-full text-[10px] text-left">
                            <thead class="bg-gray-50 border-b border-gray-200 text-gray-600">
                                <tr>
                                    <th class="px-4 py-3 font-bold">TANGGAL</th>
                                    <th class="px-4 py-3 font-bold uppercase">Jabatan Lama</th>
                                    <th class="px-4 py-3 font-bold uppercase">Jabatan Baru</th>
                                    <th class="px-4 py-3 font-bold">NO. SK</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($record->mutations->sortByDesc('mutation_date') as $mutation)
                                <tr>
                                    <td class="px-4 py-3 font-medium">{{ $mutation->mutation_date->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3 text-gray-500">{{ $mutation->oldPosition->name ?? '-' }} ({{ $mutation->oldDepartment->name ?? '-' }})</td>
                                    <td class="px-4 py-3 font-bold text-gray-900">{{ $mutation->newPosition->name ?? '-' }} ({{ $mutation->newDepartment->name ?? '-' }})</td>
                                    <td class="px-4 py-3 text-gray-400 italic">{{ $mutation->decision_letter_number ?? '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                <!-- 2. Riwayat Promosi & Demosi -->
                @if($record->careerMovements->isNotEmpty())
                <div class="avoid-break">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center text-blue-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" stroke-width="2"/></svg>
                        </div>
                        <h3 class="font-bold text-gray-900 border-b border-gray-100 pb-1 flex-1 uppercase tracking-wider text-xs">Riwayat Promosi & Demosi</h3>
                    </div>
                    <div class="overflow-hidden border border-gray-200 rounded-xl">
                        <table class="w-full text-[10px] text-left">
                            <thead class="bg-gray-50 border-b border-gray-200 text-gray-600">
                                <tr>
                                    <th class="px-4 py-3 font-bold">TANGGAL</th>
                                    <th class="px-4 py-3 font-bold">JENIS</th>
                                    <th class="px-4 py-3 font-bold uppercase">Jabatan Lama</th>
                                    <th class="px-4 py-3 font-bold uppercase">Jabatan Baru</th>
                                    <th class="px-4 py-3 font-bold">NO. SK</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($record->careerMovements->sortByDesc('movement_date') as $movement)
                                <tr>
                                    <td class="px-4 py-3 font-medium">{{ $movement->movement_date->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-0.5 rounded-full font-bold {{ $movement->type === 'promotion' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }} uppercase text-[8px]">
                                            {{ $movement->type === 'promotion' ? 'Promosi' : 'Demosi' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-500">{{ $movement->oldPosition->name ?? '-' }}</td>
                                    <td class="px-4 py-3 font-bold text-gray-900">{{ $movement->newPosition->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-gray-400 italic">{{ $movement->decision_letter_number ?? '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                <!-- 3. Riwayat Kenaikan Golongan -->
                @if($record->promotions->isNotEmpty())
                <div class="avoid-break">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center text-purple-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" stroke-width="2"/></svg>
                        </div>
                        <h3 class="font-bold text-gray-900 border-b border-gray-100 pb-1 flex-1 uppercase tracking-wider text-xs">Riwayat Kenaikan Golongan</h3>
                    </div>
                    <div class="overflow-hidden border border-gray-200 rounded-xl">
                        <table class="w-full text-[10px] text-left">
                            <thead class="bg-gray-50 border-b border-gray-200 text-gray-600">
                                <tr>
                                    <th class="px-4 py-3 font-bold">TANGGAL</th>
                                    <th class="px-4 py-3 font-bold uppercase">Gol. Lama</th>
                                    <th class="px-4 py-3 font-bold uppercase">Gol. Baru</th>
                                    <th class="px-4 py-3 font-bold">GAJI BARU</th>
                                    <th class="px-4 py-3 font-bold text-orange-600">ESTIMASI BERIKUTNYA</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($record->promotions->sortByDesc('promotion_date') as $promo)
                                <tr>
                                    <td class="px-4 py-3 font-medium">{{ $promo->promotion_date->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3 text-gray-500">{{ $promo->oldSalaryGrade?->name ?? '-' }}</td>
                                    <td class="px-4 py-3 font-bold text-gray-900">{{ $promo->newSalaryGrade?->name ?? '-' }}</td>
                                    <td class="px-4 py-3 font-bold">Rp {{ number_format($promo->newSalaryGrade?->basic_salary ?? 0, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 font-bold text-orange-600">{{ $promo->next_promotion_date ? $promo->next_promotion_date->format('d/m/Y') : '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                <!-- 4. Riwayat Pengangkatan -->
                @if($record->appointments->isNotEmpty())
                <div class="avoid-break">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-8 h-8 bg-cyan-100 rounded-full flex items-center justify-center text-cyan-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-width="2"/></svg>
                        </div>
                        <h3 class="font-bold text-gray-900 border-b border-gray-100 pb-1 flex-1 uppercase tracking-wider text-xs">Riwayat Pengangkatan Status</h3>
                    </div>
                    <div class="overflow-hidden border border-gray-200 rounded-xl">
                        <table class="w-full text-[10px] text-left">
                            <thead class="bg-gray-50 border-b border-gray-200 text-gray-600">
                                <tr>
                                    <th class="px-4 py-3 font-bold">TANGGAL</th>
                                    <th class="px-4 py-3 font-bold uppercase">Status Lama</th>
                                    <th class="px-4 py-3 font-bold uppercase">Status Baru</th>
                                    <th class="px-4 py-3 font-bold">NO. SK</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($record->appointments->sortByDesc('appointment_date') as $appointment)
                                <tr>
                                    <td class="px-4 py-3 font-medium">{{ $appointment->appointment_date->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3 text-gray-500">{{ $appointment->oldEmploymentStatus?->name ?? '-' }}</td>
                                    <td class="px-4 py-3 font-bold text-indigo-700">{{ $appointment->newEmploymentStatus?->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-gray-400 italic">{{ $appointment->decision_letter_number ?? '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

            </div>

            <!-- Footer -->
            <div class="mt-12 pt-8 border-t border-gray-100 flex justify-between items-center text-[9px] font-bold uppercase tracking-widest text-gray-400 avoid-break">
                <span class="flex items-center gap-2">
                    <span class="w-1 h-1 bg-gray-400 rounded-full"></span>
                    Dokumen Internal - HRIS Tirta Perwira
                </span>
                <span>Dicetak: {{ now()->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</span>
            </div>
        </div>
    </div>
</body>
</html>

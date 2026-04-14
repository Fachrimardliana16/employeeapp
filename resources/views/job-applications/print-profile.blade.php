<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Pelamar - {{ $record->name }}</title>
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
                backdrop-filter: none !important;
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
            <h2 class="font-bold text-gray-900">Preview Cetak Profil</h2>
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
                        @if($record->photo)
                            <img src="{{ url('image-view/' . $record->photo) }}" alt="Foto Pelamar" class="w-full h-full object-cover rounded-lg">
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-slate-800 text-slate-500 italic text-xs">No Photo</div>
                        @endif
                    </div>
                </div>

                <div class="relative z-10 flex-1 text-center md:text-left print-text-pane">
                    <span class="inline-block px-3 py-1 bg-blue-500/20 text-blue-400 text-[10px] font-bold tracking-wider uppercase rounded-full mb-3 print-white">Profil Pelamar</span>
                    <h1 class="text-3xl md:text-4xl font-extrabold text-white mb-2 leading-tight tracking-tight print-white">{{ strtoupper($record->name) }}</h1>
                    <div class="flex flex-wrap justify-center md:justify-start gap-3 items-center text-slate-400 text-sm print-white">
                        <span class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 bg-blue-400 rounded-full"></span> {{ $record->application_number }}</span>
                        <span class="text-slate-600 hidden md:inline print:inline">|</span>
                        <span class="print-white">Dikirim: {{ $record->submitted_at->format('d F Y') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-10">
            <!-- Grid Layout -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-10 print-grid-2">
                
                <!-- Left Column -->
                <div class="space-y-10">
                    <!-- Personal Info -->
                    <div>
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center text-blue-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" stroke-width="2"/></svg>
                            </div>
                            <h3 class="font-bold text-gray-900 border-b border-gray-100 pb-1 flex-1">Data Pribadi</h3>
                        </div>
                        <div class="grid gap-5 text-sm">
                            <div class="flex flex-col">
                                <span class="text-[10px] uppercase tracking-wider text-gray-400 font-bold mb-1">NIK (Nomor KTP)</span>
                                <span class="text-gray-900 font-medium">{{ $record->id_number }}</span>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="flex flex-col">
                                    <span class="text-[10px] uppercase tracking-wider text-gray-400 font-bold mb-1">Jenis Kelamin</span>
                                    <span class="text-gray-900 font-medium">{{ $record->gender === 'male' ? 'Laki-laki' : 'Perempuan' }}</span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-[10px] uppercase tracking-wider text-gray-400 font-bold mb-1">Status Sipil</span>
                                    <span class="text-gray-900 font-medium">{{ $record->marital_status }}</span>
                                </div>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-[10px] uppercase tracking-wider text-gray-400 font-bold mb-1">Kontak</span>
                                <span class="text-gray-900 font-medium flex items-center gap-1.5">
                                    <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" stroke-width="2"/></svg>
                                    {{ $record->email }}
                                </span>
                                <span class="text-gray-900 font-medium flex items-center gap-1.5 mt-1">
                                    <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" stroke-width="2"/></svg>
                                    {{ $record->phone_number }}
                                </span>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-[10px] uppercase tracking-wider text-gray-400 font-bold mb-1">Alamat Domisili</span>
                                <span class="text-gray-900 font-medium leading-relaxed">{{ $record->address }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Target Position -->
                    <div>
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" stroke-width="2"/></svg>
                            </div>
                            <h3 class="font-bold text-gray-900 border-b border-gray-100 pb-1 flex-1">Target Karir</h3>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-5 border border-gray-100 space-y-4 text-sm print-bg-gray print-border">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="flex flex-col">
                                    <span class="text-[10px] uppercase tracking-wider text-gray-400 font-bold mb-1">Bagian</span>
                                    <span class="text-gray-900 font-bold">{{ $record->appliedDepartment->name }}</span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-[10px] uppercase tracking-wider text-gray-400 font-bold mb-1">Posisi Dikamar</span>
                                    <span class="text-indigo-600 font-extrabold">{{ $record->appliedPosition->name }}</span>
                                </div>
                            </div>
                            <div class="flex flex-col pt-3 border-t border-gray-200 print-border">
                                <span class="text-[10px] uppercase tracking-wider text-gray-400 font-bold mb-1">Ekspektasi Gaji</span>
                                <span class="text-gray-900 font-bold text-base">Rp {{ number_format($record->expected_salary, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-10">
                    <!-- Education -->
                    <div>
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-8 h-8 bg-emerald-100 rounded-full flex items-center justify-center text-emerald-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 14l9-5-9-5-9 5 9 5z" stroke-width="2"/><path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" stroke-width="2"/><path d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" stroke-width="2"/></svg>
                            </div>
                            <h3 class="font-bold text-gray-900 border-b border-gray-100 pb-1 flex-1">Riwayat Pendidikan</h3>
                        </div>
                        <div class="relative pl-6 space-y-8 before:content-[''] before:absolute before:left-[5px] before:top-2 before:bottom-2 before:w-[1px] before:bg-emerald-200">
                            <div class="relative">
                                <div class="absolute -left-[25px] top-1.5 w-2.5 h-2.5 bg-emerald-500 rounded-full shadow-lg shadow-emerald-200 border-2 border-white ring-2 ring-emerald-50"></div>
                                <div class="text-sm">
                                    <span class="text-xs font-bold text-emerald-600 block mb-1">{{ $record->education_graduation_year }}</span>
                                    <h4 class="font-bold text-gray-900">{{ $record->education_institution }}</h4>
                                    <p class="text-gray-500 text-xs">{{ $record->educationLevel->name }} {{ $record->education_major }}</p>
                                    <div class="mt-2 inline-flex items-center px-2 py-0.5 bg-emerald-50 text-emerald-700 text-[10px] font-bold rounded print-bg-gray">
                                        IPK: {{ $record->education_gpa }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Work Experience -->
                    @if($record->last_company_name)
                    <div>
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-8 h-8 bg-amber-100 rounded-full flex items-center justify-center text-amber-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" stroke-width="2"/></svg>
                            </div>
                            <h3 class="font-bold text-gray-900 border-b border-gray-100 pb-1 flex-1">Pengalaman Kerja</h3>
                        </div>
                        <div class="bg-amber-50 rounded-xl p-5 border border-amber-100 text-sm print-bg-gray print-border">
                            <div class="flex flex-col mb-4">
                                <span class="text-xs font-bold text-amber-700">{{ $record->last_position }}</span>
                                <h4 class="font-bold text-gray-900 text-base">{{ $record->last_company_name }}</h4>
                            </div>
                            <div class="text-gray-600 text-xs leading-relaxed italic mb-4">
                                "{{ $record->last_work_description }}"
                            </div>
                            <div class="flex justify-between items-center text-[10px] text-amber-800 font-bold uppercase tracking-tight opacity-70">
                                <span>Gaji Terakhir:</span>
                                <span>Rp {{ number_format($record->last_salary, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Reference -->
                    @if($record->reference_name)
                    <div class="pt-2">
                        <div class="bg-gray-900 p-5 rounded-xl text-white relative overflow-hidden">
                            <div class="absolute top-0 right-0 p-2 opacity-20">
                                <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21L14.017 18C14.017 16.8954 14.9124 16 16.017 16H19.017C19.5693 16 20.017 15.5523 20.017 15V9C20.017 8.44772 19.5693 8 19.017 8H16.017C14.9124 8 14.017 7.10457 14.017 6V5C14.017 4.44772 14.4647 4 15.017 4H21.017C21.5693 4 22.017 4.44772 22.017 5V19C22.017 20.1046 21.1216 21 20.017 21H14.017ZM2.01697 21L2.01697 18C2.01697 16.8954 2.9124 16 4.01697 16H7.01697C7.56925 16 8.01697 15.5523 8.01697 15V9C8.01697 8.44772 7.56925 8 7.01697 8H4.01697C2.9124 8 2.01697 7.10457 2.01697 6V5C2.01697 4.44772 2.46468 4 3.01697 4H9.01697C9.56925 4 10.017 4.44772 10.017 5V19C10.017 20.1046 9.12155 21 8.01697 21H2.01697Z"/></svg>
                            </div>
                            <span class="text-[9px] font-bold text-gray-500 uppercase tracking-widest block mb-1">Referensi Personal</span>
                            <div class="font-bold text-sm mb-1 leading-tight">{{ $record->reference_name }}</div>
                            <div class="text-[10px] text-gray-400 mb-2">{{ $record->reference_relation }}</div>
                            <div class="inline-flex py-1 px-3 bg-white/5 rounded-full text-[10px] font-mono">{{ $record->reference_phone }}</div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-8 pt-4 border-t border-gray-100 flex justify-between items-center text-[9px] font-bold uppercase tracking-widest text-gray-400">
                <span class="flex items-center gap-2">
                    <span class="w-1 h-1 bg-gray-400 rounded-full"></span>
                    Dicetak Otomatis oleh Sistem HRIS
                </span>
                <span>{{ now()->format('d/m/Y H:i') }}</span>
            </div>
        </div>
    </div>
</body>
</html>

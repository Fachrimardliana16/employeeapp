<x-filament-panels::page>
    <div x-data="{
        latitude: null,
        longitude: null,
        currentTime: '',
        status: 'searching', // searching, success, far

        updateTime() {
            const now = new Date();
            this.currentTime = now.toLocaleTimeString('id-ID', { hour12: false });
        },

        getLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        this.latitude = position.coords.latitude;
                        this.longitude = position.coords.longitude;
                        $wire.$set('data.latitude', this.latitude);
                        $wire.$set('data.longitude', this.longitude);
                        this.status = 'success';
                    },
                    (error) => {
                        this.status = 'error';
                        console.error('Location error:', error);
                    },
                    { enableHighAccuracy: true }
                );
            } else {
                alert('Browser Anda tidak mendukung geolocation');
            }
        }
    }" x-init="getLocation(); updateTime(); setInterval(() => updateTime(), 1000)" class="space-y-8">

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- Left Column: Form and Camera -->
            <div class="lg:col-span-7 space-y-6">
                <!-- Status Card -->
                <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-4 transition-all hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-primary-50 dark:bg-primary-950/20 rounded-lg">
                                <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.15em]">Lokasi</h2>
                                <p class="text-sm font-bold text-gray-900 dark:text-white" x-text="latitude && longitude ? `${latitude.toFixed(6)}, ${longitude.toFixed(6)}` : 'Mencari...'"></p>
                            </div>
                        </div>
                        <div class="text-right">
                             <h2 class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.15em]">Waktu</h2>
                             <p class="text-xl font-bold text-gray-900 dark:text-white font-mono" x-text="currentTime"></p>
                        </div>
                    </div>
                </div>

                <!-- Main Form -->
                <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
                    <form wire:submit="recordAttendance" class="space-y-6">
                        {{ $this->form }}

                        <div class="pt-2">
                            <button
                                type="submit"
                                class="w-full relative group flex items-center justify-center gap-3 px-6 py-3 bg-primary-600 hover:bg-primary-500 text-white rounded-lg font-bold text-md transition-all active:scale-[0.98]"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>Simpan Kehadiran</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Right Column: Info & History -->
            <div class="lg:col-span-5 space-y-6">
                <!-- Info Section -->
                <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
                    <h3 class="text-sm font-bold mb-5 flex items-center gap-2 text-gray-950 dark:text-white uppercase tracking-widest">
                        <div class="p-1.5 bg-blue-500 rounded-lg">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        Panduan Kehadiran
                    </h3>
                    <div class="space-y-4">
                        <div class="flex gap-4">
                            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center text-primary-600 dark:text-primary-400 font-black text-xs border border-primary-100 dark:border-primary-500/20">1</div>
                            <div class="pt-1">
                                <p class="text-xs font-bold text-gray-900 dark:text-white">Akses Lokasi</p>
                                <p class="text-[10px] text-gray-500 leading-relaxed">Aktifkan GPS & berikan izin lokasi pada browser Anda.</p>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center text-primary-600 dark:text-primary-400 font-black text-xs border border-primary-100 dark:border-primary-500/20">2</div>
                            <div class="pt-1">
                                <p class="text-xs font-bold text-gray-900 dark:text-white">Ambil Foto Selfie</p>
                                <p class="text-[10px] text-gray-500 leading-relaxed">Pastikan wajah terlihat jelas untuk verifikasi kehadiran.</p>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center text-primary-600 dark:text-primary-400 font-black text-xs border border-primary-100 dark:border-primary-500/20">3</div>
                            <div class="pt-1">
                                <p class="text-xs font-bold text-gray-900 dark:text-white">Radius Kantor</p>
                                <p class="text-[10px] text-gray-500 leading-relaxed">Pastikan Anda berada dalam jarak maksimal 100m dari kantor.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- History List -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between px-2">
                        <h3 class="text-sm font-bold text-gray-950 dark:text-white uppercase tracking-widest">Riwayat Hari Ini</h3>
                        <div class="flex items-center gap-1.5 px-2 py-1 bg-emerald-500/10 rounded-full">
                            <div class="w-1 h-1 rounded-full bg-emerald-500 animate-pulse"></div>
                            <span class="text-[9px] font-bold text-emerald-600 uppercase tracking-tighter">Live Updates</span>
                        </div>
                    </div>

                    @php
                        $user = auth()->user();
                        $employee = \App\Models\Employee::where('users_id', $user->id)
                            ->orWhere('email', $user->email)
                            ->orWhere('office_email', $user->email)
                            ->first();
                        $todayAttendance = $employee
                            ? \App\Models\EmployeeAttendanceRecord::where('pin', $employee->pin ?? $employee->id)
                                ->whereDate('attendance_time', today())
                                ->orderBy('attendance_time', 'desc')
                                ->get()
                            : collect();
                    @endphp

                    @forelse($todayAttendance as $record)
                        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-3 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div @class([
                                    'w-10 h-10 rounded-lg flex items-center justify-center',
                                    'bg-green-50 text-green-600 dark:bg-green-500/10' => $record->state === 'in',
                                    'bg-orange-50 text-orange-600 dark:bg-orange-500/10' => $record->state === 'out',
                                    'bg-purple-50 text-purple-600 dark:bg-purple-500/10' => str_starts_with($record->state, 'ot'),
                                ])>
                                    @if($record->state === 'in')
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                                    @else
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-gray-950 dark:text-white capitalize">
                                        {{ str_replace('_', ' ', $record->state) }}
                                    </p>
                                    <p class="text-[10px] text-gray-500 font-medium">{{ $record->attendance_time->format('H:i:s') }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400">
                                    {{ number_format($record->distance_meters, 0) }}m
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="fi-section rounded-xl border border-dashed border-gray-200 dark:border-white/10 p-10 text-center bg-gray-50/30 dark:bg-transparent">
                            <div class="relative w-16 h-16 mx-auto mb-4">
                                <div class="absolute inset-0 bg-gray-200 dark:bg-gray-800 rounded-full animate-ping opacity-20"></div>
                                <div class="relative bg-gray-100 dark:bg-gray-800 rounded-full w-16 h-16 flex items-center justify-center">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <p class="text-sm font-bold text-gray-900 dark:text-white">Belum Ada Catatan</p>
                            <p class="text-[10px] text-gray-500 mt-1 max-w-[200px] mx-auto">Silakan gunakan fitur rekam kehadiran untuk mencatat aktivitas Anda hari ini.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Styles -->
    <style>
        .fi-main {
            max-width: 1400px !important;
            margin: 0 auto !important;
        }
        
        /* Premium Shadows */
        .shadow-premium {
            box-shadow: 0 10px 40px -10px rgba(0,0,0,0.05);
        }
    </style>
</x-filament-panels::page>

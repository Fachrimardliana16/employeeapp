@php
    $user = auth()->user();
    $employee = \App\Models\Employee::where('users_id', $user->id)
        ->orWhere('email', $user->email)
        ->orWhere('office_email', $user->email)
        ->first();
    $officeLocations = $employee
        ? \App\Models\MasterOfficeLocation::where('is_active', true)
            ->where(function($q) use ($employee) {
                $q->where('departments_id', $employee->departments_id)
                  ->orWhereNull('departments_id');
            })
            ->get(['id', 'name', 'latitude', 'longitude', 'radius'])
        : collect();
@endphp

<x-filament-panels::page>
    <div x-data="{
        latitude: null,
        longitude: null,
        currentTime: '',
        accuracy: null,
        jitter: null,
        distanceToOffice: null,
        closestOfficeName: null,
        status: 'idle',
        gpsStep: 0,
        readings: [],
        officeLocations: {{ $officeLocations->toJson() }},

        updateTime() {
            const now = new Date();
            this.currentTime = now.toLocaleTimeString('id-ID', { hour12: false });
        },

        calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371000; // meter
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                    Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                    Math.sin(dLon/2) * Math.sin(dLon/2);
            return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        },

        updateDistanceToOffice() {
            if (!this.latitude || !this.longitude || this.officeLocations.length === 0) {
                this.distanceToOffice = null;
                this.closestOfficeName = null;
                return;
            }
            let minDist = Infinity;
            let closestOffice = null;
            this.officeLocations.forEach(office => {
                const dist = this.calculateDistance(
                    this.latitude, this.longitude,
                    parseFloat(office.latitude), parseFloat(office.longitude)
                );
                if (dist < minDist) {
                    minDist = dist;
                    closestOffice = office;
                }
            });
            this.distanceToOffice = Math.round(minDist);
            this.closestOfficeName = closestOffice ? closestOffice.name : null;
        },

        getLocation() {
            if (!navigator.geolocation) {
                alert('Browser Anda tidak mendukung geolocation');
                return;
            }
            this.status = 'searching';
            this.readings = [];
            this.gpsStep = 0;
            this.captureReading();
        },

        captureReading() {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    this.readings.push({
                        lat: position.coords.latitude,
                        lng: position.coords.longitude,
                        acc: position.coords.accuracy,
                    });
                    this.gpsStep = this.readings.length;
                    if (this.readings.length < 3) {
                        setTimeout(() => this.captureReading(), 2000);
                    } else {
                        this.finalizeLocation();
                    }
                },
                (error) => {
                    this.status = 'error';
                    console.error('Location error:', error);
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
            );
        },

        finalizeLocation() {
            // Pilih reading dengan akurasi terbaik (nilai terkecil)
            const best = this.readings.reduce((prev, curr) => curr.acc < prev.acc ? curr : prev);
            this.latitude  = best.lat;
            this.longitude = best.lng;
            this.accuracy  = best.acc;

            // Hitung jitter: haversine antara reading pertama dan terakhir
            const r0 = this.readings[0], r2 = this.readings[2];
            const R  = 6371000;
            const dLat = (r2.lat - r0.lat) * Math.PI / 180;
            const dLng = (r2.lng - r0.lng) * Math.PI / 180;
            const a = Math.sin(dLat/2)**2
                    + Math.cos(r0.lat * Math.PI/180) * Math.cos(r2.lat * Math.PI/180)
                    * Math.sin(dLng/2)**2;
            this.jitter = R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

            this.updateDistanceToOffice();

            $wire.$set('data.latitude',     this.latitude);
            $wire.$set('data.longitude',    this.longitude);
            $wire.$set('data.gps_accuracy', this.accuracy);
            $wire.$set('data.gps_jitter',   this.jitter);
            this.status = 'success';
        }
    }" x-init="getLocation(); updateTime(); setInterval(() => updateTime(), 1000)" class="space-y-8">

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- Left Column: Form and Camera -->
            <div class="lg:col-span-7 space-y-6">
                <!-- Status Card -->
                <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-4 transition-all hover:shadow-md">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-primary-50 dark:bg-primary-950/20 rounded-lg">
                                <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.15em]">
                                    <span x-show="!closestOfficeName">Lokasi GPS</span>
                                    <span x-show="closestOfficeName" x-text="closestOfficeName"></span>
                                </h2>
                                <div class="flex items-center gap-3">
                                    <p class="text-sm font-bold text-gray-900 dark:text-white" x-text="latitude && longitude ? `${latitude.toFixed(4)}, ${longitude.toFixed(4)}` : (status === 'searching' ? `Membaca titik ${gpsStep}/3...` : 'Belum terdeteksi')"></p>
                                    <template x-if="distanceToOffice !== null">
                                        <span 
                                            class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase" 
                                            x-text="`${distanceToOffice}m`" 
                                            :class="distanceToOffice > 100 ? 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-400' : (distanceToOffice > 50 ? 'bg-orange-100 text-orange-700 dark:bg-orange-500/10 dark:text-orange-400' : 'bg-green-100 text-green-700 dark:bg-green-500/10 dark:text-green-400')"
                                            :title="`Jarak dari kantor: ${distanceToOffice}m`"
                                        ></span>
                                    </template>
                                    
                                    <!-- Refresh Location Button with better feedback -->
                                    <button 
                                        type="button" 
                                        @click="getLocation()" 
                                        class="p-1.5 text-gray-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-500/10 rounded-full transition-all active:scale-90 focus:outline-none ring-1 ring-transparent hover:ring-primary-200 dark:hover:ring-primary-500/30"
                                        title="Perbarui Lokasi GPS"
                                    >
                                        <svg :class="status === 'searching' ? 'animate-spin text-primary-600' : ''" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-row sm:flex-col justify-between items-center sm:items-end sm:text-right border-t sm:border-t-0 border-gray-100 dark:border-white/5 pt-3 sm:pt-0">
                             <h2 class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.15em] sm:mb-1">Waktu</h2>
                             <p class="text-xl font-bold text-gray-900 dark:text-white font-mono" x-text="currentTime"></p>
                        </div>
                    </div>
                </div>

                <!-- Main Form -->
                <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
                    <div class="space-y-6">
                        {{ $this->form }}

                        <div class="pt-2">
                            <button
                                type="button"
                                wire:click="submitAttendance"
                                wire:loading.attr="disabled"
                                class="w-full relative group flex items-center justify-center gap-3 px-6 py-3 bg-primary-600 hover:bg-primary-500 text-white rounded-lg font-bold text-md transition-all active:scale-[0.98] disabled:opacity-70 disabled:cursor-not-allowed"
                            >
                                <div wire:loading wire:target="submitAttendance" class="absolute left-6">
                                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                                <svg wire:loading.remove wire:target="submitAttendance" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span wire:loading.remove wire:target="submitAttendance">Simpan Kehadiran</span>
                                <span wire:loading wire:target="submitAttendance">Memproses...</span>
                            </button>
                        </div>
                    </div>
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
                                    'bg-green-50 text-green-600 dark:bg-green-500/10' => in_array($record->state, ['check_in']),
                                    'bg-orange-50 text-orange-600 dark:bg-orange-500/10' => in_array($record->state, ['check_out']),
                                    'bg-blue-50 text-blue-600 dark:bg-blue-500/10' => str_starts_with($record->state, 'dl'),
                                    'bg-purple-50 text-purple-600 dark:bg-purple-500/10' => str_starts_with($record->state, 'ot'),
                                ])>
                                    @if(in_array($record->state, ['check_in', 'ot_in', 'dl_in']))
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                                    @else
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-gray-950 dark:text-white capitalize">
                                        {{ match($record->state) {
                                            'check_in'  => 'Masuk Kerja',
                                            'check_out' => 'Pulang Kerja',
                                            'dl_in'     => 'Dinas Luar (Berangkat)',
                                            'dl_out'    => 'Dinas Luar (Kembali)',
                                            'ot_in'     => 'Lembur (Masuk)',
                                            'ot_out'    => 'Lembur (Pulang)',
                                            default     => str_replace('_', ' ', $record->state),
                                        } }}
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
    
    <x-filament-actions::modals />
</x-filament-panels::page>

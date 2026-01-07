<x-filament-panels::page>
    <div x-data="{
        latitude: null,
        longitude: null,
        getLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        this.latitude = position.coords.latitude;
                        this.longitude = position.coords.longitude;
                        @this.set('data.latitude', this.latitude);
                        @this.set('data.longitude', this.longitude);

                        $wire.$set('data.latitude', this.latitude);
                        $wire.$set('data.longitude', this.longitude);
                    },
                    (error) => {
                        alert('Gagal mendapatkan lokasi: ' + error.message);
                    }
                );
            } else {
                alert('Browser Anda tidak mendukung geolocation');
            }
        }
    }" x-init="getLocation()">

        <div class="mb-4">
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                            Informasi Penting
                        </h3>
                        <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                            <ul class="list-disc list-inside space-y-1">
                                <li>Pastikan GPS/lokasi Anda aktif</li>
                                <li>Anda harus berada dalam radius 100 meter dari kantor</li>
                                <li>Lokasi Anda: <span x-text="latitude && longitude ? `${latitude.toFixed(6)}, ${longitude.toFixed(6)}` : 'Mendeteksi...'"></span></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <form wire:submit="recordAttendance">
            {{ $this->form }}

            <div class="mt-6">
                <x-filament::button type="submit" size="lg" class="w-full">
                    Rekam Kehadiran
                </x-filament::button>
            </div>
        </form>

        <div class="mt-6">
            <h3 class="text-lg font-semibold mb-3">Riwayat Kehadiran Hari Ini</h3>
            @php
                $user = auth()->user();
                $employee = \App\Models\Employee::where('email', $user->email)->first();
                $todayAttendance = $employee
                    ? \App\Models\EmployeeAttendanceRecord::where('pin', $employee->pin ?? $employee->id)
                        ->whereDate('attendance_time', today())
                        ->orderBy('attendance_time', 'desc')
                        ->get()
                    : collect();
            @endphp

            @if($todayAttendance->count() > 0)
                <div class="space-y-2">
                    @foreach($todayAttendance as $record)
                        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                            <div class="flex justify-between items-center">
                                <div>
                                    <span class="font-medium">
                                        {{ $record->state === 'in' ? 'Check In' : 'Check Out' }}
                                    </span>
                                    <span class="text-sm text-gray-500 dark:text-gray-400 ml-2">
                                        {{ $record->attendance_time->format('H:i:s') }}
                                    </span>
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    @if($record->distance_meters)
                                        {{ number_format($record->distance_meters, 2) }}m dari kantor
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                    Belum ada kehadiran hari ini
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>

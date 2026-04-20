@php
    $dayMap = [
        'monday' => 'SENIN', 'tuesday' => 'SELASA', 'wednesday' => 'RABU',
        'thursday' => 'KAMIS', 'friday' => 'JUMAT', 'saturday' => 'SABTU', 'sunday' => 'MINGGU',
    ];

    $schedules = \App\Models\AttendanceSchedule::where('is_active', true)->get()->groupBy(fn($item) => strtolower($item->day));
    $logs = $getRecord()->attendanceMachineLogs->sortByDesc('timestamp')->take(15);
@endphp

<div class="overflow-hidden rounded-xl ring-1 ring-gray-950/5 dark:ring-white/10 mt-2 shadow-sm bg-white dark:bg-gray-900">
    <div class="overflow-x-auto">
        <table class="w-full text-left divide-y table-auto border-collapse divide-gray-200 dark:divide-white/5">
            <thead class="bg-gray-50/50 dark:bg-white/5">
                <tr>
                    <th class="px-4 py-3.5 text-sm font-semibold text-gray-900 dark:text-white">Hari & Tanggal</th>
                    <th class="px-4 py-3.5 text-sm font-semibold text-gray-900 dark:text-white">Waktu</th>
                    <th class="px-4 py-3.5 text-sm font-semibold text-gray-900 dark:text-white">Tipe Log</th>
                    <th class="px-4 py-3.5 text-sm font-semibold text-gray-900 dark:text-white">Status Performa</th>
                    <th class="px-4 py-3.5 text-sm font-semibold text-gray-900 dark:text-white text-center">Ket.</th>
                    <th class="px-4 py-3.5 text-sm font-semibold text-gray-900 dark:text-white">Lokasi / Mesin</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                @forelse($logs as $log)
                    @php
                        $date = $log->timestamp;
                        $dayEng = strtolower($date->format('l'));
                        $dayInd = $dayMap[$dayEng] ?? $dayEng;
                        $schedule = $schedules->get(strtolower($dayInd))?->first();
                        
                        $time = $date->format('H:i:s');
                        $statusLabel = null;
                        $statusColor = 'gray';
                        $statusIcon = 'heroicon-m-minus-small';

                        // Performance Status Logic
                        if (in_array((string)$log->type, ['0', '3', '4'])) {
                            $limit = $schedule?->late_threshold ?: $schedule?->check_in_end;
                            if ($limit && $time > $limit) {
                                $statusLabel = 'Terlambat';
                                $statusColor = 'danger';
                                $statusIcon = 'heroicon-m-clock';
                            } else {
                                $statusLabel = 'Tepat Waktu';
                                $statusColor = 'success';
                                $statusIcon = 'heroicon-m-check-badge';
                            }
                        } elseif ((string)$log->type === '1') {
                            if ($schedule?->check_out_start && $time < $schedule->check_out_start) {
                                $statusLabel = 'Pulang Cepat';
                                $statusColor = 'warning';
                                $statusIcon = 'heroicon-m-exclamation-triangle';
                            } else {
                                $statusLabel = 'Tepat Waktu';
                                $statusColor = 'success';
                                $statusIcon = 'heroicon-m-check-badge';
                            }
                        }

                        // Duplicate Logic
                        $dayLogs = $getRecord()->attendanceMachineLogs
                            ->whereBetween('timestamp', [$date->copy()->startOfDay(), $date->copy()->endOfDay()])
                            ->where('type', $log->type)
                            ->sortBy('timestamp');
                        
                        $isValid = true;
                        if ($dayLogs->count() > 1) {
                            if (in_array((string)$log->type, ['0', '3', '4'])) {
                                $isValid = ($log->id === $dayLogs->first()->id);
                            } elseif ((string)$log->type === '1') {
                                $isValid = ($log->id === $dayLogs->last()->id);
                            } else {
                                $isValid = ($log->id === $dayLogs->first()->id);
                            }
                        }

                        // Type Helper
                        $types = [
                            '0' => ['name' => 'MASUK', 'color' => 'success', 'icon' => 'heroicon-m-arrow-right-start-on-rectangle'],
                            '1' => ['name' => 'KELUAR', 'color' => 'danger', 'icon' => 'heroicon-m-arrow-left-start-on-rectangle'],
                            '2' => ['name' => 'ISTIRAHAT OUT', 'color' => 'warning', 'icon' => 'heroicon-m-arrow-up-on-square'],
                            '3' => ['name' => 'ISTIRAHAT IN', 'color' => 'success', 'icon' => 'heroicon-m-arrow-down-on-square'],
                            '4' => ['name' => 'LEMBUR IN', 'color' => 'info', 'icon' => 'heroicon-m-bolt'],
                            '5' => ['name' => 'LEMBUR OUT', 'color' => 'danger', 'icon' => 'heroicon-m-bolt-slash'],
                        ];
                        $typeData = $types[$log->type] ?? ['name' => 'LAINNYA', 'color' => 'gray', 'icon' => 'heroicon-m-question-mark-circle'];
                    @endphp
                    <tr @class([
                        'hover:bg-gray-50 dark:hover:bg-white/5 transition duration-75',
                        'bg-gray-50/30' => !$isValid,
                    ])>
                        <td class="px-4 py-4 text-sm align-top">
                            <div class="font-bold text-gray-900 dark:text-white uppercase">{{ $dayInd }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $date->format('d/m/Y') }}</div>
                        </td>
                        <td class="px-4 py-4 text-sm font-mono font-bold text-gray-700 dark:text-gray-300 align-top">
                            {{ $time }}
                        </td>
                        <td class="px-4 py-4 text-sm align-top">
                            <x-filament::badge :color="$typeData['color']" :icon="$typeData['icon']" size="sm">
                                {{ $typeData['name'] }}
                            </x-filament::badge>
                        </td>
                        <td class="px-4 py-4 text-sm align-top">
                            @if($statusLabel)
                                <x-filament::badge :color="$statusColor" :icon="$statusIcon" size="sm">
                                    {{ $statusLabel }}
                                </x-filament::badge>
                            @else
                                <span class="text-gray-300 dark:text-gray-600">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-sm text-center align-top">
                            @if(!$isValid)
                                <x-filament::badge color="gray" size="sm">
                                    DUPLIKAT
                                </x-filament::badge>
                            @else
                                <div class="flex justify-center">
                                    <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.5)]"></div>
                                </div>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-sm align-top">
                            <div class="font-medium text-gray-900 dark:text-white">{{ $log->machine?->name ?? '-' }}</div>
                            <div class="text-[10px] text-gray-500 dark:text-gray-400 leading-tight">{{ $log->machine?->officeLocation?->name ?? '-' }}</div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-12 text-center text-gray-500 dark:text-gray-400 italic">
                            <div class="flex flex-col items-center gap-2">
                                <x-filament::icon icon="heroicon-o-inbox" class="w-8 h-8 text-gray-300" />
                                <span>Belum ada rekaman log mesin absensi.</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4 flex justify-end">
    <x-filament::button
        href="{{ route('filament.user.resources.my-attendances.index') }}"
        tag="a"
        color="gray"
        size="xs"
        icon="heroicon-m-arrow-right"
        icon-position="after"
    >
        Lihat Riwayat Lengkap
    </x-filament::button>
</div>

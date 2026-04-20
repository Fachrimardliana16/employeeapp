@php
    $dayMap = [
        'monday' => 'SENIN', 'tuesday' => 'SELASA', 'wednesday' => 'RABU',
        'thursday' => 'KAMIS', 'friday' => 'JUMAT', 'saturday' => 'SABTU', 'sunday' => 'MINGGU',
    ];

    $schedules = \App\Models\AttendanceSchedule::where('is_active', true)->get()->groupBy(fn($item) => strtolower($item->day));
    $logs = $getRecord()->attendanceMachineLogs->sortByDesc('timestamp')->take(15);
@endphp

<div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
    <table class="w-full text-[11px] text-left border-collapse">
        <thead>
            <tr class="bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-400 font-bold uppercase tracking-wider">
                <th class="px-3 py-3 border-b text-center">Hari & Tanggal</th>
                <th class="px-3 py-3 border-b text-center">Jam</th>
                <th class="px-3 py-3 border-b text-center">Tipe Log</th>
                <th class="px-3 py-3 border-b text-center">Status</th>
                <th class="px-3 py-3 border-b text-center">Validasi</th>
                <th class="px-3 py-3 border-b">Lokasi / Mesin</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse($logs as $log)
                @php
                    $date = $log->timestamp;
                    $dayEng = strtolower($date->format('l'));
                    $dayInd = $dayMap[$dayEng] ?? $dayEng;
                    $schedule = $schedules->get(strtolower($dayInd))?->first();
                    
                    $time = $date->format('H:i:s');
                    $status = '-';
                    $statusColor = 'text-gray-500';

                    // Performance Status Logic
                    if (in_array((string)$log->type, ['0', '3', '4'])) {
                        $limit = $schedule?->late_threshold ?: $schedule?->check_in_end;
                        $status = ($limit && $time > $limit) ? 'TERLAMBAT' : 'TEPAT WAKTU';
                        $statusColor = ($status === 'TERLAMBAT') ? 'text-red-600 bg-red-50' : 'text-emerald-700 bg-emerald-50';
                    } elseif ((string)$log->type === '1') {
                        $status = ($schedule?->check_out_start && $time < $schedule->check_out_start) ? 'PULANG CEPAT' : 'TEPAT WAKTU';
                        $statusColor = ($status === 'PULANG CEPAT') ? 'text-amber-600 bg-amber-50' : 'text-emerald-700 bg-emerald-50';
                    }

                    // Duplicate Logic (Scoped to this employee/day/type)
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
                @endphp
                <tr class="{{ !$isValid ? 'bg-gray-50 italic text-gray-400' : 'hover:bg-gray-50' }}">
                    <td class="px-3 py-2.5 text-center font-medium">
                        <span class="font-bold text-gray-900">{{ $dayInd }}</span>, {{ $date->format('d/m/Y') }}
                    </td>
                    <td class="px-3 py-2.5 text-center font-mono font-bold text-gray-700">
                        {{ $time }}
                    </td>
                    <td class="px-3 py-2.5 text-center">
                        @php
                            $types = ['0' => 'MASUK', '1' => 'KELUAR', '2' => 'ISTIRAHAT OUT', '3' => 'ISTIRAHAT IN', '4' => 'LEMBUR IN', '5' => 'LEMBUR OUT'];
                            $typeName = $types[$log->type] ?? 'LAINNYA';
                            $typeColor = in_array((string)$log->type, ['0', '3', '4']) ? 'text-emerald-700 border-emerald-200 bg-emerald-50' : 'text-red-700 border-red-200 bg-red-50';
                        @endphp
                        <span class="px-2 py-0.5 rounded border {{ $typeColor }} font-bold text-[9px]">
                            {{ $typeName }}
                        </span>
                    </td>
                    <td class="px-3 py-2.5 text-center">
                        @if($status !== '-')
                            <span class="px-2 py-0.5 rounded font-black text-[9px] {{ $statusColor }}">
                                {{ $status }}
                            </span>
                        @else
                            <span class="text-gray-300">-</span>
                        @endif
                    </td>
                    <td class="px-3 py-2.5 text-center">
                        @if(!$isValid)
                            <span class="px-1.5 py-0.5 rounded bg-gray-200 text-gray-500 font-bold text-[8px]">DUPLIKAT</span>
                        @else
                            <span class="text-emerald-500 font-bold">●</span>
                        @endif
                    </td>
                    <td class="px-3 py-2.5">
                        <div class="font-medium text-gray-900">{{ $log->machine?->name ?? '-' }}</div>
                        <div class="text-[9px] text-gray-400">{{ $log->machine?->officeLocation?->name ?? '-' }}</div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="p-8 text-center text-gray-400 italic">Belum ada rekaman log mesin absensi.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-2 text-right">
    <a href="{{ route('filament.user.resources.my-attendances.index') }}" class="text-[10px] font-bold text-primary-600 hover:underline flex items-center justify-end gap-1">
        Lihat Selengkapnya
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
    </a>
</div>

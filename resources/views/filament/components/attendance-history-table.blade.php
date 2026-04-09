<div class="overflow-x-auto">
    <table class="w-full text-sm text-left">
        <thead class="bg-gray-50 dark:bg-gray-800">
            <tr>
                <th class="px-3 py-2">Tanggal & Waktu</th>
                <th class="px-3 py-2">Status</th>
                <th class="px-3 py-2">Device</th>
                <th class="px-3 py-2">Lokasi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            @foreach($getRecord()->attendanceRecords->sortByDesc('attendance_time')->take(10) as $attendance)
            <tr>
                <td class="px-3 py-2">{{ $attendance->attendance_time?->format('d/m/Y H:i') }}</td>
                <td class="px-3 py-2">
                    <span @class([
                        'px-2 py-0.5 rounded text-xs font-bold uppercase',
                        'bg-green-100 text-green-700' => in_array($attendance->state, ['C/In', 'CHECK IN', 'Check In']),
                        'bg-red-100 text-red-700' => in_array($attendance->state, ['C/Out', 'CHECK OUT', 'Check Out']),
                        'bg-gray-100 text-gray-700' => !in_array($attendance->state, ['C/In', 'CHECK IN', 'Check In', 'C/Out', 'CHECK OUT', 'Check Out']),
                    ])>
                        {{ $attendance->state }}
                    </span>
                </td>
                <td class="px-3 py-2 text-gray-500">{{ $attendance->device ?: 'Mobile' }}</td>
                <td class="px-3 py-2 text-xs text-gray-400 italic">
                    {{ Str::limit($attendance->location_address ?: 'GPS location', 30) }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @if($getRecord()->attendanceRecords->isEmpty())
        <p class="p-4 text-center text-gray-500 italic">Belum ada data kehadiran.</p>
    @endif
</div>

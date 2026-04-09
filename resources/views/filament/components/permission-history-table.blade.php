<div class="overflow-x-auto">
    <table class="w-full text-sm text-left">
        <thead class="bg-gray-50 dark:bg-gray-800">
            <tr>
                <th class="px-3 py-2">Tgl Mulai</th>
                <th class="px-3 py-2">Tgl Selesai</th>
                <th class="px-3 py-2">Jenis</th>
                <th class="px-3 py-2">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            @foreach($getRecord()->employeePermissions->sortByDesc('start_permission_date')->take(10) as $permission)
            <tr>
                <td class="px-3 py-2">{{ $permission->start_permission_date?->format('d/m/Y') }}</td>
                <td class="px-3 py-2">{{ $permission->end_permission_date?->format('d/m/Y') }}</td>
                <td class="px-3 py-2">{{ $permission->permission?->name }}</td>
                <td class="px-3 py-2">
                    <span @class([
                        'px-2 py-0.5 rounded text-xs font-bold uppercase',
                        'bg-yellow-100 text-yellow-700' => $permission->approval_status === 'pending',
                        'bg-green-100 text-green-700' => $permission->approval_status === 'approved',
                        'bg-red-100 text-red-700' => $permission->approval_status === 'rejected',
                    ])>
                        {{ $permission->approval_status }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @if($getRecord()->employeePermissions->isEmpty())
        <p class="p-4 text-center text-gray-500 italic">Belum ada data izin/cuti.</p>
    @endif
</div>

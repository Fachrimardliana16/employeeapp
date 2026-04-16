@php
    $families = $getRecord()->families;
@endphp

@if($families->isEmpty())
    <div class="text-sm text-gray-500 py-4 text-center border rounded-xl dark:border-white/10">Belum ada data keluarga.</div>
@else
    <div class="overflow-hidden rounded-xl ring-1 ring-gray-950/5 dark:ring-white/10 mt-2">
        <div class="overflow-x-auto">
            <table class="w-full text-left divide-y table-auto border-collapse divide-gray-200 dark:divide-white/5">
                <thead class="bg-gray-50 dark:bg-white/5">
                    <tr>
                        <th class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white">Nama Lengkap</th>
                        <th class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white whitespace-nowrap">Hubungan</th>
                        <th class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white whitespace-nowrap">Jenis Kelamin</th>
                        <th class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white whitespace-nowrap">No. Telepon</th>
                        <th class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white whitespace-nowrap">K. Darurat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-white/5 bg-white dark:bg-gray-900">
                    @foreach ($families as $family)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition duration-75">
                            <td class="px-4 py-4 text-sm font-medium text-gray-900 dark:text-white align-top">
                                {{ $family->family_name }}
                                <div class="text-xs text-gray-500 font-normal mt-1">NIK: {{ $family->family_id_number ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap align-top">
                                {{ $family->masterFamily->name ?? '-' }}
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap align-top">
                                {{ $family->family_gender === 'male' ? 'Laki-laki' : 'Perempuan' }}
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap align-top">
                                {{ $family->family_phone ?? '-' }}
                            </td>
                            <td class="px-4 py-4 text-sm align-top whitespace-nowrap">
                                @if ($family->is_emergency_contact)
                                    <x-filament::badge color="success" size="sm" icon="heroicon-m-check-circle">
                                        Ya
                                    </x-filament::badge>
                                @else
                                    <x-filament::badge color="gray" size="sm" icon="heroicon-m-x-circle">
                                        Tidak
                                    </x-filament::badge>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

@php
    $agreements = $getRecord()->employeeAgreements()->orderBy('agreement_date_start', 'desc')->get();
@endphp

@if($agreements->isEmpty())
    <div class="text-sm text-gray-500 py-4 text-center border rounded-xl dark:border-white/10">Belum ada histori kontrak kerja.</div>
@else
    <div class="overflow-hidden rounded-xl ring-1 ring-gray-950/5 dark:ring-white/10 mt-2">
        <div class="overflow-x-auto">
            <table class="w-full text-left divide-y table-auto border-collapse divide-gray-200 dark:divide-white/5">
                <thead class="bg-gray-50 dark:bg-white/5">
                    <tr>
                        <th class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white">Nomor Kontrak</th>
                        <th class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white whitespace-nowrap">Jenis Kontrak</th>
                        <th class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white whitespace-nowrap">Periode</th>
                        <th class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white whitespace-nowrap">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-white/5 bg-white dark:bg-gray-900">
                    @foreach ($agreements as $agreement)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition duration-75">
                            <td class="px-4 py-4 text-sm font-medium text-gray-900 dark:text-white align-top border-none">
                                {{ $agreement->agreement_number }}
                                <div class="text-xs text-gray-500 font-normal mt-1">Jabatan: {{ $agreement->employeePosition->name ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap align-top border-none">
                                {{ $agreement->masterAgreement->name ?? '-' }}
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap align-top border-none">
                                {{ \Carbon\Carbon::parse($agreement->agreement_date_start)->format('d M Y') }} - 
                                {{ $agreement->agreement_date_end ? \Carbon\Carbon::parse($agreement->agreement_date_end)->format('d M Y') : 'Sekarang' }}
                            </td>
                            <td class="px-4 py-4 text-sm align-top whitespace-nowrap border-none">
                                @if ($agreement->is_active)
                                    <x-filament::badge color="success" size="sm" icon="heroicon-m-check-circle">
                                        Aktif
                                    </x-filament::badge>
                                @else
                                    <x-filament::badge color="gray" size="sm" icon="heroicon-m-archive-box">
                                        Tidak Aktif
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

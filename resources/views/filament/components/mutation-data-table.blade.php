@php
    $mutations = $getRecord()->mutations()->orderBy('mutation_date', 'desc')->get();
@endphp

<x-filament::section
    icon="heroicon-o-arrow-path"
    heading="Histori Mutasi"
    description="Daftar perjalanan mutasi dan perubahan jabatan pegawai"
>
    @if($mutations->isEmpty())
        <div class="text-sm text-gray-500 py-4 text-center">Belum ada histori mutasi.</div>
    @else
        <div class="overflow-hidden rounded-xl ring-1 ring-gray-950/5 dark:ring-white/10 mt-2">
            <div class="overflow-x-auto">
                <table class="w-full text-left divide-y table-auto border-collapse divide-gray-200 dark:divide-white/5">
                    <thead class="bg-gray-50 dark:bg-white/5">
                        <tr>
                            <th class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white">Tanggal Mutasi</th>
                            <th class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white whitespace-nowrap">Nomor SK</th>
                            <th class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white whitespace-nowrap">Posisi/Jabatan Lama</th>
                            <th class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white whitespace-nowrap">Posisi/Jabatan Baru</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/5 bg-white dark:bg-gray-900">
                        @foreach ($mutations as $mutation)
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition duration-75">
                                <td class="px-4 py-4 text-sm font-medium text-gray-900 dark:text-white align-top whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($mutation->mutation_date)->format('d M Y') }}
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap align-top">
                                    {{ $mutation->decision_letter_number ?? '-' }}
                                    @if ($mutation->docs)
                                        <div class="mt-1">
                                            <a href="{{ url('image-view/' . $mutation->docs) }}" target="_blank" class="text-primary-600 hover:text-primary-500 text-xs flex items-center gap-1">
                                                <x-heroicon-m-document-check class="w-4 h-4" /> Lihat SK
                                            </a>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-500 dark:text-gray-400 align-top">
                                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ $mutation->oldPosition->name ?? '-' }}</div>
                                    <div class="text-xs mt-0.5">
                                        {{ $mutation->oldDepartment->name ?? '-' }}
                                        @if($mutation->oldSubDepartment)
                                            - {{ $mutation->oldSubDepartment->name }}
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-500 dark:text-gray-400 align-top">
                                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ $mutation->newPosition->name ?? '-' }}</div>
                                    <div class="text-xs mt-0.5">
                                        {{ $mutation->newDepartment->name ?? '-' }}
                                        @if($mutation->newSubDepartment)
                                            - {{ $mutation->newSubDepartment->name }}
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</x-filament::section>

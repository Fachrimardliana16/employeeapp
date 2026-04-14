@php
    $assignments = $getRecord()->assignmentLetters()->orderBy('start_date', 'desc')->get();
@endphp

<x-filament::section
    icon="heroicon-o-document-text"
    heading="Histori Surat Tugas"
    description="Daftar penugasan dan surat tugas pegawai"
>
    @if($assignments->isEmpty())
        <div class="text-sm text-gray-500 py-4 text-center">Belum ada histori surat tugas.</div>
    @else
        <div class="overflow-hidden rounded-xl ring-1 ring-gray-950/5 dark:ring-white/10 mt-2">
            <div class="overflow-x-auto">
                <table class="w-full text-left divide-y table-auto border-collapse divide-gray-200 dark:divide-white/5">
                    <thead class="bg-gray-50 dark:bg-white/5">
                        <tr>
                            <th class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white whitespace-nowrap">No. Registrasi</th>
                            <th class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white">Tugas/Pekerjaan</th>
                            <th class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white whitespace-nowrap">Tanggal</th>
                            <th class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white whitespace-nowrap">Penandatangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/5 bg-white dark:bg-gray-900">
                        @foreach ($assignments as $assignment)
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition duration-75">
                                <td class="px-4 py-4 text-sm font-medium text-gray-900 dark:text-white align-top whitespace-nowrap">
                                    {{ $assignment->registration_number }}
                                    @if ($assignment->pdf_file_path)
                                        <div class="mt-1">
                                            <a href="{{ Storage::url($assignment->pdf_file_path) }}" target="_blank" class="text-primary-600 hover:text-primary-500 text-xs flex items-center gap-1">
                                                <x-heroicon-m-document-arrow-down class="w-4 h-4" /> Download PDF
                                            </a>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-500 dark:text-gray-400 align-top">
                                    {{ $assignment->task }}
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-500 dark:text-gray-400 align-top whitespace-nowrap">
                                    {{ $assignment->start_date->format('d/m/Y') }} - {{ $assignment->end_date->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-500 dark:text-gray-400 align-top">
                                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ $assignment->signatory_name ?? '-' }}</div>
                                    <div class="text-xs mt-0.5 text-gray-400">{{ $assignment->signatory_position ?? '-' }}</div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</x-filament::section>

@php
    $travels = $getRecord()->businessTravelLetters()->orderBy('start_date', 'desc')->get();
@endphp

@if($travels->isEmpty())
    <div class="text-sm text-gray-500 py-4 text-center border rounded-xl dark:border-white/10">Belum ada histori perjalanan dinas.</div>
@else
    <div class="overflow-hidden rounded-xl ring-1 ring-gray-950/5 dark:ring-white/10 mt-2">
        <div class="overflow-x-auto">
            <table class="w-full text-left divide-y table-auto border-collapse divide-gray-200 dark:divide-white/5">
                <thead class="bg-gray-50 dark:bg-white/5">
                    <tr>
                        <th class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white whitespace-nowrap">No. Registrasi</th>
                        <th class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white">Tujuan</th>
                        <th class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white">Keperluan</th>
                        <th class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white whitespace-nowrap">Tanggal</th>
                        <th class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white whitespace-nowrap text-right">Total Biaya</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-white/5 bg-white dark:bg-gray-900">
                    @foreach ($travels as $travel)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition duration-75">
                            <td class="px-4 py-4 text-sm font-medium text-gray-900 dark:text-white align-top whitespace-nowrap">
                                {{ $travel->registration_number }}
                                @if ($travel->pdf_file_path)
                                    <div class="mt-1">
                                        <a href="{{ Storage::url($travel->pdf_file_path) }}" target="_blank" class="text-primary-600 hover:text-primary-500 text-xs flex items-center gap-1">
                                            <x-heroicon-m-document-arrow-down class="w-4 h-4" /> Download PDF
                                        </a>
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-500 dark:text-gray-400 align-top">
                                <div class="font-medium text-gray-900 dark:text-white text-sm">{{ $travel->destination }}</div>
                                <div class="text-xs mt-0.5">{{ $travel->destination_detail }}</div>
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-500 dark:text-gray-400 align-top text-sm">
                                {{ $travel->purpose_of_trip }}
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-500 dark:text-gray-400 align-top whitespace-nowrap text-sm">
                                {{ $travel->start_date->format('d/m/Y') }} - {{ $travel->end_date->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-4 text-sm font-medium text-gray-900 dark:text-white align-top text-right text-sm">
                                Rp {{ number_format($travel->total_cost, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

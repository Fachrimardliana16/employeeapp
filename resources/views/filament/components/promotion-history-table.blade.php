@php
    $promotions = $getRecord()->promotions()->orderBy('promotion_date', 'desc')->get();
@endphp

<x-filament::section
    icon="heroicon-o-chart-bar"
    heading="Histori Kenaikan Golongan"
    description="Daftar kenaikan pangkat dan golongan gaji pegawai"
>
    @if($promotions->isEmpty())
        <div class="text-sm text-gray-500 py-4 text-center">Belum ada histori kenaikan golongan.</div>
    @else
        <div class="overflow-hidden rounded-xl ring-1 ring-gray-950/5 dark:ring-white/10 mt-2">
            <div class="overflow-x-auto">
                <table class="w-full text-left divide-y table-auto border-collapse divide-gray-200 dark:divide-white/5">
                    <thead class="bg-gray-50 dark:bg-white/5">
                        <tr>
                            <th class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white whitespace-nowrap">Tanggal Berlaku</th>
                            <th class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white whitespace-nowrap">Golongan Lama</th>
                            <th class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white whitespace-nowrap">Golongan Baru</th>
                            <th class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white whitespace-nowrap">Gaji Pokok Lama</th>
                            <th class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white whitespace-nowrap">Gaji Pokok Baru</th>
                            <th class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white whitespace-nowrap">Kenaikan Berikutnya</th>
                            <th class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white whitespace-nowrap text-center">Dokumen</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/5 bg-white dark:bg-gray-900">
                        @foreach ($promotions as $promotion)
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition duration-75">
                                <td class="px-4 py-4 text-sm font-medium text-gray-900 dark:text-white align-top whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($promotion->promotion_date)->format('d M Y') }}
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-500 dark:text-gray-400 align-top whitespace-nowrap">
                                    {{ $promotion->oldSalaryGrade?->name ?? '-' }}
                                </td>
                                <td class="px-4 py-4 text-sm align-top whitespace-nowrap">
                                    <span class="font-bold text-success-600 dark:text-success-400">
                                        {{ $promotion->newSalaryGrade?->name ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-500 dark:text-gray-400 align-top whitespace-nowrap">
                                    Rp {{ number_format($promotion->oldSalaryGrade?->basic_salary ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-4 text-sm align-top whitespace-nowrap">
                                    <span class="font-medium text-gray-900 dark:text-white">
                                        Rp {{ number_format($promotion->newSalaryGrade?->basic_salary ?? 0, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-sm align-top whitespace-nowrap">
                                    @if($promotion->next_promotion_date)
                                        <div class="flex items-center gap-1 text-orange-600 dark:text-orange-400 font-medium">
                                            <x-heroicon-m-calendar class="w-4 h-4" />
                                            {{ $promotion->next_promotion_date->format('d M Y') }}
                                        </div>
                                    @else
                                        <span class="text-gray-400 italic text-xs">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-sm text-center align-top whitespace-nowrap">
                                    @if ($promotion->doc_promotion)
                                        <a href="{{ url('image-view/' . $promotion->doc_promotion) }}" target="_blank" class="inline-flex items-center gap-1 text-primary-600 hover:text-primary-500 font-medium">
                                            <x-heroicon-m-document-text class="w-5 h-5" />
                                            <span class="text-xs">Lihat SK</span>
                                        </a>
                                    @else
                                        <span class="text-gray-400 text-xs italic">-</span>
                                    @endif
                                    <div class="text-[10px] text-gray-400 mt-1">{{ $promotion->decision_letter_number ?? '' }}</div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</x-filament::section>

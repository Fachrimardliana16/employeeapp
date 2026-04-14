@php
    $appointments = $getRecord()->appointments()->orderBy('appointment_date', 'desc')->get();
@endphp

<x-filament::section
    icon="heroicon-o-check-badge"
    heading="Histori Pengangkatan"
    description="Daftar pengangkatan status kepegawaian (misal: Calon Pegawai ke Pegawai Tetap)"
>
    @if($appointments->isEmpty())
        <div class="text-sm text-gray-500 py-4 text-center">Belum ada histori pengangkatan.</div>
    @else
        <div class="overflow-hidden rounded-xl ring-1 ring-gray-950/5 dark:ring-white/10 mt-2">
            <div class="overflow-x-auto">
                <table class="w-full text-left divide-y table-auto border-collapse divide-gray-200 dark:divide-white/5">
                    <thead class="bg-gray-50 dark:bg-white/5">
                        <tr>
                            <th class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white whitespace-nowrap">Tanggal Efektif</th>
                            <th class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white whitespace-nowrap">Status Lama</th>
                            <th class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white whitespace-nowrap">Status Baru</th>
                            <th class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white whitespace-nowrap text-center">Dokumen</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/5 bg-white dark:bg-gray-900">
                        @foreach ($appointments as $appointment)
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition duration-75">
                                <td class="px-4 py-4 text-sm font-medium text-gray-900 dark:text-white align-top whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d M Y') }}
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-500 dark:text-gray-400 align-top whitespace-nowrap">
                                    {{ $appointment->oldEmploymentStatus?->name ?? '-' }}
                                </td>
                                <td class="px-4 py-4 text-sm align-top whitespace-nowrap">
                                    <div class="inline-flex items-center px-2 py-0.5 rounded font-bold bg-success-50 text-success-700 dark:bg-success-900/30 dark:text-success-400 text-xs">
                                        {{ $appointment->newEmploymentStatus?->name ?? '-' }}
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-sm text-center align-top whitespace-nowrap">
                                    @if ($appointment->docs)
                                        <a href="{{ url('image-view/' . $appointment->docs) }}" target="_blank" class="inline-flex items-center gap-1 text-primary-600 hover:text-primary-500 font-medium">
                                            <x-heroicon-m-document-text class="w-5 h-5" />
                                            <span class="text-xs">Lihat SK</span>
                                        </a>
                                    @else
                                        <span class="text-gray-400 text-xs italic">-</span>
                                    @endif
                                    <div class="text-[10px] text-gray-400 mt-1">{{ $appointment->decision_letter_number ?? '' }}</div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</x-filament::section>

@php
    $hasApplication = $getRecord()->jobApplication->isNotEmpty();
    $hasInterview = $getRecord()->interviewProcesses->isNotEmpty();
    $hasContract = $getRecord()->employeeAgreements->isNotEmpty();

    $applicationDate = $getRecord()->jobApplication->first()?->created_at?->format('d M Y');
    $interviewDate = $getRecord()->interviewProcesses->first()?->created_at?->format('d M Y');
    $contractDate = $getRecord()->employeeAgreements->first()?->created_at?->format('d M Y');

    $steps = [
        [
            'title' => 'Berkas Lamaran Diseleksi',
            'description' => 'Meninjau kelengkapan berkas administrasi dan profil awal pelamar.',
            'completed' => $hasApplication,
            'date' => $applicationDate,
        ],
        [
            'title' => 'Sesi Wawancara & Evaluasi',
            'description' => 'Melalui proses pendalaman secara teknis maupun budaya oleh HR.',
            'completed' => $hasInterview,
            'date' => $interviewDate,
        ],
        [
            'title' => 'Verifikasi Kontrak Pekerjaan',
            'description' => 'Penyusunan perjanjian kerja bersama dan peresmian data pegawai.',
            'completed' => $hasContract,
            'date' => $contractDate,
        ],
    ];
@endphp

<x-filament::section
    icon="heroicon-o-list-bullet"
    heading="Histori Rekrutmen"
    description="Tahapan seleksi dari awal lamaran masuk hingga menjadi pegawai"
>
    <div class="overflow-hidden rounded-xl ring-1 ring-gray-950/5 dark:ring-white/10 mt-2">
        <div class="overflow-x-auto">
            <table class="w-full text-left divide-y table-auto border-collapse divide-gray-200 dark:divide-white/5">
                <thead class="bg-gray-50 dark:bg-white/5">
                    <tr>
                        <th class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white">Tahapan Proses</th>
                        <th class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white">Deskripsi</th>
                        <th class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white whitespace-nowrap">Tanggal Selesai</th>
                        <th class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-white/5 bg-white dark:bg-gray-900">
                    @foreach ($steps as $step)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition duration-75">
                            <td class="px-4 py-4 text-sm font-medium text-gray-900 dark:text-white align-top">
                                {{ $step['title'] }}
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-500 dark:text-gray-400 align-top min-w-[250px]">
                                {{ $step['description'] }}
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap align-top">
                                {{ $step['date'] ?? '-' }}
                            </td>
                            <td class="px-4 py-4 text-sm align-top whitespace-nowrap">
                                @if ($step['completed'])
                                    <x-filament::badge color="success" size="sm" icon="heroicon-m-check-circle">
                                        Selesai
                                    </x-filament::badge>
                                @else
                                    <x-filament::badge color="gray" size="sm" icon="heroicon-m-clock">
                                        Menunggu
                                    </x-filament::badge>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-filament::section>

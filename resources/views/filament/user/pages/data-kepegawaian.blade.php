<x-filament-panels::page>
    @if($employee)
        <div class="space-y-6">
            {{-- Data Pribadi --}}
            <x-filament::section>
                <x-slot name="heading">Data Pribadi</x-slot>
                <div class="grid grid-cols-2 gap-4">
                    <div><strong>Nama:</strong> {{ $employee->name }}</div>
                    <div><strong>NIP:</strong> {{ $employee->nip }}</div>
                    <div><strong>Email:</strong> {{ $employee->email }}</div>
                    <div><strong>Jabatan:</strong> {{ $employee->jabatan }}</div>
                    <div><strong>Status:</strong> {{ $employee->employee_status }}</div>
                    <div><strong>Grade:</strong> {{ $employee->grade?->name }}</div>
                </div>
            </x-filament::section>

            {{-- Keluarga --}}
            <x-filament::section>
                <x-slot name="heading">Data Keluarga</x-slot>
                @if($employee->families->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead><tr><th>Nama</th><th>Hubungan</th><th>Tanggal Lahir</th></tr></thead>
                            <tbody>
                                @foreach($employee->families as $family)
                                <tr><td>{{ $family->family_name }}</td><td>{{ $family->family_relation }}</td><td>{{ $family->family_birth_date?->format('d/m/Y') }}</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-500">Belum ada data keluarga</p>
                @endif
            </x-filament::section>

            {{-- Kontrak Kerja --}}
            <x-filament::section>
                <x-slot name="heading">Riwayat Kontrak Kerja</x-slot>
                @if($employee->agreements->count() > 0)
                    <div class="space-y-2">
                        @foreach($employee->agreements as $agreement)
                        <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded">
                            <strong>{{ $agreement->agreementType?->name }}</strong> | 
                            {{ $agreement->start_date?->format('d/m/Y') }} - {{ $agreement->end_date?->format('d/m/Y') }}
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500">Belum ada data kontrak</p>
                @endif
            </x-filament::section>

            {{-- Riwayat Mutasi --}}
            <x-filament::section>
                <x-slot name="heading">Riwayat Mutasi</x-slot>
                @if($employee->mutations->count() > 0)
                    <div class="space-y-2">
                        @foreach($employee->mutations as $mutation)
                        <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded">
                            {{ $mutation->mutation_date?->format('d/m/Y') }}: 
                            {{ $mutation->old_position }} → {{ $mutation->new_position }}
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500">Belum ada riwayat mutasi</p>
                @endif
            </x-filament::section>

            {{-- Riwayat Promosi --}}
            <x-filament::section>
                <x-slot name="heading">Riwayat Promosi</x-slot>
                @if($employee->promotions->count() > 0)
                    <div class="space-y-2">
                        @foreach($employee->promotions as $promotion)
                        <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded">
                            {{ $promotion->promotion_date?->format('d/m/Y') }}: 
                            {{ $promotion->old_grade }} → {{ $promotion->new_grade }}
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500">Belum ada riwayat promosi</p>
                @endif
            </x-filament::section>

            {{-- Riwayat Gaji --}}
            <x-filament::section>
                <x-slot name="heading">Riwayat Gaji</x-slot>
                @if($employee->salaries->count() > 0)
                    <div class="space-y-2">
                        @foreach($employee->salaries as $salary)
                        <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded">
                            {{ $salary->effective_date?->format('d/m/Y') }}: 
                            Rp {{ number_format($salary->base_salary, 0, ',', '.') }}
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500">Belum ada riwayat gaji</p>
                @endif
            </x-filament::section>

            {{-- Riwayat Pelatihan --}}
            <x-filament::section>
                <x-slot name="heading">Riwayat Pelatihan</x-slot>
                @if($employee->trainings->count() > 0)
                    <div class="space-y-2">
                        @foreach($employee->trainings as $training)
                        <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded">
                            <strong>{{ $training->training_name }}</strong> | 
                            {{ $training->start_date?->format('d/m/Y') }}
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500">Belum ada riwayat pelatihan</p>
                @endif
            </x-filament::section>

            {{-- Riwayat Surat Tugas --}}
            <x-filament::section>
                <x-slot name="heading">Riwayat Surat Tugas</x-slot>
                @if($employee->assignmentLetters->count() > 0)
                    <div class="space-y-2">
                        @foreach($employee->assignmentLetters as $assignment)
                        <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded">
                            {{ $assignment->letter_number }} | {{ $assignment->assignment_date?->format('d/m/Y') }}
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500">Belum ada riwayat surat tugas</p>
                @endif
            </x-filament::section>

            {{-- Riwayat Perjalanan Dinas --}}
            <x-filament::section>
                <x-slot name="heading">Riwayat Perjalanan Dinas</x-slot>
                @if($employee->businessTravelLetters->count() > 0)
                    <div class="space-y-2">
                        @foreach($employee->businessTravelLetters as $travel)
                        <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded">
                            {{ $travel->letter_number }} | {{ $travel->start_date?->format('d/m/Y') }} - {{ $travel->end_date?->format('d/m/Y') }}
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500">Belum ada riwayat perjalanan dinas</p>
                @endif
            </x-filament::section>

            {{-- Riwayat Laporan Harian --}}
            <x-filament::section>
                <x-slot name="heading">Riwayat Laporan Harian Kerja (10 Terakhir)</x-slot>
                @if($employee->dailyReports->count() > 0)
                    <div class="space-y-2">
                        @foreach($employee->dailyReports->take(10) as $report)
                        <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded">
                            {{ $report->report_date?->format('d/m/Y') }}: {{ Str::limit($report->activity_description, 100) }}
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500">Belum ada laporan harian</p>
                @endif
            </x-filament::section>

            {{-- Riwayat Kehadiran --}}
            <x-filament::section>
                <x-slot name="heading">Riwayat Kehadiran (10 Terakhir)</x-slot>
                @if($employee->attendanceRecords->count() > 0)
                    <div class="space-y-2">
                        @foreach($employee->attendanceRecords->take(10) as $attendance)
                        <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded">
                            {{ $attendance->attendance_date?->format('d/m/Y') }} | 
                            {{ $attendance->check_in_time }} - {{ $attendance->check_out_time }}
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500">Belum ada riwayat kehadiran</p>
                @endif
            </x-filament::section>
        </div>
    @else
        <x-filament::section>
            <p class="text-red-500">Data kepegawaian tidak ditemukan</p>
        </x-filament::section>
    @endif
</x-filament-panels::page>

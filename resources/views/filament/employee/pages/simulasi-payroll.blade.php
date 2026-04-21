<x-filament-panels::page>
    <form wire:submit.prevent="calculate">
        {{ $this->form }}
    </form>

    @if($payrollResult)
        <div class="mt-6 space-y-6">
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <x-filament::icon icon="heroicon-o-document-text" class="w-6 h-6 text-primary-600"/>
                            <span class="text-xl font-bold">Simulasi Slip Gaji - {{ $selectedEmployee->name }}</span>
                        </div>
                        <div class="text-sm text-gray-500">
                            Periode: {{ \Carbon\Carbon::parse($data['payroll_period'])->format('F Y') }}
                        </div>
                    </div>
                </x-slot>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- SISI KIRI: PENDAPATAN --}}
                    <div class="space-y-4">
                        <div class="flex items-center justify-between border-b pb-2">
                            <h3 class="text-lg font-semibold text-success-600">PENERIMAAN / INCOME</h3>
                            <x-filament::icon icon="heroicon-o-plus-circle" class="w-5 h-5 text-success-600"/>
                        </div>

                        <div class="space-y-2">
                            {{-- Base Salary --}}
                            <div class="flex justify-between items-center group">
                                <span class="text-gray-600">Gaji Pokok ({{ $selectedEmployee->grade->name ?? '-' }})</span>
                                <div class="flex items-center gap-2">
                                    <span class="font-medium">Rp {{ number_format($payrollResult['base_salary'], 0, ',', '.') }}</span>
                                    @if($selectedEmployee->basic_salary_id)
                                        <a href="{{ route('filament.employee.resources.master-employee-grades.edit', $selectedEmployee->basic_salary_id) }}" target="_blank" class="opacity-0 group-hover:opacity-100 transition-opacity" title="Edit Master Golongan">
                                            <x-filament::icon icon="heroicon-o-pencil-square" class="w-4 h-4 text-gray-400 hover:text-primary-600"/>
                                        </a>
                                    @endif
                                </div>
                            </div>

                            {{-- Allowances grouped by layers --}}
                            @foreach($payrollResult['allowances'] as $item)
                                @php
                                    $isGlobal = str_contains($item['note'] ?? '', 'Metode:');
                                    $isPosition = str_contains($item['note'] ?? '', 'jabatan');
                                    $isGrade = str_contains($item['note'] ?? '', 'golongan');
                                    $isIndividual = str_contains($item['note'] ?? '', 'Tunjangan khusus');
                                @endphp
                                <div class="flex justify-between items-center group">
                                    <div class="flex flex-col">
                                        <span class="text-gray-600">{{ $item['name'] }}</span>
                                        <span class="text-[10px] text-gray-400">
                                            @if($isGlobal) [Global] @elseif($isPosition) [Posisi] @elseif($isGrade) [Golongan] @elseif($isIndividual) [Pribadi] @endif
                                            {{ $item['note'] }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium">Rp {{ number_format($item['amount'], 0, ',', '.') }}</span>
                                        
                                        @if($isPosition && $selectedEmployee->employee_position_id)
                                            <a href="{{ route('filament.employee.resources.master-employee-positions.edit', $selectedEmployee->employee_position_id) }}" target="_blank" class="opacity-0 group-hover:opacity-100 transition-opacity">
                                                <x-filament::icon icon="heroicon-o-pencil-square" class="w-4 h-4 text-gray-400 hover:text-primary-600"/>
                                            </a>
                                        @elseif($isIndividual && $selectedEmployee->id)
                                            <a href="{{ route('filament.employee.resources.employees.edit', $selectedEmployee->id) }}" target="_blank" class="opacity-0 group-hover:opacity-100 transition-opacity">
                                                <x-filament::icon icon="heroicon-o-user" class="w-4 h-4 text-gray-400 hover:text-primary-600"/>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="pt-4 border-t-2 border-dashed flex justify-between items-center font-bold text-lg">
                            <span>TOTAL BRUTO</span>
                            <span class="text-primary-600">Rp {{ number_format($payrollResult['gross_salary'], 0, ',', '.') }}</span>
                        </div>
                    </div>

                    {{-- SISI KANAN: POTONGAN --}}
                    <div class="space-y-4">
                        <div class="flex items-center justify-between border-b pb-2">
                            <h3 class="text-lg font-semibold text-danger-600">POTONGAN / DEDUCTIONS</h3>
                            <x-filament::icon icon="heroicon-o-minus-circle" class="w-5 h-5 text-danger-600"/>
                        </div>

                        <div class="space-y-2">
                            @if(empty($payrollResult['deductions']))
                                <div class="text-center py-4 text-gray-400 italic">Tidak ada potongan</div>
                            @else
                                @foreach($payrollResult['deductions'] as $item)
                                    @php
                                        $isGlobal = str_contains($item['note'] ?? '', 'Metode:');
                                        $isPosition = str_contains($item['note'] ?? '', 'jabatan');
                                        $isIndividual = str_contains($item['note'] ?? '', 'Potongan khusus');
                                    @endphp
                                    <div class="flex justify-between items-center group">
                                        <div class="flex flex-col">
                                            <span class="text-gray-600">{{ $item['name'] }}</span>
                                            <span class="text-[10px] text-gray-400">
                                                @if($isGlobal) [Global] @elseif($isPosition) [Posisi] @elseif($isIndividual) [Pribadi] @endif
                                                {{ $item['note'] }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium text-danger-500">- Rp {{ number_format($item['amount'], 0, ',', '.') }}</span>
                                            
                                            @if($isPosition && $selectedEmployee->employee_position_id)
                                                <a href="{{ route('filament.employee.resources.master-employee-positions.edit', $selectedEmployee->employee_position_id) }}" target="_blank" class="opacity-0 group-hover:opacity-100 transition-opacity">
                                                    <x-filament::icon icon="heroicon-o-pencil-square" class="w-4 h-4 text-gray-400 hover:text-primary-600"/>
                                                </a>
                                            @elseif($isIndividual && $selectedEmployee->id)
                                                <a href="{{ route('filament.employee.resources.employees.edit', $selectedEmployee->id) }}" target="_blank" class="opacity-0 group-hover:opacity-100 transition-opacity">
                                                    <x-filament::icon icon="heroicon-o-user" class="w-4 h-4 text-gray-400 hover:text-primary-600"/>
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>

                        <div class="pt-4 border-t-2 border-dashed flex justify-between items-center font-bold text-lg">
                            <span>TOTAL POTONGAN</span>
                            <span class="text-danger-600">Rp {{ number_format($payrollResult['total_deduction'], 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                {{-- FINAL NET --}}
                <div class="mt-10 p-6 bg-primary-50 dark:bg-primary-900/20 rounded-xl border-2 border-primary-500 flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-primary-500 rounded-full">
                            <x-filament::icon icon="heroicon-o-banknotes" class="w-8 h-8 text-white"/>
                        </div>
                        <div>
                            <div class="text-sm text-primary-700 dark:text-primary-300 font-semibold uppercase tracking-wider">Estimasi Gaji Bersih (Take Home Pay)</div>
                            <div class="text-3xl font-extrabold text-primary-900 dark:text-primary-100">
                                Rp {{ number_format($payrollResult['net_salary'], 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-4 text-sm text-gray-600 dark:text-gray-400">
                        <div class="flex flex-col items-end">
                            <span>Hadir: {{ $payrollResult['attendance_data']['present_days'] }} / {{ $payrollResult['attendance_data']['work_days'] }} Hari</span>
                            <span>Telat: {{ $payrollResult['attendance_data']['late_count'] }} x</span>
                        </div>
                    </div>
                </div>
            </x-filament::section>

            {{-- LEGEND / KETERANGAN --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="p-4 bg-white dark:bg-white/5 rounded-lg shadow-sm border-l-4 border-gray-400">
                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400">GLOBAL</span>
                    <p class="text-sm mt-1 text-gray-600 dark:text-gray-400">Dihitung otomatis lewat rumus sistem untuk semua pegawai.</p>
                </div>
                <div class="p-4 bg-white dark:bg-white/5 rounded-lg shadow-sm border-l-4 border-blue-400">
                    <span class="text-xs font-bold text-blue-500">POSISI / GOLONGAN</span>
                    <p class="text-sm mt-1 text-gray-600 dark:text-gray-400">Diatur per jabatan/golongan di Master Data (Fixed nominal).</p>
                </div>
                <div class="p-4 bg-white dark:bg-white/5 rounded-lg shadow-sm border-l-4 border-purple-400">
                    <span class="text-xs font-bold text-purple-500">PRIBADI</span>
                    <p class="text-sm mt-1 text-gray-600 dark:text-gray-400">Diatur khusus per pegawai (Hutang, Cicilan, Koperasi, dll).</p>
                </div>
                <div class="p-4 bg-white dark:bg-white/5 rounded-lg shadow-sm border-l-4 border-yellow-400">
                    <span class="text-xs font-bold text-yellow-500">PRESENSI</span>
                    <p class="text-sm mt-1 text-gray-600 dark:text-gray-400">Potongan otomatis jika tidak hadir atau terlambat.</p>
                </div>
            </div>
        </div>
    @else
        <div class="mt-12 flex flex-col items-center justify-center text-center p-12 bg-white/50 dark:bg-white/5 rounded-2xl border-2 border-dashed border-gray-200 dark:border-white/10">
            <div class="p-4 bg-gray-100 dark:bg-white/5 rounded-full">
                <x-filament::icon icon="heroicon-o-magnifying-glass-circle" class="w-12 h-12 text-gray-400 dark:text-gray-500"/>
            </div>
            <h3 class="mt-6 text-xl font-bold text-gray-900 dark:text-white">Belum Ada Data Simulasi</h3>
            <p class="mt-2 text-gray-500 dark:text-gray-400 max-w-sm">Pilih Pegawai dan Periode di atas untuk melihat rincian kalkulasi payroll yang akan didapat.</p>
        </div>
    @endif
</x-filament-panels::page>

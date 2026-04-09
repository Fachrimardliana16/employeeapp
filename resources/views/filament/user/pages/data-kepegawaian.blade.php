<x-filament-panels::page>
    @if($employee)
        <div class="space-y-6">
            {{ $this->employeeInfolist }}
        </div>
    @else
        <x-filament::section>
            <p class="text-red-500">Data kepegawaian tidak ditemukan</p>
        </x-filament::section>
    @endif
</x-filament-panels::page>

<x-filament-widgets::widget class="fi-wi-table">
    <div wire:key="active-permissions-section">
        <x-filament::section 
            :heading="$this->getHeading() ?? 'Daftar Pegawai Izin & Cuti'" 
            collapsible
            collapsed
        >
            {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\Widgets\View\WidgetsRenderHook::TABLE_WIDGET_START, scopes: static::class) }}

            <div class="mt-4">
                {{ $this->table }}
            </div>

            {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\Widgets\View\WidgetsRenderHook::TABLE_WIDGET_END, scopes: static::class) }}
        </x-filament::section>
    </div>
</x-filament-widgets::widget>

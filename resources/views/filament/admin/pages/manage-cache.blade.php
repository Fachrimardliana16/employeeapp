<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {{-- Full Clear Card --}}
        <x-filament::section class="col-span-full border-primary-500">
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-sparkles class="w-6 h-6 text-primary-500" />
                    <span>Bersihkan Semua Cache</span>
                </div>
            </x-slot>
            
            <p class="text-sm text-gray-500 mb-4">
                Menghapus cache aplikasi, konfigurasi, rute, dan view dalam satu aksi. Gunakan ini jika Anda baru saja melakukan update besar atau mengalami kendala tampilan/data.
            </p>

            <x-filament::button 
                wire:click="clearAllCache" 
                color="primary"
                icon="heroicon-m-trash"
                class="w-full"
            >
                Bersihkan Semua
            </x-filament::button>
        </x-filament::section>

        {{-- Individual Cache Cards --}}
        <x-filament::section>
            <x-slot name="heading">Application Cache</x-slot>
            <p class="text-xs text-gray-500 mb-4">Menghapus data yang disimpan menggunakan Cache Facade (Data temporary, query result, dll).</p>
            <x-filament::button wire:click="clearAppCache" color="gray" outline class="w-full">
                Hapus
            </x-filament::button>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Config Cache</x-slot>
            <p class="text-xs text-gray-500 mb-4">Menghapus cache file konfigurasi. Berguna jika Anda merubah file di folder /config.</p>
            <x-filament::button wire:click="clearConfigCache" color="gray" outline class="w-full">
                Hapus
            </x-filament::button>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Route Cache</x-slot>
            <p class="text-xs text-gray-500 mb-4">Menghapus cache rute aplikasi. Berguna jika Anda baru saja menambahkan URL/Route baru.</p>
            <x-filament::button wire:click="clearRouteCache" color="gray" outline class="w-full">
                Hapus
            </x-filament::button>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">View Cache</x-slot>
            <p class="text-xs text-gray-500 mb-4">Menghapus hasil compile file Blade template (.blade.php).</p>
            <x-filament::button wire:click="clearViewCache" color="gray" outline class="w-full">
                Hapus
            </x-filament::button>
        </x-filament::section>

        <x-filament::section class="border-success-500">
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-bolt class="w-5 h-5 text-success-500" />
                    <span>Optimasi Aplikasi</span>
                </div>
            </x-slot>
            <p class="text-xs text-gray-500 mb-4">Menjalankan perintah 'optimize' untuk mempercepat loading (Cache config & route).</p>
            <x-filament::button wire:click="optimizeApp" color="success" outline class="w-full">
                Optimasi
            </x-filament::button>
        </x-filament::section>
    </div>
</x-filament-panels::page>

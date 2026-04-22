<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-circle-stack class="w-6 h-6 text-primary-500" />
                    <span>Backup Database</span>
                </div>
            </x-slot>
            <p class="text-sm text-gray-500 mb-4">Mencadangkan seluruh isi database dalam satu file SQL dump tunggal.</p>
            <x-filament::button 
                wire:click="runDbBackup" 
                wire:loading.attr="disabled"
                color="primary" 
                icon="heroicon-m-circle-stack" 
                class="w-full"
            >
                <span wire:loading.remove wire:target="runDbBackup">Mulai Backup DB</span>
                <span wire:loading wire:target="runDbBackup">Memproses...</span>
            </x-filament::button>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-table-cells class="w-6 h-6 text-emerald-500" />
                    <span>Backup Per Tabel</span>
                </div>
            </x-slot>
            <p class="text-sm text-gray-500 mb-4">Mencadangkan database dengan file terpisah untuk setiap tabel.</p>
            <x-filament::button 
                wire:click="runTableLevelBackup" 
                wire:loading.attr="disabled"
                color="success" 
                icon="heroicon-m-table-cells" 
                class="w-full"
            >
                <span wire:loading.remove wire:target="runTableLevelBackup">Mulai Backup Per Tabel</span>
                <span wire:loading wire:target="runTableLevelBackup">Memproses...</span>
            </x-filament::button>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-folder-minus class="w-6 h-6 text-warning-500" />
                    <span>Backup Lengkap</span>
                </div>
            </x-slot>
            <p class="text-sm text-gray-500 mb-4">Mencadangkan seluruh kode program, folder upload, dan database.</p>
            <x-filament::button 
                wire:click="runFullBackup" 
                wire:loading.attr="disabled"
                color="warning" 
                icon="heroicon-m-arrows-right-left" 
                class="w-full"
            >
                <span wire:loading.remove wire:target="runFullBackup">Mulai Backup Lengkap</span>
                <span wire:loading wire:target="runFullBackup">Memproses...</span>
            </x-filament::button>
        </x-filament::section>
    </div>

    @if(!empty($consoleOutput))
    <x-filament::section class="mb-6 border-gray-800 bg-gray-900 overflow-hidden">
        <x-slot name="heading">
            <div class="flex items-center gap-2 text-white">
                <x-heroicon-o-command-line class="w-5 h-5 text-success-500" />
                <span>Terminal Progress Backup</span>
            </div>
        </x-slot>
        
        <div class="font-mono text-xs text-green-400 p-4 bg-black rounded shadow-inner max-h-64 overflow-y-auto whitespace-pre-wrap">
            {{ $consoleOutput }}
        </div>
    </x-filament::section>
    @endif

    <x-filament::section>
        <x-slot name="heading">Riwayat Berkas Backup</x-slot>
        
        <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-800 dark:text-gray-400">
                    <tr>
                        <th class="px-6 py-3">Nama Berkas</th>
                        <th class="px-6 py-3 text-center">Tipe</th>
                        <th class="px-6 py-3 text-center">Ukuran</th>
                        <th class="px-6 py-3 text-center">Tanggal Dibuat</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @php $backups = $this->getBackups(); @endphp
                    @forelse($backups as $backup)
                    <tr class="bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800">
                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white truncate max-w-xs">
                            {{ $backup['name'] }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($backup['type'] === 'Lengkap')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-warning-100 text-warning-800 dark:bg-warning-900/30 dark:text-warning-400 border border-warning-200 dark:border-warning-800">
                                    Full Backup
                                </span>
                            @elseif($backup['type'] === 'Per Table')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                                    Per Table
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-400 border border-primary-200 dark:border-primary-800">
                                    DB Only
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            {{ $backup['size'] }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            {{ $backup['created_at'] }}
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <x-filament::button 
                                wire:click="downloadBackup('{{ $backup['path'] }}')" 
                                size="sm" 
                                color="gray" 
                                icon="heroicon-o-arrow-down-tray"
                                tooltip="Unduh Berkas"
                            >
                                Unduh
                            </x-filament::button>
                            <x-filament::button 
                                wire:click="deleteBackup('{{ $backup['path'] }}')" 
                                size="sm" 
                                color="danger" 
                                icon="heroicon-o-trash"
                                tooltip="Hapus Permanent"
                                wire:confirm="Apakah Anda yakin ingin menghapus backup ini?"
                            >
                                Hapus
                            </x-filament::button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-400 italic">
                            Belum ada berkas backup yang tersedia.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-panels::page>

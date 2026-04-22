<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-archive-box class="w-6 h-6 text-primary-500" />
                    <span>Backup Database (Saja)</span>
                </div>
            </x-slot>
            <p class="text-sm text-gray-500 mb-4">Mencadangkan seluruh isi database saat ini. Proses ini cenderung cepat dan menghasilkan berkas yang relatif kecil.</p>
            <x-filament::button wire:click="runDbBackup" color="primary" icon="heroicon-m-circle-stack" class="w-full">
                Mulai Backup Database
            </x-filament::button>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-folder-minus class="w-6 h-6 text-warning-500" />
                    <span>Backup Lengkap (Files + DB)</span>
                </div>
            </x-slot>
            <p class="text-sm text-gray-500 mb-4">Mencadangkan seluruh kode program, folder upload, dan database. Proses ini akan memakan waktu lebih lama tergantung besar data.</p>
            <x-filament::button wire:click="runFullBackup" color="warning" icon="heroicon-m-arrows-right-left" class="w-full">
                Mulai Backup Lengkap
            </x-filament::button>
        </x-filament::section>
    </div>

    <x-filament::section>
        <x-slot name="heading">Riwayat Berkas Backup</x-slot>
        
        <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-800 dark:text-gray-400">
                    <tr>
                        <th class="px-6 py-3">Nama Berkas</th>
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
                        <td colspan="4" class="px-6 py-8 text-center text-gray-400 italic">
                            Belum ada berkas backup yang tersedia.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-panels::page>

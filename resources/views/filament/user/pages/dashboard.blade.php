<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">
                Selamat Datang di Panel Pegawai
            </x-slot>

            <x-slot name="description">
                Dashboard untuk mengelola data pribadi, cuti, dan informasi kepegawaian Anda.
            </x-slot>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary-50 dark:bg-primary-500/20">
                            <x-heroicon-o-user class="h-6 w-6 text-primary-600 dark:text-primary-400" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Profile Saya</p>
                            <p class="text-lg font-semibold text-gray-950 dark:text-white">Kelola Data Pribadi</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-success-50 dark:bg-success-500/20">
                            <x-heroicon-o-calendar class="h-6 w-6 text-success-600 dark:text-success-400" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Cuti Saya</p>
                            <p class="text-lg font-semibold text-gray-950 dark:text-white">Ajukan Cuti</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-warning-50 dark:bg-warning-500/20">
                            <x-heroicon-o-clock class="h-6 w-6 text-warning-600 dark:text-warning-400" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Kehadiran</p>
                            <p class="text-lg font-semibold text-gray-950 dark:text-white">Rekap Absensi</p>
                        </div>
                    </div>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>


@php
    use App\Filament\Employee\Resources\EmployeeResource;
@endphp

@if (empty($processedData))
    <div class="rounded-xl border border-danger-300 bg-danger-50 px-4 py-3 text-sm text-danger-700 dark:border-danger-600 dark:bg-danger-900/20 dark:text-danger-200">
        Belum ada preview yang aktif. Jalankan aksi <strong>Import / Sync Data</strong> terlebih dahulu.
    </div>
@else
    @php
        $executionStatus = $execution['status'] ?? null;
        $executionProcessed = (int) ($execution['processed_rows'] ?? 0);
        $executionTotal = (int) ($execution['total_rows'] ?? $summary['total_rows']);
    @endphp

    @if (in_array($executionStatus, ['queued', 'processing', 'completed', 'failed'], true))
        @php
            $executionClasses = match ($executionStatus) {
                'completed' => 'border-success-300 bg-success-50 text-success-700 dark:border-success-600 dark:bg-success-900/20 dark:text-success-200',
                'failed' => 'border-danger-300 bg-danger-50 text-danger-700 dark:border-danger-600 dark:bg-danger-900/20 dark:text-danger-200',
                default => 'border-info-300 bg-info-50 text-info-700 dark:border-info-600 dark:bg-info-900/20 dark:text-info-200',
            };
        @endphp

        <div class="mb-3 rounded-xl border px-4 py-3 text-sm {{ $executionClasses }}">
            @if ($executionStatus === 'queued')
                <strong>Status:</strong> Menunggu antrean queue. Progres: {{ $executionProcessed }} / {{ $executionTotal }} baris.
            @elseif ($executionStatus === 'processing')
                <strong>Status:</strong> Sedang diproses di background. Progres: {{ $executionProcessed }} / {{ $executionTotal }} baris.
            @elseif ($executionStatus === 'completed')
                <strong>Status:</strong> Selesai. Hasil: {{ $execution['created'] ?? 0 }} data baru, {{ $execution['updated'] ?? 0 }} data diperbarui.
            @elseif ($executionStatus === 'failed')
                <strong>Status:</strong> Gagal diproses. {{ $execution['error_message'] ?? 'Terjadi kesalahan saat import.' }}
            @endif
        </div>
    @endif

    @php
        $accountProvisionStatus = $execution['account_provision_status'] ?? null;
        $accountProvisionProcessed = (int) ($execution['account_provision_processed'] ?? 0);
        $accountProvisionTotal = (int) ($execution['account_provision_total'] ?? 0);
    @endphp

    @if (in_array($accountProvisionStatus, ['queued', 'processing', 'completed', 'failed'], true))
        @php
            $accountProvisionClasses = match ($accountProvisionStatus) {
                'completed' => 'border-success-300 bg-success-50 text-success-700 dark:border-success-600 dark:bg-success-900/20 dark:text-success-200',
                'failed' => 'border-danger-300 bg-danger-50 text-danger-700 dark:border-danger-600 dark:bg-danger-900/20 dark:text-danger-200',
                default => 'border-info-300 bg-info-50 text-info-700 dark:border-info-600 dark:bg-info-900/20 dark:text-info-200',
            };
        @endphp

        <div class="mb-3 rounded-xl border px-4 py-3 text-sm {{ $accountProvisionClasses }}">
            @if ($accountProvisionStatus === 'queued')
                <strong>Akun User:</strong> Menunggu antrean. Progres: {{ $accountProvisionProcessed }} / {{ $accountProvisionTotal }} pegawai.
            @elseif ($accountProvisionStatus === 'processing')
                <strong>Akun User:</strong> Sedang dibuat di background. Progres: {{ $accountProvisionProcessed }} / {{ $accountProvisionTotal }} pegawai.
            @elseif ($accountProvisionStatus === 'completed')
                <strong>Akun User:</strong> Selesai. Berhasil {{ $execution['account_provision_created'] ?? 0 }}, dilewati {{ $execution['account_provision_skipped'] ?? 0 }}, gagal {{ $execution['account_provision_failed'] ?? 0 }}.
            @elseif ($accountProvisionStatus === 'failed')
                <strong>Akun User:</strong> Gagal diproses. {{ $execution['account_provision_error_message'] ?? 'Terjadi kesalahan saat pembuatan akun.' }}
            @endif
        </div>
    @endif

    <div class="mb-3 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-xl border border-gray-200 bg-white px-3 py-2 dark:border-gray-700 dark:bg-gray-900">
            <div class="text-xs text-gray-500 dark:text-gray-400">Total Baris Valid</div>
            <div class="text-xl font-semibold text-gray-900 dark:text-white">{{ $summary['total_rows'] }}</div>
        </div>

        @if (($execution['status'] ?? null) === 'completed')
            <div class="mb-3 text-xs text-gray-500 dark:text-gray-400">
                Pegawai baru yang belum dibuatkan akun user:
                <strong>{{ (int) ($execution['pending_account_provision_count'] ?? 0) }}</strong>
            </div>
        @endif
        <div class="rounded-xl border border-success-300 bg-success-50 px-3 py-2 dark:border-success-700 dark:bg-success-900/20">
            <div class="text-xs text-success-700 dark:text-success-300">Rencana Buat Baru</div>
            <div class="text-xl font-semibold text-success-800 dark:text-success-200">{{ $summary['created'] }}</div>
        </div>
        <div class="rounded-xl border border-info-300 bg-info-50 px-3 py-2 dark:border-info-700 dark:bg-info-900/20">
            <div class="text-xs text-info-700 dark:text-info-300">Rencana Update</div>
            <div class="text-xl font-semibold text-info-800 dark:text-info-200">{{ $summary['updated'] }}</div>
        </div>
        <div class="rounded-xl border border-warning-300 bg-warning-50 px-3 py-2 dark:border-warning-700 dark:bg-warning-900/20">
            <div class="text-xs text-warning-700 dark:text-warning-300">PIN Otomatis</div>
            <div class="text-xl font-semibold text-warning-800 dark:text-warning-200">{{ $summary['auto_pin'] }}</div>
        </div>
    </div>

    @if ($generatedAtLabel)
        <div class="mb-3 text-xs text-gray-500 dark:text-gray-400">
            Preview digenerate pada: <strong>{{ $generatedAtLabel }}</strong>
        </div>
    @endif

    @if (!empty($warnings))
        <div class="mb-3 rounded-xl border border-warning-300 bg-warning-50 px-4 py-3 text-sm text-warning-800 dark:border-warning-700 dark:bg-warning-900/20 dark:text-warning-200">
            <div class="mb-1 font-semibold">Warning</div>
            <ul class="list-disc space-y-1 pl-5">
                @foreach ($warnings as $warning)
                    <li>{{ $warning }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
        <div class="max-h-[58vh] overflow-auto">
            <table class="w-full min-w-[1500px] border-collapse text-xs">
                <thead class="sticky top-0 z-[1] bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="border border-gray-200 px-2 py-2 text-left font-semibold text-gray-700 dark:border-gray-700 dark:text-gray-200">Baris</th>
                        <th class="border border-gray-200 px-2 py-2 text-left font-semibold text-gray-700 dark:border-gray-700 dark:text-gray-200">Aksi</th>
                        <th class="border border-gray-200 px-2 py-2 text-left font-semibold text-gray-700 dark:border-gray-700 dark:text-gray-200">ID Target</th>
                        <th class="border border-gray-200 px-2 py-2 text-left font-semibold text-gray-700 dark:border-gray-700 dark:text-gray-200">PIN Efektif</th>
                        <th class="border border-gray-200 px-2 py-2 text-left font-semibold text-gray-700 dark:border-gray-700 dark:text-gray-200">Sumber PIN</th>
                        @foreach ($headers as $header)
                            <th class="border border-gray-200 px-2 py-2 text-left align-top whitespace-nowrap dark:border-gray-700">
                                <div class="font-semibold text-gray-700 dark:text-gray-200">
                                    {{ $columnLabels[$header] ?? ucfirst(str_replace('_', ' ', $header)) }}
                                </div>
                                <div class="font-mono text-[11px] text-gray-500 dark:text-gray-400">{{ $header }}</div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($processedData as $item)
                        @php
                            $row = $item['row'] ?? [];
                            $pinSource = $item['pin_source'] ?? 'input';
                            $pinSourceLabel = $pinSource === 'generated' ? 'otomatis' : ($pinSource === 'existing' ? 'data lama' : 'file');
                        @endphp
                        <tr class="bg-white odd:bg-white even:bg-gray-50 dark:bg-gray-900 dark:odd:bg-gray-900 dark:even:bg-gray-800/40">
                            <td class="border border-gray-200 px-2 py-2 align-top text-gray-700 dark:border-gray-700 dark:text-gray-200">{{ $item['line_number'] ?? '-' }}</td>
                            <td class="border border-gray-200 px-2 py-2 align-top text-gray-700 dark:border-gray-700 dark:text-gray-200">{{ ($item['is_new'] ?? false) ? 'Buat Baru' : 'Update' }}</td>
                            <td class="border border-gray-200 px-2 py-2 align-top text-gray-700 dark:border-gray-700 dark:text-gray-200">{{ $row['id'] ?? '-' }}</td>
                            <td class="border border-gray-200 px-2 py-2 align-top text-gray-700 dark:border-gray-700 dark:text-gray-200">{{ $item['effective_pin'] ?? '-' }}</td>
                            <td class="border border-gray-200 px-2 py-2 align-top text-gray-700 dark:border-gray-700 dark:text-gray-200">{{ $pinSourceLabel }}</td>
                            @foreach ($headers as $header)
                                <td class="border border-gray-200 px-2 py-2 align-top whitespace-nowrap text-gray-700 dark:border-gray-700 dark:text-gray-200">
                                    {{ EmployeeResource::formatEmployeeImportPreviewValue($header, $row[$header] ?? '') }}
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

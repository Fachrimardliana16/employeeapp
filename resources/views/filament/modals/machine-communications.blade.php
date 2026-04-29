<div class="space-y-3">
    <div class="flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400 mb-4">
        <span>Total: <strong>{{ $record->communications()->count() }}</strong> log</span>
        <span>Berhasil: <strong class="text-green-600">{{ $record->communication_success_count }}</strong></span>
        <span>Gagal: <strong class="text-red-600">{{ $record->communication_error_count }}</strong></span>
    </div>

    @if($logs->isEmpty())
        <p class="text-center text-gray-400 py-8">Belum ada log komunikasi.</p>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-xs border-collapse">
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-800">
                        <th class="px-2 py-1 text-left">Waktu</th>
                        <th class="px-2 py-1 text-left">Endpoint</th>
                        <th class="px-2 py-1 text-left">IP</th>
                        <th class="px-2 py-1 text-left">Status</th>
                        <th class="px-2 py-1 text-left">Error</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                        <tr class="{{ $log->hasError() ? 'bg-red-50 dark:bg-red-900/20' : '' }} border-b border-gray-100 dark:border-gray-700">
                            <td class="px-2 py-1 whitespace-nowrap">{{ $log->created_at->format('d/m H:i:s') }}</td>
                            <td class="px-2 py-1">
                                <span class="font-mono bg-gray-200 dark:bg-gray-700 px-1 rounded">{{ $log->endpoint }}</span>
                            </td>
                            <td class="px-2 py-1 font-mono">{{ $log->ip_address ?? '-' }}</td>
                            <td class="px-2 py-1">
                                @if($log->hasError())
                                    <span class="text-red-600 font-bold">✗ {{ $log->response_code ?? 'ERR' }}</span>
                                @else
                                    <span class="text-green-600">✓ {{ $log->response_code ?? 'OK' }}</span>
                                @endif
                            </td>
                            <td class="px-2 py-1 text-red-500 max-w-xs truncate" title="{{ $log->error_message }}">
                                {{ $log->error_message ?? '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="text-xs text-gray-400 mt-2">Menampilkan 50 log terbaru.</p>
    @endif
</div>

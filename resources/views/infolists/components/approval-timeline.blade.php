@php
    $record = $getRecord();
    $isPending = $record->approval_status === 'pending';
    $isApproved = $record->approval_status === 'approved';
    $isRejected = $record->approval_status === 'rejected';
@endphp

<div style="display: flex; flex-direction: column; padding-top: 8px; padding-bottom: 8px;">
    <!-- Step 1: Diajukan -->
    <div style="display: flex;">
        <!-- Icon & Line Column -->
        <div style="flex: none; width: 40px; margin-right: 16px; display: flex; flex-direction: column; align-items: center;">
            <div style="width: 32px; height: 32px; border-radius: 9999px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;" class="bg-primary-100 dark:bg-primary-900 border border-primary-200 dark:border-primary-800">
                <x-heroicon-m-paper-airplane style="width: 16px; height: 16px;" class="text-primary-600 dark:text-primary-400"/>
            </div>
            <!-- Vertical Line -->
            <div style="flex-grow: 1; width: 2px; margin-top: 8px; margin-bottom: 8px; border-radius: 9999px;" class="bg-gray-200 dark:bg-white/10"></div>
        </div>
        
        <!-- Content Column -->
        <div style="flex-grow: 1; min-width: 0; padding-bottom: 40px;">
            <div style="padding: 16px; display: flex; flex-direction: column; border-radius: 8px;" class="shadow-sm border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-800">
                <h3 style="margin: 0; font-size: 14px; font-weight: bold;" class="text-gray-900 dark:text-white">Pengajuan Terkirim</h3>
                <time style="display: block; margin-bottom: 8px; font-size: 12px; font-weight: 500; margin-top: 4px;" class="text-gray-500 dark:text-gray-400">
                    {{ $record->created_at?->translatedFormat('l, d F Y - H:i') ?? '-' }}
                </time>
                <p style="margin: 0; font-size: 14px;" class="text-gray-600 dark:text-gray-400">Dokumen dan permohonan berhasil masuk ke sistem Kepegawaian.</p>
            </div>
        </div>
    </div>

    <!-- Step 2: Keputusan -->
    <div style="display: flex;">
        <!-- Icon Column -->
        <div style="flex: none; width: 40px; margin-right: 16px; display: flex; flex-direction: column; align-items: center;">
            @if($isPending)
                <div style="width: 32px; height: 32px; border-radius: 9999px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;" class="bg-warning-100 dark:bg-warning-900 border border-warning-200 dark:border-warning-800">
                    <x-heroicon-m-clock style="width: 16px; height: 16px; animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;" class="text-warning-600 dark:text-warning-400"/>
                </div>
            @elseif($isApproved)
                <div style="width: 32px; height: 32px; border-radius: 9999px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;" class="bg-success-100 dark:bg-success-900 border border-success-200 dark:border-success-800">
                    <x-heroicon-m-check-circle style="width: 16px; height: 16px;" class="text-success-600 dark:text-success-400"/>
                </div>
            @elseif($isRejected)
                <div style="width: 32px; height: 32px; border-radius: 9999px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;" class="bg-danger-100 dark:bg-danger-900 border border-danger-200 dark:border-danger-800">
                    <x-heroicon-m-x-circle style="width: 16px; height: 16px;" class="text-danger-600 dark:text-danger-400"/>
                </div>
            @endif
        </div>
        
        <!-- Content Column -->
        <div style="flex-grow: 1; min-width: 0; padding-bottom: 8px;">
            @if($isPending)
                <div style="padding: 16px; display: flex; flex-direction: column; border-radius: 8px;" class="shadow-sm border border-warning-200 dark:border-warning-900/50 bg-warning-50 dark:bg-warning-900/10">
                    <h3 style="margin: 0; font-size: 14px; font-weight: bold;" class="text-warning-700 dark:text-warning-400">Menunggu Diproses</h3>
                    <p style="margin: 0; font-size: 14px; margin-top: 4px;" class="text-warning-600 dark:text-warning-500">Berkas masih dalam antrean untuk dievaluasi oleh tim Kepegawaian.</p>
                </div>
            @elseif($isApproved)
                <div style="padding: 16px; display: flex; flex-direction: column; border-radius: 8px;" class="shadow-sm border border-success-200 dark:border-success-900/50 bg-success-50 dark:bg-success-900/10">
                    <h3 style="margin: 0; font-size: 14px; font-weight: bold;" class="text-success-700 dark:text-success-400">Disetujui oleh {{ $record->approver?->name ?? 'Admin' }}</h3>
                    <time style="display: block; margin-bottom: 8px; font-size: 12px; font-weight: 500; margin-top: 4px;" class="text-success-600 dark:text-success-500">
                        {{ $record->approved_at?->translatedFormat('l, d F Y - H:i') ?? '-' }}
                    </time>
                    @if($record->approval_notes)
                        <div style="margin-top: 8px; font-size: 14px; font-style: italic; padding: 8px 12px; border-radius: 4px;" class="bg-white dark:bg-gray-800 border border-success-100 dark:border-success-900 shadow-sm text-success-800 dark:text-success-300">
                            "{{ $record->approval_notes }}"
                        </div>
                    @else
                        <p style="margin: 0; font-size: 14px; margin-top: 4px;" class="text-success-600 dark:text-success-500">Pengajuan telah disetujui tanpa catatan tambahan.</p>
                    @endif
                </div>
            @elseif($isRejected)
                <div style="padding: 16px; display: flex; flex-direction: column; border-radius: 8px;" class="shadow-sm border border-danger-200 dark:border-danger-900/50 bg-danger-50 dark:bg-danger-900/10">
                    <h3 style="margin: 0; font-size: 14px; font-weight: bold;" class="text-danger-700 dark:text-danger-400">Ditolak oleh {{ $record->approver?->name ?? 'Admin' }}</h3>
                    <time style="display: block; margin-bottom: 8px; font-size: 12px; font-weight: 500; margin-top: 4px;" class="text-danger-600 dark:text-danger-500">
                        {{ $record->approved_at?->translatedFormat('l, d F Y - H:i') ?? '-' }}
                    </time>
                    @if($record->approval_notes)
                        <div style="margin-top: 8px; font-size: 14px; font-style: italic; padding: 8px 12px; border-radius: 4px;" class="bg-white dark:bg-gray-800 border border-danger-100 dark:border-danger-900 shadow-sm text-danger-800 dark:text-danger-300">
                            "{{ $record->approval_notes }}"
                        </div>
                    @else
                        <p style="margin: 0; font-size: 14px; margin-top: 4px;" class="text-danger-600 dark:text-danger-500">Pengajuan ditolak. Hubungi bagian kepegawaian untuk informasi lebih lanjut.</p>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

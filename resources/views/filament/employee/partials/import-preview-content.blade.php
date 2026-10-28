@php
    use App\Filament\Employee\Resources\EmployeeResource;
@endphp

@if (empty($processedData))
    <div style="padding:12px 14px; border-radius:12px; border:1px solid #fecaca; background:#fef2f2; color:#b91c1c; font-size:13px;">
        Belum ada preview yang aktif. Jalankan aksi <strong>Import / Sync Data</strong> terlebih dahulu.
    </div>
@else
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:10px; margin-bottom:12px;">
        <div style="padding:10px 12px; border-radius:12px; border:1px solid #e5e7eb; background:#ffffff;">
            <div style="font-size:11px; color:#6b7280;">Total Baris Valid</div>
            <div style="font-size:18px; font-weight:700; color:#111827;">{{ $summary['total_rows'] }}</div>
        </div>
        <div style="padding:10px 12px; border-radius:12px; border:1px solid #a7f3d0; background:#ecfdf5;">
            <div style="font-size:11px; color:#047857;">Rencana Buat Baru</div>
            <div style="font-size:18px; font-weight:700; color:#065f46;">{{ $summary['created'] }}</div>
        </div>
        <div style="padding:10px 12px; border-radius:12px; border:1px solid #bae6fd; background:#f0f9ff;">
            <div style="font-size:11px; color:#0369a1;">Rencana Update</div>
            <div style="font-size:18px; font-weight:700; color:#0c4a6e;">{{ $summary['updated'] }}</div>
        </div>
        <div style="padding:10px 12px; border-radius:12px; border:1px solid #fde68a; background:#fffbeb;">
            <div style="font-size:11px; color:#b45309;">PIN Otomatis</div>
            <div style="font-size:18px; font-weight:700; color:#92400e;">{{ $summary['auto_pin'] }}</div>
        </div>
    </div>

    @if ($generatedAtLabel)
        <div style="font-size:12px; color:#6b7280; margin-bottom:10px;">
            Preview digenerate pada: <strong>{{ $generatedAtLabel }}</strong>
        </div>
    @endif

    @if (!empty($warnings))
        <div style="padding:12px 14px; border-radius:12px; border:1px solid #fdba74; background:#fff7ed; color:#9a3412; margin-bottom:12px; font-size:13px;">
            <div style="font-weight:700; margin-bottom:6px;">Warning</div>
            <ul style="margin:0; padding-left:18px;">
                @foreach ($warnings as $warning)
                    <li style="margin:4px 0;">{{ $warning }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div style="border:1px solid #e5e7eb; border-radius:12px; overflow:hidden;">
        <div style="max-height:58vh; overflow:auto;">
            <table style="width:100%; min-width:1500px; border-collapse:collapse; font-size:12px;">
                <thead style="position:sticky; top:0; z-index:1; background:#f9fafb;">
                    <tr>
                        <th style="border:1px solid #e5e7eb; padding:7px 8px; text-align:left;">Baris</th>
                        <th style="border:1px solid #e5e7eb; padding:7px 8px; text-align:left;">Aksi</th>
                        <th style="border:1px solid #e5e7eb; padding:7px 8px; text-align:left;">ID Target</th>
                        <th style="border:1px solid #e5e7eb; padding:7px 8px; text-align:left;">PIN Efektif</th>
                        <th style="border:1px solid #e5e7eb; padding:7px 8px; text-align:left;">Sumber PIN</th>
                        @foreach ($headers as $header)
                            <th style="border:1px solid #e5e7eb; padding:7px 8px; text-align:left; white-space:nowrap;">
                                <div style="font-weight:700; color:#374151;">{{ $columnLabels[$header] ?? ucfirst(str_replace('_', ' ', $header)) }}</div>
                                <div style="font-family:ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size:11px; color:#6b7280;">{{ $header }}</div>
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
                        <tr>
                            <td style="border:1px solid #e5e7eb; padding:7px 8px; vertical-align:top;">{{ $item['line_number'] ?? '-' }}</td>
                            <td style="border:1px solid #e5e7eb; padding:7px 8px; vertical-align:top;">{{ ($item['is_new'] ?? false) ? 'Buat Baru' : 'Update' }}</td>
                            <td style="border:1px solid #e5e7eb; padding:7px 8px; vertical-align:top;">{{ $row['id'] ?? '-' }}</td>
                            <td style="border:1px solid #e5e7eb; padding:7px 8px; vertical-align:top;">{{ $item['effective_pin'] ?? '-' }}</td>
                            <td style="border:1px solid #e5e7eb; padding:7px 8px; vertical-align:top;">{{ $pinSourceLabel }}</td>
                            @foreach ($headers as $header)
                                <td style="border:1px solid #e5e7eb; padding:7px 8px; vertical-align:top; white-space:nowrap;">
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

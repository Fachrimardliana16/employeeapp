@extends('mobile.layouts.app')

@section('title', 'Laporan Harian')
@section('header-title', 'Laporan Harian')

@section('content')

{{-- Today's Report Status --}}
@if($todayReport)
  <div style="padding: 1.5rem; background: rgba(16, 185, 129, 0.05); border-radius: 20px; margin-bottom: 2rem; border: 1px solid rgba(16, 185, 129, 0.2); position: relative; overflow: hidden;">
    <div style="position: absolute; top: 0; left: 0; width: 4px; height: 100%; background: var(--accent);"></div>
    <div style="font-size: 0.65rem; font-weight: 800; color: var(--accent); letter-spacing: 2px; text-transform: uppercase; margin-bottom: 0.5rem;">STATUS // LAPORAN HARI INI</div>
    <div style="font-size: 0.85rem; color: var(--gray-400); line-height: 1.5; font-style: italic;">
      "{{ Str::limit($todayReport->work_description, 100) }}"
    </div>
  </div>
@endif

{{-- New Report Form --}}
<div class="section-header">
  <span class="section-title">BUAT LAPORAN BARU</span>
</div>
<div class="card" style="margin-bottom: 2rem;">
  <div class="card-body" style="padding: 1.5rem;">
    <form action="{{ route('mobile.daily-reports.store') }}" method="POST" id="reportForm">
      @csrf
      <div class="form-group">
        <label class="form-label">TANGGAL LAPORAN</label>
        <input type="date" name="daily_report_date" id="daily_report_date" class="form-control"
          value="{{ old('daily_report_date', today()->format('Y-m-d')) }}" required>
        @error('daily_report_date')<div class="form-error">⚠️ {{ $message }}</div>@enderror
      </div>
      <div class="form-group">
        <label class="form-label">STATUS PEKERJAAN</label>
        <select name="work_status" id="work_status" class="form-control" required>
          <option value="">-- PILIH STATUS --</option>
          <option value="completed" {{ old('work_status') === 'completed' ? 'selected' : '' }}>✅ SELESAI</option>
          <option value="in_progress" {{ old('work_status') === 'in_progress' ? 'selected' : '' }}>🔄 DALAM PROSES</option>
          <option value="pending" {{ old('work_status') === 'pending' ? 'selected' : '' }}>⏳ MENUNGGU</option>
        </select>
        @error('work_status')<div class="form-error">⚠️ {{ $message }}</div>@enderror
      </div>
      <div class="form-group">
        <label class="form-label">ISI LAPORAN / KEGIATAN</label>
        <textarea name="work_description" id="work_description" class="form-control" rows="5"
          placeholder="Jelaskan detail pekerjaan Anda hari ini..." required>{{ old('work_description') }}</textarea>
        @error('work_description')<div class="form-error">⚠️ {{ $message }}</div>@enderror
      </div>
      <div class="form-group" style="margin-bottom: 0;">
        <label class="form-label">CATATAN TAMBAHAN (OPSIONAL)</label>
        <textarea name="desc" id="desc" class="form-control" rows="3"
          placeholder="Catatan tambahan jika ada...">{{ old('desc') }}</textarea>
      </div>
      <button type="submit" id="reportBtn" class="btn btn-primary btn-full" style="margin-top: 1.5rem; height: 52px; border-radius: 12px; font-weight: 800; letter-spacing: 1px;">
        SIMPAN LAPORAN
      </button>
    </form>
  </div>
</div>

{{-- Report List --}}
@if($reports->isNotEmpty())
  <div class="section-header">
    <span class="section-title">LOG_ARCHIVE // SYSTEM</span>
  </div>
  <div class="card" style="margin-bottom: 3rem;">
    @foreach($reports as $report)
      @php
        $statusColors = ['completed' => 'success', 'in_progress' => 'primary', 'pending' => 'warning'];
        $statusLabels = ['completed' => 'DONE', 'in_progress' => 'PROC', 'pending' => 'WAIT'];
        $statusIcons = ['completed' => '✅', 'in_progress' => '🔄', 'pending' => '⏳'];
      @endphp
      <div style="padding: 1.25rem; border-bottom: 1px solid var(--glass-border);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.75rem; margin-bottom: 0.75rem;">
          <div>
            <div style="font-size: 0.8rem; font-weight: 800; color: var(--white); letter-spacing: 0.5px;">
              {{ \Carbon\Carbon::parse($report->daily_report_date)->format('d/m/Y') }}
            </div>
            <div style="font-size: 0.65rem; color: var(--gray-500); font-weight: 800; margin-top: 0.125rem;">{{ strtoupper($report->created_at->format('H:i')) }} // STAMP</div>
          </div>
          <span class="badge badge-{{ $statusColors[$report->work_status] ?? 'gray' }}" style="font-size: 0.6rem; flex-shrink: 0; letter-spacing: 1px; font-weight: 900; padding: 2px 8px;">
             {{ $statusLabels[$report->work_status] ?? $report->work_status }}
          </span>
        </div>
        <div style="font-size: 0.85rem; color: var(--gray-400); line-height: 1.6; font-weight: 500;">
          {{ Str::limit($report->work_description, 120) }}
        </div>
      </div>
    @endforeach
  </div>
@else
  <div class="section-header">
    <span class="section-title">LOG_ARCHIVE // SYSTEM</span>
  </div>
  <div class="card" style="margin-bottom: 3rem;">
    <div class="card-body">
      <div class="empty-state">
        <div class="empty-state-icon">📄</div>
        <div class="empty-state-title">DATA_EMPTY // ARCHIVE</div>
        <div class="empty-state-desc">No historical logs found for this user account.</div>
      </div>
    </div>
  </div>
@endif

@push('scripts')
<script>
document.getElementById('reportForm').addEventListener('submit', function() {
  const btn = document.getElementById('reportBtn');
  btn.disabled = true;
  btn.innerHTML = '<div class="spinner"></div> Menyimpan...';
});
</script>
@endpush

@endsection

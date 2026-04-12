@extends('mobile.layouts.app')

@section('title', 'Resign/Pensiun')
@section('header-title', 'Resign/Pensiun')

@section('content')

{{-- Entry Form --}}
<div class="section-header">
  <span class="section-title">PENGAJUAN BARU</span>
</div>

<div class="card" style="margin-bottom: 2rem;">
  <div class="card-body" style="padding: 1.5rem;">
    <form action="{{ route('mobile.retirement.store') }}" method="POST" id="retirementForm">
      @csrf
      <div class="form-group">
        <label class="form-label">JENIS PENGAJUAN</label>
        <select name="master_employee_retirement_type_id" class="form-control" required>
          <option value="">-- PILIH JENIS --</option>
          @foreach($retirementTypes as $type)
            <option value="{{ $type->id }}" {{ old('master_employee_retirement_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
          @endforeach
        </select>
        @error('master_employee_retirement_type_id')<div class="form-error">⚠️ {{ $message }}</div>@enderror
      </div>

      <div class="form-group">
        <label class="form-label">HARI TERAKHIR BEKERJA</label>
        <input type="date" name="last_working_day" class="form-control" value="{{ old('last_working_day') }}" required>
        @error('last_working_day')<div class="form-error">⚠️ {{ $message }}</div>@enderror
      </div>

      <div class="form-group">
        <label class="form-label">ALASAN PENGAJUAN</label>
        <textarea name="reason" class="form-control" rows="4" placeholder="Jelaskan alasan pengajuan Anda secara formal..." required>{{ old('reason') }}</textarea>
        @error('reason')<div class="form-error">⚠️ {{ $message }}</div>@enderror
      </div>

      <div class="form-group">
        <label class="form-label">ALAMAT SURAT MENYURAT POST-KERJA</label>
        <textarea name="forwarding_address" class="form-control" rows="2" placeholder="Alamat setelah tidak bekerja di perusahaan...">{{ old('forwarding_address') }}</textarea>
      </div>

      <button type="submit" id="submitBtn" class="btn btn-primary btn-full" style="margin-top: 1rem; height: 52px; border-radius: 12px; font-weight: 800; letter-spacing: 0.5px;">
        KIRIM PENGAJUAN
      </button>
    </form>
  </div>
</div>

{{-- List --}}
<div class="section-header">
  <span class="section-title">RIWAYAT PENGAJUAN</span>
</div>

@if($requests->isNotEmpty())
  <div class="card" style="margin-bottom: 3rem;">
    @foreach($requests as $req)
      <div class="list-item">
        <div class="list-icon" style="background: var(--primary-light); color: var(--primary);">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        </div>
        <div class="list-content">
          <div class="list-title" style="font-weight: 700; color: var(--gray-800);">{{ $req->retirement_type }}</div>
          <div class="list-subtitle">Harap Berhenti: {{ $req->last_working_day ? $req->last_working_day->format('d/m/Y') : '-' }}</div>
        </div>
        <div class="list-meta">
          @php
            $statusColors = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'];
            $statusLabels = ['pending' => 'MENUNGGU', 'approved' => 'DISETUJUI', 'rejected' => 'DITOLAK'];
          @endphp
          <span class="badge badge-{{ $statusColors[$req->approval_status] ?? 'gray' }}" style="font-size: 0.6rem; font-weight: 900; letter-spacing: 0.5px;">
            {{ $statusLabels[$req->approval_status] ?? $req->approval_status }}
          </span>
        </div>
      </div>
    @endforeach
  </div>
@else
  <div class="card" style="margin-bottom: 3rem;">
    <div class="card-body">
      <div class="empty-state">
        <div class="empty-state-icon">📄</div>
        <div class="empty-state-title">BELUM ADA PENGAJUAN</div>
        <div class="empty-state-desc">Riwayat pengajuan pensiun atau pengunduran diri Anda akan muncul di sini.</div>
      </div>
    </div>
  </div>
@endif

@push('scripts')
<script>
document.getElementById('retirementForm')?.addEventListener('submit', function() {
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<div class="spinner"></div> Mengirim...';
});
</script>
@endpush

@endsection

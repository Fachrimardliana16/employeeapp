@extends('mobile.layouts.app')

@section('title', 'Ajukan Izin/Cuti')
@section('header-title', 'Ajukan Izin')

@section('content')

<form action="{{ route('mobile.permissions.store') }}" method="POST" id="permForm" enctype="multipart/form-data">
  @csrf

  <div class="section-header">
    <span class="section-title">DETAIL PENGAJUAN</span>
  </div>
  <div class="card" style="margin-bottom: 2rem;">
    <div class="card-body" style="display: flex; flex-direction: column; gap: 1.5rem;">

      <div class="form-group" style="margin-bottom: 0;">
        <label class="form-label">JENIS IZIN / CUTI <span class="required">*</span></label>
        <select name="permission_id" id="permission_id" class="form-control" required>
          <option value="">-- PILIH JENIS --</option>
          @foreach($permissionTypes as $type)
            <option value="{{ $type->id }}" {{ old('permission_id') == $type->id ? 'selected' : '' }}>
              {{ $type->permission_type_name }}
            </option>
          @endforeach
        </select>
        @error('permission_id')<div class="form-error">⚠️ {{ $message }}</div>@enderror
      </div>

      <div class="form-group" style="margin-bottom: 1rem;">
        <label class="form-label">TANGGAL MULAI <span class="required">*</span></label>
        <div class="input-with-icon">
          <span class="input-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
          </span>
          <input type="date" name="start_permission_date" id="start_permission_date" class="form-control"
            value="{{ old('start_permission_date') }}" min="{{ today()->format('Y-m-d') }}" required>
        </div>
        @error('start_permission_date')<div class="form-error">⚠️ {{ $message }}</div>@enderror
      </div>

      <div class="form-group" style="margin-bottom: 0;">
        <label class="form-label">TANGGAL BERAKHIR <span class="required">*</span></label>
        <div class="input-with-icon">
          <span class="input-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
          </span>
          <input type="date" name="end_permission_date" id="end_permission_date" class="form-control"
            value="{{ old('end_permission_date') }}" min="{{ today()->format('Y-m-d') }}" required>
        </div>
        @error('end_permission_date')<div class="form-error">⚠️ {{ $message }}</div>@enderror
      </div>

      <div class="form-group" style="margin-bottom: 0;">
        <label class="form-label">ALASAN / KETERANGAN <span class="required">*</span></label>
        <textarea name="permission_desc" id="permission_desc" class="form-control" rows="4"
          placeholder="Jelaskan alasan pengajuan Anda..." required>{{ old('permission_desc') }}</textarea>
        @error('permission_desc')<div class="form-error">⚠️ {{ $message }}</div>@enderror
      </div>

    </div>
  </div>

  <div class="section-header">
    <span class="section-title">UNGGAH DOKUMEN PENDUKUNG (OPSIONAL)</span>
  </div>
  <div class="card" style="margin-bottom: 2rem;">
    <div class="card-body">
      <label for="scan_doc" class="file-upload-area" id="fileUploadLabel">
        <div class="file-upload-icon">📁</div>
        <div style="font-weight: 800; font-size: 0.7rem; color: var(--primary); margin-bottom: 0.25rem; letter-spacing: 1px;">
          KLIK UNTUK UNGGAH FILE
        </div>
        <div class="file-upload-label">PDF, JPG, PNG // MAKS 5MB</div>
        <input type="file" name="scan_doc" id="scan_doc" accept=".pdf,.jpg,.jpeg,.png" style="display:none;"
          onchange="updateFileLabel(this)">
      </label>
      @error('scan_doc')<div class="form-error mt-2">⚠️ {{ $message }}</div>@enderror
    </div>
  </div>

  <button type="submit" id="submitBtn" class="btn btn-primary btn-full" style="margin-bottom: 1rem; height: 56px; border-radius: 16px; font-weight: 900; letter-spacing: 1px;">
    KIRIM PENGAJUAN
  </button>

  <a href="{{ route('mobile.permissions') }}" class="btn btn-ghost btn-full" style="margin-bottom: 3rem; font-size: 0.75rem; font-weight: 800; opacity: 0.6;">
    ← BATALKAN
  </a>
</form>

@push('scripts')
<script>
function updateFileLabel(input) {
  const label = document.querySelector('.file-upload-label');
  if (input.files && input.files[0]) {
    label.textContent = input.files[0].name;
    document.getElementById('fileUploadLabel').style.borderColor = 'var(--accent)';
    document.getElementById('fileUploadLabel').style.background = 'var(--accent-light)';
  }
}

// Sync end date min with start date
document.getElementById('start_permission_date').addEventListener('change', function() {
  document.getElementById('end_permission_date').min = this.value;
  if (document.getElementById('end_permission_date').value < this.value) {
    document.getElementById('end_permission_date').value = this.value;
  }
});

document.getElementById('permForm').addEventListener('submit', function() {
  const btn = document.getElementById('submitBtn');
  btn.disabled = true;
  btn.innerHTML = '<div class="spinner"></div> Mengirim...';
});
</script>
@endpush

@endsection

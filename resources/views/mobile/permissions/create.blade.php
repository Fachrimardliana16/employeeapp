@extends('mobile.layouts.app')

@section('title', 'Ajukan Izin/Cuti')
@section('header-title', 'Ajukan Izin')

@section('content')

<form action="{{ route('mobile.permissions.store') }}" method="POST" id="permForm" enctype="multipart/form-data">
  @csrf

  <div class="section-header">
    <span class="section-title">REQUEST_DETAILS</span>
  </div>
  <div class="card" style="margin-bottom: 2rem;">
    <div class="card-body" style="display: flex; flex-direction: column; gap: 1.5rem;">

      <div class="form-group" style="margin-bottom: 0;">
        <label class="form-label">REQUEST_TYPE <span class="required">*</span></label>
        <select name="permission_id" id="permission_id" class="form-control" required>
          <option value="">-- UNKNOWN_TYPE --</option>
          @foreach($permissionTypes as $type)
            <option value="{{ $type->id }}" {{ old('permission_id') == $type->id ? 'selected' : '' }}>
              {{ $type->permission_type_name }}
            </option>
          @endforeach
        </select>
        @error('permission_id')<div class="form-error">⚠️ {{ $message }}</div>@enderror
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
        <div class="form-group" style="margin-bottom: 0;">
          <label class="form-label">START_TIMESTAMP <span class="required">*</span></label>
          <input type="date" name="start_permission_date" id="start_permission_date" class="form-control"
            value="{{ old('start_permission_date') }}" min="{{ today()->format('Y-m-d') }}" required>
          @error('start_permission_date')<div class="form-error">⚠️ {{ $message }}</div>@enderror
        </div>
        <div class="form-group" style="margin-bottom: 0;">
          <label class="form-label">END_TIMESTAMP <span class="required">*</span></label>
          <input type="date" name="end_permission_date" id="end_permission_date" class="form-control"
            value="{{ old('end_permission_date') }}" min="{{ today()->format('Y-m-d') }}" required>
          @error('end_permission_date')<div class="form-error">⚠️ {{ $message }}</div>@enderror
        </div>
      </div>

      <div class="form-group" style="margin-bottom: 0;">
        <label class="form-label">OBJECTIVE_LOG <span class="required">*</span></label>
        <textarea name="permission_desc" id="permission_desc" class="form-control" rows="4"
          placeholder="Enter detailed reasoning for this request session..." required>{{ old('permission_desc') }}</textarea>
        @error('permission_desc')<div class="form-error">⚠️ {{ $message }}</div>@enderror
      </div>

    </div>
  </div>

  <div class="section-header">
    <span class="section-title">ATTACH_LOG_EVIDENCE (OPTIONAL)</span>
  </div>
  <div class="card" style="margin-bottom: 2rem;">
    <div class="card-body">
      <label for="scan_doc" class="file-upload-area" id="fileUploadLabel">
        <div class="file-upload-icon">📁</div>
        <div style="font-weight: 800; font-size: 0.7rem; color: var(--primary); margin-bottom: 0.25rem; letter-spacing: 1px;">
          TAP_TO_UPLOAD_LOG
        </div>
        <div class="file-upload-label">PDF, JPG, PNG // MAX 5MB</div>
        <input type="file" name="scan_doc" id="scan_doc" accept=".pdf,.jpg,.jpeg,.png" style="display:none;"
          onchange="updateFileLabel(this)">
      </label>
      @error('scan_doc')<div class="form-error mt-2">⚠️ {{ $message }}</div>@enderror
    </div>
  </div>

  <button type="submit" id="submitBtn" class="btn btn-primary btn-full" style="margin-bottom: 1rem; height: 56px; border-radius: 16px; font-weight: 900; letter-spacing: 1px;">
    INITIALIZE_SUBMISSION
  </button>

  <a href="{{ route('mobile.permissions') }}" class="btn btn-ghost btn-full" style="margin-bottom: 3rem; font-size: 0.75rem; font-weight: 800; opacity: 0.6;">
    ← ABORT_REQUEST
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

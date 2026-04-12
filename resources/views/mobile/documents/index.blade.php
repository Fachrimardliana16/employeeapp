@extends('mobile.layouts.app')

@section('title', 'Dokumen Saya')
@section('header-title', 'Dokumen Saya')

@section('content')

{{-- Upload Form --}}
<div class="section-header">
  <span class="section-title">TAMBAH DOKUMEN</span>
</div>

<div class="card" style="margin-bottom: 2rem;">
  <div class="card-body" style="padding: 1.5rem;">
    <form action="{{ route('mobile.documents.store') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
      @csrf
      <div class="form-group">
        <label class="form-label">TIPE DOKUMEN</label>
        <select name="document_type" class="form-control" required>
          <option value="">-- PILIH TIPE --</option>
          @foreach($documentTypes as $key => $label)
            <option value="{{ $key }}" {{ old('document_type') == $key ? 'selected' : '' }}>{{ $label }}</option>
          @endforeach
        </select>
        @error('document_type')<div class="form-error">⚠️ {{ $message }}</div>@enderror
      </div>

      <div class="form-group">
        <label class="form-label">NAMA DOKUMEN</label>
        <input type="text" name="document_name" class="form-control" placeholder="Contoh: KTP Asli" value="{{ old('document_name') }}" required>
        @error('document_name')<div class="form-error">⚠️ {{ $message }}</div>@enderror
      </div>

      <div class="form-group">
        <label class="form-label">NOMOR DOKUMEN (OPSIONAL)</label>
        <input type="text" name="document_number" class="form-control" placeholder="Nomor KTP/Ijazah/Sertifikat" value="{{ old('document_number') }}">
      </div>

      <div class="form-group">
        <label class="form-label">FILE DOKUMEN (MAX 5MB)</label>
        <div class="file-upload-wrapper" id="fileUploadContainer" style="position: relative; border: 2px dashed var(--gray-200); border-radius: 12px; padding: 1.5rem; text-align: center; background: var(--gray-50); transition: all 0.2s;">
            <input type="file" name="file" id="documentFile" class="sr-only" required accept=".pdf,.jpg,.jpeg,.png">
            <label for="documentFile" style="cursor: pointer; display: block;">
                <div style="font-size: 2rem; margin-bottom: 0.5rem; filter: grayscale(1);">📄</div>
                <div class="file-upload-label" style="font-size: 0.8rem; font-weight: 700; color: var(--gray-600);">Pilih File (PDF/Gambar)</div>
                <div style="font-size: 0.7rem; color: var(--gray-400); margin-top: 0.25rem;">Klik untuk menjelajah file</div>
            </label>
        </div>
        @error('file')<div class="form-error">⚠️ {{ $message }}</div>@enderror
      </div>

      <button type="submit" id="uploadBtn" class="btn btn-primary btn-full" style="margin-top: 1rem; height: 52px; border-radius: 12px; font-weight: 800; letter-spacing: 0.5px;">
        UNGGAH DOKUMEN
      </button>
    </form>
  </div>
</div>

{{-- Document List --}}
<div class="section-header">
  <span class="section-title">DAFTAR DOKUMEN</span>
</div>

@if($documents->isNotEmpty())
  <div class="card" style="margin-bottom: 3rem;">
    @foreach($documents as $doc)
      <div class="list-item">
        <div class="list-icon" style="background: var(--primary-light); color: var(--primary);">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
        </div>
        <div class="list-content">
          <div class="list-title" style="font-weight: 700; color: var(--gray-800);">{{ $doc->document_name }}</div>
          <div class="list-subtitle">{{ $doc->document_type }} • {{ $doc->created_at->format('d/m/Y') }}</div>
        </div>
        <div class="list-meta">
          <a href="{{ asset('storage/'.$doc->file_path) }}" target="_blank" class="btn-primary" style="padding: 6px 12px; font-size: 0.7rem; border-radius: 8px; font-weight: 800;">LIHAT</a>
        </div>
      </div>
    @endforeach
  </div>
@else
  <div class="card" style="margin-bottom: 3rem;">
    <div class="card-body">
      <div class="empty-state">
        <div class="empty-state-icon">📁</div>
        <div class="empty-state-title">BELUM ADA DOKUMEN</div>
        <div class="empty-state-desc">Silakan unggah dokumen resmi Anda melalui form di atas.</div>
      </div>
    </div>
  </div>
@endif

@push('scripts')
<script>
document.getElementById('documentFile')?.addEventListener('change', function() {
    const f = this.files[0];
    const label = document.querySelector('.file-upload-label');
    const container = document.getElementById('fileUploadContainer');
    if (f) {
        label.textContent = f.name;
        container.style.borderColor = 'var(--primary)';
        container.style.background = 'var(--primary-light)';
    }
});

document.getElementById('uploadForm')?.addEventListener('submit', function() {
    const btn = document.getElementById('uploadBtn');
    btn.disabled = true;
    btn.innerHTML = '<div class="spinner"></div> Mengunggah...';
});
</script>
@endpush

@endsection

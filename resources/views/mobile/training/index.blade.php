@extends('mobile.layouts.app')

@section('title', 'Riwayat Pelatihan')
@section('header-title', 'Pelatihan')

@section('content')

{{-- Entry Form --}}
<div class="section-header">
  <span class="section-title">TAMBAH PELATIHAN</span>
</div>

<div class="card" style="margin-bottom: 2rem;">
  <div class="card-body" style="padding: 1.5rem;">
    <form action="{{ route('mobile.training.store') }}" method="POST" enctype="multipart/form-data" id="trainingForm">
      @csrf
      <div class="form-group">
        <label class="form-label">JUDUL PELATIHAN</label>
        <input type="text" name="training_title" class="form-control" placeholder="Nama kursus atau pelatihan" value="{{ old('training_title') }}" required>
        @error('training_title')<div class="form-error">⚠️ {{ $message }}</div>@enderror
      </div>

      <div class="form-group">
        <label class="form-label">TANGGAL PELAKSANAAN</label>
        <input type="date" name="training_date" class="form-control" value="{{ old('training_date', today()->format('Y-m-d')) }}" required>
        @error('training_date')<div class="form-error">⚠️ {{ $message }}</div>@enderror
      </div>

      <div class="form-group">
        <label class="form-label">PENYELENGGARA</label>
        <input type="text" name="organizer" class="form-control" placeholder="Instansi atau Lembaga" value="{{ old('organizer') }}">
      </div>

      <div class="form-group">
        <label class="form-label">LOKASI (OPSIONAL)</label>
        <input type="text" name="training_location" class="form-control" placeholder="Kota atau Alamat" value="{{ old('training_location') }}">
      </div>

      <div class="form-group">
        <label class="form-label">SERTIFIKAT (PDF/JPG, MAX 5MB)</label>
        <div class="file-upload-wrapper" id="fileUploadContainer" style="position: relative; border: 2px dashed var(--gray-200); border-radius: 12px; padding: 1.5rem; text-align: center; background: var(--gray-50); transition: all 0.2s;">
            <input type="file" name="certificate" id="certificateFile" class="sr-only" accept=".pdf,.jpg,.jpeg,.png">
            <label for="certificateFile" style="cursor: pointer; display: block;">
                <div style="font-size: 2rem; margin-bottom: 0.5rem; filter: grayscale(1);">📜</div>
                <div class="file-upload-label" style="font-size: 0.8rem; font-weight: 700; color: var(--gray-600);">Pilih Sertifikat</div>
            </label>
        </div>
        @error('certificate')<div class="form-error">⚠️ {{ $message }}</div>@enderror
      </div>

      <button type="submit" id="submitBtn" class="btn btn-primary btn-full" style="margin-top: 1rem; height: 52px; border-radius: 12px; font-weight: 800; letter-spacing: 0.5px;">
        SIMPAN RIWAYAT
      </button>
    </form>
  </div>
</div>

{{-- List --}}
<div class="section-header">
  <span class="section-title">RIWAYAT PELATIHAN</span>
</div>

@if($trainings->isNotEmpty())
  <div class="card" style="margin-bottom: 3rem;">
    @foreach($trainings as $training)
      <div class="list-item">
        <div class="list-icon" style="background: var(--primary-light); color: var(--primary);">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
        </div>
        <div class="list-content">
          <div class="list-title" style="font-weight: 700; color: var(--gray-800);">{{ $training->training_title }}</div>
          <div class="list-subtitle">{{ $training->organizer ?? 'N/A' }}</div>
          <div style="font-size: 0.7rem; color: var(--gray-500); margin-top: 0.25rem;">
            📅 {{ $training->training_date ? $training->training_date->format('d/m/Y') : '-' }}
          </div>
        </div>
        @if($training->docs_training)
        <div class="list-meta">
          <a href="{{ url('image-view/'.$training->docs_training) }}" target="_blank" class="btn-primary" style="padding: 6px 12px; font-size: 0.65rem; border-radius: 8px; font-weight: 800;">DOK</a>
        </div>
        @endif
      </div>
    @endforeach
  </div>
@else
  <div class="card" style="margin-bottom: 3rem;">
    <div class="card-body">
      <div class="empty-state">
        <div class="empty-state-icon">🎓</div>
        <div class="empty-state-title">BELUM ADA PELATIHAN</div>
        <div class="empty-state-desc">Riwayat pendidikan & pelatihan Anda akan muncul di sini.</div>
      </div>
    </div>
  </div>
@endif

@push('scripts')
<script>
document.getElementById('certificateFile')?.addEventListener('change', function() {
    const f = this.files[0];
    const label = document.querySelector('.file-upload-label');
    const container = document.getElementById('fileUploadContainer');
    if (f) {
        label.textContent = f.name;
        container.style.borderColor = 'var(--primary)';
        container.style.background = 'var(--primary-light)';
    }
});

document.getElementById('trainingForm')?.addEventListener('submit', function() {
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<div class="spinner"></div> Menyimpan...';
});
</script>
@endpush

@endsection

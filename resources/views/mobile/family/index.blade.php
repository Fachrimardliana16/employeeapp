@extends('mobile.layouts.app')

@section('title', 'Data Keluarga')
@section('header-title', 'Keluarga')

@section('content')

{{-- Entry Form --}}
<div class="section-header">
  <span class="section-title">TAMBAH ANGGOTA KELUARGA</span>
</div>

<div class="card" style="margin-bottom: 2rem;">
  <div class="card-body" style="padding: 1.5rem;">
    <form action="{{ route('mobile.family.store') }}" method="POST" id="familyForm">
      @csrf
      <div class="form-group">
        <label class="form-label">HUBUNGAN KELUARGA</label>
        <select name="master_employee_families_id" class="form-control" required>
          <option value="">-- PILIH HUBUNGAN --</option>
          @foreach($masterFamilies as $mf)
            <option value="{{ $mf->id }}" {{ old('master_employee_families_id') == $mf->id ? 'selected' : '' }}>{{ $mf->name }}</option>
          @endforeach
        </select>
        @error('master_employee_families_id')<div class="form-error">⚠️ {{ $message }}</div>@enderror
      </div>

      <div class="form-group">
        <label class="form-label">NAMA LENGKAP</label>
        <input type="text" name="family_name" class="form-control" placeholder="Nama sesuai KTP" value="{{ old('family_name') }}" required>
        @error('family_name')<div class="form-error">⚠️ {{ $message }}</div>@enderror
      </div>

      <div class="form-group">
        <label class="form-label">JENIS KELAMIN</label>
        <select name="family_gender" class="form-control" required>
          <option value="L" {{ old('family_gender') == 'L' ? 'selected' : '' }}>Laki-laki</option>
          <option value="P" {{ old('family_gender') == 'P' ? 'selected' : '' }}>Perempuan</option>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label">NIK (OPSIONAL)</label>
        <input type="text" name="family_id_number" class="form-control" placeholder="Nomor Induk Kependudukan" value="{{ old('family_id_number') }}">
      </div>

      <div class="form-group">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
            <div>
                <label class="form-label">TEMPAT LAHIR</label>
                <input type="text" name="family_place_birth" class="form-control" placeholder="Kota" value="{{ old('family_place_birth') }}">
            </div>
            <div>
                <label class="form-label">TGL LAHIR</label>
                <input type="date" name="family_date_birth" class="form-control" value="{{ old('family_date_birth') }}">
            </div>
        </div>
      </div>

      <button type="submit" id="submitBtn" class="btn btn-primary btn-full" style="margin-top: 1rem; height: 52px; border-radius: 12px; font-weight: 800; letter-spacing: 0.5px;">
        SIMPAN DATA
      </button>
    </form>
  </div>
</div>

{{-- List --}}
<div class="section-header">
  <span class="section-title">ANGGOTA KELUARGA</span>
</div>

@if($families->isNotEmpty())
  <div class="card" style="margin-bottom: 3rem;">
    @foreach($families as $family)
      <div class="list-item">
        <div class="list-icon" style="background: var(--primary-light); color: var(--primary);">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <div class="list-content">
          <div class="list-title" style="font-weight: 700; color: var(--gray-800);">{{ $family->family_name }}</div>
          <div class="list-subtitle">{{ $family->masterFamily?->name ?? 'Keluarga' }} • {{ $family->family_gender == 'L' ? 'Laki-laki' : 'Perempuan' }}</div>
          @if($family->family_date_birth)
          <div style="font-size: 0.7rem; color: var(--gray-500); margin-top: 0.25rem;">
            📅 {{ $family->family_date_birth->format('d/m/Y') }}
          </div>
          @endif
        </div>
      </div>
    @endforeach
  </div>
@else
  <div class="card" style="margin-bottom: 3rem;">
    <div class="card-body">
      <div class="empty-state">
        <div class="empty-state-icon">👨‍👩‍👧‍👦</div>
        <div class="empty-state-title">BELUM ADA DATA</div>
        <div class="empty-state-desc">Data keluarga Anda akan muncul di sini.</div>
      </div>
    </div>
  </div>
@endif

@push('scripts')
<script>
document.getElementById('familyForm')?.addEventListener('submit', function() {
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<div class="spinner"></div> Menyimpan...';
});
</script>
@endpush

@endsection

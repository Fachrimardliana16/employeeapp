@extends('mobile.layouts.app')

@section('title', 'Profil Saya')
@section('header-title', 'Profil Saya')

@section('content')

@if(!$employee)
  <div class="card" style="margin-top: 2rem;">
    <div class="card-body">
      <div class="empty-state" style="padding: 2.5rem 1.5rem; text-align: center;">
        <div style="font-size: 3rem; margin-bottom: 1rem;">🛡️</div>
        <div class="empty-state-title">Akses Data Terbatas</div>
        <div class="empty-state-desc">
          Akun Anda belum terverifikasi dengan sistem kepegawaian internal. Silakan hubungi <strong>Bagian Umum & Kepegawaian</strong>.
        </div>
      </div>
    </div>
  </div>
@else

{{-- Profile Header --}}
<div class="card" style="margin-bottom: 2rem; background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); border: none;">
  <div class="card-body" style="padding: 2.5rem 1.5rem; text-align: center;">
    <div class="profile-avatar-wrapper" style="width: 100px; height: 100px; margin: 0 auto 1.5rem; position: relative;">
      @if($employee->image)
        <img src="{{ asset('storage/'.$employee->image) }}" style="width: 100%; height: 100%; border-radius: 30px; object-fit: cover; border: 4px solid rgba(255,255,255,0.2);">
      @else
        <div style="width: 100%; height: 100%; border-radius: 30px; background: rgba(255,255,255,0.1); border: 2px dashed rgba(255,255,255,0.3); display: flex; align-items: center; justify-content: center; color: white; font-size: 2.5rem; font-weight: 800;">{{ substr($employee->name ?? 'P', 0, 1) }}</div>
      @endif
    </div>
    <div style="font-size: 0.75rem; color: rgba(255,255,255,0.7); font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.25rem;">NIPPAM: {{ $employee->nippam }}</div>
    <div style="font-size: 1.5rem; font-weight: 800; color: white; margin-bottom: 0.5rem; letter-spacing: -0.02em;">{{ $employee->name }}</div>
    <div style="display: inline-block; background: rgba(255,255,255,0.2); padding: 4px 16px; border-radius: 100px; color: white; font-size: 0.75rem; font-weight: 700;">{{ $employee->position->name ?? 'Pegawai' }}</div>
  </div>
</div>

{{-- Stats --}}
<div class="section-header">
  <span class="section-title">STATISTIK BULANAN</span>
</div>
<div class="stats-grid" style="margin-bottom: 2rem;">
  <div class="stat-card" style="border-left: 4px solid var(--success);">
    <div class="stat-value" style="color: var(--gray-800);">{{ $monthlyStats['presence'] }}</div>
    <div class="stat-label">Hadir</div>
  </div>
  <div class="stat-card" style="border-left: 4px solid var(--warning);">
    <div class="stat-value" style="color: var(--gray-800);">{{ $monthlyStats['late'] }}</div>
    <div class="stat-label">Terlambat</div>
  </div>
  <div class="stat-card" style="border-left: 4px solid var(--primary);">
    <div class="stat-value" style="color: var(--gray-800);">{{ $monthlyStats['permit'] }}</div>
    <div class="stat-label">Izin</div>
  </div>
  <div class="stat-card" style="border-left: 4px solid var(--danger);">
    <div class="stat-value" style="color: var(--gray-800);">{{ $monthlyStats['absence'] }}</div>
    <div class="stat-label">Alpa</div>
  </div>
</div>

<div class="section-header">
  <span class="section-title">DATA PRIBADI</span>
</div>

<div class="card" style="margin-bottom: 2rem;">
  <div class="card-body">
    <div class="info-row">
      <div class="info-key">No. KTP</div>
      <div class="info-value">{{ $employee->id_number ?? '-' }}</div>
    </div>
    <div class="info-row">
      <div class="info-key">Jenis Kelamin</div>
      <div class="info-value">{{ $employee->gender === 'male' ? 'Laki-laki' : ($employee->gender === 'female' ? 'Perempuan' : '-') }}</div>
    </div>
    <div class="info-row">
      <div class="info-key">Tempat Lahir</div>
      <div class="info-value">{{ $employee->place_birth ?? '-' }}</div>
    </div>
    <div class="info-row">
      <div class="info-key">Tanggal Lahir</div>
      <div class="info-value">{{ $employee->date_birth ? \Carbon\Carbon::parse($employee->date_birth)->format('d F Y') : '-' }}</div>
    </div>
    <div class="info-row">
      <div class="info-key">Status Nikah</div>
      <div class="info-value">{{ $employee->marital_status ?? '-' }}</div>
    </div>
  </div>
</div>

<div class="section-header">
  <span class="section-title">KONTAK & ALAMAT</span>
</div>

<div class="card" style="margin-bottom: 2rem;">
  <div class="card-body">
    <div class="info-row">
      <div class="info-key">Email</div>
      <div class="info-value">{{ $employee->email ?? '-' }}</div>
    </div>
    <div class="info-row">
      <div class="info-key">No. Telepon</div>
      <div class="info-value">{{ $employee->phone_number ?? '-' }}</div>
    </div>
    <div class="info-row">
      <div class="info-key">Alamat</div>
      <div class="info-value">{{ $employee->address ?? '-' }}</div>
    </div>
  </div>
</div>

<div class="section-header">
  <span class="section-title">KEPEGAWAIAN</span>
</div>

<div class="card" style="margin-bottom: 2rem;">
  <div class="card-body">
    <div class="info-row">
      <div class="info-key">Departemen</div>
      <div class="info-value">{{ $employee->department?->name ?? '-' }}</div>
    </div>
    <div class="info-row">
      <div class="info-key">Pangkat/Golongan</div>
      <div class="info-value">{{ $employee->masterRank?->rank_name ?? '-' }} ({{ $employee->masterRank?->rank_code ?? '-' }})</div>
    </div>
    <div class="info-row">
      <div class="info-key">Unit Kerja</div>
      <div class="info-value">{{ $employee->masterDepartment?->name ?? 'Pusat' }}</div>
    </div>
    <div class="info-row">
      <div class="info-key">Tgl Masuk</div>
      <div class="info-value">{{ $employee->entry_date ? \Carbon\Carbon::parse($employee->entry_date)->format('d M Y') : '-' }}</div>
    </div>
  </div>
</div>

@endif

@endsection

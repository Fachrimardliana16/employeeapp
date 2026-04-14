@extends('mobile.layouts.app')

@section('title', 'Dashboard')
@section('header-title', 'Dashboard')

@section('content')

{{-- Attendance Hub v3 --}}
<div class="attendance-hub-v3" style="margin-bottom: 2rem; position: relative;">
  <div class="hub-content" style="position: relative; z-index: 2; padding: 1.5rem; border-radius: 24px; background: white; border: 1px solid var(--gray-200); box-shadow: 0 10px 25px rgba(0,0,0,0.05);">
    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
      <div>
        <div class="hub-status-tag" style="display: flex; align-items: center; gap: 8px; background: var(--gray-50); padding: 4px 12px; border-radius: 100px; border: 1px solid var(--gray-200); width: fit-content; margin-bottom: 1rem;">
          <span class="pulse-dot" style="width: 8px; height: 8px; background: {{ count($todayRecords) > 0 ? 'var(--success)' : 'var(--warning)' }}; border-radius: 50%;"></span>
          <span style="font-size: 0.7rem; font-weight: 700; color: var(--gray-600); text-transform: uppercase; letter-spacing: 0.5px;">
            {{ count($todayRecords) > 0 ? 'SUDAH ABSEN' : 'BELUM ABSEN HARI INI' }}
          </span>
        </div>
        <div id="liveClock" style="font-size: 3rem; font-weight: 800; color: var(--gray-800); line-height: 1; letter-spacing: -1px;">{{ now()->format('H:i:s') }}</div>
        <div style="font-size: 0.8rem; font-weight: 700; color: var(--primary); margin-top: 0.5rem;">{{ now()->locale('id')->translatedFormat('l, d F Y') }}</div>
      </div>
      @if($employee)
        <a href="{{ route('mobile.profile') }}" class="hub-avatar-wrapper" style="width: 60px; height: 60px; border-radius: 18px; overflow: hidden; border: 2px solid var(--gray-200);">
          @if($employee->image)
            <img src="{{ Storage::url($employee->image) }}" style="width: 100%; height: 100%; object-fit: cover;">
          @else
            <div style="width: 100%; height: 100%; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: 800;">{{ substr($employee->name ?? 'P', 0, 1) }}</div>
          @endif
        </a>
      @endif
    </div>
  </div>
</div>

{{-- Stats Grid --}}
<div class="section-header">
  <span class="section-title">STATISTIK</span>
  <span style="font-size: 0.7rem; color: var(--primary); font-weight: 700;">{{ now()->locale('id')->format('F Y') }}</span>
</div>

<div class="stats-grid" style="margin-bottom: 2rem;">
  <div class="stat-card" style="border-left: 4px solid var(--success);">
    <div class="stat-value" style="color: var(--gray-800);">{{ $stats['presence'] }}</div>
    <div class="stat-label">Hadir</div>
  </div>
  <div class="stat-card" style="border-left: 4px solid var(--warning);">
    <div class="stat-value" style="color: var(--gray-800);">{{ $stats['late'] }}</div>
    <div class="stat-label">Terlambat</div>
  </div>
  <div class="stat-card" style="border-left: 4px solid var(--primary);">
    <div class="stat-value" style="color: var(--gray-800);">{{ $stats['permit'] }}</div>
    <div class="stat-label">Izin</div>
  </div>
  <div class="stat-card" style="border-left: 4px solid var(--danger);">
    <div class="stat-value" style="color: var(--gray-800);">{{ $stats['absence'] }}</div>
    <div class="stat-label">Alpa</div>
  </div>
</div>

{{-- Menu Utama --}}
<div class="section-header">
  <span class="section-title">MENU UTAMA</span>
</div>

<div class="menu-section">
  <div class="menu-grid">
    <a href="{{ route('mobile.attendance') }}" class="menu-item">
      <div class="menu-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
      </div>
      <span class="menu-label">Presensi</span>
    </a>
    <a href="{{ route('mobile.permissions.create') }}" class="menu-item">
      <div class="menu-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><line x1="12" y1="14" x2="12" y2="18"/><line x1="10" y1="16" x2="14" y2="16"/></svg>
      </div>
      <span class="menu-label">Buat Izin</span>
    </a>
    <a href="{{ route('mobile.daily-reports') }}" class="menu-item">
      <div class="menu-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
      </div>
      <span class="menu-label">Laporan</span>
    </a>
    <a href="{{ route('mobile.profile') }}" class="menu-item">
      <div class="menu-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      </div>
      <span class="menu-label">Profil</span>
    </a>
    <a href="{{ route('mobile.documents') }}" class="menu-item">
      <div class="menu-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
      </div>
      <span class="menu-label">Dokumen</span>
    </a>
    <a href="{{ route('mobile.training') }}" class="menu-item">
      <div class="menu-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
      </div>
      <span class="menu-label">Pelatihan</span>
    </a>
    <a href="{{ route('mobile.family') }}" class="menu-item">
      <div class="menu-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      </div>
      <span class="menu-label">Keluarga</span>
    </a>
    <a href="{{ route('mobile.permissions') }}" class="menu-item">
      <div class="menu-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 8 12 12 14 14"/><path d="M12 2a10 10 0 1 0 10 10"/></svg>
      </div>
      <span class="menu-label">Riwayat Izin</span>
    </a>
    <a href="{{ route('mobile.retirement') }}" class="menu-item">
      <div class="menu-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
      </div>
      <span class="menu-label">Resign/Pensiun</span>
    </a>
  </div>
</div>

{{-- Recent Activity --}}
@if($recentAttendance->isNotEmpty())
<div class="section-header">
  <span class="section-title">AKTIVITAS TERBARU</span>
</div>
<div class="card" style="margin-bottom: 2rem;">
  @foreach($recentAttendance->take(3) as $record)
    @php
      $stateColors = ['in' => 'success', 'out' => 'primary', 'ot_in' => 'info', 'ot_out' => 'warning'];
      $stateLabels = ['in' => 'Absen Masuk', 'out' => 'Absen Pulang', 'ot_in' => 'Mulai Lembur', 'ot_out' => 'Selesai Lembur'];
    @endphp
    <div class="list-item">
      <div class="list-icon" style="background: var(--{{ $stateColors[$record->state] ?? 'gray' }}-light);">
        <div style="width: 8px; height: 8px; border-radius: 50%; background: var(--{{ $stateColors[$record->state] ?? 'success' }});"></div>
      </div>
      <div class="list-content">
        <div class="list-title" style="font-weight: 700; color: var(--gray-800);">{{ $stateLabels[$record->state] ?? $record->state }}</div>
        <div class="list-subtitle">{{ $record->attendance_time->format('d/m/Y') }}</div>
      </div>
      <div class="list-meta">
        <div style="font-size: 0.9rem; font-weight: 800; color: var(--primary);">{{ $record->attendance_time->format('H:i') }}</div>
      </div>
    </div>
  @endforeach
</div>
@endif

@endsection

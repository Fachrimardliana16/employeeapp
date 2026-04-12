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
            <img src="{{ asset('storage/'.$employee->image) }}" style="width: 100%; height: 100%; object-fit: cover;">
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

<div class="menu-grid" style="margin-bottom: 2rem;">
  <a href="{{ route('mobile.attendance') }}" class="menu-item">
    <div class="menu-icon">🕐</div>
    <span class="menu-label">Presensi</span>
  </a>
  <a href="{{ route('mobile.permissions.create') }}" class="menu-item">
    <div class="menu-icon">📝</div>
    <span class="menu-label">Buat Izin</span>
  </a>
  <a href="{{ route('mobile.daily-reports') }}" class="menu-item">
    <div class="menu-icon">📄</div>
    <span class="menu-label">Laporan</span>
  </a>
  <a href="{{ route('mobile.profile') }}" class="menu-item">
    <div class="menu-icon">👤</div>
    <span class="menu-label">Profil</span>
  </a>
  <a href="{{ route('mobile.documents') }}" class="menu-item">
    <div class="menu-icon">📁</div>
    <span class="menu-label">Dokumen</span>
  </a>
  <a href="{{ route('mobile.training') }}" class="menu-item">
    <div class="menu-icon">🎓</div>
    <span class="menu-label">Pelatihan</span>
  </a>
  <a href="{{ route('mobile.family') }}" class="menu-item">
    <div class="menu-icon">👨‍👩‍👧</div>
    <span class="menu-label">Keluarga</span>
  </a>
  <a href="{{ route('mobile.permissions') }}" class="menu-item">
    <div class="menu-icon">📅</div>
    <span class="menu-label">Riwayat Izin</span>
  </a>
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

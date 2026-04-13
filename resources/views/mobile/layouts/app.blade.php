<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="apple-mobile-web-app-title" content="Portal Pegawai">
  <meta name="theme-color" content="#0ea5e9">
  <meta name="description" content="Portal Pegawai PDAM Tirta Perwira — Layanan Mandiri Pegawai">
  <title>@yield('title', 'Portal Pegawai') — Tirta Perwira</title>

  {{-- PWA Manifest --}}
  <link rel="manifest" href="/manifest.json">
  <link rel="icon" href="/images/icons/icon-72x72.png">
  <link rel="apple-touch-icon" href="/images/icons/icon-192x192.png">
  <link rel="apple-touch-icon" sizes="152x152" href="/images/icons/icon-152x152.png">
  <link rel="apple-touch-icon" sizes="180x180" href="/images/icons/icon-180x180.png">

  {{-- Styles --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/css/mobile-app.css?v=2.7">
  <style>
    /* Bypassing internal PWA cache and forcing light scheme for OS native pickers */
    :root {
      color-scheme: light;
    }
    .form-control, select.form-control, textarea.form-control { 
      color: #1e293b !important; 
      color-scheme: light;
    }
    select.form-control option {
      background-color: #ffffff !important;
      color: #1e293b !important;
    }
  </style>
  @stack('styles')
</head>
<body>

{{-- Offline Banner --}}
<div class="offline-banner">⚠️ Tidak ada koneksi internet</div>

{{-- Toast Container --}}
<div id="toastContainer" class="toast-container"></div>

{{-- Header --}}
<header class="mobile-header">
  @if(!request()->routeIs('mobile.dashboard'))
    <a href="{{ route('mobile.dashboard') }}" class="header-btn" style="background: transparent; border: none; box-shadow: none;">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
    </a>
  @else
    <img src="/images/icons/icon-128x128.png" alt="Logo" class="header-logo">
  @endif

  <div class="header-title">
    <h1 style="font-size: 1.1rem; letter-spacing: -0.02em;">@yield('header-title', 'Portal Pegawai')</h1>
    <p style="color: rgba(255,255,255,0.85); font-weight: 600;">{{ auth()->user()?->name ?? 'Pegawai' }}</p>
  </div>
  <div class="header-actions">
    @yield('header-actions')
    <form action="{{ route('mobile.logout') }}" method="POST" style="display:inline">
      @csrf
      <button type="submit" class="header-btn" title="Keluar" onclick="return confirm('Yakin ingin keluar?')">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
      </button>
    </form>
  </div>
</header>

{{-- Main Content --}}
<main class="main-content">
  {{-- Flash Messages --}}
  @if(session('success'))
    <div data-flash-success="{{ session('success') }}"></div>
  @endif
  @if(session('error'))
    <div data-flash-error="{{ session('error') }}"></div>
  @endif

  @yield('content')
</main>

{{-- Bottom Navigation --}}
<nav class="bottom-nav">
  {{-- Home --}}
  <a href="{{ route('mobile.dashboard') }}" class="nav-item {{ request()->routeIs('mobile.dashboard') ? 'active' : '' }}">
    <div class="nav-icon">
      <svg viewBox="0 0 24 24" fill="{{ request()->routeIs('mobile.dashboard') ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
    </div>
    <span>Beranda</span>
  </a>

  {{-- Laporan --}}
  <a href="{{ route('mobile.daily-reports') }}" class="nav-item {{ request()->routeIs('mobile.daily-reports') ? 'active' : '' }}">
    <div class="nav-icon">
      <svg viewBox="0 0 24 24" fill="{{ request()->routeIs('mobile.daily-reports') ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
    </div>
    <span>Laporan</span>
  </a>

  {{-- Absensi (FAB) --}}
  <a href="{{ route('mobile.attendance') }}" class="nav-item nav-fab {{ request()->routeIs('mobile.attendance') ? 'active' : '' }}">
    <div class="nav-fab-btn">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
    </div>
    <span>Presensi</span>
  </a>

  {{-- Izin --}}
  <a href="{{ route('mobile.permissions') }}" class="nav-item {{ request()->routeIs('mobile.permissions*') ? 'active' : '' }}">
    <div class="nav-icon">
      <svg viewBox="0 0 24 24" fill="{{ request()->routeIs('mobile.permissions*') ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
    </div>
    <span>Izin</span>
  </a>

  {{-- Profil --}}
  <a href="{{ route('mobile.profile') }}" class="nav-item {{ request()->routeIs('mobile.profile') ? 'active' : '' }}">
    <div class="nav-icon">
      <svg viewBox="0 0 24 24" fill="{{ request()->routeIs('mobile.profile') ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
    </div>
    <span>Profil</span>
  </a>
</nav>

<script src="/js/mobile-app.js?v=2.0"></script>
@stack('scripts')
</body>
</html>

@extends('mobile.layouts.auth')

@section('content')
<div class="auth-page light-theme">
  {{-- Fluid Background Orbs --}}
  <div class="fluid-orb orb-1"></div>
  <div class="fluid-orb orb-2"></div>

  {{-- Hero Section --}}
  <div class="auth-hero">
    <div class="hero-logo-wrapper">
      <img src="/images/icons/icon-192x192.png" alt="Logo" class="auth-logo">
    </div>
    <div class="auth-brand">
      <h1>PORTAL PEGAWAI</h1>
      <p class="subtitle">Tirta Perwira Purbalingga</p>
    </div>
  </div>

  {{-- Login Card --}}
  <div class="auth-card">
    <div class="auth-header-simple">
      <h3>Masuk ke Portal</h3>
    </div>

    @if($errors->any())
      <div class="error-box">
        <span>{{ $errors->first() }}</span>
      </div>
    @endif

    <form action="{{ route('mobile.login.post') }}" method="POST" id="loginForm" class="simple-form">
      @csrf

      <div class="input-group-simple">
        <label>Email / NIPPAM</label>
        <div class="input-wrapper">
          <input type="email" name="email" value="{{ old('email') }}" placeholder="Contoh: user@pdam.com" required>
        </div>
      </div>

      <div class="input-group-simple">
        <label>Kata Sandi</label>
        <div class="input-wrapper">
          <input type="password" id="password" name="password" placeholder="Masukkan kata sandi" required>
          <button type="button" class="eye-toggle" onclick="togglePwd()">
            <svg id="eyeIcon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
            </svg>
          </button>
        </div>
      </div>

      <div class="auth-options">
        <label class="check-container">
          <input type="checkbox" name="remember">
          <span class="checkmark"></span>
          Ingat saya
        </label>
        <a href="#" class="forgot-link">Lupa Sandi?</a>
      </div>

      <button type="submit" id="loginBtn" class="btn-primary-blue">
        <span class="btn-text">MASUK</span>
      </button>
    </form>

    <div class="auth-footer">
      <p>Versi 3.0 // Portal Pegawai</p>
    </div>
  </div>
</div>

<style>
.auth-page { 
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  background: linear-gradient(135deg, #ffffff 0%, #e0f2fe 100%);
  padding: 2rem 1rem;
  position: relative;
  overflow: hidden;
}
.fluid-orb { position: absolute; border-radius: 50%; filter: blur(60px); opacity: 0.3; pointer-events: none; z-index: 1; }
.orb-1 { width: 250px; height: 250px; background: #0ea5e9; top: -50px; left: -50px; }
.orb-2 { width: 200px; height: 200px; background: #38bdf8; bottom: 5%; right: -30px; }

.auth-hero { text-align: center; margin-bottom: 2.5rem; position: relative; z-index: 2; }
.auth-logo { width: 90px; height: 90px; border-radius: 20px; background: white; padding: 10px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }

.auth-brand h1 { font-weight: 800; color: #075985; letter-spacing: 1px; font-size: 1.5rem; margin-top: 1rem; }
.subtitle { color: #0ea5e9; font-size: 0.75rem; font-weight: 700; letter-spacing: 1px; margin-top: 0.25rem; }

.auth-card { 
  background: white; 
  border-radius: 24px; 
  padding: 2.5rem 1.5rem; 
  width: 100%; 
  max-width: 400px; 
  box-shadow: 0 20px 40px rgba(0,0,0,0.05); 
  border: 1px solid #f1f5f9;
  position: relative; 
  z-index: 2;
}

.auth-header-simple h3 { color: #64748b; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 800; margin-bottom: 1.5rem; text-align: center; }

.input-group-simple { margin-bottom: 1.25rem; }
.input-group-simple label { display: block; color: #475569; font-size: 0.75rem; font-weight: 700; margin-bottom: 0.5rem; }
.input-wrapper { position: relative; }
.input-wrapper input { width: 100%; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 0.875rem 1rem; color: #1e293b; font-size: 0.95rem; }
.input-wrapper input:focus { border-color: #0ea5e9; outline: none; background: white; box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.1); }
.eye-toggle { position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); color: #94a3b8; border: none; background: none; }

.auth-options { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
.check-container { font-size: 0.8rem; color: #64748b; font-weight: 500; display: flex; align-items: center; gap: 0.5rem; }
.forgot-link { color: #0ea5e9; font-size: 0.8rem; font-weight: 600; }

.btn-primary-blue { width: 100%; height: 50px; background: #0ea5e9; border-radius: 12px; color: white; border: none; font-weight: 800; font-size: 1rem; letter-spacing: 1px; box-shadow: 0 8px 20px rgba(14, 165, 233, 0.2); }
.btn-primary-blue:active { transform: scale(0.98); }

.auth-footer { margin-top: 2rem; text-align: center; border-top: 1px solid #f1f5f9; padding-top: 1rem; }
.auth-footer p { font-size: 0.65rem; color: #94a3b8; font-weight: 600; }

.error-box { background: #fee2e2; border: 1px solid #ef4444; border-radius: 12px; padding: 0.75rem 1rem; color: #b91c1c; font-size: 0.8rem; font-weight: 600; margin-bottom: 1.5rem; }
</style>

<script>
function togglePwd() {
  const pwd = document.getElementById('password');
  const icon = document.getElementById('eyeIcon');
  if (pwd.type === 'password') {
    pwd.type = 'text';
    icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';
  } else {
    pwd.type = 'password';
    icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
  }
}

document.getElementById('loginForm')?.addEventListener('submit', function() {
  const btn = document.getElementById('loginBtn');
  btn.disabled = true;
  btn.querySelector('.btn-text').innerHTML = 'INITIALIZING...';
});
</script>
@endsection

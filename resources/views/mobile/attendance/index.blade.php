@extends('mobile.layouts.app')

@section('title', 'Rekam Absensi')
@section('header-title', 'Rekam Absensi')

@section('content')

@if(!$employee)
  <div class="card">
    <div class="empty-state">
      <div class="empty-state-icon">⚠️</div>
      <div class="empty-state-title">Data Pegawai Tidak Ditemukan</div>
      <div class="empty-state-desc">Akun Anda belum terhubung dengan data kepegawaian. Hubungi Admin HRD.</div>
    </div>
  </div>
@else

{{-- Today Status --}}
@if(count($todayRecords) > 0)
<div class="card" style="margin-bottom: 1.25rem; border: 1px solid var(--accent-light); background: linear-gradient(135deg, white, var(--accent-light));">
  <div class="card-body">
    <div style="font-size: 0.7rem; font-weight: 800; color: var(--accent-dark); margin-bottom: 0.75rem; letter-spacing: 0.05em; text-transform: uppercase;">✅ KEHADIRAN HARI INI</div>
    <div class="timeline" style="padding: 0;">
      @foreach($todayRecords as $rec)
        @php
          $stateLabels = ['in' => 'Check In', 'out' => 'Check Out', 'ot_in' => 'Overtime In', 'ot_out' => 'Overtime Out'];
          $stateColors = ['in' => 'green', 'out' => 'blue', 'ot_in' => 'purple', 'ot_out' => 'yellow'];
        @endphp
        <div class="timeline-item">
          <div class="timeline-left">
            <div class="timeline-dot {{ $stateColors[$rec->state] ?? '' }}"></div>
            @if(!$loop->last)<div class="timeline-line"></div>@endif
          </div>
          <div class="timeline-body">
            <div class="timeline-time">{{ $rec->attendance_time->format('H:i') }} WIB</div>
            <div class="timeline-title">{{ $stateLabels[$rec->state] ?? $rec->state }}</div>
            @if($rec->attendance_status)
              <div class="timeline-desc">
                @if($rec->attendance_status === 'late') ⚠️ Terlambat
                @elseif($rec->attendance_status === 'early') ⏩ Terlalu awal
                @else ✅ Tepat waktu
                @endif
              </div>
            @endif
          </div>
        </div>
      @endforeach
    </div>
  </div>
</div>
@endif

{{-- Attendance Form --}}
<form action="{{ route('mobile.attendance.store') }}" method="POST" id="attendanceForm" enctype="multipart/form-data">
  @csrf

  <input type="hidden" name="latitude" id="latitudeField">
  <input type="hidden" name="longitude" id="longitudeField">
  <input type="hidden" name="picture" id="photoData">

  {{-- Schedule Info --}}
  @if($schedule)
  <div style="padding: 0.75rem 1rem; background: var(--primary-light); border-radius: var(--radius); margin-bottom: 1rem; font-size: 0.8rem; color: var(--primary-dark);">
    <div style="font-weight: 700; margin-bottom: 0.25rem;">📋 Jadwal {{ now()->format('l') }}</div>
    <div>Check In: {{ $schedule->check_in_start }} – {{ $schedule->check_in_end }}</div>
    <div>Check Out: {{ $schedule->check_out_start }} – {{ $schedule->check_out_end }}</div>
  </div>
  @endif

  {{-- State Select --}}
  <div class="card" style="margin-bottom: 1rem;">
    <div class="card-header">
      <div class="card-header-icon">📌</div>
      Status Kehadiran
    </div>
    <div class="card-body">
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
        @foreach(['in' => ['Check In', '▶️', 'accent'], 'out' => ['Check Out', '⏹️', 'primary'], 'ot_in' => ['OT In', '⏩', 'info'], 'ot_out' => ['OT Out', '⏏️', 'warning']] as $value => $info)
          <label style="cursor: pointer;">
            <input type="radio" name="state" value="{{ $value }}" style="display:none;" class="state-radio">
            <div class="state-option" data-value="{{ $value }}" style="padding: 0.875rem; border: 1.5px solid var(--gray-200); border-radius: var(--radius); text-align: center; transition: all 0.2s;">
              <div style="font-size: 1.5rem; margin-bottom: 0.25rem;">{{ $info[1] }}</div>
              <div style="font-size: 0.8rem; font-weight: 600; color: var(--gray-700);">{{ $info[0] }}</div>
            </div>
          </label>
        @endforeach
      </div>
      @error('state')<div class="form-error mt-2">⚠️ {{ $message }}</div>@enderror
    </div>
  </div>

  {{-- Location --}}
  <div class="card" style="margin-bottom: 1rem;">
    <div class="card-header">
      <div class="card-header-icon">📍</div>
      Lokasi GPS
    </div>
    <div class="card-body">
      <div id="locationPill" class="location-pill">
        <div class="location-dot pulsing"></div>
        Mendeteksi lokasi...
      </div>
      @error('latitude')<div class="form-error mt-2">⚠️ {{ $message }}</div>@enderror
    </div>
  </div>

  {{-- Camera --}}
  <div class="card" style="margin-bottom: 1rem;">
    <div class="card-header">
      <div class="card-header-icon">📸</div>
      Foto Selfie
    </div>
    <div class="card-body">
      {{-- Camera Error --}}
      <div id="cameraError" class="hidden">
        <div style="padding: 1rem; background: var(--danger-light); border-radius: var(--radius); font-size: 0.8rem; color: var(--danger); text-align: center;">
          ❌ Kamera tidak dapat diakses. Pastikan izin kamera sudah diberikan.
        </div>
      </div>

      {{-- Camera Video --}}
      <div id="cameraSection">
        <div class="camera-container">
          <video id="cameraVideo" autoplay playsinline muted></video>
          <div class="camera-face-guide"></div>
          <div class="camera-overlay">
            <div style="text-align: center; margin-bottom: 0.75rem;">
              <div style="font-size: 0.7rem; color: rgba(255,255,255,0.8);">Posisikan wajah Anda di dalam bingkai</div>
            </div>
            <button type="button" class="camera-shutter" onclick="capturePhoto()">
              📷
            </button>
          </div>
        </div>
      </div>

      {{-- Preview --}}
      <img id="photoPreview" class="photo-preview hidden" alt="Foto Selfie">
      <button type="button" id="retakeBtn" class="btn btn-ghost btn-full mt-3 hidden" onclick="retakePhoto()">
        🔄 Ambil Ulang
      </button>
      @error('picture')<div class="form-error mt-2">⚠️ {{ $message }}</div>@enderror
    </div>
  </div>

  {{-- Submit --}}
  <button type="submit" id="submitBtn" class="btn btn-success btn-full btn-lg" style="margin-bottom: 1rem;" disabled>
    ✅ Simpan Kehadiran
  </button>
</form>

@endif

{{-- Attendance History --}}
@if($history->isNotEmpty())
<div class="section-header">
  <span class="section-title">📜 Riwayat Absensi</span>
</div>
<div class="card" style="margin-bottom: 2rem;">
  @php
    $stateColors = ['in' => 'success', 'out' => 'primary', 'ot_in' => 'info', 'ot_out' => 'warning'];
    $stateLabels = ['in' => 'Check In', 'out' => 'Check Out', 'ot_in' => 'OT In', 'ot_out' => 'OT Out'];
    $statusColors = ['on_time' => 'success', 'late' => 'warning', 'early' => 'info'];
  @endphp
  @foreach($history as $record)
    <div class="list-item">
      <div class="list-icon" style="background: var(--{{ $stateColors[$record->state] ?? 'gray' }}-light, var(--gray-100)); color: var(--{{ $stateColors[$record->state] ?? 'gray' }}, var(--gray-400));">
        {{ $record->state === 'in' ? '▶' : ($record->state === 'out' ? '⏹' : '⏩') }}
      </div>
      <div class="list-content">
        <div class="list-title">{{ $stateLabels[$record->state] ?? $record->state }}</div>
        <div class="list-subtitle">{{ $record->attendance_time->format('l, d M Y') }}</div>
        @if($record->office_location_id)
          <div style="font-size: 0.7rem; color: var(--gray-400); margin-top: 0.125rem;">📍 {{ $record->officeLocation?->name ?? '-' }}</div>
        @endif
      </div>
      <div class="list-meta">
        <div style="font-size: 1rem; font-weight: 700;">{{ $record->attendance_time->format('H:i') }}</div>
        @if($record->attendance_status)
          <span class="badge badge-{{ $statusColors[$record->attendance_status] ?? 'gray' }}" style="font-size: 0.6rem;">
            {{ $record->attendance_status === 'on_time' ? 'Tepat' : ($record->attendance_status === 'late' ? 'Terlambat' : 'Awal') }}
          </span>
        @endif
      </div>
    </div>
  @endforeach
</div>
@endif

@push('scripts')
<script>
// Init camera and geolocation
document.addEventListener('DOMContentLoaded', function() {
  Camera.init();
  GeoLocation.get(
    (pos) => { checkFormReady(); },
    (err) => { console.warn('Geo error:', err); }
  );
  setupStateOptions();
});

function setupStateOptions() {
  document.querySelectorAll('.state-radio').forEach(radio => {
    radio.addEventListener('change', function() {
      document.querySelectorAll('.state-option').forEach(opt => {
        opt.style.borderColor = 'var(--gray-200)';
        opt.style.background = 'white';
      });
      const opt = document.querySelector(`.state-option[data-value="${this.value}"]`);
      if (opt) {
        opt.style.borderColor = 'var(--primary)';
        opt.style.background = 'var(--primary-50)';
      }
      checkFormReady();
    });
  });
}

function capturePhoto() {
  Camera.capture();
  document.getElementById('retakeBtn').classList.remove('hidden');
  checkFormReady();
}

function retakePhoto() {
  Camera.retake();
  document.getElementById('retakeBtn').classList.add('hidden');
  checkFormReady();
}

function checkFormReady() {
  const hasState = document.querySelector('.state-radio:checked');
  const hasLocation = document.getElementById('latitudeField').value;
  const hasPhoto = document.getElementById('photoData').value;
  document.getElementById('submitBtn').disabled = !(hasState && hasLocation && hasPhoto);
}

document.getElementById('attendanceForm')?.addEventListener('submit', function() {
  const btn = document.getElementById('submitBtn');
  btn.disabled = true;
  btn.innerHTML = '<div class="spinner"></div> Menyimpan...';
});
</script>
@endpush

@endsection

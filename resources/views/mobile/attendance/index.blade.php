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
                @if($rec->attendance_status === 'late') Terlambat
                @elseif($rec->attendance_status === 'early') Terlalu awal
                @else Tepat waktu
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

  {{-- State & Location Selection --}}
  <div class="card" style="margin-bottom: 1rem;">
    <div class="card-body" style="padding: 1.25rem;">
      <div class="form-group" style="margin-bottom: 1.25rem;">
        <label class="form-label">STATUS KEHADIRAN</label>
        <select name="state" id="stateSelect" class="form-control" required onchange="checkFormReady()">
          <option value="">-- PILIH STATUS --</option>
          <option value="in" {{ old('state') === 'in' ? 'selected' : '' }}>Masuk/Check In</option>
          <option value="out" {{ old('state') === 'out' ? 'selected' : '' }}>Pulang/Check Out</option>
          <option value="ot_in" {{ old('state') === 'ot_in' ? 'selected' : '' }}>Lembur Masuk</option>
          <option value="ot_out" {{ old('state') === 'ot_out' ? 'selected' : '' }}>Lembur Selesai</option>
        </select>
        @error('state')<div class="form-error">⚠️ {{ $message }}</div>@enderror
      </div>

      <div class="form-group" style="margin-bottom: 0;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
          <label class="form-label" style="margin-bottom: 0;">LOKASI SEKARANG</label>
          <button type="button" onclick="GeoLocation.get()" style="font-size: 0.7rem; font-weight: 700; color: var(--primary); display: flex; align-items: center; gap: 4px;">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M23 4v6h-6"/><path d="M1 20v-6h6"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
            REFRESH
          </button>
        </div>
        <div id="locationPill" class="location-pill">
          <div class="location-dot pulsing"></div>
          Mendeteksi lokasi...
        </div>
        @error('latitude')<div class="form-error">⚠️ {{ $message }}</div>@enderror
      </div>
    </div>
  </div>

  {{-- Camera --}}
  <div class="card" style="margin-bottom: 1rem;">
    <div class="card-header" style="color: var(--gray-800); font-weight: 800; border-bottom: 1px solid var(--gray-100);">
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
          <video id="cameraVideo" autoplay playsinline muted style="width: 100%; border-radius: 12px; transform: scaleX(-1);"></video>
          <div class="camera-face-guide"></div>
          <div class="camera-overlay">
            <div style="text-align: center; margin-bottom: 0.75rem;">
              <div style="font-size: 0.7rem; color: rgba(255,255,255,0.8);">Posisikan wajah Anda di dalam bingkai</div>
            </div>
            <button type="button" class="camera-shutter" onclick="capturePhoto()" style="width: 64px; height: 64px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 4px solid rgba(14, 165, 233, 0.3); font-size: 1.5rem; margin: 0 auto; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
              📸
            </button>
          </div>
        </div>
      </div>

      {{-- Preview --}}
      <div id="previewContainer" class="hidden" style="text-align: center;">
        <img id="photoPreview" style="width: 100%; border-radius: 12px; margin-bottom: 1rem; border: 1px solid var(--gray-200); box-shadow: var(--shadow);">
        <button type="button" id="retakeBtn" class="btn btn-ghost btn-full" onclick="retakePhoto()" style="background: var(--gray-100); border: 1px solid var(--gray-200); font-weight: 700;">
          Ambil Ulang Foto
        </button>
      </div>

      <canvas id="cameraCanvas" class="hidden"></canvas>
      @error('picture')<div class="form-error mt-2">⚠️ {{ $message }}</div>@enderror
    </div>
  </div>

  {{-- Submit --}}
  <button type="button" id="submitBtn" class="btn btn-success btn-full btn-lg" style="margin-bottom: 1rem;" disabled onclick="handleAttendanceSubmit()">
    Simpan Kehadiran
  </button>
</form>

{{-- Daily Report Modal --}}
<div id="reportModal" class="modal-overlay">
  <div class="modal-container">
    <div class="modal-header">
      <div class="modal-title">Laporan Kerja Harian</div>
      <button type="button" class="modal-close" onclick="closeModal()">✕</button>
    </div>
    <div class="modal-body">
      <p style="font-size: 0.85rem; color: var(--gray-500); margin-bottom: 1.25rem;">Silakan isi laporan pekerjaan Anda sebelum menyimpan kehadiran.</p>
      
      <div class="form-group">
        <label class="form-label">TANGGAL LAPORAN</label>
        <input type="text" class="form-control" value="{{ now()->format('d M Y') }}" readonly style="background: var(--gray-50);">
      </div>

      <div class="form-group">
        <label class="form-label">STATUS PEKERJAAN</label>
        <select id="work_status" class="form-control">
          <option value="completed">Selesai</option>
          <option value="in_progress">Proses</option>
          <option value="pending">Tertunda</option>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label">ISI LAPORAN KERJA <span class="required">*</span></label>
        <textarea id="work_description" class="form-control" rows="4" placeholder="Apa yang Anda kerjakan hari ini?"></textarea>
      </div>

      <div class="form-group">
        <label class="form-label">CATATAN (OPSIONAL)</label>
        <textarea id="desc" class="form-control" rows="2" placeholder="Catatan tambahan..."></textarea>
      </div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-ghost flex-1" onclick="closeModal()">Batal</button>
      <button type="button" class="btn btn-primary flex-1" onclick="finalSubmit()">Simpan & Absen</button>
    </div>
  </div>
</div>

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
document.addEventListener('DOMContentLoaded', function() {
  Camera.init();
  GeoLocation.get(
    (pos) => { checkFormReady(); },
    (err) => { console.warn('Geo error:', err); }
  );
});

function capturePhoto() {
  Camera.capture();
  checkFormReady();
}

function retakePhoto() {
  Camera.retake();
  checkFormReady();
}

function checkFormReady() {
  const stateVal = document.getElementById('stateSelect').value;
  const hasLocation = document.getElementById('latitudeField').value;
  const hasPhoto = document.getElementById('photoData').value;
  
  const submitBtn = document.getElementById('submitBtn');
  if (submitBtn) {
    submitBtn.disabled = !(stateVal && hasLocation && hasPhoto);
  }
}

function handleAttendanceSubmit() {
  const stateSelect = document.getElementById('stateSelect');
  if (!stateSelect) return;
  
  const stateVal = stateSelect.value;
  console.log('Att submit state:', stateVal);
  
  // Minimal: out (Check Out) or ot_out (Overtime Out) triggers report modal
  if (stateVal === 'out' || stateVal === 'ot_out') {
    openModal();
  } else {
    const form = document.getElementById('attendanceForm');
    if (form) form.submit();
  }
}

function openModal() {
  const modal = document.getElementById('reportModal');
  if (modal) {
    // Pindahkan modal ke luar dari container main content agar z-index maksimal bekerja
    document.body.appendChild(modal);
    modal.style.display = 'flex';
    // Trigger reflow
    modal.offsetHeight;
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
  }
}

function closeModal() {
  const modal = document.getElementById('reportModal');
  if (modal) {
    modal.classList.remove('active');
    setTimeout(() => {
      modal.style.display = 'none';
      // Kembalikan ke form (opsional, tapi membiarkannya di body juga aman selama id tetap sama)
    }, 300);
    document.body.style.overflow = '';
  }
}

function finalSubmit() {
  const workDesc = document.getElementById('work_description').value;
  const workStatus = document.getElementById('work_status').value;
  const desc = document.getElementById('desc').value;

  if (!workDesc.trim()) {
    alert('Isi laporan kerja wajib diisi!');
    return;
  }

  // Show loading on the modal button
  const finalBtn = event.target;
  if (finalBtn) {
    finalBtn.disabled = true;
    finalBtn.innerHTML = '<div class="spinner"></div> Menyimpan...';
  }

  // Create hidden inputs for the modal data
  const form = document.getElementById('attendanceForm');
  
  const inputs = {
    'work_description': workDesc,
    'work_status': workStatus,
    'desc': desc,
    'daily_report_date': '{{ date("Y-m-d") }}'
  };

  for (const [name, value] of Object.entries(inputs)) {
    let input = document.createElement('input');
    input.type = 'hidden';
    input.name = name;
    input.value = value;
    form.appendChild(input);
  }

  form.submit();
}

document.getElementById('attendanceForm')?.addEventListener('submit', function() {
  const btn = document.getElementById('submitBtn');
  if (btn) {
    btn.disabled = true;
    btn.innerHTML = '<div class="spinner"></div> Menyimpan...';
  }
  closeModal();
});
</script>
@endpush

@endsection

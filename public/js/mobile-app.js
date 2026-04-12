/**
 * Portal Pegawai Mobile — App JS
 */

// ─── Service Worker Registration ──────────────────────────
if ('serviceWorker' in navigator) {
  window.addEventListener('load', async () => {
    try {
      const reg = await navigator.serviceWorker.register('/sw.js', { scope: '/mobile/' });
      console.log('[SW] Registered:', reg.scope);
    } catch (err) {
      console.warn('[SW] Registration failed:', err);
    }
  });
}

// ─── Online/Offline Detection ─────────────────────────────
function updateOnlineStatus() {
  document.body.classList.toggle('offline', !navigator.onLine);
}
window.addEventListener('online', updateOnlineStatus);
window.addEventListener('offline', updateOnlineStatus);
updateOnlineStatus();

// ─── Toast Notification System ────────────────────────────
const Toast = {
  container: null,

  init() {
    this.container = document.getElementById('toastContainer');
    if (!this.container) {
      this.container = document.createElement('div');
      this.container.id = 'toastContainer';
      this.container.className = 'toast-container';
      document.body.appendChild(this.container);
    }
  },

  show(message, type = 'info', duration = 3000) {
    if (!this.container) this.init();
    const icons = { success: '✅', error: '❌', warning: '⚠️', info: 'ℹ️' };
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `<span>${icons[type] || 'ℹ️'}</span><span>${message}</span>`;
    this.container.appendChild(toast);
    setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transform = 'translateY(-12px)';
      toast.style.transition = 'all 0.3s ease';
      setTimeout(() => toast.remove(), 300);
    }, duration);
  },

  success: (msg, d) => Toast.show(msg, 'success', d),
  error: (msg, d) => Toast.show(msg, 'error', d),
  warning: (msg, d) => Toast.show(msg, 'warning', d),
  info: (msg, d) => Toast.show(msg, 'info', d),
};

// ─── Loading Manager ──────────────────────────────────────
const Loader = {
  show(text = 'Memuat...') {
    let el = document.getElementById('pageLoader');
    if (!el) {
      el = document.createElement('div');
      el.id = 'pageLoader';
      el.className = 'page-loader';
      el.innerHTML = `<div class="loader-ring"></div><div class="loader-text">${text}</div>`;
      document.body.appendChild(el);
    } else {
      el.querySelector('.loader-text').textContent = text;
    }
    el.style.display = 'flex';
  },
  hide() {
    const el = document.getElementById('pageLoader');
    if (el) el.style.display = 'none';
  },
};

// ─── Clock Display ────────────────────────────────────────
function initClock() {
  const el = document.getElementById('liveClock');
  if (!el) return;
  const update = () => {
    const now = new Date();
    const h = String(now.getHours()).padStart(2, '0');
    const m = String(now.getMinutes()).padStart(2, '0');
    const s = String(now.getSeconds()).padStart(2, '0');
    el.textContent = `${h}:${m}:${s}`;
  };
  update();
  setInterval(update, 1000);
}

// ─── Accordion Component ──────────────────────────────────
function initAccordions() {
  document.querySelectorAll('.accordion-trigger').forEach((trigger) => {
    trigger.addEventListener('click', () => {
      const item = trigger.closest('.accordion-item');
      const isOpen = item.classList.contains('open');
      // Close all siblings
      item.closest('.accordion')?.querySelectorAll('.accordion-item.open').forEach((el) => {
        if (el !== item) el.classList.remove('open');
      });
      item.classList.toggle('open', !isOpen);
    });
  });
}

// ─── Camera Capture ───────────────────────────────────────
const Camera = {
  stream: null,
  videoEl: null,
  canvasEl: null,
  previewEl: null,
  photoDataEl: null,

  async init(opts = {}) {
    this.videoEl = document.getElementById(opts.video || 'cameraVideo');
    this.canvasEl = document.getElementById(opts.canvas || 'cameraCanvas');
    this.previewEl = document.getElementById(opts.preview || 'photoPreview');
    this.photoDataEl = document.getElementById(opts.data || 'photoData');
    if (!this.videoEl) return;
    await this.startStream();
  },

  async startStream() {
    try {
      this.stream = await navigator.mediaDevices.getUserMedia({
        video: { facingMode: 'user', width: { ideal: 720 }, height: { ideal: 960 } },
        audio: false,
      });
      this.videoEl.srcObject = this.stream;
      await this.videoEl.play();
      document.getElementById('cameraSection')?.classList.remove('hidden');
    } catch (err) {
      console.error('Camera error:', err);
      document.getElementById('cameraError')?.classList.remove('hidden');
      document.getElementById('cameraSection')?.classList.add('hidden');
    }
  },

  capture() {
    if (!this.canvasEl || !this.videoEl) return null;
    const w = this.videoEl.videoWidth;
    const h = this.videoEl.videoHeight;
    this.canvasEl.width = w;
    this.canvasEl.height = h;
    const ctx = this.canvasEl.getContext('2d');
    ctx.drawImage(this.videoEl, 0, 0, w, h);
    const data = this.canvasEl.toDataURL('image/jpeg', 0.8);
    if (this.previewEl) {
      this.previewEl.src = data;
      this.previewEl.classList.remove('hidden');
    }
    if (this.videoEl.parentElement) this.videoEl.parentElement.classList.add('hidden');
    if (this.photoDataEl) this.photoDataEl.value = data;
    this.stopStream();
    return data;
  },

  retake() {
    if (this.previewEl) {
      this.previewEl.classList.add('hidden');
      this.previewEl.src = '';
    }
    if (this.photoDataEl) this.photoDataEl.value = '';
    if (this.videoEl?.parentElement) this.videoEl.parentElement.classList.remove('hidden');
    this.startStream();
  },

  stopStream() {
    this.stream?.getTracks().forEach((t) => t.stop());
    this.stream = null;
  },
};

// ─── Geolocation ──────────────────────────────────────────
const GeoLocation = {
  current: null,

  get(onSuccess, onError) {
    const pill = document.getElementById('locationPill');
    if (pill) {
      pill.className = 'location-pill';
      pill.innerHTML = '<div class="location-dot pulsing"></div> Mendeteksi lokasi...';
    }
    if (!navigator.geolocation) {
      onError?.('Browser tidak mendukung geolocation');
      return;
    }
    navigator.geolocation.getCurrentPosition(
      (pos) => {
        this.current = { lat: pos.coords.latitude, lng: pos.coords.longitude, acc: pos.coords.accuracy };
        // Update hidden fields
        const latEl = document.getElementById('latitudeField');
        const lngEl = document.getElementById('longitudeField');
        if (latEl) latEl.value = this.current.lat;
        if (lngEl) lngEl.value = this.current.lng;
        // Update pill
        if (pill) {
          pill.className = 'location-pill success';
          pill.innerHTML = `<div class="location-dot"></div> Lokasi ditemukan (±${Math.round(this.current.acc)}m)`;
        }
        onSuccess?.(this.current);
      },
      (err) => {
        if (pill) {
          pill.className = 'location-pill error';
          pill.innerHTML = '<div class="location-dot"></div> Gagal mendapatkan lokasi';
        }
        onError?.(err.message);
      },
      { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
    );
  },
};

// ─── Form Submit with Loading ─────────────────────────────
function initFormSubmit(formId, btnId) {
  const form = document.getElementById(formId);
  const btn = document.getElementById(btnId);
  if (!form || !btn) return;
  form.addEventListener('submit', () => {
    btn.disabled = true;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<div class="spinner"></div> Menyimpan...';
    // Re-enable after 10s fallback
    setTimeout(() => { btn.disabled = false; btn.innerHTML = originalText; }, 10000);
  });
}

// ─── Pull-to-Refresh ──────────────────────────────────────
function initPullToRefresh() {
  let startY = 0;
  let pulling = false;
  document.addEventListener('touchstart', (e) => { startY = e.touches[0].clientY; }, { passive: true });
  document.addEventListener('touchmove', (e) => {
    if (window.scrollY === 0 && e.touches[0].clientY - startY > 80) pulling = true;
  }, { passive: true });
  document.addEventListener('touchend', () => {
    if (pulling) { pulling = false; window.location.reload(); }
  });
}

// ─── File Upload Preview ──────────────────────────────────
function initFileUpload(inputId, labelId) {
  const input = document.getElementById(inputId);
  const label = document.getElementById(labelId);
  if (!input || !label) return;
  input.addEventListener('change', () => {
    const f = input.files[0];
    if (f) label.querySelector('.file-upload-label').textContent = f.name;
  });
}

// ─── Initialize ───────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  Toast.init();
  initClock();
  initAccordions();
  initPullToRefresh();

  // Auto-flash from session
  const flashSuccess = document.querySelector('[data-flash-success]')?.dataset.flashSuccess;
  const flashError = document.querySelector('[data-flash-error]')?.dataset.flashError;
  if (flashSuccess) Toast.success(flashSuccess);
  if (flashError) Toast.error(flashError);
});

// Expose globally
window.Toast = Toast;
window.Loader = Loader;
window.Camera = Camera;
window.GeoLocation = GeoLocation;

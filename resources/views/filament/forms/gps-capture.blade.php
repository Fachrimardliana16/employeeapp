{{-- GPS Capture Component for Online Attendance
     Collects 3 GPS readings 2 seconds apart to calculate jitter.
     Jitter < 0.3m with accuracy < 10m = suspected mock GPS.
     Sets Livewire data fields via $wire.set() --}}
<div
    x-data="{
        status: 'idle',
        message: '',
        readings: [],

        haversine(lat1, lng1, lat2, lng2) {
            const R = 6371000;
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLng = (lng2 - lng1) * Math.PI / 180;
            const a = Math.sin(dLat/2)**2 +
                      Math.cos(lat1*Math.PI/180) * Math.cos(lat2*Math.PI/180) * Math.sin(dLng/2)**2;
            return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        },

        getPos() {
            return new Promise((resolve, reject) => {
                navigator.geolocation.getCurrentPosition(resolve, reject, {
                    enableHighAccuracy: true,
                    timeout: 12000,
                    maximumAge: 0
                });
            });
        },

        async capture() {
            if (!navigator.geolocation) {
                this.status = 'error';
                this.message = 'Browser tidak mendukung GPS. Gunakan browser modern (Chrome/Firefox).';
                return;
            }
            this.status = 'loading';
            this.readings = [];

            try {
                for (let i = 0; i < 3; i++) {
                    this.message = `Membaca GPS ${i+1}/3... (tunggu ±6 detik)`;
                    const pos = await this.getPos();
                    this.readings.push({
                        lat: pos.coords.latitude,
                        lng: pos.coords.longitude,
                        acc: pos.coords.accuracy
                    });
                    if (i < 2) await new Promise(r => setTimeout(r, 2000));
                }

                const first  = this.readings[0];
                const last   = this.readings[this.readings.length - 1];
                const jitter = this.haversine(first.lat, first.lng, last.lat, last.lng);
                const acc    = Math.min(...this.readings.map(r => r.acc));

                // Set Livewire form fields
                $wire.set('data.check_latitude',  last.lat.toFixed(8));
                $wire.set('data.check_longitude', last.lng.toFixed(8));
                $wire.set('data.gps_accuracy',    acc.toFixed(2));
                $wire.set('data.gps_jitter',      jitter.toFixed(4));

                this.status  = 'done';
                this.message = `✓ Lokasi berhasil ditangkap — Akurasi: ${acc.toFixed(0)}m, Jitter: ${jitter.toFixed(2)}m`;

            } catch (e) {
                this.status  = 'error';
                this.message = 'Gagal mendapatkan lokasi: ' + e.message + '. Pastikan GPS/Lokasi diaktifkan di browser.';
            }
        }
    }"
    class="space-y-3 py-1"
>
    <div class="flex flex-wrap items-center gap-3">
        <button
            type="button"
            @click="capture()"
            :disabled="status === 'loading'"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white transition
                   bg-primary-600 hover:bg-primary-700 disabled:bg-gray-400 disabled:cursor-not-allowed"
        >
            {{-- Spinner --}}
            <svg x-show="status === 'loading'" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4z"/>
            </svg>
            {{-- Map pin icon --}}
            <svg x-show="status !== 'loading'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span x-text="status === 'loading' ? 'Mengambil GPS...' : (status === 'done' ? 'Ambil Ulang GPS' : 'Tangkap Lokasi GPS')"></span>
        </button>

        <span
            x-show="status === 'done'"
            class="inline-flex items-center gap-1 text-sm font-semibold text-success-600"
        >
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414L8.414 15l-4.121-4.121a1 1 0 011.414-1.414L8.414 12.172l7.879-7.879a1 1 0 011.414 0z" clip-rule="evenodd"/>
            </svg>
            GPS Berhasil
        </span>
    </div>

    <p
        x-show="message !== ''"
        x-text="message"
        :class="{
            'text-danger-600':  status === 'error',
            'text-success-600': status === 'done',
            'text-primary-600': status === 'loading'
        }"
        class="text-sm"
    ></p>

    <p class="text-xs text-gray-400 dark:text-gray-500">
        GPS diambil 3× untuk mendeteksi manipulasi lokasi (anti-fake GPS).
        Wajib dilakukan sebelum submit.
    </p>
</div>

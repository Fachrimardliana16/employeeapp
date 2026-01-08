<div class="camera-capture-component" x-data="cameraCapture('{{ $fieldName }}')">
    <div class="space-y-4">
        <!-- Camera Preview -->
        <div class="relative bg-gray-900 rounded-lg overflow-hidden" style="aspect-ratio: 4/3;">
            <video
                x-ref="video"
                x-show="!captured && cameraActive"
                autoplay
                playsinline
                class="w-full h-full object-cover"
            ></video>

            <canvas
                x-ref="canvas"
                x-show="false"
                class="hidden"
            ></canvas>

            <img
                x-show="captured"
                x-bind:src="photoDataUrl"
                class="w-full h-full object-cover"
            />

            <div x-show="!cameraActive && !captured" class="absolute inset-0 flex items-center justify-center">
                <div class="text-center text-white">
                    <svg class="w-16 h-16 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <p class="opacity-50">Klik tombol untuk mengaktifkan kamera</p>
                </div>
            </div>

            <div x-show="error" class="absolute inset-0 flex items-center justify-center bg-red-900 bg-opacity-75">
                <div class="text-center text-white p-4">
                    <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <p x-text="errorMessage"></p>
                </div>
            </div>
        </div>

        <!-- Controls -->
        <div class="flex gap-2 justify-center">
            <button
                type="button"
                x-show="!cameraActive && !captured"
                @click="startCamera()"
                class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition flex items-center gap-2"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
                Aktifkan Kamera
            </button>

            <button
                type="button"
                x-show="cameraActive && !captured"
                @click="capturePhoto()"
                class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition flex items-center gap-2"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Ambil Foto
            </button>

            <button
                type="button"
                x-show="captured"
                @click="retakePhoto()"
                class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition flex items-center gap-2"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Ambil Ulang
            </button>

            <button
                type="button"
                x-show="cameraActive"
                @click="stopCamera()"
                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition flex items-center gap-2"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Tutup Kamera
            </button>
        </div>

        <p x-show="captured" class="text-sm text-green-600 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Foto berhasil diambil
        </p>
    </div>
</div>

<script>
function cameraCapture(fieldName) {
    return {
        cameraActive: false,
        captured: false,
        photoDataUrl: null,
        stream: null,
        error: false,
        errorMessage: '',

        async startCamera() {
            try {
                this.error = false;
                this.errorMessage = '';

                // Request camera access - user-facing camera for selfie
                this.stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: 'user', // Front camera for selfie
                        width: { ideal: 1280 },
                        height: { ideal: 720 }
                    },
                    audio: false
                });

                this.$refs.video.srcObject = this.stream;
                this.cameraActive = true;
            } catch (err) {
                console.error('Camera error:', err);
                this.error = true;
                this.errorMessage = 'Tidak dapat mengakses kamera. Pastikan Anda telah memberikan izin akses kamera.';
            }
        },

        capturePhoto() {
            const video = this.$refs.video;
            const canvas = this.$refs.canvas;

            // Set canvas size to match video
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;

            // Draw video frame to canvas
            const context = canvas.getContext('2d');
            context.drawImage(video, 0, 0, canvas.width, canvas.height);

            // Convert to data URL and save
            canvas.toBlob(async (blob) => {
                // Create data URL for preview
                this.photoDataUrl = URL.createObjectURL(blob);

                // Upload to server
                await this.uploadPhoto(blob);

                this.captured = true;
                this.stopCamera();
            }, 'image/jpeg', 0.9);
        },

        async uploadPhoto(blob) {
            const formData = new FormData();
            formData.append('photo', blob, `${fieldName}_${Date.now()}.jpg`);
            formData.append('field', fieldName);

            try {
                const response = await fetch('/api/attendance/upload-photo', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();

                if (data.success) {
                    // Set the field value with the uploaded file path
                    this.$wire.set(fieldName, data.path);
                }
            } catch (err) {
                console.error('Upload error:', err);
                this.error = true;
                this.errorMessage = 'Gagal mengunggah foto. Silakan coba lagi.';
            }
        },

        retakePhoto() {
            this.captured = false;
            this.photoDataUrl = null;
            this.$wire.set(fieldName, null);
            this.startCamera();
        },

        stopCamera() {
            if (this.stream) {
                this.stream.getTracks().forEach(track => track.stop());
                this.stream = null;
            }
            this.cameraActive = false;
        },

        init() {
            // Cleanup on component destroy
            this.$watch('cameraActive', (active) => {
                if (!active && this.stream) {
                    this.stopCamera();
                }
            });
        }
    }
}
</script>

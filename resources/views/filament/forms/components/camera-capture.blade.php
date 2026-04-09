<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div x-data="{
        state: $wire.entangle('{{ $getStatePath() }}'),
        stream: null,
        capturedImage: null,
        showVideo: true,

        async initCamera() {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                alert('Browser Anda tidak mendukung akses kamera atau Anda sedang tidak menggunakan HTTPS.');
                return;
            }

            try {
                this.stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: 'user',
                        width: { ideal: 1024 },
                        height: { ideal: 768 }
                    }
                });
                
                this.$nextTick(() => {
                    this.$refs.video.srcObject = this.stream;
                    this.$refs.video.play().catch(e => console.error('Play error:', e));
                });
            } catch (err) {
                console.error('Camera error:', err);
                let message = 'TIDAK DAPAT MENGAKSES KAMERA: ';
                if (err.name === 'NotAllowedError') message += 'Izin ditolak.';
                else if (err.name === 'NotFoundError') message += 'Kamera tidak ditemukan.';
                else message += err.message;
                alert(message);
            }
        },

        capture() {
            const video = this.$refs.video;
            const canvas = this.$refs.canvas;
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);

            this.capturedImage = canvas.toDataURL('image/jpeg', 0.8);
            this.state = this.capturedImage;
            this.showVideo = false;
            this.stopCamera();
        },

        retake() {
            this.capturedImage = null;
            this.state = null;
            this.showVideo = true;
            this.initCamera();
        },

        stopCamera() {
            if (this.stream) {
                this.stream.getTracks().forEach(track => track.stop());
                this.stream = null;
            }
        }
    }"
    x-init="initCamera()"
    class="space-y-4"
    @dispose="stopCamera()"
    >
        <!-- Camera Preview Container -->
        <div 
            class="relative overflow-hidden rounded-xl bg-gray-950 shadow-inner flex items-center justify-center border border-gray-200 dark:border-gray-800"
            style="aspect-ratio: 16/9; max-width: 500px; margin: 0 auto; min-height: 200px;"
        >
            <!-- Loading Indicator -->
            <div x-show="showVideo && !stream" class="flex flex-col items-center gap-3 text-gray-500">
                <svg class="w-10 h-10 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-xs font-medium tracking-widest uppercase">Mempersiapkan Kamera...</span>
            </div>

            <!-- Video Feed -->
            <video
                x-ref="video"
                x-show="showVideo"
                autoplay
                muted
                playsinline
                class="absolute inset-0 w-full h-full object-cover -scale-x-100"
            ></video>

            <!-- Captured Image Preview -->
            <template x-if="capturedImage">
                <img :src="capturedImage" class="absolute inset-0 w-full h-full object-cover rounded-xl scale-[1.01]">
            </template>

            <!-- Overlay Indicators -->
            <div class="absolute inset-0 pointer-events-none border border-white/10 rounded-xl m-2"></div>
            <div class="absolute top-2 left-2 flex items-center gap-2 px-2 py-1 rounded-full bg-black/60 backdrop-blur-md border border-white/10 text-white text-[8px] font-medium tracking-wider uppercase">
                <div class="w-1 h-1 rounded-full bg-red-500 animate-pulse"></div>
                LIVE
            </div>

            <!-- Focus Corners -->
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute top-8 left-8 w-6 h-6 border-t-2 border-l-2 border-white/40"></div>
                <div class="absolute top-8 right-8 w-6 h-6 border-t-2 border-r-2 border-white/40"></div>
                <div class="absolute bottom-8 left-8 w-6 h-6 border-b-2 border-l-2 border-white/40"></div>
                <div class="absolute bottom-8 right-8 w-6 h-6 border-b-2 border-r-2 border-white/40"></div>
            </div>
        </div>

        <!-- Controls -->
        <div class="flex justify-center flex-wrap gap-3">
            <template x-if="showVideo">
                <button
                    type="button"
                    @click="capture()"
                    class="group relative flex items-center gap-2 px-8 py-3 bg-primary-600 hover:bg-primary-500 text-white rounded-full font-semibold transition-all shadow-lg hover:shadow-primary-500/25 active:scale-95 overflow-hidden"
                    >
                    <div class="absolute inset-0 bg-white/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Ambil Foto
                </button>
            </template>

            <template x-if="capturedImage">
                <button
                    type="button"
                    @click="retake()"
                    class="flex items-center gap-2 px-8 py-3 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-full font-semibold transition-all active:scale-95"
                    >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Foto Ulang
                </button>
            </template>
        </div>

        <!-- Hidden canvas for capturing -->
        <canvas x-ref="canvas" style="display:none;"></canvas>
    </div>
</x-dynamic-component>

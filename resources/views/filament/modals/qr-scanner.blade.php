<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>

<div
    x-data="{
        scanning: false,
        detected: null,
        error: null,
        stream: null,
        animFrame: null,

        async init() {
            try {
                this.stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'environment' }
                });
                this.$refs.video.srcObject = this.stream;
                await this.$refs.video.play();
                this.scanning = true;
                this.scan();
            } catch (e) {
                this.error = 'კამერაზე წვდომა ვერ მოხერხდა. შეამოწმე ბრაუზერის ნებართვები.';
            }
        },

        scan() {
            const tick = () => {
                const video = this.$refs.video;
                const canvas = this.$refs.canvas;
                if (video.readyState === video.HAVE_ENOUGH_DATA) {
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    canvas.getContext('2d').drawImage(video, 0, 0);
                    const img = canvas.getContext('2d').getImageData(0, 0, canvas.width, canvas.height);
                    const code = jsQR(img.data, img.width, img.height);
                    if (code) {
                        this.stop();
                        this.detected = code.data;
                        this.scanning = false;
                        $wire.set('tableSearch', code.data);
                        setTimeout(() => $wire.unmountAction(), 800);
                        return;
                    }
                }
                this.animFrame = requestAnimationFrame(tick);
            };
            this.animFrame = requestAnimationFrame(tick);
        },

        stop() {
            if (this.animFrame) cancelAnimationFrame(this.animFrame);
            if (this.stream) this.stream.getTracks().forEach(t => t.stop());
        },

        manualSubmit() {
            const val = this.$refs.manual.value.trim();
            if (!val) return;
            this.stop();
            this.scanning = false;
            $wire.set('tableSearch', val);
            setTimeout(() => $wire.unmountAction(), 300);
        }
    }"
    x-init="init()"
    @close-modal.window="stop()"
    class="space-y-4 pb-2"
>
    {{-- Camera --}}
    <div class="relative overflow-hidden rounded-xl bg-black" style="aspect-ratio:4/3;">
        <video
            x-ref="video"
            class="w-full h-full object-cover"
            playsinline
            muted
        ></video>
        <canvas x-ref="canvas" class="hidden"></canvas>

        {{-- Scanning overlay --}}
        <div x-show="scanning" class="absolute inset-0 flex items-center justify-center pointer-events-none">
            <div class="w-48 h-48 border-2 border-white rounded-xl opacity-60"></div>
        </div>

        {{-- Detected --}}
        <div
            x-show="detected"
            x-cloak
            class="absolute inset-0 flex flex-col items-center justify-center bg-success-500/90 rounded-xl"
        >
            <x-heroicon-o-check-circle class="w-12 h-12 text-white mb-2"/>
            <p class="text-white font-semibold text-sm" x-text="detected"></p>
        </div>

        {{-- Error --}}
        <div
            x-show="error"
            x-cloak
            class="absolute inset-0 flex flex-col items-center justify-center bg-gray-900/90 rounded-xl px-4"
        >
            <x-heroicon-o-exclamation-triangle class="w-10 h-10 text-danger-400 mb-2"/>
            <p class="text-white text-sm text-center" x-text="error"></p>
        </div>
    </div>

    {{-- Manual input --}}
    <div class="flex gap-2">
        <input
            x-ref="manual"
            type="text"
            placeholder="კოდის ხელით შეყვანა..."
            class="flex-1 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500"
            @keydown.enter="manualSubmit()"
        />
        <button
            @click="manualSubmit()"
            class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500"
        >
            ძებნა
        </button>
    </div>
</div>

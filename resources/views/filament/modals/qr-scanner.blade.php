<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<div
    x-data="{
        detected: null,
        error: null,
        scanner: null,

        init() {
            this.$nextTick(() => this.start());
        },

        start() {
            this.scanner = new Html5Qrcode('qr-reader-box');
            this.scanner.start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: { width: 220, height: 220 } },
                (code) => this.onDetected(code),
                () => {}
            ).catch((err) => {
                this.error = 'კამერაზე წვდომა ვერ მოხერხდა. შეამოწმე ბრაუზერის ნებართვები.';
            });
        },

        onDetected(code) {
            this.scanner.stop().catch(() => {});
            this.detected = code;
            $wire.set('tableSearch', code);
        },

        stop() {
            if (this.scanner) {
                this.scanner.stop().catch(() => {});
            }
        },

        manualSubmit() {
            const val = this.$refs.manual.value.trim();
            if (!val) return;
            this.stop();
            this.detected = val;
            $wire.set('tableSearch', val);
        }
    }"
    x-init="init()"
    @close-modal.window="stop()"
    class="space-y-4 pb-2"
>
    {{-- Scanner box --}}
    <div x-show="!detected && !error" class="overflow-hidden rounded-xl bg-black">
        <div id="qr-reader-box" style="width:100%;"></div>
    </div>

    {{-- Success --}}
    <div
        x-show="detected"
        x-cloak
        class="flex flex-col items-center gap-2 rounded-xl bg-success-50 dark:bg-success-950 border border-success-200 dark:border-success-800 p-6"
    >
        <x-heroicon-o-check-circle class="w-10 h-10 text-success-500"/>
        <p class="text-sm font-semibold text-success-700 dark:text-success-300">ნაიძებნა:</p>
        <p class="text-base font-bold text-success-900 dark:text-success-100" x-text="detected"></p>
        <p class="text-xs text-success-600 dark:text-success-400">ცხრილი გაფილტრულია — შეგიძლია დახუროთ</p>
    </div>

    {{-- Error --}}
    <div
        x-show="error"
        x-cloak
        class="flex flex-col items-center gap-2 rounded-xl bg-danger-50 dark:bg-danger-950 border border-danger-200 p-4"
    >
        <x-heroicon-o-exclamation-triangle class="w-8 h-8 text-danger-500"/>
        <p class="text-sm text-center text-danger-700 dark:text-danger-300" x-text="error"></p>
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

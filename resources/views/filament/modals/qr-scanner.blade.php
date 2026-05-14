<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>

<div
    x-data="{
        detected: null,
        error: null,
        processing: false,

        async processFile(event) {
            const file = event.target.files[0];
            if (!file) return;

            this.error = null;
            this.processing = true;

            const bitmap = await createImageBitmap(file);
            const canvas = this.$refs.canvas;
            canvas.width = bitmap.width;
            canvas.height = bitmap.height;
            canvas.getContext('2d').drawImage(bitmap, 0, 0);
            const imgData = canvas.getContext('2d').getImageData(0, 0, canvas.width, canvas.height);
            const code = jsQR(imgData.data, imgData.width, imgData.height);

            this.processing = false;

            if (code) {
                this.detected = code.data;
                $wire.set('tableSearch', code.data);
            } else {
                this.error = 'QR კოდი ვერ ამოიცნო — სცადე ახლოდან ან სხვა კუთხით.';
                this.$refs.fileInput.value = '';
            }
        },

        manualSubmit() {
            const val = this.$refs.manual.value.trim();
            if (!val) return;
            this.detected = val;
            $wire.set('tableSearch', val);
        }
    }"
    class="space-y-4 pb-2"
>
    <canvas x-ref="canvas" class="hidden"></canvas>
    <input x-ref="fileInput" type="file" accept="image/*" capture="environment" class="hidden" @change="processFile($event)">

    {{-- Scan button --}}
    <div x-show="!detected">
        <button
            @click="$refs.fileInput.click()"
            :disabled="processing"
            class="w-full flex items-center justify-center gap-3 rounded-xl border-2 border-dashed border-primary-300 dark:border-primary-700 bg-primary-50 dark:bg-primary-950 py-8 text-primary-700 dark:text-primary-300 hover:bg-primary-100 dark:hover:bg-primary-900 transition disabled:opacity-50"
        >
            <template x-if="!processing">
                <span class="flex items-center gap-2 font-semibold text-base">
                    <x-heroicon-o-qr-code class="w-7 h-7"/>
                    კამერით სკანირება
                </span>
            </template>
            <template x-if="processing">
                <span class="flex items-center gap-2 text-sm">
                    <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                    </svg>
                    მუშავდება...
                </span>
            </template>
        </button>

        {{-- Error --}}
        <div x-show="error" x-cloak class="mt-3 flex items-start gap-2 rounded-lg bg-danger-50 dark:bg-danger-950 border border-danger-200 dark:border-danger-800 p-3">
            <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-danger-500 shrink-0 mt-0.5"/>
            <p class="text-sm text-danger-700 dark:text-danger-300" x-text="error"></p>
        </div>
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

    <div class="relative">
        <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-200 dark:border-gray-700"></div></div>
        <div class="relative flex justify-center"><span class="bg-white dark:bg-gray-900 px-3 text-xs text-gray-400">ან ხელით</span></div>
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

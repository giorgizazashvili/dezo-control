@php
    use Illuminate\View\ComponentAttributeBag;
@endphp

@props([
    'debounce' => '500ms',
    'onBlur' => false,
    'placeholder' => __('filament-tables::table.fields.search.placeholder'),
    'wireModel' => 'tableSearch',
])

@php
    $wireModelAttribute = $onBlur ? 'wire:model.live.blur' : "wire:model.live.debounce.{$debounce}";
@endphp

<div
    x-id="['input']"
    x-data="{
        scannerOpen: false,
        stream: null,
        detected: false,
        rafId: null,

        init() {
            document.addEventListener('livewire:navigating', () => this.closeScanner());
        },

        async openScanner() {
            if (!window.jsQR) {
                await new Promise((resolve) => {
                    const s = document.createElement('script');
                    s.src = 'https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js';
                    s.onload = resolve;
                    document.head.appendChild(s);
                });
            }

            this.detected = false;
            this.scannerOpen = true;

            await this.$nextTick();

            try {
                this.stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: { ideal: 'environment' }, width: { ideal: 1280 }, height: { ideal: 720 } }
                });
                const video = this.$refs.qrVideo;
                video.srcObject = this.stream;
                await video.play();
                this.tick();
            } catch (err) {
                this.scannerOpen = false;
                alert('კამერაზე წვდომა ვერ მოხერხდა.');
            }
        },

        tick() {
            if (!this.scannerOpen) return;
            const video = this.$refs.qrVideo;
            const canvas = this.$refs.qrCanvas;
            if (video.readyState === video.HAVE_ENOUGH_DATA) {
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                canvas.getContext('2d').drawImage(video, 0, 0);
                const imgData = canvas.getContext('2d').getImageData(0, 0, canvas.width, canvas.height);
                const code = jsQR(imgData.data, imgData.width, imgData.height, { inversionAttempts: 'dontInvert' });
                if (code) {
                    this.detected = true;
                    this.$wire.set('{{ $wireModel }}', code.data);
                    setTimeout(() => this.closeScanner(), 700);
                    return;
                }
            }
            this.rafId = requestAnimationFrame(() => this.tick());
        },

        closeScanner() {
            this.scannerOpen = false;
            if (this.rafId) { cancelAnimationFrame(this.rafId); this.rafId = null; }
            if (this.stream) { this.stream.getTracks().forEach(t => t.stop()); this.stream = null; }
        }
    }"
    {{ $attributes->class(['fi-ta-search-field']) }}
    style="display: flex; align-items: center; gap: 0.375rem;"
>
    <label x-bind:for="$id('input')" class="fi-sr-only">
        {{ __('filament-tables::table.fields.search.label') }}
    </label>

    <div style="flex: 1; min-width: 0;">
        <x-filament::input.wrapper
            inline-prefix
            :prefix-icon="\Filament\Support\Icons\Heroicon::MagnifyingGlass"
            :prefix-icon-alias="\Filament\Tables\View\TablesIconAlias::SEARCH_FIELD"
            :wire:target="$wireModel"
        >
            <x-filament::input
                :attributes="
                    (new ComponentAttributeBag)->merge([
                        'autocomplete' => 'off',
                        'inlinePrefix' => true,
                        'maxlength' => 1000,
                        'placeholder' => $placeholder,
                        'type' => 'search',
                        'wire:key' => $this->getId() . '.table.' . $wireModel . '.field.input',
                        $wireModelAttribute => $wireModel,
                        'x-bind:id' => '$id(\'input\')',
                        'x-on:keyup' => 'if ($event.key === \'Enter\') { $wire.$refresh() }',
                    ], escape: false)
                "
            />
        </x-filament::input.wrapper>
    </div>

    {{-- QR scan button --}}
    <button
        type="button"
        class="fi-icon-btn fi-size-sm"
        style="margin: 0; flex-shrink: 0;"
        @click="openScanner()"
        title="QR სკანირება"
    >
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="fi-icon fi-size-sm">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75ZM6.75 16.5h.75v.75h-.75v-.75ZM16.5 6.75h.75v.75h-.75v-.75ZM13.5 13.5h.75v.75h-.75v-.75ZM13.5 19.5h.75v.75h-.75v-.75ZM19.5 13.5h.75v.75h-.75v-.75ZM19.5 19.5h.75v.75h-.75v-.75ZM16.5 16.5h.75v.75h-.75v-.75Z" />
        </svg>
    </button>

    {{-- Live QR Scanner Modal --}}
    <div
        x-show="scannerOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @keydown.escape.window="closeScanner()"
        style="position: fixed; inset: 0; z-index: 9999; background: rgba(0,0,0,0.93); display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 1rem;"
        x-cloak
    >
        <div style="width: 100%; max-width: 400px; display: flex; flex-direction: column; align-items: center; gap: 1rem;">

            {{-- Video container --}}
            <div style="position: relative; width: 100%; border-radius: 16px; overflow: hidden; background: #000; aspect-ratio: 3/4; max-height: 65vh;">
                <video
                    x-ref="qrVideo"
                    autoplay
                    playsinline
                    muted
                    style="width: 100%; height: 100%; object-fit: cover; display: block;"
                ></video>

                {{-- Dark overlay with cutout effect --}}
                <div style="position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;">
                    <div
                        :style="detected
                            ? 'border-color: #22c55e; box-shadow: 0 0 0 4px rgba(34,197,94,0.4), inset 0 0 0 9999px rgba(0,0,0,0.35);'
                            : 'border-color: rgba(255,255,255,0.9); box-shadow: inset 0 0 0 9999px rgba(0,0,0,0.35);'"
                        style="position: relative; width: 220px; height: 220px; border: 2px solid rgba(255,255,255,0.9); border-radius: 12px; transition: border-color 0.25s, box-shadow 0.25s;"
                    >
                        {{-- Corner markers --}}
                        <span style="position:absolute;top:-3px;left:-3px;width:22px;height:22px;border-top:3px solid white;border-left:3px solid white;border-radius:3px 0 0 0;"></span>
                        <span style="position:absolute;top:-3px;right:-3px;width:22px;height:22px;border-top:3px solid white;border-right:3px solid white;border-radius:0 3px 0 0;"></span>
                        <span style="position:absolute;bottom:-3px;left:-3px;width:22px;height:22px;border-bottom:3px solid white;border-left:3px solid white;border-radius:0 0 0 3px;"></span>
                        <span style="position:absolute;bottom:-3px;right:-3px;width:22px;height:22px;border-bottom:3px solid white;border-right:3px solid white;border-radius:0 0 3px 0;"></span>

                        {{-- Scan line --}}
                        <div
                            x-show="!detected"
                            style="position: absolute; left: 0; right: 0; height: 2px; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.9), transparent); animation: qr-scan-line 2s ease-in-out infinite;"
                        ></div>

                        {{-- Success checkmark --}}
                        <div
                            x-show="detected"
                            style="position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; background: rgba(34,197,94,0.15); border-radius: 10px;"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" style="width: 3.5rem; height: 3.5rem; color: #22c55e;" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <canvas x-ref="qrCanvas" style="display: none;"></canvas>

            {{-- Status text --}}
            <p
                :style="detected ? 'color: #4ade80;' : 'color: rgba(255,255,255,0.65);'"
                style="margin: 0; font-size: 0.875rem; text-align: center; transition: color 0.2s;"
                x-text="detected ? 'ნაიძებნია!' : 'QR კოდი ჩასვი ჩარჩოში'"
            ></p>

            {{-- Close button --}}
            <button
                @click="closeScanner()"
                style="padding: 0.5rem 2rem; background: rgba(255,255,255,0.1); color: rgba(255,255,255,0.8); border: 1px solid rgba(255,255,255,0.15); border-radius: 8px; cursor: pointer; font-size: 0.875rem; transition: background 0.15s;"
                @mouseenter="$el.style.background='rgba(255,255,255,0.2)'"
                @mouseleave="$el.style.background='rgba(255,255,255,0.1)'"
            >
                დახურვა
            </button>
        </div>
    </div>

    <style>
        @keyframes qr-scan-line {
            0%   { top: 8px; opacity: 0; }
            10%  { opacity: 1; }
            90%  { opacity: 1; }
            100% { top: calc(100% - 10px); opacity: 0; }
        }
    </style>
</div>

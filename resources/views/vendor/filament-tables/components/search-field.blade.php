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
        scanning: false,
        async scanQr() {
            if (!window.jsQR) {
                await new Promise((resolve) => {
                    const script = document.createElement('script');
                    script.src = 'https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js';
                    script.onload = resolve;
                    document.head.appendChild(script);
                });
            }

            const fileInput = document.createElement('input');
            fileInput.type = 'file';
            fileInput.accept = 'image/*';
            fileInput.setAttribute('capture', 'environment');
            fileInput.style.cssText = 'position:fixed;top:-9999px;opacity:0;';
            document.body.appendChild(fileInput);

            const wire = this.$wire;

            fileInput.onchange = async (e) => {
                const file = e.target.files[0];
                document.body.removeChild(fileInput);
                if (!file) return;

                this.scanning = true;

                try {
                    const imgData = await new Promise((resolve, reject) => {
                        const reader = new FileReader();
                        reader.onerror = reject;
                        reader.onload = (ev) => {
                            const img = new Image();
                            img.onerror = reject;
                            img.onload = () => {
                                const MAX = 1280;
                                let w = img.width, h = img.height;
                                if (w > MAX || h > MAX) {
                                    const r = Math.min(MAX / w, MAX / h);
                                    w = Math.round(w * r);
                                    h = Math.round(h * r);
                                }
                                const canvas = document.createElement('canvas');
                                canvas.width = w;
                                canvas.height = h;
                                canvas.getContext('2d').drawImage(img, 0, 0, w, h);
                                resolve(canvas.getContext('2d').getImageData(0, 0, w, h));
                            };
                            img.src = ev.target.result;
                        };
                        reader.readAsDataURL(file);
                    });

                    const code = jsQR(imgData.data, imgData.width, imgData.height);
                    if (code) {
                        wire.set('{{ $wireModel }}', code.data);
                    } else {
                        alert('QR კოდი ვერ ამოიცნო — სცადე ახლოდან ან გასწორებული კუთხით.');
                    }
                } finally {
                    this.scanning = false;
                }
            };

            fileInput.click();
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

    <button
        type="button"
        class="fi-icon-btn fi-size-sm"
        style="margin: 0; flex-shrink: 0;"
        @click="scanQr()"
        :title="scanning ? 'მუშავდება...' : 'QR სკანირება'"
        :disabled="scanning"
    >
        <svg
            x-show="!scanning"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="1.5"
            stroke="currentColor"
            class="fi-icon fi-size-sm"
        >
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75ZM6.75 16.5h.75v.75h-.75v-.75ZM16.5 6.75h.75v.75h-.75v-.75ZM13.5 13.5h.75v.75h-.75v-.75ZM13.5 19.5h.75v.75h-.75v-.75ZM19.5 13.5h.75v.75h-.75v-.75ZM19.5 19.5h.75v.75h-.75v-.75ZM16.5 16.5h.75v.75h-.75v-.75Z" />
        </svg>
        <svg
            x-show="scanning"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            class="fi-icon fi-size-sm"
            style="animation: spin 1s linear infinite;"
        >
            <circle style="opacity: 0.25;" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path style="opacity: 0.75;" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
        </svg>
    </button>
</div>

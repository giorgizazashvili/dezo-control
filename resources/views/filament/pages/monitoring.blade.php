<x-filament-panels::page>

    <style>
        #qr-reader video { border-radius: 0.5rem; }
        #qr-reader img { display: none !important; }
        #qr-reader > div:last-child { display: none !important; }
    </style>

    <div class="max-w-2xl mx-auto space-y-6">

        {{-- ობიექტის არჩევა --}}
        <x-filament::section>
            <x-slot name="heading">ობიექტი</x-slot>
            <x-slot name="description">QR სკანირებამდე აირჩიეთ ობიექტი</x-slot>

            <x-filament::input.wrapper>
                <x-filament::input.select wire:model.live="organizationId">
                    <option value="">-- აირჩიეთ ობიექტი --</option>
                    @foreach($this->getOrganizations() as $org)
                        <option value="{{ $org->id }}" @selected($organizationId == $org->id)>{{ $org->name }}</option>
                    @endforeach
                </x-filament::input.select>
            </x-filament::input.wrapper>
        </x-filament::section>

        @if($organizationId)

            @if(! $scannedData)

                {{-- QR სკანერი --}}
                <x-filament::section>
                    <x-slot name="heading">QR სკანირება</x-slot>
                    <x-slot name="description">მიუახლოეთ კამერა QR კოდს — ავტომატურად ამოიცნობს</x-slot>

                    <div x-data="monitoringQr()"
                         x-init="start()"
                         x-on:reset-scanner.window="restart()"
                         class="flex flex-col items-center gap-4">

                        <div id="qr-reader" class="w-full overflow-hidden rounded-xl"
                             style="max-width: 340px;"></div>

                        <p x-show="error"
                           x-text="error"
                           class="text-sm text-danger-600 dark:text-danger-400 text-center"></p>

                        <p x-show="!error"
                           class="text-sm text-gray-500 dark:text-gray-400 text-center">
                            კამერა მზადაა სკანირებისთვის
                        </p>
                    </div>
                </x-filament::section>

            @else

                {{-- სკანირებული პროდუქტი --}}
                <x-filament::section>
                    <x-slot name="heading">{{ $scannedData['product'] ?? '—' }}</x-slot>
                    <x-slot name="description">
                        @if($scannedData['dimension'] ?? ''){{ $scannedData['dimension'] }} &middot; @endif
                        რაოდ. {{ $scannedData['quantity'] ?? '—' }} &middot; {{ $scannedData['date'] ?? '—' }}
                    </x-slot>

                    <x-filament::button
                        wire:click="resetScan"
                        color="gray"
                        size="sm"
                        icon="heroicon-m-arrow-path">
                        ახლიდან სკანირება
                    </x-filament::button>
                </x-filament::section>

                {{-- კომპონენტები --}}
                @if($components && count($components) > 0)

                    @php
                        $lowCount = collect($components)->filter(fn($c) => $c['stock'] < $c['needed'])->count();
                    @endphp

                    <x-filament::section>
                        <x-slot name="heading">კომპონენტები</x-slot>
                        <x-slot name="description">
                            {{ count($components) }} კომპონენტი
                            @if($lowCount > 0)
                                &middot;
                                <span class="text-danger-600 dark:text-danger-400 font-semibold">
                                    {{ $lowCount }} ნაკლები ნაშთით
                                </span>
                            @endif
                        </x-slot>

                        <div class="-mx-6 -mb-6 divide-y divide-gray-100 dark:divide-white/5">
                            @foreach($components as $component)
                                @php $low = $component['stock'] < $component['needed']; @endphp

                                <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-4">

                                    {{-- სახელი + ნაშთი --}}
                                    <div class="flex-1 min-w-0 space-y-1">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-medium text-gray-950 dark:text-white">
                                                {{ $component['name'] }}
                                            </span>
                                            @if($component['dimension'])
                                                <x-filament::badge color="gray" size="sm">
                                                    {{ $component['dimension'] }}
                                                </x-filament::badge>
                                            @endif
                                        </div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            ნაშთი:
                                            <x-filament::badge :color="$low ? 'danger' : 'success'" size="sm">
                                                {{ number_format($component['stock'], 2) }}
                                            </x-filament::badge>
                                            &nbsp;საჭ:
                                            <span class="font-medium text-gray-700 dark:text-gray-300">
                                                {{ number_format($component['needed'], 2) }}
                                            </span>
                                        </p>
                                    </div>

                                    {{-- ღილაკები --}}
                                    <div class="flex items-center gap-2">
                                        <x-filament::button
                                            wire:click="openWriteOff({{ $component['id'] }}, {{ $component['needed'] }})"
                                            color="danger"
                                            size="sm"
                                            icon="heroicon-m-minus-circle">
                                            ჩამოწერა
                                        </x-filament::button>

                                        <x-filament::button
                                            wire:click="openReplacement({{ $component['id'] }}, {{ $component['needed'] }})"
                                            color="success"
                                            size="sm"
                                            icon="heroicon-m-plus-circle">
                                            შეცვლა
                                        </x-filament::button>
                                    </div>

                                </div>
                            @endforeach
                        </div>
                    </x-filament::section>

                @else
                    <x-filament::section>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            ამ პროდუქტს კომპონენტები არ აქვს.
                        </p>
                    </x-filament::section>
                @endif

            @endif

        @endif

    </div>

    {{-- ჩამოწერის მოდალი --}}
    <x-filament::modal id="write-off-modal" width="sm">
        <x-slot name="heading">კომპონენტის ჩამოწერა</x-slot>
        <x-slot name="description">{{ $this->getComponentName($writeOffComponentId) }}</x-slot>

        <div class="space-y-2">
            <x-filament::input.wrapper label="ჩამოსაწერი რაოდენობა">
                <x-filament::input
                    type="number"
                    wire:model="writeOffQuantity"
                    step="0.01"
                    min="0.01"
                    placeholder="0.00"
                />
            </x-filament::input.wrapper>
        </div>

        <x-slot name="footer">
            <x-filament::button wire:click="confirmWriteOff" color="danger" class="w-full">
                ჩამოწერა
            </x-filament::button>
        </x-slot>
    </x-filament::modal>

    {{-- შეცვლის მოდალი --}}
    <x-filament::modal id="replacement-modal" width="sm">
        <x-slot name="heading">კომპონენტის შეცვლა</x-slot>
        <x-slot name="description">{{ $this->getComponentName($replacementComponentId) }}</x-slot>

        <div class="space-y-2">
            <x-filament::input.wrapper label="დასამატებელი რაოდენობა">
                <x-filament::input
                    type="number"
                    wire:model="replacementQuantity"
                    step="0.01"
                    min="0.01"
                    placeholder="0.00"
                />
            </x-filament::input.wrapper>
        </div>

        <x-slot name="footer">
            <x-filament::button wire:click="confirmReplacement" color="success" class="w-full">
                შეცვლა
            </x-filament::button>
        </x-slot>
    </x-filament::modal>

    @assets
        <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    @endassets

    @script
    <script>
        Alpine.data('monitoringQr', () => ({
            scanner: null,
            error: null,

            start() {
                this.$nextTick(() => this.initScanner());
            },

            initScanner() {
                if (this.scanner || ! document.getElementById('qr-reader')) return;

                this.scanner = new Html5Qrcode('qr-reader');

                this.scanner.start(
                    { facingMode: 'environment' },
                    { fps: 10, qrbox: { width: 240, height: 240 } },
                    (text) => {
                        this.scanner.stop().then(() => {
                            this.scanner = null;
                            this.$wire.processQr(text);
                        });
                    },
                    () => {}
                ).catch(() => {
                    this.error = 'კამერაზე წვდომა ვერ მოხერხდა. გთხოვთ, დართოთ ნებართვა.';
                });
            },

            restart() {
                if (this.scanner) {
                    this.scanner.stop().catch(() => {}).finally(() => {
                        this.scanner = null;
                        this.$nextTick(() => this.initScanner());
                    });
                } else {
                    this.$nextTick(() => this.initScanner());
                }
            },
        }));
    </script>
    @endscript

</x-filament-panels::page>

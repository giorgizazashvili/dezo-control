<x-filament-panels::page>
    <div class="space-y-6">

        {{-- ორგანიზაცია --}}
        <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
            <label class="block text-sm font-semibold text-gray-950 dark:text-white mb-2">
                ობიექტი (ორგანიზაცია) <span class="text-red-500">*</span>
            </label>
            <select wire:model.live="organizationId"
                    class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-950 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white">
                <option value="">-- აირჩიეთ ორგანიზაცია --</option>
                @foreach($this->getOrganizations() as $org)
                    <option value="{{ $org->id }}" @selected($organizationId == $org->id)>{{ $org->name }}</option>
                @endforeach
            </select>
        </div>

        @if($organizationId)

            @if(! $scannedData)

                {{-- QR სკანერი --}}
                <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
                    <h3 class="text-sm font-semibold text-gray-950 dark:text-white mb-4">QR კოდის სკანირება</h3>

                    <div x-data="monitoringQr()"
                         x-init="start()"
                         x-on:reset-scanner.window="restart()">

                        <div id="qr-reader" class="mx-auto overflow-hidden rounded-lg" style="max-width: 380px;"></div>

                        <p x-show="error"
                           x-text="error"
                           class="mt-3 text-sm text-red-600 dark:text-red-400 text-center"></p>

                        <p x-show="!error"
                           class="mt-3 text-xs text-gray-500 dark:text-gray-400 text-center">
                            კამერა ავტომატურად ამოიცნობს QR კოდს
                        </p>
                    </div>
                </div>

            @else

                {{-- პროდუქტის ინფორმაცია --}}
                <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-base font-semibold text-gray-950 dark:text-white">
                                {{ $scannedData['product'] ?? '—' }}
                            </h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                @if($scannedData['dimension'] ?? '')
                                    {{ $scannedData['dimension'] }} &bull;
                                @endif
                                რაოდ.: {{ $scannedData['quantity'] ?? '—' }}
                                &bull; {{ $scannedData['date'] ?? '—' }}
                            </p>
                        </div>

                        <button wire:click="resetScan"
                                class="shrink-0 rounded-lg px-3 py-1.5 text-xs font-semibold text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50 dark:text-gray-300 dark:ring-white/20 dark:hover:bg-white/5">
                            ახლიდან სკანირება
                        </button>
                    </div>

                    {{-- კომპონენტების ცხრილი --}}
                    @if($components && count($components) > 0)
                        <div class="mt-6 overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-gray-200 dark:border-white/10 text-left">
                                        <th class="pb-3 pr-4 font-semibold text-gray-950 dark:text-white">კომპონენტი</th>
                                        <th class="pb-3 pr-4 font-semibold text-gray-950 dark:text-white">განზომ.</th>
                                        <th class="pb-3 pr-4 font-semibold text-gray-950 dark:text-white">საჭირო</th>
                                        <th class="pb-3 pr-4 font-semibold text-gray-950 dark:text-white">ნაშთი</th>
                                        <th class="pb-3 font-semibold text-gray-950 dark:text-white">მოქმედება</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($components as $component)
                                        <tr class="border-b border-gray-100 dark:border-white/5">
                                            <td class="py-3 pr-4 text-gray-950 dark:text-white font-medium">
                                                {{ $component['name'] }}
                                            </td>
                                            <td class="py-3 pr-4 text-gray-500 dark:text-gray-400">
                                                {{ $component['dimension'] }}
                                            </td>
                                            <td class="py-3 pr-4 text-gray-700 dark:text-gray-300">
                                                {{ number_format($component['needed'], 2) }}
                                            </td>
                                            <td class="py-3 pr-4">
                                                @php $low = $component['stock'] < $component['needed']; @endphp
                                                <span class="{{ $low ? 'font-semibold text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                                    {{ number_format($component['stock'], 2) }}
                                                </span>
                                            </td>
                                            <td class="py-3">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <button wire:click="openWriteOff({{ $component['id'] }}, {{ $component['needed'] }})"
                                                            class="rounded-lg bg-red-600 px-2.5 py-1.5 text-xs font-semibold text-white hover:bg-red-500 transition-colors">
                                                        ჩამოწერა
                                                    </button>
                                                    <button wire:click="openReplacement({{ $component['id'] }}, {{ $component['needed'] }})"
                                                            class="rounded-lg bg-green-600 px-2.5 py-1.5 text-xs font-semibold text-white hover:bg-green-500 transition-colors">
                                                        შეცვლა
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                            ამ პროდუქტს კომპონენტები არ აქვს.
                        </p>
                    @endif
                </div>

            @endif

        @endif

    </div>

    {{-- ჩამოწერის მოდალი --}}
    @if($showWriteOffModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/60" wire:click="$set('showWriteOffModal', false)"></div>
            <div class="relative z-10 w-full max-w-sm rounded-xl bg-white p-6 shadow-xl dark:bg-gray-900">
                <h3 class="text-base font-semibold text-gray-950 dark:text-white mb-1">კომპონენტის ჩამოწერა</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    {{ $this->getComponentName($writeOffComponentId) }}
                </p>

                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        ჩამოსაწერი რაოდენობა
                    </label>
                    <input type="number"
                           wire:model="writeOffQuantity"
                           step="0.01"
                           min="0.01"
                           class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white">
                </div>

                <div class="flex justify-end gap-3">
                    <button wire:click="$set('showWriteOffModal', false)"
                            class="rounded-lg px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50 dark:text-gray-300 dark:ring-white/20 dark:hover:bg-white/5">
                        გაუქმება
                    </button>
                    <button wire:click="confirmWriteOff"
                            class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-500 transition-colors">
                        ჩამოწერა
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- შეცვლის მოდალი --}}
    @if($showReplacementModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/60" wire:click="$set('showReplacementModal', false)"></div>
            <div class="relative z-10 w-full max-w-sm rounded-xl bg-white p-6 shadow-xl dark:bg-gray-900">
                <h3 class="text-base font-semibold text-gray-950 dark:text-white mb-1">კომპონენტის შეცვლა</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    {{ $this->getComponentName($replacementComponentId) }}
                </p>

                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        დასამატებელი რაოდენობა
                    </label>
                    <input type="number"
                           wire:model="replacementQuantity"
                           step="0.01"
                           min="0.01"
                           class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white">
                </div>

                <div class="flex justify-end gap-3">
                    <button wire:click="$set('showReplacementModal', false)"
                            class="rounded-lg px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50 dark:text-gray-300 dark:ring-white/20 dark:hover:bg-white/5">
                        გაუქმება
                    </button>
                    <button wire:click="confirmReplacement"
                            class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-500 transition-colors">
                        შეცვლა
                    </button>
                </div>
            </div>
        </div>
    @endif

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

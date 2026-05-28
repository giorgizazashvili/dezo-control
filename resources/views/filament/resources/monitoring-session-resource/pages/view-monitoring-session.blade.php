<x-filament-panels::page>
    <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
        <div class="grid grid-cols-2 gap-6 sm:grid-cols-4">
            <div class="flex flex-col gap-1">
                <span class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">ობიექტი</span>
                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $this->record->organization->name }}</span>
            </div>

            <div class="flex flex-col gap-1">
                <span class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">ტექნიკოსი</span>
                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $this->record->technician ?? '—' }}</span>
            </div>

            <div class="flex flex-col gap-1">
                <span class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">დაწყება</span>
                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $this->record->started_at?->format('d.m.Y H:i') ?? '—' }}</span>
            </div>

            <div class="flex flex-col gap-1">
                <span class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">დასრულება</span>
                <span class="text-sm font-semibold {{ $this->record->finished_at ? 'text-success-600 dark:text-success-400' : 'text-warning-500' }}">
                    {{ $this->record->finished_at?->format('d.m.Y H:i') ?? 'მიმდინარე' }}
                </span>
            </div>
        </div>
    </div>

    @if ($this->record->notes || $this->record->photos)
        @php
            $photoUrls = collect($this->record->photos ?? [])->map(fn($p) => asset('storage/' . $p))->values()->toJson();
        @endphp

        <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6 space-y-4">

            @if ($this->record->notes)
                <div class="flex flex-col gap-1">
                    <span class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">კომენტარი</span>
                    <p class="text-sm text-gray-900 dark:text-white whitespace-pre-wrap">{{ $this->record->notes }}</p>
                </div>
            @endif

            @if ($this->record->photos)
                <div
                    x-data="{
                        open: false,
                        current: 0,
                        photos: {{ $photoUrls }},
                        prev() { this.current = (this.current - 1 + this.photos.length) % this.photos.length },
                        next() { this.current = (this.current + 1) % this.photos.length },
                    }"
                    @keydown.escape.window="open = false"
                    @keydown.arrow-left.window="if(open) prev()"
                    @keydown.arrow-right.window="if(open) next()"
                >
                    <button
                        type="button"
                        @click.stop="open = !open; current = 0"
                        class="inline-flex items-center gap-2 rounded-lg bg-white shadow-sm ring-1 ring-gray-950/10 dark:bg-gray-800 dark:ring-white/10 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                    >
                        ფოტოების ნახვა
                        <span class="rounded-full bg-gray-100 dark:bg-white/10 px-2 py-0.5 text-xs font-semibold text-gray-600 dark:text-gray-300">
                            {{ count($this->record->photos) }}
                        </span>
                    </button>

                    {{-- Lightbox --}}
                    <div
                        x-show="open"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="fixed inset-0 z-50 flex items-center justify-center bg-black/85"
                        @click.self="open = false"
                    >
                        <button
                            type="button"
                            @click="open = false"
                            class="absolute top-4 right-4 rounded-full bg-white/10 p-2 text-white hover:bg-white/25 transition-colors"
                        >
                            <x-heroicon-o-x-mark class="h-6 w-6" />
                        </button>

                        <button
                            type="button"
                            x-show="photos.length > 1"
                            @click="prev()"
                            class="absolute left-4 rounded-full bg-white/10 p-3 text-white hover:bg-white/25 transition-colors"
                        >
                            <x-heroicon-o-chevron-left class="h-7 w-7" />
                        </button>

                        <div class="flex flex-col items-center gap-3 px-24 max-w-5xl w-full">
                            <img
                                :src="photos[current]"
                                class="max-h-[82vh] max-w-full rounded-xl object-contain shadow-2xl"
                            >
                            <span
                                x-show="photos.length > 1"
                                x-text="(current + 1) + ' / ' + photos.length"
                                class="text-sm text-white/60"
                            ></span>
                        </div>

                        <button
                            type="button"
                            x-show="photos.length > 1"
                            @click="next()"
                            class="absolute right-4 rounded-full bg-white/10 p-3 text-white hover:bg-white/25 transition-colors"
                        >
                            <x-heroicon-o-chevron-right class="h-7 w-7" />
                        </button>
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{ $this->table }}
</x-filament-panels::page>

<x-filament-panels::page>
    <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">ობიექტი</p>
                <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $this->record->organization->name }}</p>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">ტექნიკოსი</p>
                <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $this->record->technician ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">დაწყება</p>
                <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $this->record->started_at?->format('d.m.Y H:i') ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">დასრულება</p>
                <p class="mt-1 text-sm font-semibold {{ $this->record->finished_at ? 'text-success-600' : 'text-warning-500' }}">
                    {{ $this->record->finished_at?->format('d.m.Y H:i') ?? 'მიმდინარე' }}
                </p>
            </div>
        </div>
    </div>

    {{ $this->table }}
</x-filament-panels::page>

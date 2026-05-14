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

    {{ $this->table }}
</x-filament-panels::page>

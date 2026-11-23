<x-filament-panels::page>
    <div class="space-y-6">
        <div class="max-w-xs">
            <label for="selected-month" class="mb-2 block text-sm font-medium text-gray-200">
                Reporting month
            </label>
            <input
                id="selected-month"
                type="month"
                wire:model.live="selectedMonth"
                class="block w-64 rounded-lg border px-3 py-2 text-sm shadow-sm outline-none transition focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                style="color-scheme: dark; background-color: #18181b; border-color: #3f3f46; color: #f4f4f5;"
            >
            @error('selectedMonth')
                <p class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
            @enderror
        </div>

        {{ $this->table }}
    </div>
</x-filament-panels::page>

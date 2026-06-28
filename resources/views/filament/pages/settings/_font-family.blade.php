<div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-900/50">
    <label class="block text-sm font-medium text-gray-900 dark:text-white">Font Family</label>
    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Base font for the admin panel interface.</p>
    <div class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2">
        @foreach(['Plus Jakarta Sans', 'Inter', 'Instrument Sans', 'system-ui'] as $fontOption)
            <label class="relative flex cursor-pointer rounded-lg border-2 p-3 transition-all duration-150"
                :class="$wire.settings.font_family === '{{ $fontOption }}' ? 'border-primary-500 bg-primary-50 dark:border-primary-500 dark:bg-primary-900/20' : 'border-gray-200 hover:border-gray-300 dark:border-gray-700 dark:hover:border-gray-600'">
                <input type="radio" name="font_family" value="{{ $fontOption }}" wire:model.live="settings.font_family" class="sr-only">
                <div class="w-full">
                    <span class="block text-sm font-medium text-gray-900 dark:text-white" style="font-family: '{{ $fontOption }}', sans-serif">{{ $fontOption }}</span>
                    <span class="mt-1 block text-xs text-gray-500 dark:text-gray-400" style="font-family: '{{ $fontOption }}', sans-serif">The quick brown fox jumps over the lazy dog.</span>
                </div>
            </label>
        @endforeach
    </div>
</div>

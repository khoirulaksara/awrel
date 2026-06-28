<div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-900/50">
    <label class="block text-sm font-medium text-gray-900 dark:text-white">Border Radius</label>
    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Controls the rounding of cards, buttons, and panels.</p>
    @php $rValues = ['sm' => '0.375rem', 'md' => '0.5rem', 'lg' => '0.75rem', 'xl' => '1rem', '2xl' => '1.25rem']; @endphp
    <div class="mt-3 flex flex-wrap items-center gap-2">
        @foreach(['sm', 'md', 'lg', 'xl', '2xl'] as $radiusOption)
            @php $rVal = $rValues[$radiusOption]; @endphp
            <label class="relative flex cursor-pointer items-center gap-2 rounded-lg border-2 px-4 py-3 transition-all duration-150"
                :class="$wire.settings.border_radius === '{{ $radiusOption }}' ? 'border-primary-500 bg-primary-50 dark:border-primary-500 dark:bg-primary-900/20' : 'border-gray-200 hover:border-gray-300 dark:border-gray-700 dark:hover:border-gray-600'"
                style="border-radius: {{ $rVal }}">
                <input type="radio" name="border_radius" value="{{ $radiusOption }}" wire:model.live="settings.border_radius" class="sr-only">
                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $radiusOption }}</span>
            </label>
        @endforeach
    </div>
</div>

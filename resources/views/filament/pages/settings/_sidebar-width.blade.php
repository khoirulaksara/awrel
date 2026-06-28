<div x-data="awrelRangeSlider({{ $settings['sidebar_width'] ?? 256 }})" class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-900/50">
    <label for="sidebar_width" class="block text-sm font-medium text-gray-900 dark:text-white">Sidebar Width</label>
    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Adjust the sidebar width (180px — 400px).</p>
    <div class="mt-3 flex items-center gap-4">
        <input type="range" id="sidebar_width" min="180" max="400" step="4" wire:model.live="settings.sidebar_width" x-on:input="updateWidth($event.target.value)" class="w-full accent-primary-500" />
        <span class="min-w-[3rem] text-right text-sm font-mono font-medium text-gray-700 dark:text-gray-300" x-text="currentWidth + 'px'"></span>
    </div>
</div>

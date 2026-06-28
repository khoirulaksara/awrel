<div x-data="awrelColorPicker('{{ $settings['primary_color'] ?? '#f59e0b' }}')" class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-900/50">
    <label class="block text-sm font-medium text-gray-900 dark:text-white">Primary Color</label>
    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">The main accent color used across the admin panel.</p>
    <div class="mt-3 flex items-center gap-4">
        <input type="color" x-ref="colorInput" x-on:input="setColor($refs.colorInput.value)" class="h-10 w-10 cursor-pointer rounded-lg border border-gray-300 dark:border-gray-600">
        <div class="flex-1">
            <input type="text" x-model="previewColor" x-on:input="setColor(previewColor)" class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" placeholder="#f59e0b" />
        </div>
        <div class="flex h-10 items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 text-xs font-medium text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
            <span class="flex h-6 w-6 rounded" x-bind:style="'background-color: ' + previewColor"></span>
            <span x-text="previewColor" class="font-mono tabular-nums"></span>
        </div>
    </div>
    @php $presets = ['#f59e0b', '#ef4444', '#3b82f6', '#10b981', '#8b5cf6', '#ec4899', '#06b6d4', '#f97316', '#6366f1', '#14b8a6']; @endphp
    <div class="mt-3 flex flex-wrap gap-2">
        @foreach($presets as $preset)
            <button type="button" x-on:click="setColor('{{ $preset }}')" class="h-7 w-7 rounded-full border-2 border-white shadow-sm transition-transform hover:scale-110 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:border-gray-800" style="background-color: {{ $preset }}" title="{{ $preset }}"></button>
        @endforeach
    </div>
</div>

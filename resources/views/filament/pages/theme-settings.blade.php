<x-filament-panels::page>

    <form wire:submit="save" class="space-y-8">
        {{-- General Card --}}
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700/50 dark:bg-gray-900">
            <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <div class="flex items-center gap-3">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary-50 text-primary-600 dark:bg-primary-900/50 dark:text-primary-400">
                        <x-heroicon-m-cog-6-tooth class="h-4 w-4" />
                    </span>
                    <div>
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">General</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Core behavior settings</p>
                    </div>
                </div>
            </div>
            <div class="space-y-6 px-6 py-5">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-1">
                    {{-- Favicon Spinner Toggle --}}
                    <div class="flex items-center justify-between rounded-xl border border-gray-100 bg-gray-50/50 px-4 py-3 dark:border-gray-800 dark:bg-gray-900/50">
                        <div class="flex items-start gap-3">
                            <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-white text-gray-500 shadow-sm dark:bg-gray-800 dark:text-gray-400">
                                <x-heroicon-m-globe-alt class="h-3.5 w-3.5" />
                            </span>
                            <div>
                                <label for="favicon_spinner" class="text-sm font-medium text-gray-900 dark:text-white">
                                    Animated Favicon Spinner
                                </label>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Spinner in browser tab on navigation
                                </p>
                            </div>
                        </div>
                        <label class="relative inline-flex cursor-pointer items-center">
                            <input type="checkbox" id="favicon_spinner" wire:model.live="settings.favicon_spinner" class="peer sr-only">
                            <div class="h-5 w-9 rounded-full bg-gray-200 after:absolute after:start-[2px] after:top-[2px] after:h-4 after:w-4 after:rounded-full after:bg-white after:transition-all after:content-[''] peer-checked:bg-primary-500 peer-checked:after:translate-x-full dark:bg-gray-600"></div>
                        </label>
                    </div>
                    {{-- Sticky Table Actions Toggle --}}
                    <div class="flex items-center justify-between rounded-xl border border-gray-100 bg-gray-50/50 px-4 py-3 dark:border-gray-800 dark:bg-gray-900/50">
                        <div class="flex items-start gap-3">
                            <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-white text-gray-500 shadow-sm dark:bg-gray-800 dark:text-gray-400">
                                <x-heroicon-m-table-cells class="h-3.5 w-3.5" />
                            </span>
                            <div>
                                <label for="sticky_table_actions" class="text-sm font-medium text-gray-900 dark:text-white">
                                    Sticky Table Actions
                                </label>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Pin actions column on table horizontal scroll
                                </p>
                            </div>
                        </div>
                        <label class="relative inline-flex cursor-pointer items-center">
                            <input type="checkbox" id="sticky_table_actions" wire:model.live="settings.sticky_table_actions" class="peer sr-only">
                            <div class="h-5 w-9 rounded-full bg-gray-200 after:absolute after:start-[2px] after:top-[2px] after:h-4 after:w-4 after:rounded-full after:bg-white after:transition-all after:content-[''] peer-checked:bg-primary-500 peer-checked:after:translate-x-full dark:bg-gray-600"></div>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        {{-- Appearance Card --}}
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700/50 dark:bg-gray-900">
            <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <div class="flex items-center gap-3">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary-50 text-primary-600 dark:bg-primary-900/50 dark:text-primary-400">
                        <x-heroicon-m-paint-brush class="h-4 w-4" />
                    </span>
                    <div>
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Appearance</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Colors, fonts, and border radius</p>
                    </div>
                </div>
            </div>
            <div class="space-y-6 px-6 py-5">
                {{-- Primary Color --}}
                <div
                    x-data="awrelColorPicker('{{ $settings['primary_color'] ?? '#f59e0b' }}')"
                    class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-900/50"
                >
                    <label class="block text-sm font-medium text-gray-900 dark:text-white">
                        Primary Color
                    </label>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                        The main accent color used across the admin panel.
                    </p>
                    <div class="mt-3 flex items-center gap-4">
                        <div class="relative">
                            <input
                                type="color"
                                wire:model.live="settings.primary_color"
                                x-ref="colorInput"
                                x-on:input="updatePreview($refs.colorInput.value)"
                                class="h-10 w-10 cursor-pointer rounded-lg border border-gray-300 dark:border-gray-600"
                            >
                        </div>
                        <div class="flex-1">
                            <input
                                type="text"
                                wire:model.live="settings.primary_color"
                                class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                placeholder="#f59e0b"
                            />
                        </div>
                        <div class="flex h-10 items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 text-xs font-medium text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                            <span class="flex h-6 w-6 rounded" x-bind:style="'background-color: ' + previewColor"></span>
                            <span x-text="previewColor" class="font-mono tabular-nums"></span>
                        </div>
                    </div>
                    {{-- Color preset swatches --}}
                    <div class="mt-3 flex flex-wrap gap-2">
                        @php $presets = ['#f59e0b', '#ef4444', '#3b82f6', '#10b981', '#8b5cf6', '#ec4899', '#06b6d4', '#f97316', '#6366f1', '#14b8a6']; @endphp
                        @foreach($presets as $preset)
                            <button
                                type="button"
                                x-on:click="setColor('{{ $preset }}')"
                                class="h-7 w-7 rounded-full border-2 border-white shadow-sm transition-transform hover:scale-110 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:border-gray-800"
                                style="background-color: {{ $preset }}"
                                title="{{ $preset }}"
                            ></button>
                        @endforeach
                    </div>
                </div>

                {{-- Font Family --}}
                <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-900/50">
                    <label for="font_family" class="block text-sm font-medium text-gray-900 dark:text-white">
                        Font Family
                    </label>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                        Base font for the admin panel interface.
                    </p>
                    <div class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2">
                        @foreach(['Plus Jakarta Sans', 'Inter', 'Instrument Sans', 'system-ui'] as $fontOption)
                            <label class="relative flex cursor-pointer rounded-lg border-2 p-3 transition-all duration-150"
                                :class="$wire.settings.font_family === '{{ $fontOption }}'
                                    ? 'border-primary-500 bg-primary-50 dark:border-primary-500 dark:bg-primary-900/20'
                                    : 'border-gray-200 hover:border-gray-300 dark:border-gray-700 dark:hover:border-gray-600'">
                                <input type="radio" name="font_family" value="{{ $fontOption }}" wire:model.live="settings.font_family" class="sr-only">
                                <div class="w-full">
                                    <span class="block text-sm font-medium text-gray-900 dark:text-white" style="font-family: '{{ $fontOption }}', sans-serif">
                                        {{ $fontOption }}
                                    </span>
                                    <span class="mt-1 block text-xs text-gray-500 dark:text-gray-400" style="font-family: '{{ $fontOption }}', sans-serif">
                                        The quick brown fox jumps over the lazy dog.
                                    </span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Border Radius --}}
                <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-900/50">
                    <label for="border_radius" class="block text-sm font-medium text-gray-900 dark:text-white">
                        Border Radius
                    </label>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                        Controls the rounding of cards, buttons, and panels.
                    </p>
                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        @php $rValues = ['sm' => '0.375rem', 'md' => '0.5rem', 'lg' => '0.75rem', 'xl' => '1rem', '2xl' => '1.25rem']; @endphp
                        @foreach(['sm', 'md', 'lg', 'xl', '2xl'] as $radiusOption)
                            @php $rVal = $rValues[$radiusOption]; @endphp
                            <label class="relative flex cursor-pointer items-center gap-2 rounded-lg border-2 px-4 py-3 transition-all duration-150"
                                :class="$wire.settings.border_radius === '{{ $radiusOption }}'
                                    ? 'border-primary-500 bg-primary-50 dark:border-primary-500 dark:bg-primary-900/20'
                                    : 'border-gray-200 hover:border-gray-300 dark:border-gray-700 dark:hover:border-gray-600'"
                                style="border-radius: {{ $rVal }}">
                                <input type="radio" name="border_radius" value="{{ $radiusOption }}" wire:model.live="settings.border_radius" class="sr-only">
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $radiusOption }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Layout Card --}}
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700/50 dark:bg-gray-900">
            <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <div class="flex items-center gap-3">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary-50 text-primary-600 dark:bg-primary-900/50 dark:text-primary-400">
                        <x-heroicon-m-view-columns class="h-4 w-4" />
                    </span>
                    <div>
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Layout</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Sidebar width</p>
                    </div>
                </div>
            </div>
            <div class="space-y-6 px-6 py-5">
                {{-- Sidebar Width --}}
                <div
                    x-data="awrelRangeSlider({{ $settings['sidebar_width'] ?? 256 }})"
                    class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-900/50"
                >
                    <label for="sidebar_width" class="block text-sm font-medium text-gray-900 dark:text-white">
                        Sidebar Width
                    </label>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                        Adjust the sidebar width (180px — 400px).
                    </p>
                    <div class="mt-3 flex items-center gap-4">
                        <input
                            type="range"
                            id="sidebar_width"
                            min="180"
                            max="400"
                            step="4"
                            wire:model.live="settings.sidebar_width"
                            x-on:input="updateWidth($event.target.value)"
                            class="w-full accent-primary-500"
                        />
                        <span class="min-w-[3rem] text-right text-sm font-mono font-medium text-gray-700 dark:text-gray-300" x-text="currentWidth + 'px'"></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Save Button --}}
        <div class="flex justify-end">
            <button
                type="submit"
                class="inline-flex items-center gap-2 rounded-xl bg-primary-500 px-6 py-3 text-sm font-semibold text-white shadow-sm transition-all hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-950"
            >
                <x-heroicon-m-check class="h-4 w-4" />
                Save Settings
            </button>
        </div>
    </form>
</x-filament-panels::page>

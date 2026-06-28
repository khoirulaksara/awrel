<x-filament-panels::page>

    <form wire:submit="save" class="space-y-8">

        {{-- ================================================================ --}}
        {{--  1. General                                                      --}}
        {{-- ================================================================ --}}
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
            <div class="grid grid-cols-1 gap-4 px-6 py-5 sm:grid-cols-2">
                @include('awrel::filament.pages.settings._toggle', [
                    'id' => 'favicon_spinner',
                    'label' => 'Animated Favicon Spinner',
                    'description' => 'Spinner in browser tab on navigation',
                    'icon' => 'heroicon-m-globe-alt',
                ])
                @include('awrel::filament.pages.settings._toggle', [
                    'id' => 'sticky_table_actions',
                    'label' => 'Sticky Table Actions',
                    'description' => 'Pin actions column on table horizontal scroll',
                    'icon' => 'heroicon-m-table-cells',
                ])
            </div>
        </div>

        {{-- ================================================================ --}}
        {{--  2. Branding                                                     --}}
        {{-- ================================================================ --}}
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700/50 dark:bg-gray-900">
            <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <div class="flex items-center gap-3">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary-50 text-primary-600 dark:bg-primary-900/50 dark:text-primary-400">
                        <x-heroicon-m-photo class="h-4 w-4" />
                    </span>
                    <div>
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Branding</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Upload your brand logo</p>
                    </div>
                </div>
            </div>
            <div class="px-6 py-5">
                <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-900/50">
                    <label class="block text-sm font-medium text-gray-900 dark:text-white">Logo</label>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Upload your own logo to replace the default brand text.</p>
                    <div class="mt-3 space-y-3">
                        {{-- Preview of newly uploaded logo --}}
                        @if ($logo)
                            <div class="flex items-center gap-3 rounded-lg border border-gray-200 bg-white p-3 dark:border-gray-700 dark:bg-gray-800">
                                <img src="{{ $logo->temporaryUrl() }}" class="h-10 max-w-[180px] rounded object-contain" alt="Logo preview">
                                <span class="text-xs text-gray-500">Preview (not saved yet)</span>
                            </div>
                        @elseif ($settings['logo_path'] ?? false)
                            {{-- Currently saved logo --}}
                            <div class="flex items-center gap-3 rounded-lg border border-gray-200 bg-white p-3 dark:border-gray-700 dark:bg-gray-800">
                                <img src="{{ Storage::url($settings['logo_path']) }}" class="h-10 max-w-[180px] rounded object-contain" alt="Current logo">
                                <span class="text-xs text-gray-500">Current logo</span>
                                <button type="button" wire:click="removeLogo" class="ml-auto text-xs font-medium text-danger-600 hover:text-danger-500 dark:text-danger-400">
                                    Remove
                                </button>
                            </div>
                        @endif
                        <label class="relative flex cursor-pointer items-center gap-2 rounded-lg border-2 border-dashed border-gray-300 p-4 transition-colors hover:border-primary-400 dark:border-gray-600 dark:hover:border-primary-500">
                            <x-heroicon-m-cloud-arrow-up class="h-5 w-5 text-gray-400" />
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                @if ($settings['logo_path'] ?? false)
                                    Replace logo
                                @else
                                    Choose an image
                                @endif
                            </span>
                            <input type="file" wire:model="logo" accept="image/*" class="sr-only">
                        </label>
                        @error('logo') <span class="text-xs text-danger-600">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- ================================================================ --}}
        {{--  3. Theme Presets                                                 --}}
        {{-- ================================================================ --}}
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700/50 dark:bg-gray-900">
            <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <div class="flex items-center gap-3">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary-50 text-primary-600 dark:bg-primary-900/50 dark:text-primary-400">
                        <x-heroicon-m-swatch class="h-4 w-4" />
                    </span>
                    <div>
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Theme Presets</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Quickly apply a pre-configured theme</p>
                    </div>
                </div>
            </div>
            <div class="px-6 py-5">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach (config('awrel.presets', []) as $key => $preset)
                        <button type="button" wire:click="applyPreset('{{ $key }}')"
                            class="group relative flex flex-col items-start rounded-xl border-2 p-5 text-left transition-all duration-150 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-primary-500"
                            :class="$wire.settings.primary_color === '{{ $preset['primary_color'] }}' ? 'border-primary-500 bg-primary-50 dark:border-primary-500 dark:bg-primary-900/20' : 'border-gray-200 hover:border-gray-300 dark:border-gray-700 dark:hover:border-gray-600'">
                            <div class="mb-3 flex items-center gap-2">
                                <span class="inline-block h-5 w-5 rounded-full border-2 border-white shadow-sm dark:border-gray-700" style="background-color: {{ $preset['primary_color'] }}"></span>
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $preset['name'] }}</span>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $preset['description'] }}</p>
                            <div class="mt-2 flex flex-wrap gap-1.5 text-[10px] font-medium text-gray-400 dark:text-gray-500">
                                <span>{{ $preset['font_family'] }}</span>
                                <span>&middot;</span>
                                <span>rounded-{{ $preset['border_radius'] }}</span>
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ================================================================ --}}
        {{--  4. Appearance                                                    --}}
        {{-- ================================================================ --}}
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
                @include('awrel::filament.pages.settings._color-picker')
                @include('awrel::filament.pages.settings._font-family')
                @include('awrel::filament.pages.settings._border-radius')
            </div>
        </div>

        {{-- ================================================================ --}}
        {{--  5. Login Page                                                    --}}
        {{-- ================================================================ --}}
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700/50 dark:bg-gray-900">
            <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <div class="flex items-center gap-3">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary-50 text-primary-600 dark:bg-primary-900/50 dark:text-primary-400">
                        <x-heroicon-m-lock-closed class="h-4 w-4" />
                    </span>
                    <div>
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Login Page</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Customize the authentication page</p>
                    </div>
                </div>
            </div>
            <div class="space-y-6 px-6 py-5">

                {{-- Layout Style --}}
                <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-900/50">
                    <label class="block text-sm font-medium text-gray-900 dark:text-white">Login Layout</label>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Choose between centered card or split-screen layout.</p>
                    <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                        @foreach(['centered' => 'Centered', 'split' => 'Split Screen'] as $layoutVal => $layoutLabel)
                            <label class="relative flex cursor-pointer rounded-lg border-2 p-4 transition-all duration-150"
                                :class="$wire.settings.login_layout === '{{ $layoutVal }}'
                                    ? 'border-primary-500 bg-primary-50 dark:border-primary-500 dark:bg-primary-900/20'
                                    : 'border-gray-200 hover:border-gray-300 dark:border-gray-700 dark:hover:border-gray-600'">
                                <input type="radio" name="login_layout" value="{{ $layoutVal }}" wire:model.live="settings.login_layout" class="sr-only">
                                <div class="w-full text-center">
                                    @if ($layoutVal === 'centered')
                                        <div class="mx-auto mb-2 flex h-16 w-24 items-center justify-center rounded-lg border-2 border-gray-300 bg-white dark:border-gray-600 dark:bg-gray-800">
                                            <div class="h-8 w-16 rounded border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-900"></div>
                                        </div>
                                    @else
                                        <div class="mx-auto mb-2 flex h-16 w-24 overflow-hidden rounded-lg border-2 border-gray-300 dark:border-gray-600">
                                            <div class="w-1/2 bg-gray-800"></div>
                                            <div class="w-1/2 bg-white dark:bg-gray-900"></div>
                                        </div>
                                    @endif
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $layoutLabel }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Background Color --}}
                <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-900/50">
                    <label for="login_background_color" class="block text-sm font-medium text-gray-900 dark:text-white">Background Color</label>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Solid background color for the login page.</p>
                    <div class="mt-3 flex items-center gap-4">
                        <input type="color" wire:model.live="settings.login_background_color" class="h-10 w-10 cursor-pointer rounded-lg border border-gray-300 dark:border-gray-600">
                        <div class="flex-1">
                            <input type="text" wire:model.live="settings.login_background_color" class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" placeholder="#1e293b" />
                        </div>
                    </div>
                </div>

                {{-- Background Image --}}
                <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-900/50">
                    <label class="block text-sm font-medium text-gray-900 dark:text-white">Background Image</label>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">A background image that overlays the solid color.</p>
                    <div class="mt-3 space-y-3">
                        {{-- Preview of newly uploaded bg image --}}
                        @if ($loginBackgroundImage)
                            <div class="flex items-center gap-3 rounded-lg border border-gray-200 bg-white p-3 dark:border-gray-700 dark:bg-gray-800">
                                <img src="{{ $loginBackgroundImage->temporaryUrl() }}" class="h-16 w-32 rounded object-cover" alt="Background preview">
                                <span class="text-xs text-gray-500">Preview (not saved yet)</span>
                            </div>
                        @elseif ($settings['login_background_image'] ?? false)
                            {{-- Currently saved background --}}
                            <div class="flex items-center gap-3 rounded-lg border border-gray-200 bg-white p-3 dark:border-gray-700 dark:bg-gray-800">
                                <img src="{{ Storage::url($settings['login_background_image']) }}" class="h-16 w-32 rounded object-cover" alt="Current background">
                                <span class="text-xs text-gray-500">Current background</span>
                                <button type="button" wire:click="removeLoginBackground" class="ml-auto text-xs font-medium text-danger-600 hover:text-danger-500 dark:text-danger-400">
                                    Remove
                                </button>
                            </div>
                        @endif
                        <label class="relative flex cursor-pointer items-center gap-2 rounded-lg border-2 border-dashed border-gray-300 p-4 transition-colors hover:border-primary-400 dark:border-gray-600 dark:hover:border-primary-500">
                            <x-heroicon-m-cloud-arrow-up class="h-5 w-5 text-gray-400" />
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                @if ($settings['login_background_image'] ?? false)
                                    Replace background
                                @else
                                    Choose an image
                                @endif
                            </span>
                            <input type="file" wire:model="loginBackgroundImage" accept="image/*" class="sr-only">
                        </label>
                        @error('loginBackgroundImage') <span class="text-xs text-danger-600">{{ $message }}</span> @enderror
                    </div>
                </div>

            </div>
        </div>

        {{-- ================================================================ --}}
        {{--  6. Layout                                                        --}}
        {{-- ================================================================ --}}
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700/50 dark:bg-gray-900">
            <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <div class="flex items-center gap-3">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary-50 text-primary-600 dark:bg-primary-900/50 dark:text-primary-400">
                        <x-heroicon-m-view-columns class="h-4 w-4" />
                    </span>
                    <div>
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Layout</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Navigation style, boxed mode, sidebar position &amp; width</p>
                    </div>
                </div>
            </div>
            <div class="space-y-6 px-6 py-5">

                {{-- Navigation Style --}}
                <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-900/50">
                    <label class="block text-sm font-medium text-gray-900 dark:text-white">Navigation Style</label>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Switch between sidebar and top horizontal navigation.</p>
                    <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                        @foreach(['sidebar' => 'Sidebar', 'horizontal' => 'Top Navigation'] as $navVal => $navLabel)
                            <label class="relative flex cursor-pointer rounded-lg border-2 p-4 transition-all duration-150"
                                :class="$wire.settings.layout_variant === '{{ $navVal }}'
                                    ? 'border-primary-500 bg-primary-50 dark:border-primary-500 dark:bg-primary-900/20'
                                    : 'border-gray-200 hover:border-gray-300 dark:border-gray-700 dark:hover:border-gray-600'">
                                <input type="radio" name="layout_variant" value="{{ $navVal }}" wire:model.live="settings.layout_variant" class="sr-only">
                                <div class="w-full text-center">
                                    @if ($navVal === 'sidebar')
                                        <div class="mx-auto mb-2 flex h-16 w-24 rounded-lg border-2 border-gray-300 dark:border-gray-600">
                                            <div class="flex w-1/3 items-center justify-center rounded-l-md bg-gray-100 dark:bg-gray-800">
                                                <span class="block h-3 w-2 rounded bg-gray-400"></span>
                                            </div>
                                            <div class="w-2/3 bg-white dark:bg-gray-900"></div>
                                        </div>
                                    @else
                                        <div class="mx-auto mb-2 h-16 w-24 rounded-lg border-2 border-gray-300 dark:border-gray-600">
                                            <div class="h-1/3 rounded-t-md bg-gray-100 dark:bg-gray-800"></div>
                                            <div class="h-2/3 bg-white dark:bg-gray-900"></div>
                                        </div>
                                    @endif
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $navLabel }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Boxed Layout Toggle --}}
                @include('awrel::filament.pages.settings._toggle', [
                    'id' => 'boxed_layout',
                    'label' => 'Boxed Layout',
                    'description' => 'Constrain main content to a max-width container',
                    'icon' => 'heroicon-m-arrows-pointing-in',
                ])

                {{-- Sidebar Position --}}
                <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-900/50">
                    <label class="block text-sm font-medium text-gray-900 dark:text-white">Sidebar Position</label>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Place the sidebar on the left or right side.</p>
                    <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                        @foreach(['left' => 'Left', 'right' => 'Right'] as $posVal => $posLabel)
                            <label class="relative flex cursor-pointer rounded-lg border-2 p-4 transition-all duration-150"
                                :class="$wire.settings.sidebar_position === '{{ $posVal }}'
                                    ? 'border-primary-500 bg-primary-50 dark:border-primary-500 dark:bg-primary-900/20'
                                    : 'border-gray-200 hover:border-gray-300 dark:border-gray-700 dark:hover:border-gray-600'">
                                <input type="radio" name="sidebar_position" value="{{ $posVal }}" wire:model.live="settings.sidebar_position" class="sr-only">
                                <div class="w-full text-center">
                                    @if ($posVal === 'left')
                                        <div class="mx-auto mb-2 flex h-16 w-24 rounded-lg border-2 border-gray-300 dark:border-gray-600">
                                            <div class="flex w-1/3 items-center justify-center rounded-l-md bg-gray-100 dark:bg-gray-800">
                                                <span class="block h-3 w-2 rounded bg-gray-400"></span>
                                            </div>
                                            <div class="w-2/3 bg-white dark:bg-gray-900"></div>
                                        </div>
                                    @else
                                        <div class="mx-auto mb-2 flex h-16 w-24 rounded-lg border-2 border-gray-300 dark:border-gray-600">
                                            <div class="w-2/3 bg-white dark:bg-gray-900"></div>
                                            <div class="flex w-1/3 items-center justify-center rounded-r-md bg-gray-100 dark:bg-gray-800">
                                                <span class="block h-3 w-2 rounded bg-gray-400"></span>
                                            </div>
                                        </div>
                                    @endif
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $posLabel }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Sidebar Width --}}
                @include('awrel::filament.pages.settings._sidebar-width')

            </div>
        </div>

        {{-- ================================================================ --}}
        {{--  Save Button                                                      --}}
        {{-- ================================================================ --}}
        <div class="sticky bottom-0 z-10 -mx-4 -mb-4 rounded-b-2xl border-t border-gray-200 bg-white/80 px-4 py-4 backdrop-blur dark:border-gray-700/50 dark:bg-gray-900/80">
            <div class="flex items-center justify-end gap-3">
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                    <x-heroicon-m-check class="h-4 w-4" />
                    Save Settings
                </button>
            </div>
        </div>

    </form>

</x-filament-panels::page>

<div
    x-data="{
        primaryColor: @js($settings['primary_color'] ?? '#f59e0b'),
        init() {
            this.updateColors(this.primaryColor);
            this.$watch('primaryColor', (val) => {
                if (val) this.updateColors(val);
            });
            $wire.on('awrel-color-synced', (event) => {
                if (event.color && event.color !== this.primaryColor) {
                    this.primaryColor = event.color;
                    this.updateColors(event.color);
                }
            });
        },
        updateColors(hex) {
            if (! hex || hex === '') return;
            var rgb = this.hexToRgb(hex);
            if (! rgb) return;
            for (var i = 50; i <= 950; i += 50) {
                document.documentElement.style.setProperty('--color-primary-' + i, this.shadeRgb(rgb, i));
            }
        },
        hexToRgb(hex) {
            hex = hex.replace('#', '');
            if (hex.length === 3) hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
            if (hex.length !== 6) return null;
            var r = parseInt(hex.substring(0, 2), 16);
            var g = parseInt(hex.substring(2, 4), 16);
            var b = parseInt(hex.substring(4, 6), 16);
            if (isNaN(r) || isNaN(g) || isNaN(b)) return null;
            return r + ' ' + g + ' ' + b;
        },
        shadeRgb(rgb, shade) {
            var parts = rgb.split(' ');
            var r = parseInt(parts[0]), g = parseInt(parts[1]), b = parseInt(parts[2]);
            var ratio = shade / 500;
            if (ratio <= 1) {
                var t = 1 - ratio;
                return Math.round(r + (255 - r) * t) + ' ' + Math.round(g + (255 - g) * t) + ' ' + Math.round(b + (255 - b) * t);
            } else {
                var t = (shade - 500) / 450;
                return Math.round(r * (1 - t)) + ' ' + Math.round(g * (1 - t)) + ' ' + Math.round(b * (1 - t));
            }
        },
        setColor(hex) {
            if (! hex || hex === '') return;
            this.primaryColor = hex;
            this.updateColors(hex);
            $wire.set('settings.primary_color', hex);
        }
    }"
    class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-900/50"
>
    <label class="block text-sm font-medium text-gray-900 dark:text-white">Primary Color</label>
    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">The main accent color used across the admin panel.</p>
    <div class="mt-3 flex items-center gap-4">
        <input
            type="color"
            x-bind:value="primaryColor"
            x-on:input="setColor($event.target.value)"
            class="h-10 w-10 cursor-pointer rounded-lg border border-gray-300 dark:border-gray-600"
        >
        <div class="flex-1">
            <input
                type="text"
                x-model="primaryColor"
                x-on:input.debounce.300ms="setColor(primaryColor)"
                class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 font-mono"
                placeholder="#f59e0b"
            />
        </div>
        <div class="flex h-10 items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 text-xs font-medium text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
            <span class="flex h-6 w-6 rounded" x-bind:style="'background-color: ' + primaryColor"></span>
            <span x-text="primaryColor" class="font-mono tabular-nums"></span>
        </div>
    </div>

    @php $presets = ['#f59e0b', '#ef4444', '#3b82f6', '#10b981', '#8b5cf6', '#ec4899', '#06b6d4', '#f97316', '#6366f1', '#14b8a6']; @endphp
    <div class="mt-3 flex flex-wrap gap-2">
        @foreach($presets as $preset)
            <button type="button" x-on:click="setColor('{{ $preset }}')" class="h-7 w-7 rounded-full border-2 border-white shadow-sm transition-transform hover:scale-110 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:border-gray-800" style="background-color: {{ $preset }}" title="{{ $preset }}"></button>
        @endforeach
    </div>
</div>

<div class="space-y-3">
    <div>
        {{ $this->colorPicker }}
    </div>

    @php $presets = ['#f59e0b', '#ef4444', '#3b82f6', '#10b981', '#8b5cf6', '#ec4899', '#06b6d4', '#f97316', '#6366f1', '#14b8a6']; @endphp
    <div class="flex flex-wrap gap-2">
        @foreach($presets as $preset)
            <button 
                type="button" 
                x-on:click="$wire.set('settings.primary_color', '{{ $preset }}')" 
                class="h-7 w-7 rounded-full border-2 border-white shadow-sm transition-transform hover:scale-110 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:border-gray-800" 
                style="background-color: {{ $preset }}" 
                title="{{ $preset }}">
            </button>
        @endforeach
    </div>
</div>

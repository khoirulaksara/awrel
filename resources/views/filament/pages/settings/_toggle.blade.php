@props(['id', 'label', 'description', 'icon'])
<div class="flex items-center justify-between rounded-xl border border-gray-100 bg-gray-50/50 px-4 py-3 dark:border-gray-800 dark:bg-gray-900/50">
    <div class="flex items-start gap-3">
        <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-white text-gray-500 shadow-sm dark:bg-gray-800 dark:text-gray-400">
            <x-dynamic-component :component="$icon" class="h-3.5 w-3.5" />
        </span>
        <div>
            <label for="{{ $id }}" class="text-sm font-medium text-gray-900 dark:text-white">{{ $label }}</label>
            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $description }}</p>
        </div>
    </div>
    <label class="relative inline-flex cursor-pointer items-center">
        <input type="checkbox" id="{{ $id }}" wire:model.live="settings.{{ $id }}" class="peer sr-only">
        <div class="h-5 w-9 rounded-full bg-gray-200 after:absolute after:start-[2px] after:top-[2px] after:h-4 after:w-4 after:rounded-full after:bg-white after:transition-all after:content-[''] peer-checked:bg-primary-500 peer-checked:after:translate-x-full dark:bg-gray-600"></div>
    </label>
</div>

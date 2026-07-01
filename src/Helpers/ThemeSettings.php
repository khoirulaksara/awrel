<?php

namespace Khoirulaksara\Awrel\Helpers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Khoirulaksara\Awrel\Models\AwrelSetting;

/**
 * Central helper for reading Awrel theme settings.
 *
 * Reads from the database settings table with a fallback
 * to the config defaults. Results are cached in memory
 * for the duration of the request.
 */
class ThemeSettings
{
    private static ?array $settings = null;

    /**
     * Override settings in-memory for the current request.
     */
    public static function setOverride(array $settings): void
    {
        static::$settings = array_merge(static::all(), $settings);
    }

    /**
     * Get all settings (merged DB over config defaults).
     */
    public static function all(): array
    {
        if (static::$settings !== null) {
            return static::$settings;
        }

        $defaults = config('awrel', []);

        return static::$settings = Cache::remember('awrel_settings', 300, function () use ($defaults) {
            $db = [];

            try {
                $record = AwrelSetting::record();
                $db = $record->settings ?? [];
            } catch (\Throwable) {
                // Table may not exist yet (fresh install, first migration)
            }

            return array_merge($defaults, $db);
        });
    }

    /**
     * Get a single setting value.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return static::all()[$key] ?? $default;
    }

    /**
     * Save settings to the database.
     */
    public static function save(array $settings): void
    {
        $record = AwrelSetting::record();
        $merged = array_merge($record->settings ?? [], $settings);
        $record->update(['settings' => $merged]);

        // Clear the in-memory cache
        static::$settings = null;
    }

    /**
     * Remove a single key from settings.
     */
    public static function forget(string $key): void
    {
        $record = AwrelSetting::record();
        $current = $record->settings ?? [];
        unset($current[$key]);
        $record->update(['settings' => $current]);
        static::$settings = null;
    }

    /**
     * Flush the in-memory cache (useful for testing).
     */
    public static function flush(): void
    {
        static::$settings = null;
    }

    /**
     * Get all defined presets from config.
     */
    public static function presets(): array
    {
        return config('awrel.presets', []);
    }

    /*
    |--------------------------------------------------------------------------
    | Convenience Getters — General
    |--------------------------------------------------------------------------
    */

    public static function isFaviconSpinnerEnabled(): bool
    {
        return (bool) static::get('favicon_spinner', false);
    }

    public static function isStickyTableActionsEnabled(): bool
    {
        return (bool) static::get('sticky_table_actions', false);
    }

    public static function isLoadingBarEnabled(): bool
    {
        return (bool) static::get('loading_bar', true);
    }

    public static function isPageTransitionEnabled(): bool
    {
        return (bool) static::get('page_transition', true);
    }

    public static function isButtonSubmitLoadingEnabled(): bool
    {
        return (bool) static::get('button_submit_loading', true);
    }

    public static function isUnsavedChangesGuardEnabled(): bool
    {
        return (bool) static::get('unsaved_changes_guard', true);
    }

    /*
    |--------------------------------------------------------------------------
    | Convenience Getters — Appearance
    |--------------------------------------------------------------------------
    */

    public static function primaryColor(): string
    {
        return static::get('primary_color', '#f59e0b');
    }

    public static function fontFamily(): string
    {
        return static::get('font_family', 'Plus Jakarta Sans');
    }

    public static function borderradius(): string
    {
        return static::get('border_radius', '2xl');
    }

    /*
    |--------------------------------------------------------------------------
    | Convenience Getters — Branding
    |--------------------------------------------------------------------------
    */

    public static function logoPath(): ?string
    {
        $path = static::get('logo_path');

        return is_string($path) && $path !== '' ? $path : null;
    }

    public static function logoUrl(): ?string
    {
        $path = static::logoPath();

        if (! $path) {
            return null;
        }

        try {
            return Storage::disk('public')->url($path);
        } catch (\Throwable) {
            return null;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Convenience Getters — Login Page
    |--------------------------------------------------------------------------
    */

    public static function loginLayout(): string
    {
        return static::get('login_layout', 'centered');
    }

    public static function loginBackgroundColor(): ?string
    {
        $color = static::get('login_background_color');

        return is_string($color) && $color !== '' ? $color : null;
    }

    public static function loginBackgroundImagePath(): ?string
    {
        $path = static::get('login_background_image');

        return is_string($path) && $path !== '' ? $path : null;
    }

    public static function loginBackgroundImageUrl(): ?string
    {
        $path = static::loginBackgroundImagePath();

        if (! $path) {
            return null;
        }

        try {
            return Storage::disk('public')->url($path);
        } catch (\Throwable) {
            return null;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Convenience Getters — Layout Variants
    |--------------------------------------------------------------------------
    */

    public static function layoutVariant(): string
    {
        return static::get('layout_variant', 'sidebar');
    }

    public static function isHorizontalNavigation(): bool
    {
        return static::layoutVariant() === 'horizontal';
    }

    public static function isBoxedLayout(): bool
    {
        return (bool) static::get('boxed_layout', false);
    }

    public static function sidebarWidth(): int
    {
        return (int) static::get('sidebar_width', 256);
    }

    public static function sidebarPosition(): string
    {
        return static::get('sidebar_position', 'left');
    }

    public static function isSidebarRight(): bool
    {
        return static::sidebarPosition() === 'right';
    }
}

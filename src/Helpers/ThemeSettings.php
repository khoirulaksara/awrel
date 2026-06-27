<?php

namespace Khoirulaksara\Awrel\Helpers;

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
     * Get all settings (merged DB over config defaults).
     */
    public static function all(): array
    {
        if (static::$settings !== null) {
            return static::$settings;
        }

        $defaults = config('awrel', []);
        $db = [];

        try {
            $record = AwrelSetting::record();
            $db = $record->settings ?? [];
        } catch (\Throwable) {
            // Table may not exist yet (fresh install, first migration)
        }

        return static::$settings = array_merge($defaults, $db);
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
     * Flush the in-memory cache (useful for testing).
     */
    public static function flush(): void
    {
        static::$settings = null;
    }

    /*
    |--------------------------------------------------------------------------
    | Convenience Getters
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

    public static function sidebarWidth(): int
    {
        return (int) static::get('sidebar_width', 256);
    }
}

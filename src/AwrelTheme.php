<?php

namespace Khoirulaksara\Awrel;

/**
 * @deprecated Configuration is now managed via ThemeSettings helper
 *             and stored in the database / config. This class is kept
 *             for backward compatibility.
 */
class AwrelTheme
{
    protected static ?AwrelPlugin $plugin = null;

    public static function setPlugin(?AwrelPlugin $plugin): void
    {
        static::$plugin = $plugin;
    }

    public static function getPlugin(): ?AwrelPlugin
    {
        return static::$plugin;
    }
}

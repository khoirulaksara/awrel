<?php

namespace Khoirulaksara\Awrel;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Khoirulaksara\Awrel\Helpers\ThemeSettings;
use Khoirulaksara\Awrel\Services\HookRegistrar;

class AwrelPlugin implements Plugin
{
    /**
     * When null, the DB settings control the feature.
     * Set to true/false via fluent method to override DB.
     */
    protected ?bool $faviconSpinnerEnabled = null;

    protected ?bool $stickyTableActionsEnabled = null;

    public function getId(): string
    {
        return 'awrel-theme';
    }

    public function faviconSpinner(bool $condition = true): static
    {
        $this->faviconSpinnerEnabled = $condition;

        return $this;
    }

    public function isFaviconSpinnerEnabled(): bool
    {
        return $this->faviconSpinnerEnabled ??
            ThemeSettings::isFaviconSpinnerEnabled();
    }

    public function stickyTableActions(bool $condition = true): static
    {
        $this->stickyTableActionsEnabled = $condition;

        return $this;
    }

    public function isStickyTableActionsEnabled(): bool
    {
        return $this->stickyTableActionsEnabled ??
            ThemeSettings::isStickyTableActionsEnabled();
    }

    public function register(Panel $panel): void
    {
        // Primary color is handled dynamically via CSS vars in render hooks.
    }

    public function boot(Panel $panel): void
    {
        new HookRegistrar($this)->registerHooks();
    }

    public static function make(): static
    {
        return app(static::class);
    }
}

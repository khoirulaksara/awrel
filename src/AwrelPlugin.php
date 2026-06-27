<?php

namespace Khoirulaksara\Awrel;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Khoirulaksara\Awrel\Helpers\ThemeSettings;
use Khoirulaksara\Awrel\Services\HookRegistrar;

class AwrelPlugin implements Plugin
{
    protected bool $faviconSpinnerEnabled = false;

    protected bool $stickyTableActionsEnabled = false;

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
        if ($this->faviconSpinnerEnabled) {
            return true;
        }

        return ThemeSettings::isFaviconSpinnerEnabled();
    }

    public function stickyTableActions(bool $condition = true): static
    {
        $this->stickyTableActionsEnabled = $condition;

        return $this;
    }

    public function isStickyTableActionsEnabled(): bool
    {
        if ($this->stickyTableActionsEnabled) {
            return true;
        }

        return ThemeSettings::isStickyTableActionsEnabled();
    }

    public function register(Panel $panel): void
    {
        $panel->colors([
            'primary' => ThemeSettings::primaryColor(),
        ]);
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

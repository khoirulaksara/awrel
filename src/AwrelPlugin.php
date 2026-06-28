<?php

namespace Khoirulaksara\Awrel;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Khoirulaksara\Awrel\Filament\Pages\ThemeSettingsPage;
use Khoirulaksara\Awrel\Helpers\ThemeSettings;
use Khoirulaksara\Awrel\Services\HookRegistrar;
use UnitEnum; // For navigationGroup type hint

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
        return "awrel-theme";
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
        $panel->pages([ThemeSettingsPage::class]);

        // Apply primary color from DB settings
        try {
            $primaryColor = ThemeSettings::primaryColor();
            if ($primaryColor) {
                $panel->colors([
                    "primary" => Color::hex($primaryColor),
                ]);
            }
        } catch (\Throwable) {
            // DB not available yet (fresh install)
        }

        // Apply sidebar width from DB settings
        try {
            $sidebarWidth = ThemeSettings::sidebarWidth();
            $panel->sidebarWidth($sidebarWidth . "px");
        } catch (\Throwable) {
            // DB not available yet (fresh install)
        }

        // Apply horizontal navigation from DB settings
        try {
            if (ThemeSettings::isHorizontalNavigation()) {
                $panel->topNavigation(true);
            }
        } catch (\Throwable) {
            // DB not available yet (fresh install)
        }
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

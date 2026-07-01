<?php

namespace Khoirulaksara\Awrel;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Colors\Color;
use Khoirulaksara\Awrel\Filament\Pages\ThemeSettingsPage;
use Khoirulaksara\Awrel\Helpers\ThemeSettings;
use Khoirulaksara\Awrel\Services\HookRegistrar;

// For navigationGroup type hint

class AwrelPlugin implements Plugin
{
    /**
     * When null, the DB settings control the feature.
     * Set to true/false via fluent method to override DB.
     */
    protected ?bool $faviconSpinnerEnabled = null;

    protected ?bool $stickyTableActionsEnabled = null;

    protected ?bool $loadingBarEnabled = null;

    protected ?bool $pageTransitionEnabled = null;

    protected ?bool $buttonSubmitLoadingEnabled = null;

    protected ?bool $unsavedChangesGuardEnabled = null;

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

    public function loadingBar(bool $condition = true): static
    {
        $this->loadingBarEnabled = $condition;

        return $this;
    }

    public function isLoadingBarEnabled(): bool
    {
        return $this->loadingBarEnabled ??
            ThemeSettings::isLoadingBarEnabled();
    }

    public function pageTransition(bool $condition = true): static
    {
        $this->pageTransitionEnabled = $condition;

        return $this;
    }

    public function isPageTransitionEnabled(): bool
    {
        return $this->pageTransitionEnabled ??
            ThemeSettings::isPageTransitionEnabled();
    }

    public function buttonSubmitLoading(bool $condition = true): static
    {
        $this->buttonSubmitLoadingEnabled = $condition;

        return $this;
    }

    public function isButtonSubmitLoadingEnabled(): bool
    {
        return $this->buttonSubmitLoadingEnabled ??
            ThemeSettings::isButtonSubmitLoadingEnabled();
    }

    public function unsavedChangesGuard(bool $condition = true): static
    {
        $this->unsavedChangesGuardEnabled = $condition;

        return $this;
    }

    public function isUnsavedChangesGuardEnabled(): bool
    {
        return $this->unsavedChangesGuardEnabled ??
            ThemeSettings::isUnsavedChangesGuardEnabled();
    }

    public function getPages(): array
    {
        return [ThemeSettingsPage::class];
    }

    public function register(Panel $panel): void
    {
        $panel->pages($this->getPages());

        // Apply primary color from DB settings
        try {
            $primaryColor = ThemeSettings::primaryColor();
            if ($primaryColor) {
                $panel->colors([
                    'primary' => Color::hex($primaryColor),
                ]);
            }
        } catch (\Throwable) {
            // DB not available yet (fresh install)
        }

        // Apply sidebar width from DB settings
        try {
            $sidebarWidth = ThemeSettings::sidebarWidth();
            $panel->sidebarWidth($sidebarWidth.'px');
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

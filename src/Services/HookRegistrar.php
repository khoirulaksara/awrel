<?php

namespace Khoirulaksara\Awrel\Services;

use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Khoirulaksara\Awrel\AwrelPlugin;
use Khoirulaksara\Awrel\Helpers\ThemeSettings;

class HookRegistrar
{
    public function __construct(protected AwrelPlugin $awrelPlugin) {}

    public function registerHooks(): void
    {
        $this->registerDynamicStyles();

        if ($this->awrelPlugin->isFaviconSpinnerEnabled()) {
            $this->registerFaviconSpinner();
        }

        if ($this->awrelPlugin->isStickyTableActionsEnabled()) {
            $this->registerStickyTableActions();
        }
    }

    private function registerDynamicStyles(): void
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_START,
            function (): string {
                $font = e(ThemeSettings::fontFamily());
                $radius = e(ThemeSettings::borderradius());
                $sidebarWidth = (int) ThemeSettings::sidebarWidth();

                return <<<HTML
                <style>
                    :root {
                        --awrel-font-family: "{$font}", ui-sans-serif, system-ui, sans-serif;
                        --awrel-border-radius: {$radius};
                        --awrel-sidebar-width: {$sidebarWidth}px;
                    }
                </style>
                HTML;
            },
        );
    }

    private function registerFaviconSpinner(): void
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_START,
            fn (): string => '<script>document.documentElement.dataset.awrelFaviconSpinner = "";</script>',
        );
    }

    private function registerStickyTableActions(): void
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_START,
            fn (): string => '<script>document.documentElement.dataset.awrelStickyActions = "";</script>',
        );
    }
}

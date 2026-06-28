<?php

namespace Khoirulaksara\Awrel\Services;

use Filament\Support\Colors\Color;
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
        $this->registerJavascript();

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
                $radiusCssValue = match ($radius) {
                    'sm' => '0.375rem',
                    'md' => '0.5rem',
                    'lg' => '0.75rem',
                    'xl' => '1rem',
                    '2xl' => '1.25rem',
                    default => '1.25rem',
                };
                $sidebarWidth = (int) ThemeSettings::sidebarWidth();
                $primaryHex = ThemeSettings::primaryColor();
                $logoUrl = ThemeSettings::logoUrl();

                // Generate all primary color shades dynamically
                $primaryCss = '';
                try {
                    $shades = Color::hex($primaryHex);
                    foreach ($shades as $shade => $rgb) {
                        if (is_array($rgb) && count($rgb) === 3) {
                            $primaryCss .= "    --color-primary-{$shade}: {$rgb[0]} {$rgb[1]} {$rgb[2]}; \n";
                        }
                    }
                } catch (\Throwable) {
                    $primaryCss = <<<'CSS'
                        --color-primary-50: 255 248 240;
                        --color-primary-100: 255 236 213;
                        --color-primary-200: 255 219 170;
                        --color-primary-300: 255 204 128;
                        --color-primary-400: 255 187 85;
                        --color-primary-500: 255 171 43;
                        --color-primary-600: 204 136 34;
                        --color-primary-700: 153 102 25;
                        --color-primary-800: 102 68 17;
                        --color-primary-900: 51 34 8;
                        --color-primary-950: 26 17 4;
                    CSS;
                }

                $logoStyles = '';
                if ($logoUrl) {
                    $safeLogoUrl = e($logoUrl);
                    $logoStyles = <<<CSS

                    /* Custom logo — replaces default \"Laravel\" text */
                    .fi-logo {
                        background: url({$safeLogoUrl}) no-repeat center;
                        background-size: contain;
                        color: transparent !important;
                        overflow: hidden;
                        width: 130px;
                        height: 2rem;
                    }
                    .fi-logo * {
                        visibility: hidden;
                    }

                    .fi-sidebar-header .fi-logo {
                        width: 100%;
                        max-width: 180px;
                        height: 2.5rem;
                    }
                    CSS;
                }

                return <<<HTML
                <style>
                    :root {
                        --awrel-font-family: "{$font}", ui-sans-serif, system-ui, sans-serif;
                        --awrel-sidebar-width: {$sidebarWidth}px;
                        --awrel-border-radius: {$radiusCssValue};
                {$primaryCss}
                    }

                    /* Apply dynamic border radius to all relevant elements */
                    .fi-section,
                    .fi-wi-stats-overview-stat,
                    .fi-ta-ctn,
                    .fi-dropdown-panel,
                    .fi-modal-window,
                    .fi-input,
                    .fi-btn,
                    .badge,
                    .fi-section-header,
                    .awrel-table-skeleton-header,
                    .awrel-skeleton-card {
                        border-radius: var(--awrel-border-radius);
                    }
                    {$logoStyles}
                </style>
                HTML;
            },
        );
    }

    /**
     * Inject the Awrel JavaScript bundle via a render hook.
     *
     * Uses a render hook instead of FilamentAsset/Vite so the JS loads
     * reliably without requiring a Vite build step. The script is loaded
     * with defer so it executes after DOM parsing but the Alpine component
     * registrations handle both pre-init and post-init scenarios.
     */
    private function registerJavascript(): void
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_START,
            function (): string {
                $path = asset('vendor/awrel/awrel.js');

                return '<script defer src="'.$path.'"></script>';
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

    /**
     * Convert a hex color to an RGB string suitable for Tailwind color vars.
     */
    private function hexToRgb(string $hex): string
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        return "{$r} {$g} {$b}";
    }
}

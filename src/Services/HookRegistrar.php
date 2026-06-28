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
            PanelsRenderHook::HEAD_END,
            function (): string {
                $font = e(ThemeSettings::fontFamily());
                $radius = e(ThemeSettings::borderradius());
                $radiusCssValue = match ($radius) {
                    "sm" => "0.375rem",
                    "md" => "0.5rem",
                    "lg" => "0.75rem",
                    "xl" => "1rem",
                    "2xl" => "1.25rem",
                    default => "1.25rem",
                };
                $sidebarWidth = (int) ThemeSettings::sidebarWidth();
                $primaryHex = ThemeSettings::primaryColor();
                $logoUrl = ThemeSettings::logoUrl();
                $loginLayout = ThemeSettings::loginLayout();
                $loginBgColor = ThemeSettings::loginBackgroundColor();
                $loginBgImageUrl = ThemeSettings::loginBackgroundImageUrl();
                $isBoxed = ThemeSettings::isBoxedLayout();
                $isSidebarRight = ThemeSettings::isSidebarRight();

                // Generate all primary color shades dynamically
                $primaryCss = "";
                try {
                    $shades = Color::hex($primaryHex);
                    foreach ($shades as $shade => $rgb) {
                        if (is_array($rgb) && count($rgb) === 3) {
                            $primaryCss .= "    --color-primary-{$shade}: {$rgb[0]} {$rgb[1]} {$rgb[2]}; \n";
                            $primaryCss .= "    --primary-{$shade}: {$rgb[0]} {$rgb[1]} {$rgb[2]}; \n";
                        }
                    }
                } catch (\Throwable) {
                    $primaryCss = <<<'CSS'
                        --color-primary-50: 255 248 240;
                        --primary-50: 255 248 240;
                        --color-primary-100: 255 236 213;
                        --primary-100: 255 236 213;
                        --color-primary-200: 255 219 170;
                        --primary-200: 255 219 170;
                        --color-primary-300: 255 204 128;
                        --primary-300: 255 204 128;
                        --color-primary-400: 255 187 85;
                        --primary-400: 255 187 85;
                        --color-primary-500: 255 171 43;
                        --primary-500: 255 171 43;
                        --color-primary-600: 204 136 34;
                        --primary-600: 204 136 34;
                        --color-primary-700: 153 102 25;
                        --primary-700: 153 102 25;
                        --color-primary-800: 102 68 17;
                        --primary-800: 102 68 17;
                        --color-primary-900: 51 34 8;
                        --primary-900: 51 34 8;
                        --color-primary-950: 26 17 4;
                        --primary-950: 26 17 4;
                    CSS;
                }

                // ── Logo CSS ──
                $logoStyles = "";
                if ($logoUrl) {
                    $safeLogoUrl = e($logoUrl);
                    $logoStyles = <<<CSS

                    .fi-logo {
                        background: url({$safeLogoUrl}) no-repeat center !important;
                        background-size: contain !important;
                        color: transparent !important;
                        text-indent: -9999px !important;
                        overflow: hidden !important;
                        width: 130px !important;
                        height: 2rem !important;
                    }
                    .fi-logo * {
                        display: none !important;
                        visibility: hidden !important;
                    }
                    .fi-sidebar-header .fi-logo {
                        width: 100% !important;
                        max-width: 180px !important;
                        height: 2.5rem !important;
                    }
                    
                    /* Auto-mask topbar logo to white in both light and dark modes */
                    .fi-topbar .fi-logo {
                        -webkit-mask: url({$safeLogoUrl}) no-repeat center !important;
                        mask: url({$safeLogoUrl}) no-repeat center !important;
                        -webkit-mask-size: contain !important;
                        mask-size: contain !important;
                        background-image: none !important;
                        background: white !important;
                        background-color: white !important;
                    }
                    CSS;
                }

                // ── Login Page CSS ──
                $loginStyles = "";
                if (
                    $loginLayout === "split" ||
                    $loginBgColor ||
                    $loginBgImageUrl
                ) {
                    $loginStyles .= "\n\n/* ── Custom Login Page ── */\n";

                    if ($loginBgImageUrl) {
                        $safeBg = e($loginBgImageUrl);
                        $loginStyles .= <<<CSS
                        .fi-simple-layout {
                            background-image: url({$safeBg}) !important;
                            background-size: cover !important;
                            background-position: center !important;
                        }
                        .fi-simple-layout .fi-simple-main {
                            background: rgba(255, 255, 255, 0.9) !important;
                            backdrop-filter: blur(12px) !important;
                        }
                        .dark .fi-simple-layout .fi-simple-main {
                            background: rgba(17, 24, 39, 0.9) !important;
                        }
                        CSS;
                    }

                    if ($loginBgColor) {
                        $safeColor = e($loginBgColor);
                        $loginStyles .= <<<CSS
                        .fi-simple-layout {
                            background-color: {$safeColor} !important;
                        }
                        CSS;
                    }

                    if ($loginLayout === "split") {
                        $loginStyles .= <<<'CSS'
                        @media (min-width: 1024px) {
                            .fi-simple-layout {
                                flex-direction: row !important;
                                align-items: stretch !important;
                                min-height: 100vh !important;
                            }
                            .fi-simple-layout::before {
                                content: '' !important;
                                display: block !important;
                                flex: 1.2 !important;
                                background-image: inherit !important;
                                background-color: inherit !important;
                                background-size: cover !important;
                                background-position: center !important;
                                border-right: 1px solid rgba(229, 231, 235, 0.8) !important;
                            }
                            .dark .fi-simple-layout::before {
                                border-right-color: rgba(55, 65, 81, 0.4) !important;
                            }
                            .fi-simple-main-ctn {
                                flex: 1 !important;
                                background: white !important;
                                display: flex !important;
                                align-items: center !important;
                                justify-content: center !important;
                                padding: 2rem !important;
                            }
                            .dark .fi-simple-main-ctn {
                                background: var(--color-gray-950) !important;
                            }
                            .fi-simple-main {
                                max-width: 28rem !important;
                                width: 100% !important;
                                box-shadow: none !important;
                                ring: 0 !important;
                                border: none !important;
                                background: transparent !important;
                                margin: 0 !important;
                            }
                        }
                        CSS;
                    }
                }

                // ── Boxed Layout CSS ──
                $boxedStyles = "";
                if ($isBoxed) {
                    $boxedStyles = <<<'CSS'

                    /* ── Boxed Layout ── */
                    .fi-main {
                        max-width: 80rem !important;
                        margin-left: auto !important;
                        margin-right: auto !important;
                    }
                    .fi-main-ctn {
                        max-width: 100%;
                        overflow-x: hidden;
                    }
                    CSS;
                }

                // ── Sidebar Position CSS ──
                $sidebarStyles = "";
                if ($isSidebarRight) {
                    $sidebarStyles = <<<'CSS'

                    /* ── Sidebar Right ── */
                    .fi-layout > *:has(.fi-sidebar) {
                        order: 1 !important;
                    }
                    .fi-sidebar {
                        left: auto !important;
                        right: 0 !important;
                    }
                    .fi-sidebar.fi-sidebar-open {
                        border-left: 1px solid var(--color-gray-200) !important;
                        border-right: none !important;
                    }
                    .dark .fi-sidebar.fi-sidebar-open {
                        border-left-color: var(--color-gray-800) !important;
                        border-right: none !important;
                    }
                    .fi-main-ctn {
                        order: 0 !important;
                    }
                    .fi-layout {
                        display: flex !important;
                        flex-direction: row !important;
                    }
                    
                    /* Move floating collapse button to the right side border when open */
                    @media (min-width: 1024px) {
                        .fi-main-ctn-sidebar-open .fi-topbar-collapse-sidebar-btn-ctn {
                            left: auto !important;
                            right: 0.75rem !important;
                            transform: translateY(-50%) !important;
                        }
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
                    {$loginStyles}
                    {$boxedStyles}
                    {$sidebarStyles}
                </style>
                HTML;
            },
        );
    }

    private function registerJavascript(): void
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_START,
            function (): string {
                $path = asset("vendor/awrel/awrel.js");

                return '<script src="' . $path . '" defer></script>';
            },
        );
    }

    private function registerFaviconSpinner(): void
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_START,
            fn(): string => '<script>document.documentElement.dataset.awrelFaviconSpinner = "";</script>',
        );
    }

    private function registerStickyTableActions(): void
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_START,
            fn(): string => '<script>document.documentElement.dataset.awrelStickyActions = "";</script>',
        );
    }

    private function hexToRgb(string $hex): string
    {
        $hex = ltrim($hex, "#");
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        return "{$r} {$g} {$b}";
    }
}

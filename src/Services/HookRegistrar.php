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

        foreach ($this->featureFlags() as $dataset => $isEnabledMethod) {
            if ($this->awrelPlugin->{$isEnabledMethod}()) {
                $this->registerFeatureFlag($dataset);
            }
        }
    }

    /**
     * Maps an opt-in dataset flag (read by resources/js/index.js) to the
     * AwrelPlugin::is*Enabled() method that decides whether the feature is on.
     *
     * @return array<string, string>
     */
    private function featureFlags(): array
    {
        return [
            'awrelFaviconSpinner' => 'isFaviconSpinnerEnabled',
            'awrelLoadingBar' => 'isLoadingBarEnabled',
            'awrelPageTransition' => 'isPageTransitionEnabled',
            'awrelButtonSubmitLoading' => 'isButtonSubmitLoadingEnabled',
            'awrelUnsavedChangesGuard' => 'isUnsavedChangesGuardEnabled',
            'awrelStickyActions' => 'isStickyTableActionsEnabled',
        ];
    }

    private function registerFeatureFlag(string $dataset): void
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_START,
            fn (): string => "<script>document.documentElement.dataset.{$dataset} = \"\";</script>",
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DYNAMIC STYLES (composed from focused segment methods; output is
    // byte-for-byte identical to the previous monolithic version).
    // ─────────────────────────────────────────────────────────────────────────
    private function registerDynamicStyles(): void
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_END,
            fn (): string => $this->renderHeadStyles(),
        );
    }

    private function renderHeadStyles(): string
    {
        $font = e(ThemeSettings::fontFamily());
        $radiusCss = $this->radiusCssValue(ThemeSettings::borderradius());
        $sidebarWidth = (int) ThemeSettings::sidebarWidth();

        return $this->fontLink($font)
            .$this->wrapStyle(
                $this->rootVariables($font, $radiusCss, $sidebarWidth)
                .$this->globalFontFace($font)
                .$this->borderRadiusRules()
                .$this->logoCss()
                .$this->loginCss()
                .$this->boxedLayoutCss()
                .$this->sidebarPositionCss(),
            );
    }

    private function wrapStyle(string $rules): string
    {
        return "<style>{$rules}</style>";
    }

    private function radiusCssValue(string $radius): string
    {
        return match ($radius) {
            'sm' => '0.375rem',
            'md' => '0.5rem',
            'lg' => '0.75rem',
            'xl' => '1rem',
            '2xl' => '1.25rem',
            default => '1.25rem',
        };
    }

    private function fontLink(string $font): string
    {
        if (
            ! $font ||
            in_array(strtolower($font), [
                'sans-serif',
                'serif',
                'monospace',
                'system-ui',
            ])
        ) {
            return '';
        }

        $fontUrlName = str_replace(' ', '+', $font);

        return <<<HTML

                    <link rel="preconnect" href="https://fonts.googleapis.com">
                    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
                    <link href="https://fonts.googleapis.com/css2?family={$fontUrlName}:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
        HTML;
    }

    private function rootVariables(
        string $font,
        string $radiusCss,
        int $sidebarWidth,
    ): string {
        $primaryCss = $this->primaryColorCss();

        return <<<CSS

                    :root {
                        --awrel-font-family: "{$font}", ui-sans-serif, system-ui, sans-serif;
                        --awrel-sidebar-width: {$sidebarWidth}px;
                        --awrel-border-radius: {$radiusCss};
                {$primaryCss}
                    }
        CSS;
    }

    private function primaryColorCss(): string
    {
        $primaryHex = ThemeSettings::primaryColor();
        $css = '';

        try {
            $shades = Color::hex($primaryHex);
            foreach ($shades as $shade => $rgb) {
                if (is_array($rgb) && count($rgb) === 3) {
                    $css .= "    --color-primary-{$shade}: {$rgb[0]} {$rgb[1]} {$rgb[2]}; \n";
                    $css .= "    --primary-{$shade}: {$rgb[0]} {$rgb[1]} {$rgb[2]}; \n";
                } elseif (is_string($rgb)) {
                    $css .= "    --color-primary-{$shade}: {$rgb}; \n";
                    $css .= "    --primary-{$shade}: {$rgb}; \n";
                }
            }
        } catch (\Throwable) {
            $css = <<<'CSS'
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

        return $css;
    }

    private function globalFontFace(string $font): string
    {
        return <<<CSS

                    :root, html, body, .fi-body {
                        --font-sans: "{$font}", ui-sans-serif, system-ui, sans-serif !important;
                        --font-family-sans: "{$font}", ui-sans-serif, system-ui, sans-serif !important;
                        font-family: "{$font}", ui-sans-serif, system-ui, sans-serif !important;
                    }
        CSS;
    }

    private function borderRadiusRules(): string
    {
        return <<<'CSS'

                    .fi-section:not(.fi-section-not-contained),
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
        CSS;
    }

    private function logoCss(): string
    {
        $logoUrl = ThemeSettings::logoUrl();
        if (! $logoUrl) {
            return '';
        }

        $safeLogoUrl = e($logoUrl);

        return <<<CSS

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

    private function loginCss(): string
    {
        $loginLayout = ThemeSettings::loginLayout();
        $loginBgColor = ThemeSettings::loginBackgroundColor();
        $loginBgImageUrl = ThemeSettings::loginBackgroundImageUrl();

        if ($loginLayout !== 'split' && ! $loginBgColor && ! $loginBgImageUrl) {
            return '';
        }

        $css = "\n\n/* ── Custom Login Page ── */\n";

        if ($loginBgImageUrl) {
            $safeBg = e($loginBgImageUrl);
            $css .= <<<CSS
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
            $css .= <<<CSS
                        .fi-simple-layout {
                            background-color: {$safeColor} !important;
                        }
            CSS;
        }

        if ($loginLayout === 'split') {
            $css .= <<<'CSS'
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

        return $css;
    }

    private function boxedLayoutCss(): string
    {
        if (! ThemeSettings::isBoxedLayout()) {
            return '';
        }

        return <<<'CSS'

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

    private function sidebarPositionCss(): string
    {
        if (! ThemeSettings::isSidebarRight()) {
            return '';
        }

        return <<<'CSS'

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
}

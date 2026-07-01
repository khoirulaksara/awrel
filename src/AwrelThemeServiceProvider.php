<?php

namespace Khoirulaksara\Awrel;

use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class AwrelThemeServiceProvider extends PackageServiceProvider
{
    public static string $name = 'awrel';

    public static string $viewNamespace = 'awrel';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasCommands($this->getCommands())
            ->hasConfigFile()
            ->hasMigrations($this->getMigrations())
            ->hasViews(static::$viewNamespace);
    }

    public function packageRegistered(): void
    {
        //
    }

    public function packageBooted(): void
    {
        FilamentAsset::register(
            $this->getAssets(),
            $this->getAssetPackageName(),
        );
    }

    protected function getAssetPackageName(): ?string
    {
        return 'khoirulaksara/awrel';
    }

    /**
     * @return array<Asset>
     */
    protected function getAssets(): array
    {
        return [
            Js::make(
                'awrel-scripts',
                __DIR__.'/../resources/js/dist/index.js',
            ),
            AlpineComponent::make(
                'awrel-color-picker',
                __DIR__.'/../resources/js/dist/components/color-picker.js',
            ),
            AlpineComponent::make(
                'awrel-range-slider',
                __DIR__.'/../resources/js/dist/components/range-slider.js',
            ),
            AlpineComponent::make(
                'awrel-settings-tabs',
                __DIR__.'/../resources/js/dist/components/settings-tabs.js',
            ),
            Css::make(
                'awrel-styles',
                __DIR__.'/../resources/css/filament/admin/theme.css',
            ),
        ];
    }

    /**
     * @return array<class-string>
     */
    protected function getCommands(): array
    {
        return [
            Commands\AwrelInstallCommand::class,
            Commands\AwrelUninstallCommand::class,
        ];
    }

    /**
     * @return array<string>
     */
    protected function getMigrations(): array
    {
        return ['create_awrel_settings_table'];
    }
}

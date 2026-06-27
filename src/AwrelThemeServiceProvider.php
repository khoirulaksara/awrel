<?php

namespace Khoirulaksara\Awrel;

use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Khoirulaksara\Awrel\Commands\AwrelInstallCommand;
use Khoirulaksara\Awrel\Commands\AwrelUninstallCommand;

class AwrelThemeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . "/../config/awrel.php", "awrel");
    }

    public function boot(): void
    {
        // Load views from package
        $this->loadViewsFrom(__DIR__ . "/../resources/views", "awrel");

        // Load migrations from package
        $this->loadMigrationsFrom(__DIR__ . "/../database/migrations");

        // Register compiled JS asset globally for all Filament panels
        FilamentAsset::register([
            Js::make("awrel-theme", Vite::asset("resources/js/app.js")),
        ]);

        // Publisheable assets
        if ($this->app->runningInConsole()) {
            $this->commands([
                AwrelInstallCommand::class,
                AwrelUninstallCommand::class,
            ]);

            $this->publishes(
                [__DIR__ . "/../config/awrel.php" => config_path("awrel.php")],
                "awrel-config",
            );

            $this->publishes(
                [
                    __DIR__ . "/../resources/views" => resource_path(
                        "views/vendor/awrel",
                    ),
                ],
                "awrel-views",
            );

            $this->publishes(
                [
                    __DIR__ . "/../resources/js" => resource_path(
                        "js/vendor/awrel",
                    ),
                ],
                "awrel-js",
            );

            $this->publishes(
                [__DIR__ . "/../public" => public_path("vendor/awrel")],
                "awrel-public",
            );
        }
    }
}

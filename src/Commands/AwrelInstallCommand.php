<?php

namespace Khoirulaksara\Awrel\Commands;

use Illuminate\Console\Command;
use Khoirulaksara\Awrel\Models\AwrelSetting;

class AwrelInstallCommand extends Command
{
    protected $signature = "awrel:install {--force : Re-run the migration even if it has already been run}";

    protected $description = "Install Awrel Theme (publish assets, run migration, seed defaults, auto-wire plugin)";

    public function handle(): int
    {
        $this->components->info("Installing Awrel Theme...");

        // ── 1. Publish assets ──

        $this->components->task("Publishing config", function () {
            $this->callSilently("vendor:publish", [
                "--tag" => "awrel-config",
                "--force" => true,
            ]);

            return true;
        });

        $this->components->task("Publishing views", function () {
            $this->callSilently("vendor:publish", [
                "--tag" => "awrel-views",
                "--force" => true,
            ]);

            return true;
        });

        $this->components->task("Publishing CSS", function () {
            $this->callSilently("vendor:publish", [
                "--tag" => "awrel-css",
                "--force" => true,
            ]);

            return true;
        });

        $this->components->task("Publishing JS", function () {
            $this->callSilently("vendor:publish", [
                "--tag" => "awrel-js",
                "--force" => true,
            ]);

            return true;
        });

        $this->components->task("Publishing public assets", function () {
            $this->callSilently("vendor:publish", [
                "--tag" => "awrel-public",
                "--force" => true,
            ]);

            return true;
        });

        // ── 2. Migration ──

        $this->components->task("Running migration", function () {
            $this->callSilently("migrate", [
                "--force" => $this->option("force"),
            ]);

            return true;
        });

        // ── 3. Seed defaults ──

        $this->components->task("Seeding default settings", function () {
            if (!AwrelSetting::first()) {
                AwrelSetting::create(["settings" => config("awrel")]);

                return "Created";
            }

            return "Skipped (already exists)";
        });

        // ── 4. Auto-wire service provider ──

        $this->wireServiceProvider();

        // ── 5. Auto-wire plugin in AdminPanelProvider ──

        $this->wirePanelPlugin();

        // ── 6. Update vite.config.js ──

        $this->wireViteConfig();

        // ── 7. Done ──

        $this->components->info("Awrel Theme installed successfully.");

        $this->components->twoColumnDetail(
            "<fg=green;options=bold>Plugin registered</>",
            "AdminPanelProvider",
        );
        $this->components->twoColumnDetail(
            "<fg=green;options=bold>Service provider registered</>",
            "bootstrap/providers.php",
        );
        $this->components->twoColumnDetail(
            "<fg=green;options=bold>Theme CSS linked</>",
            "resources/css/vendor/awrel/filament/admin/theme.css",
        );

        $this->newLine();
        $this->components->bulletList([
            "Build assets:",
            "    npm run build",
            "",
            "Login and visit Settings > Awrel Theme Settings to customize.",
        ]);

        return self::SUCCESS;
    }

    /**
     * Register the service provider in bootstrap/providers.php.
     *
     * Laravel auto-discovery already handles this via composer.json extra.laravel.providers,
     * but we add it here for explicitness and to ensure it works even with
     * auto-discovery disabled.
     */
    protected function wireServiceProvider(): void
    {
        $path = base_path("bootstrap/providers.php");

        if (!file_exists($path)) {
            $this->components->warn(
                "bootstrap/providers.php not found. Skipping service provider registration.",
            );

            return;
        }

        $contents = file_get_contents($path);
        $className = "AwrelThemeServiceProvider";
        $fqn = "Khoirulaksara\\Awrel\\" . $className;
        $useStatement = "use " . $fqn . ";";

        // Check if already registered (use statement OR return array entry)
        if (str_contains($contents, $className)) {
            $this->components->task(
                "Registering service provider",
                fn() => "Skipped (already registered)",
            );

            return;
        }

        // Add use statement before the return array
        $contents = preg_replace(
            "/^(return\s+)/m",
            $useStatement . "\n\n$1",
            $contents,
            1,
        );

        // Add to the return array
        $contents = preg_replace(
            "/(return\s+\[)/",
            "$1\n    " . $className . "::class,",
            $contents,
            1,
        );

        file_put_contents($path, $contents);

        $this->components->task(
            "Registering service provider",
            fn() => "Registered",
        );
    }

    /**
     * Add AwrelPlugin to AdminPanelProvider.php.
     */
    protected function wirePanelPlugin(): void
    {
        $paths = [
            app_path("Providers/Filament/AdminPanelProvider.php"),
            // Also check common alternative paths
        ];

        $panelPath = null;
        foreach ($paths as $path) {
            if (file_exists($path)) {
                $panelPath = $path;
                break;
            }
        }

        if (!$panelPath) {
            $this->components->warn(
                "AdminPanelProvider.php not found. Skipping plugin registration.",
            );

            return;
        }

        $contents = file_get_contents($panelPath);
        $import = "use Khoirulaksara\Awrel\AwrelPlugin;";
        $importPage =
            "use Khoirulaksara\Awrel\Filament\Pages\ThemeSettingsPage;";

        // ── Add imports if missing ──
        $changed = false;

        if (!str_contains($contents, $import)) {
            $pattern = '/^(use\s+.+;)$/m';
            if (preg_match_all($pattern, $contents, $matches, PREG_SET_ORDER)) {
                $lastUse = end($matches);
                $pos = strpos($contents, $lastUse[0]) + strlen($lastUse[0]);
                $contents = substr_replace($contents, "\n" . $import, $pos, 0);
                $changed = true;
            }
        }

        if (!str_contains($contents, $importPage)) {
            $pattern = '/^(use\s+.+;)$/m';
            if (preg_match_all($pattern, $contents, $matches, PREG_SET_ORDER)) {
                $lastUse = end($matches);
                $pos = strpos($contents, $lastUse[0]) + strlen($lastUse[0]);
                $contents = substr_replace(
                    $contents,
                    "\n" . $importPage,
                    $pos,
                    0,
                );
                $changed = true;
            }
        }

        // ── Update viteTheme path if needed ──
        if (preg_match("/->viteTheme\(([^)]+)\)/", $contents, $match)) {
            $current = trim($match[1], "'\"");
            $desired = "resources/css/vendor/awrel/filament/admin/theme.css";

            if (
                $current !== $desired &&
                !str_contains($current, "vendor/awrel")
            ) {
                $contents = str_replace(
                    $match[0],
                    "->viteTheme('{$desired}')",
                    $contents,
                );
                $changed = true;
            }
        }

        // ── Add ->plugin(...) before the closing semicolon of the return ──
        $pluginCall =
            "->plugin(AwrelPlugin::make()->faviconSpinner()->stickyTableActions())";

        if (!str_contains($contents, "AwrelPlugin::make()")) {
            $pattern = '/\);\s*\n\s*\}/';
            if (preg_match($pattern, $contents)) {
                $contents = preg_replace(
                    $pattern,
                    "\n            " . $pluginCall . "\n    );\n\}",
                    $contents,
                    1,
                );
                $changed = true;
            }
        }

        if ($changed) {
            file_put_contents($panelPath, $contents);

            $this->components->task(
                "Wiring plugin to panel",
                fn() => "Updated " . class_basename($panelPath),
            );
        } else {
            $this->components->task(
                "Wiring plugin to panel",
                fn() => "Skipped (already configured)",
            );
        }
    }

    /**
     * Ensure the published theme CSS is in vite.config.js inputs.
     */
    protected function wireViteConfig(): void
    {
        $path = base_path("vite.config.js");

        if (!file_exists($path)) {
            return;
        }

        $contents = file_get_contents($path);
        $cssPath = "resources/css/vendor/awrel/filament/admin/theme.css";

        if (str_contains($contents, $cssPath)) {
            return;
        }

        // Add the CSS to the input array if it's not there
        $pattern = "/input:\s*\[([^\]]*)\]/s";
        if (preg_match($pattern, $contents, $match)) {
            $inputContent = $match[1];
            // Find the last string entry in the input array
            $lines = explode("\n", $inputContent);
            $lastLine = trim(end($lines));
            if ($lastLine === "") {
                // Find the second-to-last line
                $clean = array_filter($lines, fn($l) => trim($l) !== "");
                $lastLine = end($clean);
            }

            if ($lastLine) {
                $replacement = str_replace(
                    $lastLine,
                    $lastLine . "," . "\n                \"" . $cssPath . '"',
                    $match[0],
                );
                $contents = str_replace($match[0], $replacement, $contents);
                file_put_contents($path, $contents);
            }
        }
    }
}

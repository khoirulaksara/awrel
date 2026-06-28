<?php

namespace Khoirulaksara\Awrel\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Khoirulaksara\Awrel\Models\AwrelSetting;

class AwrelInstallCommand extends Command
{
    protected $signature = "awrel:install {--force : Force re-publish all assets}";

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

        $this->components->task("Installing theme CSS", function () {
            return $this->installThemeCss();
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
            // If the table doesn't exist but a stale migration record does
            // (from a previous install/uninstall cycle), delete the record
            // so the migration re-runs.
            if (!Schema::hasTable("awrel_settings")) {
                DB::table("migrations")
                    ->where("migration", "like", "%awrel%")
                    ->delete();
            }

            $this->callSilently("migrate", [
                "--force" => true,
            ]);

            return true;
        });

        // ── 3. Seed defaults ──

        $this->components->task("Seeding default settings", function () {
            if (!Schema::hasTable("awrel_settings")) {
                return "Skipped (table not found)";
            }

            if (!AwrelSetting::first()) {
                AwrelSetting::create(["settings" => config("awrel")]);

                return "Created";
            }

            return "Skipped (already exists)";
        });

        // ── 4. Auto-wire service provider ──

        $this->wireServiceProvider();

        // ── 5. Auto-wire plugin and fix paths ──

        $this->wirePanelPlugin();
        $this->fixViteThemePath();
        $this->fixViteConfig();

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
            "resources/css/filament/admin/theme.css",
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
     * Install the Awrel theme CSS.
     *
     * 1. Removes any stale vendor-path CSS from old installs
     *    (resources/css/vendor/awrel/).
     * 2. Backs up the original theme.css if it exists and is not
     *    already the Awrel CSS.
     * 3. Copies the Awrel CSS to resources/css/filament/admin/theme.css.
     */
    protected function installThemeCss(): string
    {
        $target = resource_path("css/filament/admin/theme.css");
        $source = __DIR__ . "/../../resources/css/filament/admin/theme.css";

        if (!file_exists($source)) {
            return "Skipped (package CSS not found)";
        }

        // ── 1. Clean up any stale vendor-path files from old installs ──
        $staleVendorDir = resource_path("css/vendor/awrel");
        if (is_dir($staleVendorDir)) {
            $this->rmdirRecursive($staleVendorDir);
        }

        // ── 2. Back up the original ──
        if (file_exists($target)) {
            $originalContent = file_get_contents($target);
            $awrelContent = file_get_contents($source);

            if ($originalContent !== $awrelContent) {
                $backup = $target . ".awrel-backup";
                copy($target, $backup);
            }
        }

        // ── 3. Ensure target dir exists and copy ──
        $dir = dirname($target);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        copy($source, $target);

        return "Installed";
    }

    /**
     * Recursively delete a directory and all its contents.
     */
    protected function rmdirRecursive(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $dir,
                \RecursiveDirectoryIterator::SKIP_DOTS,
            ),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($items as $item) {
            if ($item->isDir()) {
                rmdir($item->getRealPath());
            } else {
                unlink($item->getRealPath());
            }
        }

        rmdir($dir);
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

        // ── Add ->plugin(…) at the end of the return statement ──
        $pluginCall =
            "->plugin(AwrelPlugin::make()->faviconSpinner()->stickyTableActions())";

        if (!str_contains($contents, "AwrelPlugin::make()")) {
            // Find `return $panel` to anchor the search
            $returnPos = strpos($contents, 'return $panel');
            if ($returnPos === false) {
                $this->components->warn(
                    "Could not find 'return \$panel' in panel file.",
                );

                return;
            }

            // From there, find the LAST `);` (the return statement closing)
            $searchStart = $returnPos;
            $closingPos = false;

            while (($found = strpos($contents, ");", $searchStart)) !== false) {
                $closingPos = $found;
                $searchStart = $found + 1;
            }

            if ($closingPos === false) {
                $this->components->warn(
                    "Could not find closing ');' for the return statement.",
                );

                return;
            }

            // Replace `);` with `)\n            ->plugin(...);`
            // This preserves `]` before `)` in `]);`
            $contents = substr_replace(
                $contents,
                ")\n            " . $pluginCall . ";",
                $closingPos,
                2, // length of ');'
            );
            $changed = true;
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
     * Ensure viteTheme is set in AdminPanelProvider.
     *
     * 1. Fixes the path if it points to a stale vendor location.
     * 2. Adds ->viteTheme('resources/css/filament/admin/theme.css')
     *    if no viteTheme call exists at all.
     */
    protected function fixViteThemePath(): void
    {
        $panelPath = app_path("Providers/Filament/AdminPanelProvider.php");

        if (!file_exists($panelPath)) {
            $this->components->warn(
                "AdminPanelProvider.php not found. Skipping viteTheme setup.",
            );

            return;
        }

        $contents = file_get_contents($panelPath);
        $goodPath = "resources/css/filament/admin/theme.css";
        $badPath = "resources/css/vendor/awrel/filament/admin/theme.css";
        $changed = false;

        // 1. Fix stale vendor path if present
        if (str_contains($contents, $badPath)) {
            $contents = str_replace($badPath, $goodPath, $contents);
            $changed = true;
        }

        // 2. Add viteTheme call if missing entirely
        if (!str_contains($contents, "viteTheme(") && !$changed) {
            // Find the closing ");" of the return statement
            $returnPos = strpos($contents, 'return $panel');
            if ($returnPos !== false) {
                $searchStart = $returnPos;
                $closingPos = false;

                while (
                    ($found = strpos($contents, ");", $searchStart)) !== false
                ) {
                    $closingPos = $found;
                    $searchStart = $found + 1;
                }

                if ($closingPos !== false) {
                    $viteThemeCall =
                        "\n            ->viteTheme('" . $goodPath . "')";
                    $contents = substr_replace(
                        $contents,
                        $viteThemeCall,
                        $closingPos,
                        0,
                    );
                    $changed = true;
                }
            }
        }

        if ($changed) {
            file_put_contents($panelPath, $contents);
        }
    }

    /**
     * Fix vite.config.js to properly reference Awrel's theme CSS.
     *
     * 1. Removes any stale vendor-path CSS references from old installs.
     * 2. Adds 'resources/css/filament/admin/theme.css' to the input
     *    array if it isn't already present.
     */
    protected function fixViteConfig(): void
    {
        $vitePath = base_path("vite.config.js");

        if (!file_exists($vitePath)) {
            return;
        }

        $contents = file_get_contents($vitePath);
        $original = $contents;
        $ourCssEntry = "resources/css/filament/admin/theme.css";

        // 1. Remove any input lines referencing the stale vendor CSS path
        $contents = preg_replace(
            '/\s*[\'"][^\'"]*resources\/css\/vendor\/awrel[^\'"]*[\'"][,\s]*\n?/',
            "\n",
            $contents,
        );

        // 2. Add our CSS to the input array if missing
        if (!str_contains($contents, $ourCssEntry)) {
            // Find the last ']' in the input array and insert before it.
            // Match pattern: input: [...items...]
            if (
                preg_match(
                    "/input:\s*\[/",
                    $contents,
                    $match,
                    PREG_OFFSET_CAPTURE,
                )
            ) {
                $inputStart = $match[0][1] + strlen($match[0][0]);
                $depth = 1;
                $i = $inputStart;

                while ($depth > 0 && $i < strlen($contents)) {
                    if ($contents[$i] === "[") {
                        $depth++;
                    } elseif ($contents[$i] === "]") {
                        $depth--;
                    }
                    $i++;
                }

                $insertPos = $i - 1; // position of the closing ']'

                // Determine indentation from the line before ']'
                $lineStart = strrpos(substr($contents, 0, $insertPos), "\n");
                $indent = "";
                if ($lineStart !== false) {
                    $lineBefore = substr(
                        $contents,
                        $lineStart + 1,
                        $insertPos - $lineStart - 1,
                    );
                    if (preg_match("/^(\s+)/", $lineBefore, $indentMatch)) {
                        $indent = $indentMatch[1];
                    }
                }

                $entry = "\n" . $indent . '"' . $ourCssEntry . '",';
                $contents = substr_replace($contents, $entry, $insertPos, 0);
            }
        }

        // 3. Clean up extra blank lines left behind
        $contents = preg_replace("/\n{3,}/", "\n\n", $contents);

        if ($contents !== $original) {
            file_put_contents($vitePath, $contents);
        }
    }
}

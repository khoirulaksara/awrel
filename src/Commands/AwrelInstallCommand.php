<?php

namespace Khoirulaksara\Awrel\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Khoirulaksara\Awrel\Models\AwrelSetting;

class AwrelInstallCommand extends Command
{
    protected $signature = 'awrel:install {--force : Force re-publish all assets}';

    protected $description = 'Install Awrel Theme (publish assets, run migration, seed defaults, auto-wire plugin)';

    public function handle(): int
    {
        $this->components->info('Installing Awrel Theme...');

        // ── 1. Publish assets ──

        $this->components->task('Publishing public assets', function () {
            $this->callSilently('vendor:publish', [
                '--tag' => 'awrel-public',
                '--force' => true,
            ]);

            return true;
        });

        // ── 2. Migration ──

        $this->components->task('Running migration', function () {
            // If the table doesn't exist but a stale migration record does
            // (from a previous install/uninstall cycle), delete the record
            // so the migration re-runs.
            if (! Schema::hasTable('awrel_settings')) {
                DB::table('migrations')
                    ->where('migration', 'like', '%awrel%')
                    ->delete();
            }

            $this->callSilently('migrate', [
                '--force' => true,
            ]);

            return true;
        });

        // ── 3. Seed defaults ──

        $this->components->task('Seeding default settings', function () {
            if (! Schema::hasTable('awrel_settings')) {
                return 'Skipped (table not found)';
            }

            if (! AwrelSetting::first()) {
                AwrelSetting::create(['settings' => config('awrel')]);

                return 'Created';
            }

            return 'Skipped (already exists)';
        });

        // ── 4. Auto-wire service provider ──

        $this->wireServiceProvider();

        // ── 5. Auto-wire plugin and fix paths ──

        $this->wirePanelPlugin();
        $this->fixViteConfig();

        // ── 7. Done ──

        $this->components->info('Awrel Theme installed successfully.');

        $this->components->twoColumnDetail(
            '<fg=green;options=bold>Plugin registered</>',
            'AdminPanelProvider',
        );
        $this->components->twoColumnDetail(
            '<fg=green;options=bold>Service provider registered</>',
            'bootstrap/providers.php',
        );
        $this->components->twoColumnDetail(
            '<fg=green;options=bold>Theme CSS linked</>',
            'resources/css/filament/admin/theme.css',
        );

        $this->newLine();
        $this->components->bulletList([
            'Build assets:',
            '    npm run build',
            '',
            'Login and visit Settings > Awrel Theme Settings to customize.',
        ]);

        return self::SUCCESS;
    }

    protected function installThemeCss(): string
    {
        return 'Handled by FilamentAsset';
    }

    /**
     * Recursively delete a directory and all its contents.
     */
    protected function rmdirRecursive(string $dir): void
    {
        if (! is_dir($dir)) {
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
        $path = base_path('bootstrap/providers.php');

        if (! file_exists($path)) {
            $this->components->warn(
                'bootstrap/providers.php not found. Skipping service provider registration.',
            );

            return;
        }

        $contents = file_get_contents($path);
        $className = 'AwrelThemeServiceProvider';
        $fqn = 'Khoirulaksara\\Awrel\\'.$className;
        $useStatement = 'use '.$fqn.';';

        // Check if already registered (use statement OR return array entry)
        if (str_contains($contents, $className)) {
            $this->components->task(
                'Registering service provider',
                fn () => 'Skipped (already registered)',
            );

            return;
        }

        // Add use statement before the return array
        $contents = preg_replace(
            "/^(return\s+)/m",
            $useStatement."\n\n$1",
            $contents,
            1,
        );

        // Add to the return array
        $contents = preg_replace(
            "/(return\s+\[)/",
            "$1\n    ".$className.'::class,',
            $contents,
            1,
        );

        file_put_contents($path, $contents);

        $this->components->task(
            'Registering service provider',
            fn () => 'Registered',
        );
    }

    /**
     * Add AwrelPlugin to AdminPanelProvider.php.
     */
    protected function wirePanelPlugin(): void
    {
        $paths = [
            app_path('Providers/Filament/AdminPanelProvider.php'),
            // Also check common alternative paths
        ];

        $panelPath = null;
        foreach ($paths as $path) {
            if (file_exists($path)) {
                $panelPath = $path;
                break;
            }
        }

        if (! $panelPath) {
            $this->components->warn(
                'AdminPanelProvider.php not found. Skipping plugin registration.',
            );

            return;
        }

        $contents = file_get_contents($panelPath);
        $import = "use Khoirulaksara\Awrel\AwrelPlugin;";
        $importPage =
            "use Khoirulaksara\Awrel\Filament\Pages\ThemeSettingsPage;";

        // ── Add imports if missing ──
        $changed = false;

        if (! str_contains($contents, $import)) {
            $pattern = '/^(use\s+.+;)$/m';
            if (preg_match_all($pattern, $contents, $matches, PREG_SET_ORDER)) {
                $lastUse = end($matches);
                $pos = strpos($contents, $lastUse[0]) + strlen($lastUse[0]);
                $contents = substr_replace($contents, "\n".$import, $pos, 0);
                $changed = true;
            }
        }

        if (! str_contains($contents, $importPage)) {
            $pattern = '/^(use\s+.+;)$/m';
            if (preg_match_all($pattern, $contents, $matches, PREG_SET_ORDER)) {
                $lastUse = end($matches);
                $pos = strpos($contents, $lastUse[0]) + strlen($lastUse[0]);
                $contents = substr_replace(
                    $contents,
                    "\n".$importPage,
                    $pos,
                    0,
                );
                $changed = true;
            }
        }

        // ── Add ->plugin(…) at the end of the return statement ──
        $pluginCall =
            '->plugin(AwrelPlugin::make()->faviconSpinner()->stickyTableActions())';

        if (! str_contains($contents, 'AwrelPlugin::make()')) {
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

            while (($found = strpos($contents, ');', $searchStart)) !== false) {
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
                ")\n            ".$pluginCall.';',
                $closingPos,
                2, // length of ');'
            );
            $changed = true;
        }

        if ($changed) {
            file_put_contents($panelPath, $contents);

            $this->components->task(
                'Wiring plugin to panel',
                fn () => 'Updated '.class_basename($panelPath),
            );
        } else {
            $this->components->task(
                'Wiring plugin to panel',
                fn () => 'Skipped (already configured)',
            );
        }
    }

    /**
     * Ensure viteTheme is set in AdminPanelProvider.
     * Handled by FilamentAsset - this method only cleans up stale paths.
     */
    protected function fixViteThemePath(): void
    {
        // CSS is now registered via FilamentAsset::register() in the service provider.
        // This method is kept for backward compatibility and cleanup only.
    }

    /**
     * Fix vite.config.js to remove stale vendor CSS references.
     *
     * Removes any stale vendor-path CSS references from old installs.
     * The theme CSS is now registered via FilamentAsset.
     */
    protected function fixViteConfig(): void
    {
        $vitePath = base_path('vite.config.js');

        if (! file_exists($vitePath)) {
            return;
        }

        $contents = file_get_contents($vitePath);
        $original = $contents;

        // Remove any input lines referencing the stale vendor CSS path
        $contents = preg_replace(
            '/\s*[\'"][^\'"]*resources\/css\/vendor\/awrel[^\'"]*[\'"][,\s]*\n?/',
            "\n",
            $contents,
        );

        // Clean up extra blank lines left behind
        $contents = preg_replace("/\n{3,}/", "\n\n", $contents);

        if ($contents !== $original) {
            file_put_contents($vitePath, $contents);
        }
    }
}

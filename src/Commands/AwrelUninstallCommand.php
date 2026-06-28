<?php

namespace Khoirulaksara\Awrel\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class AwrelUninstallCommand extends Command
{
    protected $signature = 'awrel:uninstall {--force : Skip confirmation prompt}';

    protected $description = 'Uninstall Awrel Theme and revert all auto-wired changes';

    public function handle(): int
    {
        if (
            ! $this->option('force') &&
            ! $this->confirm(
                'This will remove all Awrel Theme files, settings, database table, and revert all configuration changes. Continue?',
            )
        ) {
            return self::SUCCESS;
        }

        $this->components->info('Uninstalling Awrel Theme...');

        // ── 1. Revert AdminPanelProvider.php ──
        $this->revertPanelPlugin();

        // ── 2. Revert bootstrap/providers.php ──
        $this->revertServiceProvider();

        // ── 3. Restore original theme CSS ──
        $this->restoreThemeCss();

        // ── 4. Drop the awrel_settings table ──
        $this->dropSettingsTable();

        // ── 5. Remove published assets ──
        $this->removePublishedAssets();

        // ── 6. Clean stale vite.config.js references ──
        $this->fixViteConfig();

        $this->components->info('Awrel Theme uninstalled successfully.');

        return self::SUCCESS;
    }

    /**
     * Remove AwrelPlugin and ThemeSettingsPage from AdminPanelProvider.php.
     */
    protected function revertPanelPlugin(): void
    {
        $panelPath = app_path('Providers/Filament/AdminPanelProvider.php');

        if (! file_exists($panelPath)) {
            $this->components->warn(
                'AdminPanelProvider.php not found. Skipping panel cleanup.',
            );

            return;
        }

        $contents = file_get_contents($panelPath);
        $original = $contents;
        $changed = false;

        // Remove use statements
        $contents = preg_replace(
            '/^use Khoirulaksara\\\\Awrel\\\\AwrelPlugin;\s*$/m',
            '',
            $contents,
        );
        $contents = preg_replace(
            '/^use Khoirulaksara\\\\Awrel\\\\Filament\\\\Pages\\\\ThemeSettingsPage;\s*$/m',
            '',
            $contents,
        );

        // Remove ->plugin(AwrelPlugin::make(...)...) block
        // Uses .*? (non-greedy) with /s flag to span the entire
        // injected call up to the first `);` which closes the statement.
        $contents = preg_replace(
            "/\s*->plugin\(AwrelPlugin::make.*?\);/s",
            '',
            $contents,
        );

        // Remove ->viteTheme(...) call
        $contents = preg_replace("/\s*->viteTheme\([^)]+\)/", '', $contents);

        // Remove whitespace left from empty lines after removal
        $contents = preg_replace('/\n{3,}/', "\n\n", $contents);

        // Optionally remove ThemeSettingsPage from pages array.
        // Preserves Dashboard::class if it's the only remaining page.
        $contents = preg_replace(
            "/,\s*ThemeSettingsPage::class/",
            '',
            $contents,
        );

        if ($contents !== $original) {
            file_put_contents($panelPath, $contents);
            $changed = true;
        }

        $this->components->task(
            'Reverting plugin registration',
            fn () => $changed
                ? 'Removed from AdminPanelProvider'
                : 'Skipped (not found)',
        );
    }

    /**
     * Remove AwrelThemeServiceProvider from bootstrap/providers.php.
     */
    protected function revertServiceProvider(): void
    {
        $path = base_path('bootstrap/providers.php');

        if (! file_exists($path)) {
            $this->components->warn(
                'bootstrap/providers.php not found. Skipping.',
            );

            return;
        }

        $contents = file_get_contents($path);
        $original = $contents;

        // Remove use statement
        $contents = preg_replace(
            '/^use Khoirulaksara\\\\Awrel\\\\AwrelThemeServiceProvider;\s*$/m',
            '',
            $contents,
        );

        // Remove class from return array
        $contents = preg_replace(
            "/\s+AwrelThemeServiceProvider::class,\s*/",
            '',
            $contents,
        );

        // Clean up empty lines
        $contents = preg_replace('/\n{3,}/', "\n\n", $contents);

        $changed = $contents !== $original;
        if ($changed) {
            file_put_contents($path, $contents);
        }

        $this->components->task(
            'Reverting service provider',
            fn () => $changed
                ? 'Removed from bootstrap/providers.php'
                : 'Skipped (not found)',
        );
    }

    /**
     * Restore the original theme.css from backup, or remove the Awrel CSS.
     */
    protected function restoreThemeCss(): void
    {
        $target = resource_path('css/filament/admin/theme.css');
        $backup = $target.'.awrel-backup';

        if (file_exists($backup)) {
            copy($backup, $target);
            unlink($backup);

            $this->components->task(
                'Restoring theme CSS',
                fn () => 'Restored from backup',
            );
        } elseif (file_exists($target)) {
            unlink($target);

            $this->components->task(
                'Removing theme CSS',
                fn () => 'Removed (no backup found)',
            );
        } else {
            $this->components->task('Theme CSS', fn () => 'Skipped (not found)');
        }
    }

    /**
     * Drop the awrel_settings table.
     */
    protected function dropSettingsTable(): void
    {
        if (Schema::hasTable('awrel_settings')) {
            Schema::dropIfExists('awrel_settings');

            $this->components->task(
                'Dropping settings table',
                fn () => 'Dropped awrel_settings',
            );
        } else {
            $this->components->task(
                'Dropping settings table',
                fn () => 'Skipped (table not found)',
            );
        }
    }

    /**
     * Remove published assets from the application.
     */
    protected function removePublishedAssets(): void
    {
        $paths = [
            config_path('awrel.php'),
            resource_path('views/vendor/awrel'),
            resource_path('js/vendor/awrel'),
            public_path('vendor/awrel'),
        ];

        $count = 0;
        foreach ($paths as $path) {
            if (is_file($path)) {
                unlink($path);
                $count++;
            } elseif (is_dir($path)) {
                $this->rmdirRecursive($path);
                $count++;
            }
        }

        $this->components->task(
            'Removing published assets',
            fn () => "Removed {$count} assets",
        );
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
     * Remove Awrel theme CSS references from vite.config.js.
     *
     * 1. Removes any stale vendor-path CSS references from old installs.
     * 2. Removes 'resources/css/filament/admin/theme.css' from the
     *    input array.
     */
    protected function fixViteConfig(): void
    {
        $vitePath = base_path('vite.config.js');

        if (! file_exists($vitePath)) {
            return;
        }

        $contents = file_get_contents($vitePath);
        $original = $contents;

        // 1. Remove stale vendor CSS references
        $contents = preg_replace(
            '/\s*[\'"][^\'"]*resources\/css\/vendor\/awrel[^\'"]*[\'"][,\s]*\n?/',
            "\n",
            $contents,
        );

        // 2. Remove the current theme CSS path from the input array
        $contents = preg_replace(
            '/\s*[\'"]resources\/css\/filament\/admin\/theme\.css[\'"][,\s]*\n?/',
            "\n",
            $contents,
        );

        $contents = preg_replace("/\n{3,}/", "\n\n", $contents);

        if ($contents !== $original) {
            file_put_contents($vitePath, $contents);
        }
    }
}

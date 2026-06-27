<?php

namespace Khoirulaksara\Awrel\Commands;

use Illuminate\Console\Command;
use Khoirulaksara\Awrel\Models\AwrelSetting;

class AwrelInstallCommand extends Command
{
    protected $signature = 'awrel:install {--force : Re-run the migration even if it has already been run}';

    protected $description = 'Install Awrel Theme (publish assets, run migration, seed defaults)';

    public function handle(): int
    {
        $this->components->info('Installing Awrel Theme...');

        $this->components->task('Publishing config', function () {
            $this->callSilently('vendor:publish', [
                '--tag' => 'awrel-config',
                '--force' => true,
            ]);

            return true;
        });

        $this->components->task('Publishing views', function () {
            $this->callSilently('vendor:publish', [
                '--tag' => 'awrel-views',
                '--force' => true,
            ]);

            return true;
        });

        $this->components->task('Publishing CSS', function () {
            $this->callSilently('vendor:publish', [
                '--tag' => 'awrel-css',
                '--force' => true,
            ]);

            return true;
        });

        $this->components->task('Publishing JS', function () {
            $this->callSilently('vendor:publish', [
                '--tag' => 'awrel-js',
                '--force' => true,
            ]);

            return true;
        });

        $this->components->task('Publishing public assets', function () {
            $this->callSilently('vendor:publish', [
                '--tag' => 'awrel-public',
                '--force' => true,
            ]);

            return true;
        });

        $this->components->task('Running migration', function () {
            $this->callSilently('migrate', [
                '--force' => $this->option('force'),
            ]);

            return true;
        });

        $this->components->task('Seeding default settings', function () {
            if (! AwrelSetting::first()) {
                AwrelSetting::create(['settings' => config('awrel')]);

                return 'Created';
            }

            return 'Skipped (already exists)';
        });

        $this->components->info('Awrel Theme installed successfully.');

        $this->components->bulletList([
            'Add AwrelPlugin to your panel:',
            '    ->plugin(AwrelPlugin::make()->stickyTableActions()->faviconSpinner())',
            '',
            'In your panel config, set the theme:',
            '    ->viteTheme(\'resources/css/vendor/awrel/filament/admin/theme.css\')',
            '',
            'Or copy the CSS to your Filament admin theme location.',
            '',
            'Build assets:',
            '    npm run build',
            '',
            'Login and visit Settings > Theme Settings to customize.',
        ]);

        return self::SUCCESS;
    }
}

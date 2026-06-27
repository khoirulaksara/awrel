<?php

namespace Khoirulaksara\Awrel\Filament\Pages;

use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Khoirulaksara\Awrel\Helpers\ThemeSettings;
use UnitEnum;

class ThemeSettingsPage extends Page
{
    protected static ?string $title = 'Awrel Theme Settings';

    protected ?string $heading = 'Awrel Theme Settings';

    protected static ?string $navigationLabel = 'Awrel Theme Settings';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-swatch';

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 1;

    protected string $view = 'awrel::filament.pages.theme-settings';

    public array $settings = [];

    public function mount(): void
    {
        $this->settings = ThemeSettings::all();
    }

    public function updated($name, $value): void
    {
        // When a setting changes, update the session cache so Alpine/JS
        // can read revised values without a page refresh.
        // This method is triggered by wire:model.live on the frontend.
    }

    public function save(): void
    {
        $validated = $this->resolveAndValidate($this->settings);

        ThemeSettings::save($validated);

        Notification::make()
            ->title('Settings saved')
            ->body(
                'Theme settings have been updated. Refreshing the page to apply all changes.',
            )
            ->success()
            ->send();
    }

    protected function resolveAndValidate(array $data): array
    {
        $rules = [
            'favicon_spinner' => ['boolean'],
            'sticky_table_actions' => ['boolean'],
            'primary_color' => ['required', 'regex:/^#[a-f0-9]{6}$/i'],
            'font_family' => ['required', 'string', 'max:100'],
            'border_radius' => ['required', 'in:sm,md,lg,xl,2xl'],
            'sidebar_width' => ['required', 'integer', 'min:180', 'max:400'],
        ];

        $messages = [];
        $attributes = [];

        return validator($data, $rules, $messages, $attributes)->validate();
    }
}

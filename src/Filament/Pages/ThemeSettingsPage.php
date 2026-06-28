<?php

namespace Khoirulaksara\Awrel\Filament\Pages;

use BackedEnum;
use Filament\Forms\Components\ColorPicker;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\Storage;
use Khoirulaksara\Awrel\Helpers\ThemeSettings;
use Livewire\WithFileUploads;
use UnitEnum;

class ThemeSettingsPage extends Page
{
    use WithFileUploads;

    protected static ?string $title = "Awrel Theme Settings";

    protected ?string $heading = "Awrel Theme Settings";

    protected static ?string $navigationLabel = "Awrel Theme Settings";

    protected static string|BackedEnum|null $navigationIcon = "heroicon-o-swatch";

    protected static string|UnitEnum|null $navigationGroup = "Settings";

    protected static ?int $navigationSort = 1;

    protected string $view = "awrel::filament.pages.theme-settings";

    public array $settings = [];

    public $logo = null;

    public $loginBackgroundImage = null;

    public function mount(): void
    {
        $this->settings = ThemeSettings::all();
    }

    public function colorPicker(Schema $schema): Schema
    {
        return $schema
            ->components([
                ColorPicker::make("primary_color")
                    ->label("Primary Color")
                    ->live()
                    ->afterStateUpdated(function ($state) {
                        try {
                            $shades = Color::hex($state);
                            $this->dispatch(
                                "awrel-color-synced",
                                color: $state,
                                shades: $shades,
                            );
                        } catch (\Throwable) {
                            $this->dispatch(
                                "awrel-color-synced",
                                color: $state,
                                shades: [],
                            );
                        }
                    }),
            ])
            ->statePath("settings.primary_color");
    }

    public function updated($name, $value): void
    {
        if (str_starts_with($name, "settings.")) {
            $key = (string) str($name)->after("settings.");
            if ($key === "primary_color") {
                // Generate proper RGB shades using Filament's color engine
                try {
                    $shades = Color::hex($value);
                    $this->dispatch(
                        "awrel-color-synced",
                        color: $value,
                        shades: $shades,
                    );
                } catch (\Throwable) {
                    $this->dispatch(
                        "awrel-color-synced",
                        color: $value,
                        shades: [],
                    );
                }
            }
        }
    }

    public function save(): void
    {
        // Handle logo upload
        if ($this->logo) {
            $path = $this->logo->store("awrel", "public");
            $this->settings["logo_path"] = $path;
        }

        // Handle login background image upload
        if ($this->loginBackgroundImage) {
            $path = $this->loginBackgroundImage->store("awrel/login", "public");
            $this->settings["login_background_image"] = $path;
        }

        $validated = $this->resolveAndValidate($this->settings);

        ThemeSettings::save($validated);

        $this->logo = null;
        $this->loginBackgroundImage = null;

        Notification::make()
            ->title("Settings saved")
            ->body(
                "Theme settings have been updated. Refreshing the page to apply all changes.",
            )
            ->success()
            ->send();
    }

    public function removeLogo(): void
    {
        $path = ThemeSettings::logoPath();

        if ($path) {
            Storage::disk("public")->delete($path);
        }

        $this->settings["logo_path"] = null;
        $this->logo = null;

        ThemeSettings::forget("logo_path");

        Notification::make()->title("Logo removed")->success()->send();
    }

    public function removeLoginBackground(): void
    {
        $path = ThemeSettings::loginBackgroundImagePath();

        if ($path) {
            Storage::disk("public")->delete($path);
        }

        $this->settings["login_background_image"] = null;
        $this->loginBackgroundImage = null;

        ThemeSettings::forget("login_background_image");

        Notification::make()
            ->title("Login background removed")
            ->success()
            ->send();
    }

    protected function resolveAndValidate(array $data): array
    {
        $rules = [
            "favicon_spinner" => ["boolean"],
            "sticky_table_actions" => ["boolean"],
            "primary_color" => ["required", 'regex:/^#[a-f0-9]{6}$/i'],
            "font_family" => ["required", "string", "max:100"],
            "border_radius" => ["required", "in:sm,md,lg,xl,2xl"],
            "sidebar_width" => ["required", "integer", "min:180", "max:400"],
            "logo_path" => ["nullable", "string", "max:255"],
            "login_layout" => ["required", "in:centered,split"],
            "login_background_color" => ["nullable", 'regex:/^#[a-f0-9]{6}$/i'],
            "login_background_image" => ["nullable", "string", "max:255"],
            "layout_variant" => ["required", "in:sidebar,horizontal"],
            "boxed_layout" => ["boolean"],
            "sidebar_position" => ["required", "in:left,right"],
        ];

        return validator($data, $rules)->validate();
    }
}

<?php

namespace Khoirulaksara\Awrel\Filament\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Khoirulaksara\Awrel\Helpers\ThemeSettings; // Added this

class ThemeSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'awrel::filament.pages.theme-settings-page';

    protected static ?string $title = 'Awrel Theme Settings';

    protected ?string $heading = 'Awrel Theme Settings';

    public ?array $data = [];

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-adjustments-horizontal';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Settings';
    }

    public static function getNavigationSort(): ?int
    {
        return 10;
    }

    public function mount(): void
    {
        $this->form->fill(ThemeSettings::all());
    }

    public function form(Schema $schema): Schema
    {
        return $schema->statePath('data')->schema([
            Tabs::make('Theme Settings')
                ->tabs([
                    Tab::make('General')
                        ->icon('heroicon-m-cog-6-tooth')
                        ->columns(2)
                        ->schema([
                            Toggle::make('favicon_spinner')
                                ->label('Animated Favicon Spinner')
                                ->helperText(
                                    'Spinner in browser tab on navigation',
                                ),
                            Toggle::make('sticky_table_actions')
                                ->label('Sticky Table Actions')
                                ->helperText(
                                    'Pin actions column on table horizontal scroll',
                                ),
                            Toggle::make('loading_bar')
                                ->label('Livewire Loading Bar')
                                ->helperText(
                                    'Progress bar at the top of the viewport during Livewire requests.',
                                )
                                ->default(true),
                            Toggle::make('page_transition')
                                ->label('Page Transition')
                                ->helperText(
                                    'Smooth fade transition when navigating between pages.',
                                )
                                ->default(true),
                            Toggle::make('button_submit_loading')
                                ->label('Button Submit Loading')
                                ->helperText(
                                    'Show a spinner inside action buttons while Livewire requests are in progress.',
                                )
                                ->default(true),
                            Toggle::make('unsaved_changes_guard')
                                ->label('Unsaved Changes Guard')
                                ->helperText(
                                    'Warn before leaving a page with unsaved changes.',
                                )
                                ->default(true),
                        ]),
                    Tab::make('Branding')
                        ->icon('heroicon-m-photo')
                        ->schema([
                            FileUpload::make('logo_path')
                                ->label('Logo')
                                ->directory('awrel')
                                ->disk('public')
                                ->image()
                                ->maxSize(1024)
                                ->helperText(
                                    'Upload your own logo to replace the default brand text.',
                                )
                                ->preserveFilenames()
                                ->panelLayout('integrated')
                                ->columnSpanFull(),
                        ]),
                    Tab::make('Appearance')
                        ->icon('heroicon-m-paint-brush')
                        ->columns(2)
                        ->schema([
                            ColorPicker::make('primary_color')
                                ->label('Primary Color')
                                ->hex()
                                ->live()
                                ->helperText(
                                    'The main accent color used across the admin panel.',
                                )
                                ->afterStateUpdated(function ($state) {
                                    try {
                                        $shades = Color::hex($state);
                                        $this->dispatch(
                                            'awrel-color-synced',
                                            color: $state,
                                            shades: $shades,
                                        );
                                    } catch (\Throwable $th) {
                                        $this->dispatch(
                                            'awrel-color-synced',
                                            color: $state,
                                            shades: [],
                                        );
                                    }
                                }),
                            Select::make('font_family')
                                ->label('Font Family')
                                ->options([
                                    'Plus Jakarta Sans' => 'Plus Jakarta Sans',
                                    'Inter' => 'Inter',
                                    'Instrument Sans' => 'Instrument Sans',
                                    'system-ui' => 'system-ui',
                                ])
                                ->native(false)
                                ->helperText(
                                    'Base font for the admin panel interface.',
                                ),
                            Select::make('border_radius')
                                ->label('Border Radius')
                                ->options([
                                    'sm' => 'sm (0.375rem)',
                                    'md' => 'md (0.5rem)',
                                    'lg' => 'lg (0.75rem)',
                                    'xl' => 'xl (1rem)',
                                    '2xl' => '2xl (1.25rem)',
                                ])
                                ->native(false)
                                ->default('2xl')
                                ->helperText(
                                    'Controls the rounding of cards, buttons, and panels.',
                                ),
                        ]),
                    Tab::make('Login')
                        ->icon('heroicon-m-lock-closed')
                        ->columns(2)
                        ->schema([
                            Radio::make('login_layout')
                                ->label('Login Page Layout')
                                ->options([
                                    'centered' => 'Centered',
                                    'split' => 'Split',
                                ])
                                ->inline(false)
                                ->default('centered')
                                ->helperText(
                                    'Choose between a centered or split layout for the login page.',
                                ),
                            ColorPicker::make('login_background_color')
                                ->label('Login Background Color')
                                ->hex()
                                ->nullable()
                                ->helperText(
                                    'Set a background color for the login page.',
                                ),
                            FileUpload::make('login_background_image')
                                ->label('Login Background Image')
                                ->directory('awrel/login')
                                ->disk('public')
                                ->image()
                                ->maxSize(2048)
                                ->helperText(
                                    'Upload a background image for the login page.',
                                )
                                ->preserveFilenames()
                                ->panelLayout('integrated')
                                ->columnSpanFull(),
                        ]),
                    Tab::make('Layout')
                        ->icon('heroicon-m-view-columns')
                        ->columns(2)
                        ->schema([
                            Radio::make('layout_variant')
                                ->label('Layout Variant')
                                ->options([
                                    'sidebar' => 'Sidebar',
                                    'horizontal' => 'Horizontal',
                                ])
                                ->inline(false)
                                ->default('sidebar')
                                ->helperText(
                                    'Choose between a sidebar or horizontal navigation layout.',
                                ),
                            Toggle::make('boxed_layout')
                                ->label('Boxed Layout')
                                ->helperText(
                                    'Apply a boxed layout to the main content area.',
                                ),
                            Radio::make('sidebar_position')
                                ->label('Sidebar Position')
                                ->options([
                                    'left' => 'Left',
                                    'right' => 'Right',
                                ])
                                ->inline(false)
                                ->default('left')
                                ->helperText(
                                    'Set the position of the sidebar (left or right).',
                                ),
                            TextInput::make('sidebar_width')
                                ->label('Sidebar Width')
                                ->numeric()
                                ->suffix('px')
                                ->minValue(180)
                                ->maxValue(400)
                                ->default(256)
                                ->helperText(
                                    'Adjust the sidebar width (180px — 400px).',
                                ),
                        ]),
                ])
                ->columnSpanFull(),
        ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        ThemeSettings::save($data);

        Notification::make()
            ->title('Settings saved successfully.')
            ->success()
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(
                    __(
                        'filament-panels::resources/pages/edit-record.form.actions.save.label',
                    ),
                )
                ->submit('save')
                ->keyBindings(['mod+s']),
        ];
    }

    public function content(Schema $schema): Schema
    {
        return $this->form($schema);
    }
}

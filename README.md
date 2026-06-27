# Awrel Theme

**Premium dashboard theme plugin for Filament v5** — high-end micro-interactions, skeleton loaders, glassmorphism, sticky table actions, and more. Breaks away from the mainstream Tailwind UI look with custom modern layouts and visual enhancements.

## Features

- **Skeleton Loaders** — Automatic table & stats overview skeleton placeholders using `deferLoading()`.
- **Sticky Table Actions** — Pin the actions column on horizontal table scroll (opt-in).
- **Loading Bar** — Livewire request progress bar at the top of the viewport.
- **Animated Favicon Spinner** — Replaces the favicon with an animated spinner during navigation (opt-in).
- **Disabled Button Shake** — Tactile shake animation when clicking disabled buttons.
- **Glassmorphism Topbar** — Modern frosted-glass effect on the top navigation bar.
- **Animated Notification Bell** — Custom CSS keyframe ring animation on hover/new notifications.
- **Full-width Layout** — Maximizes content area by removing max-width constraints.
- **Dynamic Color Picker** — Change the primary color from the settings page.
- **Custom Font & Border Radius** — Choose from multiple font families and border radius values.
- **Sidebar Width Control** — Adjust sidebar width from the settings page.
- **Collapsible Sidebar** — Desktop sidebar collapse/expand with smooth animation.
- **Dark/Light Mode** — Full dark mode support.

## Requirements

- PHP 8.2+
- Laravel 11.x, 12.x, or 13.x
- Filament 5.x
- Livewire 4.x
- Tailwind CSS v4

## Installation

### 1. Require the package via Composer

```bash
composer require khoirulaksara/awrel
```

### 2. Register the Service Provider

The package auto-discovers its service provider via Composer. If auto-discovery is disabled, add it to `bootstrap/providers.php`:

```php
<?php

return [
    // ...
    Khoirulaksara\Awrel\AwrelThemeServiceProvider::class,
];
```

### 3. Run the install command

```bash
php artisan awrel:install
```

This will:
- Publish the config file
- Publish views
- Publish CSS
- Publish JS
- Publish public assets (favicon spinner)
- Run the database migration
- Seed default settings

### 4. Add the plugin to your Filament panel

In `app/Providers/Filament/AdminPanelProvider.php`:

```php
use Khoirulaksara\Awrel\AwrelPlugin;

// Add to your panel configuration:
$panel
    ->plugin(
        AwrelPlugin::make()
            ->faviconSpinner()        // Enable animated favicon spinner (opt-in)
            ->stickyTableActions()     // Enable sticky table actions (opt-in)
    );
```

### 5. Set the Vite theme

```php
$panel
    ->viteTheme('resources/css/filament/admin/theme.css')
```

Or if you published the CSS:

```php
$panel
    ->viteTheme('resources/css/vendor/awrel/filament/admin/theme.css')
```

### 6. Run database migrations

```bash
php artisan migrate
```

### 7. Build assets

```bash
npm run build
```

### 8. Login and configure

Navigate to **Settings → Awrel Theme Settings** in your Filament admin panel to customize colors, fonts, border radius, and layout options.

## Configuration

### Env Variables

| Variable | Default | Description |
|----------|---------|-------------|
| `AWREL_FAVICON_SPINNER` | `false` | Enable favicon spinner on navigation |
| `AWREL_STICKY_TABLE_ACTIONS` | `false` | Enable sticky table actions column |
| `AWREL_PRIMARY_COLOR` | `#f59e0b` | Default primary color (amber) |
| `AWREL_FONT_FAMILY` | `Plus Jakarta Sans` | Default font family |
| `AWREL_BORDER_RADIUS` | `2xl` | Default border radius |
| `AWREL_SIDEBAR_WIDTH` | `256` | Default sidebar width in pixels |

### Plugin Options

```php
AwrelPlugin::make()
    ->faviconSpinner()              // Enable animated favicon spinner
    ->stickyTableActions()          // Enable sticky table actions column
```

Both options can also be toggled from the Theme Settings page in the admin panel.

## Features Detail

### Skeleton Loaders

Automatic skeleton placeholders for:
- **Tables** — Replaces Filament's default spinner with a realistic table skeleton (header row + 8 body rows with pulsing content bars). Works on any table with `deferLoading()` enabled.
- **Stats Overview** — Shows animated stat cards matching the real widget layout while data loads.

No configuration needed — works automatically with `deferLoading()`.

### Sticky Table Actions

When enabled, the actions column pins to the right edge when tables overflow horizontally. Includes custom scrollbar styling and drag-to-scroll support.

Enable on the plugin:
```php
AwrelPlugin::make()->stickyTableActions();
```

### Loading Bar

A lightweight progress bar at the top of the viewport that activates on Livewire SPA navigation. Always-on, no configuration needed.

### Animated Favicon Spinner

Replaces the browser tab favicon with an animated SVG spinner during Livewire navigation requests, then restores the original favicon when the request finishes. Uses your panel's primary color.

Enable on the plugin:
```php
AwrelPlugin::make()->faviconSpinner();
```

### Disabled Button Shake

A snappy side-to-side shake micro-interaction when clicking disabled buttons. Always-on, CSS and JS included by default.

### Glassmorphism Topbar

Modern frosted-glass effect (`backdrop-filter: blur()`) on the top navigation bar with semi-transparent background that adapts to light/dark mode.

## Customization

### Theme Settings Page

Navigate to **Settings → Awrel Theme Settings** in your Filament admin panel to customize:

- **Primary Color** — Pick any color with the color picker or presets
- **Font Family** — Choose from Plus Jakarta Sans, Inter, Instrument Sans, or system-ui
- **Border Radius** — sm, md, lg, xl, or 2xl
- **Sidebar Width** — Adjustable from 180px to 400px
- **Favicon Spinner** — Toggle on/off
- **Sticky Table Actions** — Toggle on/off

### CSS Custom Properties

The theme exposes CSS custom properties that you can override:

```css
:root {
    --awrel-font-family: "Plus Jakarta Sans", ui-sans-serif, system-ui, sans-serif;
    --awrel-border-radius: 2xl;
    --awrel-sidebar-width: 256px;
}
```

### Publishing Assets

```bash
# Publish all assets
php artisan vendor:publish --tag=awrel-config
php artisan vendor:publish --tag=awrel-views
php artisan vendor:publish --tag=awrel-css
php artisan vendor:publish --tag=awrel-js
php artisan vendor:publish --tag=awrel-public

# Or publish specific tags
php artisan vendor:publish --tag=awrel-config
```



## License

MIT

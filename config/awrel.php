<?php

return [
    'favicon_spinner' => env('AWREL_FAVICON_SPINNER', false),
    'sticky_table_actions' => env('AWREL_STICKY_TABLE_ACTIONS', false),
    'primary_color' => env('AWREL_PRIMARY_COLOR', '#f59e0b'),
    'font_family' => env('AWREL_FONT_FAMILY', 'Plus Jakarta Sans'),
    'border_radius' => env('AWREL_BORDER_RADIUS', '2xl'),
    'sidebar_width' => env('AWREL_SIDEBAR_WIDTH', 256),

    /*
    |--------------------------------------------------------------------------
    | Branding
    |--------------------------------------------------------------------------
    */
    'logo_path' => null,

    /*
    |--------------------------------------------------------------------------
    | Login Page
    |--------------------------------------------------------------------------
    */
    'login_layout' => env('AWREL_LOGIN_LAYOUT', 'centered'),
    'login_background_color' => env('AWREL_LOGIN_BG_COLOR', null),
    'login_background_image' => null,

    /*
    |--------------------------------------------------------------------------
    | Layout Variants
    |--------------------------------------------------------------------------
    */
    'layout_variant' => env('AWREL_LAYOUT_VARIANT', 'sidebar'),
    'boxed_layout' => env('AWREL_BOXED_LAYOUT', false),
    'sidebar_position' => env('AWREL_SIDEBAR_POSITION', 'left'),

    /*
    |--------------------------------------------------------------------------
    | Theme Presets
    |--------------------------------------------------------------------------
    */
    'presets' => [
        'amber' => [
            'name' => 'Amber',
            'description' => 'Warm and energetic default',
            'primary_color' => '#f59e0b',
            'font_family' => 'Plus Jakarta Sans',
            'border_radius' => '2xl',
        ],
        'ocean' => [
            'name' => 'Ocean',
            'description' => 'Cool blue tones',
            'primary_color' => '#0ea5e9',
            'font_family' => 'Inter',
            'border_radius' => 'xl',
        ],
        'emerald' => [
            'name' => 'Emerald',
            'description' => 'Fresh and natural',
            'primary_color' => '#10b981',
            'font_family' => 'Instrument Sans',
            'border_radius' => 'lg',
        ],
        'rose' => [
            'name' => 'Rose',
            'description' => 'Bold and elegant',
            'primary_color' => '#e11d48',
            'font_family' => 'Plus Jakarta Sans',
            'border_radius' => 'md',
        ],
        'violet' => [
            'name' => 'Violet',
            'description' => 'Creative and modern',
            'primary_color' => '#8b5cf6',
            'font_family' => 'system-ui',
            'border_radius' => '2xl',
        ],
        'slate' => [
            'name' => 'Slate',
            'description' => 'Minimal monochrome',
            'primary_color' => '#64748b',
            'font_family' => 'Inter',
            'border_radius' => 'sm',
        ],
    ],
];

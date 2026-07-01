<?php

return [
    'favicon_spinner' => env('AWREL_FAVICON_SPINNER', false),
    'sticky_table_actions' => env('AWREL_STICKY_TABLE_ACTIONS', false),
    'loading_bar' => env('AWREL_LOADING_BAR', true),
    'page_transition' => env('AWREL_PAGE_TRANSITION', true),
    'button_submit_loading' => env('AWREL_BUTTON_SUBMIT_LOADING', true),
    'unsaved_changes_guard' => env('AWREL_UNSAVED_CHANGES_GUARD', true),
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
];

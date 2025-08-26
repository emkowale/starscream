<?php
/*
 * File: inc/admin/customizer.php
 * Path: /wp-content/themes/starscream/inc/admin/customizer.php
 * Description: Theme Customizer panel: Starscream options (logo, colors, font, contact, hero video, socials).
 * Theme: Starscream
 * Author: Eric Kowalewski
 * Version: 1.4.23
 * Last Updated: 2025-08-26 — 10:12 EDT
 */

// Keep the existing section ID 'beartraxs_colors' to preserve saved settings.
add_action('customize_register', function ($wp_customize) {

    // ---------------------------------------
    // Section
    // ---------------------------------------
    $wp_customize->add_section('beartraxs_colors', [
        'title'    => 'Starscream Options',
        'priority' => 30,
    ]);

    // ---------------------------------------
    // Settings
    // ---------------------------------------
    // Company logo (attachment ID)
    $wp_customize->add_setting('company_logo_id', [
        'default'           => 0,
        'sanitize_callback' => 'absint',
    ]);

    // Colors
    $wp_customize->add_setting('header_bg_color',          ['default' => '#eeeeee', 'sanitize_callback' => 'sanitize_hex_color']);
    $wp_customize->add_setting('footer_bg_color',          ['default' => '#eeeeee', 'sanitize_callback' => 'sanitize_hex_color']);
    $wp_customize->add_setting('header_footer_text_color', ['default' => '#000000', 'sanitize_callback' => 'sanitize_hex_color']);

    // Font choice
    $wp_customize->add_setting('header_footer_font', [
        'default'           => 'Roboto',
        'sanitize_callback' => function($value){
            $allowed = ['Roboto','Open Sans','Montserrat','Lato','Oswald','Raleway','Poppins','Nunito','Inter','PT Sans'];
            return in_array($value, $allowed, true) ? $value : 'Roboto';
        }
    ]);

    // Accent color
    $wp_customize->add_setting('accent_color', [
        'default'           => '#0073aa',
        'sanitize_callback' => 'sanitize_hex_color'
    ]);

    // Contact + hero
    $wp_customize->add_setting('phone_number',  ['default' => 'xxx-xxx-xxxx',   'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_setting('email_address', ['default' => 'you@example.com', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_setting('hero_video_url',['default' => '',                'sanitize_callback' => 'esc_url_raw']);

    // ---------------------------------------
    // Controls
    // ---------------------------------------

    // Company Logo (prefer Media picker; fallback to Image picker if class not available)
    if (class_exists('WP_Customize_Media_Control')) {
        $wp_customize->add_control(new WP_Customize_Media_Control($wp_customize, 'company_logo_id', [
            'label'       => 'Company Logo',
            'description' => 'Choose a logo from the Media Library.',
            'section'     => 'beartraxs_colors',
            'mime_type'   => 'image',
            'priority'    => 5, // top of section
        ]));
    } else {
        $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'company_logo_id', [
            'label'       => 'Company Logo',
            'description' => 'Choose a logo from the Media Library.',
            'section'     => 'beartraxs_colors',
            'priority'    => 5,
        ]));
    }

    // Header BG
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'header_bg_color', [
        'label'    => 'Header Background Color',
        'section'  => 'beartraxs_colors',
        'settings' => 'header_bg_color',
        'priority' => 10,
    ]));

    // Footer BG
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'footer_bg_color', [
        'label'    => 'Footer Background Color',
        'section'  => 'beartraxs_colors',
        'settings' => 'footer_bg_color',
        'priority' => 20,
    ]));

    // Header/Footer Text
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'header_footer_text_color', [
        'label'    => 'Header & Footer Text Color',
        'section'  => 'beartraxs_colors',
        'settings' => 'header_footer_text_color',
        'priority' => 30,
    ]));

    // Header/Footer Font
    $wp_customize->add_control('header_footer_font', [
        'label'    => 'Header & Footer Font',
        'section'  => 'beartraxs_colors',
        'type'     => 'select',
        'choices'  => [
            'Roboto'     => 'Roboto',
            'Open Sans'  => 'Open Sans',
            'Montserrat' => 'Montserrat',
            'Lato'       => 'Lato',
            'Oswald'     => 'Oswald',
            'Raleway'    => 'Raleway',
            'Poppins'    => 'Poppins',
            'Nunito'     => 'Nunito',
            'Inter'      => 'Inter',
            'PT Sans'    => 'PT Sans',
        ],
        'priority' => 40,
    ]);

    // Accent
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'accent_color', [
        'label'    => 'Accent Color (Icons & Links)',
        'section'  => 'beartraxs_colors',
        'settings' => 'accent_color',
        'priority' => 50,
    ]));

    // Contact
    $wp_customize->add_control('phone_number', [
        'label'    => 'Phone Number',
        'section'  => 'beartraxs_colors',
        'type'     => 'text',
        'priority' => 60,
    ]);

    $wp_customize->add_control('email_address', [
        'label'    => 'Email Address',
        'section'  => 'beartraxs_colors',
        'type'     => 'text',
        'priority' => 70,
    ]);

    // Hero video (blank = no hero)
    $wp_customize->add_control('hero_video_url', [
        'label'    => 'Hero Video URL',
        'section'  => 'beartraxs_colors',
        'type'     => 'text',
        'priority' => 80,
    ]);

    // Socials (four slots)
    for ($i = 1; $i <= 4; $i++) {
        $base = 100 + ($i - 1) * 10;

        // Settings for each social slot
        $wp_customize->add_setting("social_icon_$i", [
            'default'           => '',
            'sanitize_callback' => function($val){
                $allowed = [
                    'fab fa-facebook-f','fab fa-youtube','fab fa-instagram','fab fa-twitter',
                    'fab fa-pinterest','fab fa-linkedin-in','fab fa-tiktok','fab fa-snapchat-ghost',
                    'fab fa-discord','fab fa-reddit-alien'
                ];
                return in_array($val, $allowed, true) ? $val : '';
            }
        ]);
        $wp_customize->add_setting("social_url_$i",  [
            'default'           => '',
            'sanitize_callback' => 'esc_url_raw'
        ]);

        // Controls
        $wp_customize->add_control("social_icon_$i", [
            'label'    => "Social Icon $i (Font Awesome class)",
            'section'  => 'beartraxs_colors',
            'type'     => 'select',
            'choices'  => [
                'fab fa-facebook-f'     => 'Facebook',
                'fab fa-youtube'        => 'YouTube',
                'fab fa-instagram'      => 'Instagram',
                'fab fa-twitter'        => 'Twitter',
                'fab fa-pinterest'      => 'Pinterest',
                'fab fa-linkedin-in'    => 'LinkedIn',
                'fab fa-tiktok'         => 'TikTok',
                'fab fa-snapchat-ghost' => 'Snapchat',
                'fab fa-discord'        => 'Discord',
                'fab fa-reddit-alien'   => 'Reddit',
            ],
            'priority' => $base,
        ]);

        $wp_customize->add_control("social_url_$i", [
            'label'    => "Social Icon $i URL",
            'section'  => 'beartraxs_colors',
            'type'     => 'text',
            'priority' => $base + 5,
        ]);
    }
});

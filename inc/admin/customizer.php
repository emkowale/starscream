<?php
/*
 * File: inc/admin/customizer.php
 * Path: /wp-content/themes/starscream/inc/admin/customizer.php
 * Description: Theme Customizer panel: options (logo, colors, font, contact, hero video, socials) + font helpers & CSS.
 * Theme: Starscream
 * Author: Eric Kowalewski
 * Version: 1.4.23
 * Last Updated: 2025-08-28 — 12:58 EDT
 */

// -----------------------------
// Customizer settings & controls
// -----------------------------
add_action('customize_register', function ($wp_customize) {

    // Keep section ID to preserve saved settings across versions.
    $wp_customize->add_section('beartraxs_colors', [
        'title'    => 'Starscream Options',
        'priority' => 30,
    ]);

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

    // Controls
    if (class_exists('WP_Customize_Media_Control')) {
        $wp_customize->add_control(new WP_Customize_Media_Control($wp_customize, 'company_logo_id', [
            'label'       => 'Company Logo',
            'description' => 'Choose a logo from the Media Library.',
            'section'     => 'beartraxs_colors',
            'mime_type'   => 'image',
            'priority'    => 5,
        ]));
    } else {
        $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'company_logo_id', [
            'label'       => 'Company Logo',
            'description' => 'Choose a logo from the Media Library.',
            'section'     => 'beartraxs_colors',
            'priority'    => 5,
        ]));
    }

    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'header_bg_color', [
        'label'    => 'Header Background Color',
        'section'  => 'beartraxs_colors',
        'settings' => 'header_bg_color',
        'priority' => 10,
    ]));

    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'footer_bg_color', [
        'label'    => 'Footer Background Color',
        'section'  => 'beartraxs_colors',
        'settings' => 'footer_bg_color',
        'priority' => 20,
    ]));

    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'header_footer_text_color', [
        'label'    => 'Header & Footer Text Color',
        'section'  => 'beartraxs_colors',
        'settings' => 'header_footer_text_color',
        'priority' => 30,
    ]));

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

    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'accent_color', [
        'label'    => 'Accent Color (Icons & Links)',
        'section'  => 'beartraxs_colors',
        'settings' => 'accent_color',
        'priority' => 50,
    ]));

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

    $wp_customize->add_control('hero_video_url', [
        'label'    => 'Hero Video URL',
        'section'  => 'beartraxs_colors',
        'type'     => 'text',
        'priority' => 80,
    ]);

    // Socials (four slots)
    for ($i = 1; $i <= 4; $i++) {
        $base = 100 + ($i - 1) * 10;

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

// -----------------------------
// Font helpers + CSS application
// -----------------------------
if ( ! function_exists('btx_get_selected_font_name') ) {
  function btx_get_selected_font_name() {
    // Try both setting IDs; default to Inter to ensure a modern stack
    $font = get_theme_mod('btx_header_footer_font');
    if (!$font) { $font = get_theme_mod('header_footer_font'); }
    if (!$font || !is_string($font)) { $font = 'Inter'; }
    return trim($font);
  }
}

if ( ! function_exists('btx_font_stack_for') ) {
  function btx_font_stack_for( $font ) {
    $stacks = [
      'Inter'       => '"Inter", system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif',
      'Roboto'      => '"Roboto", system-ui, -apple-system, "Segoe UI", Arial, sans-serif',
      'Poppins'     => '"Poppins", system-ui, -apple-system, "Segoe UI", Arial, sans-serif',
      'Lato'        => '"Lato", system-ui, -apple-system, "Segoe UI", Arial, sans-serif',
      'Montserrat'  => '"Montserrat", system-ui, -apple-system, "Segoe UI", Arial, sans-serif',
      'Open Sans'   => '"Open Sans", system-ui, -apple-system, "Segoe UI", Arial, sans-serif',
      'Oswald'      => '"Oswald", system-ui, -apple-system, "Segoe UI", Arial, sans-serif',
      'Nunito'      => '"Nunito", system-ui, -apple-system, "Segoe UI", Arial, sans-serif',
      'Raleway'     => '"Raleway", system-ui, -apple-system, "Segoe UI", Arial, sans-serif',
      'PT Sans'     => '"PT Sans", system-ui, -apple-system, "Segoe UI", Arial, sans-serif',
    ];
    return isset($stacks[$font])
      ? $stacks[$font]
      : '"' . esc_attr($font) . '", system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif';
  }
}

if ( ! function_exists('btx_enqueue_selected_font') ) {
  function btx_enqueue_selected_font() {
    $font = btx_get_selected_font_name();
    $google_fonts = ['Inter','Roboto','Poppins','Lato','Montserrat','Open Sans','Oswald','Nunito','Raleway','PT Sans'];
    if ( in_array($font, $google_fonts, true) ) {
      $family = str_replace(' ', '+', $font);
      wp_enqueue_style(
        'btx-google-font-' . sanitize_title($font),
        'https://fonts.googleapis.com/css2?family=' . rawurlencode($family) . ':wght@300;400;500;600;700;800;900&display=swap',
        [],
        null
      );
    }
  }
  add_action('wp_enqueue_scripts', 'btx_enqueue_selected_font');
}

if ( ! function_exists('btx_print_font_css') ) {
  function btx_print_font_css() {
    $font       = btx_get_selected_font_name();
    $font_stack = btx_font_stack_for($font);
    ?>
    <style id="btx-site-font-css">
      :root{ --header-footer-font: <?php echo $font_stack; ?>; }
      header, .site-header, .main-header, .page-header,
      nav, .main-navigation, .topbar,
      footer, .site-footer {
        font-family: var(--header-footer-font) !important;
      }
      /* Optional: apply globally
      html, body, .woocommerce, .woocommerce * { font-family: var(--header-footer-font) !important; } */
    </style>
    <?php
  }
  add_action('wp_head', 'btx_print_font_css', 99);
}

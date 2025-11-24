<?php
if (!defined('ABSPATH')) exit;

/**
 * Login screen branding: pull colors from Starscream Customizer and reuse the site logo.
 * - Background uses header background color
 * - Button uses accent color
 * - Logo uses the site's custom logo (falls back to WP default)
 */
add_action('login_enqueue_scripts', function () {
  $accent      = sanitize_hex_color(get_theme_mod('accent_color', '#0073aa')) ?: '#0073aa';
  $header_bg   = sanitize_hex_color(get_theme_mod('header_bg_color', '#eeeeee')) ?: '#eeeeee';
  $logo_src    = '';
  $custom_logo = (int) get_theme_mod('custom_logo', 0);

  if ($custom_logo) {
    $img = wp_get_attachment_image_src($custom_logo, 'full');
    if (!empty($img[0])) $logo_src = esc_url_raw($img[0]);
  }

  if (!$logo_src) {
    // Fallback to WordPress core logo
    $logo_src = esc_url_raw(includes_url('images/w-logo-blue.png'));
  }

  $css = "
    body.login {
      background-color: {$header_bg} !important;
      font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif !important;
    }
    #login h1 a {
      background-image: url('{$logo_src}') !important;
      background-size: contain !important;
      background-repeat: no-repeat !important;
      width: 200px !important;
      height: 200px !important;
      margin: 0 auto 20px auto !important;
    }
    .login form {
      border-radius: 0 !important;
      border: none !important;
      box-shadow: 0 6px 20px rgba(0,0,0,0.06);
    }
    .login #wp-submit {
      background-color: {$accent} !important;
      border: 1px solid {$accent} !important;
      color: #fff !important;
      font-weight: 700 !important;
      border-radius: 0 !important;
      text-transform: uppercase !important;
      padding: 10px 20px !important;
      transition: filter .2s ease;
    }
    .login #wp-submit:hover {
      filter: brightness(0.9);
    }
    .login #nav a, .login #backtoblog a {
      color: #000 !important;
    }
  ";

  wp_register_style('starscream-login', false);
  wp_enqueue_style('starscream-login');
  wp_add_inline_style('starscream-login', $css);
});

// Clicking the logo returns to the site front page.
add_filter('login_headerurl', function () {
  return home_url('/');
});

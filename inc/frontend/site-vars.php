<?php
/*
 * File: inc/frontend/site-vars.php
 * Path: /wp-content/themes/starscream/inc/frontend/site-vars.php
 * Description: Prints CSS custom properties in <head> based on Customizer settings.
 * Theme: Starscream
 * Author: Eric Kowalewski
 * Version: 1.4.23
 * Last Updated: 2025-08-26 — 10:45 EDT
 */

if (!function_exists('starscream_print_design_vars')) {
  function starscream_print_design_vars() {
    // Font stack (use your helpers if present)
    if (function_exists('btx_get_selected_font_name')) {
      $font = btx_get_selected_font_name();
    } else {
      $font = get_theme_mod('header_footer_font', 'Roboto');
    }
    if (function_exists('btx_font_stack_for')) {
      $font_stack = btx_font_stack_for($font);
    } else {
      $font_stack = '"' . esc_attr($font) . '", system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif';
    }

    $header_bg = get_theme_mod('header_bg_color', '#eeeeee');
    $text_col  = get_theme_mod('header_footer_text_color', '#000000');
    $accent    = get_theme_mod('accent_color', '#0073aa');
    $logo_h    = '100px'; // adjust later via Customizer if you want

    ?>
    <style id="starscream-vars">
      :root{
        --header-footer-font: <?php echo $font_stack; ?>;
        --header-bg-color: <?php echo esc_html($header_bg); ?>;
        --header-text-color: <?php echo esc_html($text_col); ?>;
        --accent-color: <?php echo esc_html($accent); ?>;
        --logo-max-h: <?php echo esc_html($logo_h); ?>;
      }
    </style>
    <?php
  }
  // Print early so variables are available to all CSS
  add_action('wp_head', 'starscream_print_design_vars', 20);
}

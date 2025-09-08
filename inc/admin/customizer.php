<?php
/**
 * Starscream Customizer loader (keeps each part under 100 lines)
 * Ensures the section exists, then loads modular files.
 */
if (!defined('ABSPATH')) exit;

/** Create the section early so subfiles can attach controls to it */
add_action('customize_register', function($wp_customize){
  if (!isset($wp_customize)) return;
  if (!$wp_customize->get_section('beartraxs_colors')) {
    $wp_customize->add_section('beartraxs_colors', [
      'title'    => 'Starscream Options',
      'priority' => 30,
    ]);
  }
}, 1);

/** Helper: child-first require */
if (!function_exists('starscream_customizer_require')) {
  function starscream_customizer_require($rel) {
    if (function_exists('starscream_locate')) {
      $p = starscream_locate($rel);
      if ($p && file_exists($p)) { require_once $p; return; }
    }
    $fallback = get_template_directory() . '/' . ltrim($rel, '/');
    if (file_exists($fallback)) require_once $fallback;
  }
}

/** Load each small module (each file should hook its own controls/settings) */
$parts = [
  'inc/admin/customizer/helpers.php',
  'inc/admin/customizer/logo.php',
  'inc/admin/customizer/colors.php',       // header_bg, footer_bg, header_text, footer_text, accent
  'inc/admin/customizer/fonts.php',        // header_footer_font
  'inc/admin/customizer/contact_hero.php', // phone, email, hero video
  'inc/admin/customizer/banners.php',      // header/footer banner media, link, alt
  'inc/admin/customizer/socials.php',      // social icon + url slots
];

foreach ($parts as $rel) {
  starscream_customizer_require($rel);
}

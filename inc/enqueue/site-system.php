<?php
if (!defined('ABSPATH')) exit;

/**
 * Reusable design-system assets:
 * - assets/css/site.css        (global section/layout/component primitives)
 * - assets/css/pages.css       (content-page typography/composition helpers)
 * - assets/css/woocommerce.css (WooCommerce foundation layer)
 */
add_action('wp_enqueue_scripts', function () {
  if (!function_exists('starscream_locate') || !function_exists('starscream_asset_uri')) return;

  $theme_version = wp_get_theme()->get('Version');

  $enqueue_style = static function ($handle, $relpath, $deps = []) use ($theme_version) {
    $path = starscream_locate($relpath);
    if (!$path || !file_exists($path)) return;

    $version = filemtime($path);
    if (!$version) $version = $theme_version;

    wp_enqueue_style(
      $handle,
      starscream_asset_uri($relpath),
      $deps,
      $version
    );
  };

  $enqueue_style('starscream-site', 'assets/css/site.css', ['starscream-base']);
  $enqueue_style('starscream-pages', 'assets/css/pages.css', ['starscream-site']);

  $is_woo_context = false;
  if (function_exists('is_woocommerce') && is_woocommerce()) $is_woo_context = true;
  if (function_exists('is_cart') && is_cart()) $is_woo_context = true;
  if (function_exists('is_checkout') && is_checkout()) $is_woo_context = true;
  if (function_exists('is_account_page') && is_account_page()) $is_woo_context = true;

  if ($is_woo_context) {
    $enqueue_style(
      'starscream-site-woocommerce',
      'assets/css/woocommerce.css',
      ['starscream-site', 'starscream-buttons', 'starscream-checkout']
    );
  }
}, 60);

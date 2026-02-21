<?php
if (!defined('ABSPATH')) exit;

/**
 * Force permalinks to Post name and Woo product base /product/%product_cat%/.
 * Queue a one-time rewrite flush so changes take effect reliably.
 */
add_action('init', function () {
  $needs_flush = false;

  $desired_structure = '/%postname%/';
  if (get_option('permalink_structure') !== $desired_structure) {
    update_option('permalink_structure', $desired_structure);
    $needs_flush = true;
  }

  $woo_permalinks = get_option('woocommerce_permalinks', []);
  if (!is_array($woo_permalinks)) $woo_permalinks = [];
  $desired_product_base = '/product/%product_cat%/';
  if (!isset($woo_permalinks['product_base']) || $woo_permalinks['product_base'] !== $desired_product_base) {
    $woo_permalinks['product_base'] = $desired_product_base;
    update_option('woocommerce_permalinks', $woo_permalinks);
    $needs_flush = true;
  }

  $flush_flag = 'starscream_permalinks_flush_required';
  $flushed_ver_key = 'starscream_permalinks_flushed_for_version';
  $theme = wp_get_theme(get_template());
  $theme_version = (string) $theme->get('Version');
  $last_flushed_for = (string) get_option($flushed_ver_key, '');

  // Force one flush per theme version, and whenever permalink options were changed.
  if ($needs_flush || ($theme_version !== '' && $last_flushed_for !== $theme_version)) {
    update_option($flush_flag, '1', false);
  }
}, 1);

add_action('wp_loaded', function () {
  $flush_flag = 'starscream_permalinks_flush_required';
  if (get_option($flush_flag) !== '1') return;

  flush_rewrite_rules(false);
  delete_option($flush_flag);

  $flushed_ver_key = 'starscream_permalinks_flushed_for_version';
  $theme = wp_get_theme(get_template());
  $theme_version = (string) $theme->get('Version');
  if ($theme_version !== '') update_option($flushed_ver_key, $theme_version, false);
}, 99);

// Force a rewrite flush after switching to this theme.
add_action('after_switch_theme', function () {
  update_option('starscream_permalinks_flush_required', '1', false);
  delete_option('starscream_permalinks_flushed_for_version');
});

// Force a rewrite flush after this theme is updated.
add_action('upgrader_process_complete', function ($upgrader, $hook_extra) {
  if (empty($hook_extra['type']) || $hook_extra['type'] !== 'theme') return;
  $updated = $hook_extra['themes'] ?? [];
  if (!is_array($updated) || empty($updated)) return;

  $template = (string) get_template();
  $stylesheet = (string) get_stylesheet();
  if (!in_array($template, $updated, true) && !in_array($stylesheet, $updated, true)) return;

  update_option('starscream_permalinks_flush_required', '1', false);
  delete_option('starscream_permalinks_flushed_for_version');
}, 10, 2);

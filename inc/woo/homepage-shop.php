<?php
if (!defined('ABSPATH')) exit;

/**
 * Seed Shop as the default homepage once.
 * Do not overwrite user changes after initial setup.
 */
add_action('init', function () {
  $seed_key = 'starscream_homepage_shop_seeded';
  if (get_option($seed_key) === '1') return;

  $show_on_front = (string) get_option('show_on_front', 'posts');
  $page_on_front = (int) get_option('page_on_front', 0);

  // If a static homepage is already selected, preserve it and stop seeding.
  if ($show_on_front === 'page' && $page_on_front > 0) {
    update_option($seed_key, '1', false);
    return;
  }

  if (!function_exists('wc_get_page_id')) return;
  $shop_id = (int) wc_get_page_id('shop');
  if ($shop_id <= 0) return;

  // Only set Shop by default when no static front page has been assigned yet.
  if ($page_on_front <= 0) {
    update_option('show_on_front', 'page');
    update_option('page_on_front', $shop_id);
  }

  update_option($seed_key, '1', false);
}, 20);

// Re-run the one-time seed when this theme is activated.
add_action('after_switch_theme', function () {
  delete_option('starscream_homepage_shop_seeded');
});

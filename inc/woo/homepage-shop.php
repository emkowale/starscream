<?php
if (!defined('ABSPATH')) exit;

// Force the homepage to use the Shop page as a static front page.
add_action('init', function () {
  if (!function_exists('wc_get_page_id')) return;
  $shop_id = (int) wc_get_page_id('shop');
  if ($shop_id <= 0) return;

  if (get_option('show_on_front') !== 'page' || (int) get_option('page_on_front') !== $shop_id) {
    update_option('show_on_front', 'page');
    update_option('page_on_front', $shop_id);
  }
});

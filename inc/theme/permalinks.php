<?php
if (!defined('ABSPATH')) exit;

/**
 * Force permalinks to Post name and Woo product base /product/%product_cat%/.
 * Flushes rewrite rules only when a change is applied.
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

  if ($needs_flush) flush_rewrite_rules(false);
});

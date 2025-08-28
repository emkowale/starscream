<?php
/*
 * Module: Woo Extras
 * Purpose: Remove My Account "Downloads" + prevent duplicate related products
 * Last Updated: 2025-08-13
 */

if (!defined('ABSPATH')) exit;

/* 4) Remove "Downloads" from My Account menu */
add_filter('woocommerce_account_menu_items', function($items){
  unset($items['downloads']);   // hide Downloads tab
  return $items;
}, 20);

/* 9) De-duplicate "Related products" on product pages */
add_action('after_setup_theme', function () {
  // Woo default hook prints related products. Some themes also add their own → duplicates.
  remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);
});

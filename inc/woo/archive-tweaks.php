<?php
/*
 * File: inc/woo/archive-tweaks.php
 * Description: Remove add-to-cart on archive tiles, set columns & thumbnail size.
 * Plugin: Starscream
 * Author: Eric Kowalewski
 * Last Updated: 2025-09-02 21:35 EDT
 */
if (!defined('ABSPATH')) exit;

add_action('init', function () {
  remove_action('woocommerce_after_shop_loop_item','woocommerce_template_loop_add_to_cart',10);
}, 20);

add_filter('loop_shop_columns', fn($c)=>3, 99);
add_filter('single_product_archive_thumbnail_size', fn($s)=>'large', 99);

// Hide the default "Shop" H1 on archive pages
add_filter('woocommerce_show_page_title', '__return_false');

// Hide "Showing x results" (and optionally the sort dropdown)
add_action('init', function () {
    remove_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
    // Also hide the sort dropdown (uncomment if desired)
    // remove_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);
});

// Force the default catalog order back to WooCommerce manual sorting.
add_filter('woocommerce_default_catalog_orderby', function ($orderby) {
    return 'menu_order';
}, 99);

// Keep WooCommerce-selected ordering intact, but normalize the default/manual mode.
add_filter('woocommerce_get_catalog_ordering_args', function ($args, $orderby, $order) {
    if ($orderby !== 'menu_order') {
        return $args;
    }

    $args['orderby'] = 'menu_order title';
    $args['order']   = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';

    return $args;
}, 99, 3);

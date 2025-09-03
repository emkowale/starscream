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

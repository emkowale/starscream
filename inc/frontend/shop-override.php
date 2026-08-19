<?php
/*
 * File: inc/frontpage/shop-override.php
 * Description: Front page → Shop template with hero + product loop.
 * Plugin: Starscream
 * Author: Eric Kowalewski
 * Last Updated: 2025-09-02 21:35 EDT
 */
if (!defined('ABSPATH')) exit;

function starscream_get_catalog_query_args_for_loop() {
  $ordering_args = function_exists('WC') && WC()->query
    ? WC()->query->get_catalog_ordering_args()
    : ['orderby' => 'menu_order title', 'order' => 'ASC', 'meta_key' => ''];

  $query_args = [
    'post_type'   => 'product',
    'post_status' => 'publish',
    'paged'       => max(1, (int) get_query_var('paged', 0), (int) get_query_var('page', 0)),
    'orderby'     => $ordering_args['orderby'] ?? 'menu_order title',
    'order'       => $ordering_args['order'] ?? 'ASC',
  ];

  if (!empty($ordering_args['meta_key'])) {
    $query_args['meta_key'] = $ordering_args['meta_key'];
  }

  return $query_args;
}

add_action('template_redirect', function () {
  if (!function_exists('is_shop') || !is_shop() || !is_front_page()) return;

  get_header();
  echo "\n<!-- BTX FORCE: front-page Shop override ran (no sidebar) -->\n";

  $hero = starscream_get_hero_url();
  if (!empty($hero)) starscream_render_hero($hero);

  do_action('woocommerce_before_main_content');

  $loop = new WP_Query(starscream_get_catalog_query_args_for_loop());

  if ($loop->have_posts()){
    woocommerce_product_loop_start();
    while ($loop->have_posts()){ $loop->the_post(); do_action('woocommerce_shop_loop'); wc_get_template_part('content','product'); }
    woocommerce_product_loop_end();

    wp_reset_postdata();
    // Use WooCommerce's built-in pagination output.
    do_action('woocommerce_after_shop_loop');
  } else {
    do_action('woocommerce_no_products_found');
  }

  do_action('woocommerce_after_main_content');
  get_footer();
  exit;
}, 1);

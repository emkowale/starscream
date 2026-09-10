<?php
/*
 * File: inc/frontpage/shop-override.php
 * Description: Front page → Shop template with hero + product loop.
 * Plugin: Starscream
 * Author: Eric Kowalewski
 * Last Updated: 2025-09-02 21:35 EDT
 */
if (!defined('ABSPATH')) exit;

add_action('template_redirect', function () {
  if (!function_exists('is_shop') || !is_shop() || !is_front_page()) return;

  get_header();
  echo "\n<!-- BTX FORCE: front-page Shop override ran (no sidebar) -->\n";

  $hero = starscream_get_hero_url();
  if (!empty($hero)) starscream_render_hero($hero);

  do_action('woocommerce_before_main_content');
  do_action('woocommerce_archive_description');

  if (woocommerce_product_loop()){
    woocommerce_product_loop_start();
    while (have_posts()){ the_post(); do_action('woocommerce_shop_loop'); wc_get_template_part('content','product'); }
    woocommerce_product_loop_end();
    do_action('woocommerce_after_shop_loop');
  } else {
    do_action('woocommerce_no_products_found');
  }

  do_action('woocommerce_after_main_content');
  get_footer();
  exit;
}, 1);

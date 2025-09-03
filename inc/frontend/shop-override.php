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

  $paged = max(1, (int)get_query_var('paged',0), (int)get_query_var('page',0));
  $loop  = new WP_Query(['post_type'=>'product','post_status'=>'publish','paged'=>$paged]);

  if ($loop->have_posts()){
    woocommerce_product_loop_start();
    while ($loop->have_posts()){ $loop->the_post(); do_action('woocommerce_shop_loop'); wc_get_template_part('content','product'); }
    woocommerce_product_loop_end();

    echo '<nav class="woocommerce-pagination">';
    echo paginate_links(['total'=>max(1,(int)$loop->max_num_pages),'current'=>$paged]);
    echo '</nav>';

    wp_reset_postdata();
    do_action('woocommerce_after_shop_loop');
  } else {
    do_action('woocommerce_no_products_found');
  }

  do_action('woocommerce_after_main_content');
  get_footer();
  exit;
}, 1);

<?php
/*
 * File: inc/enqueue/vars.php
 * Description: Output CSS vars (accent + image widths/ratios) into <head>.
 * Plugin: Starscream
 * Author: Eric Kowalewski
 * Last Updated: 2025-09-02 21:35 EDT
 */
if (!defined('ABSPATH')) exit;

add_action('wp_head', function () {
  $accent = get_theme_mod('accent_color', '#0073aa');
  if (!preg_match('/^#([0-9a-f]{3}){1,2}$/i', $accent)) $accent = '#0073aa';
  echo '<style id="btx-accent-var">:root{--btx-accent:' . esc_html($accent) . ';}</style>';
}, 101);

add_action('wp_head', function () {
  $crop = get_option('woocommerce_thumbnail_cropping', '1:1');
  $ratio_css='5 / 7';
  if ($crop==='1:1') $ratio_css='1 / 1';
  elseif ($crop==='custom'){
    $w=max(1,(int)get_option('woocommerce_thumbnail_cropping_custom_width',5));
    $h=max(1,(int)get_option('woocommerce_thumbnail_cropping_custom_height',7));
    $ratio_css="$w / $h";
  }
  echo '<style id="btx-woo-image-vars">:root{--btx-thumb-aspect:'.esc_html($ratio_css).';}</style>';
}, 99);

add_action('wp_head', function () {
  $single_w=(int)get_option('woocommerce_single_image_width',600);
  $thumb_w =(int)get_option('woocommerce_thumbnail_image_width',300);
  $single_w=max(200,$single_w); $thumb_w=max(150,$thumb_w);
  ?>
  <style id="btx-woo-image-widths">
    :root{--btx-main-img-width:<?php echo $single_w; ?>px;--btx-thumb-img-width:<?php echo $thumb_w; ?>px;}
    .single-product div.product .woocommerce-product-gallery,
    .single-product div.product .images,
    .single-product div.product .product-gallery,
    .single-product div.product .product-images{max-width:var(--btx-main-img-width)!important;}
    .woocommerce ul/products li.product .btx-card-media{max-width:var(--btx-thumb-img-width)!important;margin-left:auto;margin-right:auto;}
    .woocommerce ul.products li.product .woocommerce-LoopProduct-link img{max-width:var(--btx-thumb-img-width)!important;width:100%;height:auto;}
  </style>
  <?php
}, 100);

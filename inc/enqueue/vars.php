<?php
/*
 * File: inc/enqueue/vars.php
 * Description: Output CSS vars (accent, header/footer colors, image widths/ratios) into <head>.
 * Plugin: Starscream
 * Author: Eric Kowalewski
 * Last Updated: 2025-09-07
 */
if (!defined('ABSPATH')) exit;

add_filter('body_class', function ($classes) {
  if (get_theme_mod('transparent_header_enabled', false)) {
    $classes[] = 'btx-transparent-header-enabled';
  }
  return $classes;
}, 20);

/** 1) Theme color variables (header/footer + accent) */
add_action('wp_head', function () {
  $hex = function($v,$fallback){ return preg_match('/^#(?:[0-9a-f]{3}){1,2}$/i',$v) ? $v : $fallback; };
  $contrast = function($hex_color){
    $hex_color = ltrim($hex_color, '#');
    if (strlen($hex_color) === 3) {
      $hex_color = $hex_color[0].$hex_color[0].$hex_color[1].$hex_color[1].$hex_color[2].$hex_color[2];
    }
    $r = hexdec(substr($hex_color, 0, 2));
    $g = hexdec(substr($hex_color, 2, 2));
    $b = hexdec(substr($hex_color, 4, 2));
    $luma = (0.299 * $r) + (0.587 * $g) + (0.114 * $b);
    return $luma > 160 ? '#111111' : '#ffffff';
  };

  $header_bg  = $hex(get_theme_mod('header_bg_color', '#eeeeee'), '#eeeeee');
  $footer_bg  = $hex(get_theme_mod('footer_bg_color', '#eeeeee'), '#eeeeee');
  $header_txt = $hex(get_theme_mod('header_text_color', '#000000'), '#000000');
  $footer_txt = $hex(get_theme_mod('footer_text_color', $header_txt), $header_txt);
  $accent     = $hex(get_theme_mod('accent_color', '#0073aa'), '#0073aa');
  $announcement_bg  = $hex(get_theme_mod('announcement_bar_bg_color', '#151515'), '#151515');
  $announcement_txt = $contrast($announcement_bg);

  $font_stack = '"' . trim( (string) get_theme_mod('header_footer_font', 'Inter') ) . '", system-ui, -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif';


  echo '<style id="btx-theme-vars">:root{'
      .'--header-bg-color:'.$header_bg.';'
      .'--footer-bg-color:'.$footer_bg.';'
      .'--header-text-color:'.$header_txt.';'
      .'--footer-text-color:'.$footer_txt.';'
      .'--accent-color:'.$accent.';'
      .'--announcement-bar-bg-color:'.$announcement_bg.';'
      .'--announcement-bar-text-color:'.$announcement_txt.';'
      .'--header-footer-font:'.$font_stack.';'
      .'}</style>';
}, 98);

/** 2) Woo thumbnail cropping aspect ratio → --btx-thumb-aspect (unchanged) */
add_action('wp_head', function () {
  $crop = get_option('woocommerce_thumbnail_cropping', '1:1');
  $ratio_css='5 / 7';
  if ($crop==='1:1') $ratio_css='1 / 1';
  elseif ($crop==='custom'){
    $w=max(1,(int)get_option('woocommerce_thumbnail_cropping_custom_width',5));
    $h=max(1,(int)get_option('woocommerce_thumbnail_cropping_custom_height',7));
    $ratio_css="$w / $h";
  }
  echo '<style id="btx-woo-image-vars">:root{--btx-thumb-aspect:'.$ratio_css.';}</style>';
}, 99);

/** 3) Woo single/thumb image widths + helpers (kept; minor selector fix) */
add_action('wp_head', function () {
  $single_w=(int)get_option('woocommerce_single_image_width',600);
  $thumb_w =(int)get_option('woocommerce_thumbnail_image_width',300);
  $single_w=max(200,$single_w); $thumb_w=max(150,$thumb_w);
  ?>
  <style id="btx-woo-image-widths">
    :root{
      --btx-main-img-width:<?php echo $single_w; ?>px;
      --btx-thumb-img-width:<?php echo $thumb_w; ?>px;
    }
    .single-product div.product .woocommerce-product-gallery,
    .single-product div.product .images,
    .single-product div.product .product-gallery,
    .single-product div.product .product-images{
      max-width:var(--btx-main-img-width) !important;
    }
    /* fixed selector: ul.products (not ul/products) */
    .woocommerce ul.products li.product .btx-card-media{
      max-width:var(--btx-thumb-img-width) !important;
      margin-left:auto; margin-right:auto;
    }
    .woocommerce ul.products li.product .woocommerce-LoopProduct-link img{
      max-width:var(--btx-thumb-img-width) !important;
      width:100%; height:auto;
    }
  </style>
  <?php
}, 100);

<?php
/*
 * File: inc/woo/image-sizes.php
 * Description: One-time Woo image defaults + size filters for single/thumbnail/gallery.
 * Plugin: Starscream
 * Author: Eric Kowalewski
 * Last Updated: 2025-09-02 21:35 EDT
 */
if (!defined('ABSPATH')) exit;

add_action('after_setup_theme', function () {
  if (get_option('btx_wc_image_defaults_set')) return;

  update_option('woocommerce_single_image_width',1064);
  update_option('woocommerce_thumbnail_image_width',1064);
  update_option('woocommerce_thumbnail_cropping','custom');
  update_option('woocommerce_thumbnail_cropping_custom_width',5);
  update_option('woocommerce_thumbnail_cropping_custom_height',7);

  update_option('btx_wc_image_defaults_set',1);
}, 11);

add_filter('woocommerce_get_image_size_single', function($size){
  $w=(int)get_option('woocommerce_single_image_width',600);
  return ['width'=>max(200,$w),'height'=>0,'crop'=>0];
}, 10);

add_filter('woocommerce_get_image_size_thumbnail', function($size){
  $w=(int)get_option('woocommerce_thumbnail_image_width',300);
  $crop_setting=get_option('woocommerce_thumbnail_cropping','1:1');
  $crop=($crop_setting!=='uncropped');
  $h=($crop && $crop_setting==='1:1')?max(150,$w):0;
  return ['width'=>max(150,$w),'height'=>$h,'crop'=>$crop];
}, 10);

add_filter('woocommerce_get_image_size_gallery_thumbnail', fn($s)=>['width'=>100,'height'=>0,'crop'=>0],10);

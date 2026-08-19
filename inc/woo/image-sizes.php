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
  update_option('woocommerce_single_image_width', 500);
  update_option('woocommerce_thumbnail_image_width', 500);
  update_option('woocommerce_thumbnail_cropping', '1:1');
  update_option('woocommerce_thumbnail_cropping_custom_width', 1);
  update_option('woocommerce_thumbnail_cropping_custom_height', 1);
}, 11);

add_filter('woocommerce_get_image_size_single', function($size){
  $w = (int) get_option('woocommerce_single_image_width', 500);
  return ['width' => max(200, $w), 'height' => max(200, $w), 'crop' => 1];
}, 10);

add_filter('woocommerce_get_image_size_thumbnail', function($size){
  $w = (int) get_option('woocommerce_thumbnail_image_width', 500);
  return ['width' => max(150, $w), 'height' => max(150, $w), 'crop' => 1];
}, 10);

add_filter('woocommerce_get_image_size_gallery_thumbnail', fn($s)=>['width'=>100,'height'=>0,'crop'=>0],10);

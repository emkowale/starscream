<?php
/*
 * File: inc/enqueue/gallery.php
 * Description: Woo gallery controls—disable lightbox, keep zoom/slider; enqueue JS/CSS with child→parent fallback.
 * Plugin: Starscream
 * Author: Eric Kowalewski
 * Last Updated: 2025-09-02 21:35 EDT
 */
if (!defined('ABSPATH')) exit;

add_action('after_setup_theme', function () {
  add_theme_support('wc-product-gallery-zoom');
  add_theme_support('wc-product-gallery-slider');
  remove_theme_support('wc-product-gallery-lightbox');
}, 999);

add_action('wp_enqueue_scripts', function () {
  if (!function_exists('is_product') || !is_product()) return;

  wp_enqueue_script(
    'starscream-product-gallery',
    starscream_asset_uri('assets/js/product-gallery.js'),
    ['jquery'],
    '1.0.0',
    true
  );

  wp_enqueue_style(
    'starscream-product-gallery',
    starscream_asset_uri('assets/css/product-gallery.css'),
    [],
    '1.0.0'
  );
}, 20);

add_action('wp_enqueue_scripts', function () {
  if (!function_exists('is_product') || !is_product()) return;
  wp_dequeue_script('photoswipe');
  wp_dequeue_script('photoswipe-ui-default');
  wp_dequeue_style('photoswipe');
  wp_dequeue_style('photoswipe-default-skin');
}, 100);

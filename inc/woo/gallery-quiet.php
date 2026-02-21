<?php
if (!defined('ABSPATH')) exit;

/**
 * Quiet gallery:
 * - Disable Photoswipe lightbox (no full-screen)
 * - Keep Woo hover zoom
 * - Make clicks on the main product image do nothing
 * - Pure add-only; no template edits
 */

add_action('after_setup_theme', function () {
  // Ensure zoom is ON; lightbox is OFF.
  add_theme_support('wc-product-gallery-zoom');
  remove_theme_support('wc-product-gallery-lightbox');
}, 20);

// Belt & suspenders: if Photoswipe got enqueued, dequeue it.
add_action('wp_enqueue_scripts', function(){
  wp_dequeue_script('photoswipe');
  wp_dequeue_script('photoswipe-ui-default');
  wp_dequeue_style('photoswipe');

  // Enqueue our tiny CSS/JS
  $css_path = starscream_locate('assets/css/product-gallery-quiet.css');
  if (file_exists($css_path)) {
    $css_uri = (strpos($css_path, get_stylesheet_directory())===0)
      ? trailingslashit(get_stylesheet_directory_uri()).'assets/css/product-gallery-quiet.css'
      : trailingslashit(get_template_directory_uri()).'assets/css/product-gallery-quiet.css';
    wp_enqueue_style('starscream-gallery-quiet', $css_uri, [], filemtime($css_path));
  }

  $js_path = starscream_locate('assets/js/product-gallery-quiet.js');
  if (file_exists($js_path)) {
    $js_uri = (strpos($js_path, get_stylesheet_directory())===0)
      ? trailingslashit(get_stylesheet_directory_uri()).'assets/js/product-gallery-quiet.js'
      : trailingslashit(get_template_directory_uri()).'assets/js/product-gallery-quiet.js';
    wp_enqueue_script('starscream-gallery-quiet', $js_uri, ['jquery'], filemtime($js_path), true);
  }
}, 100);

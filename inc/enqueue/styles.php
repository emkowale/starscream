<?php
/*
 * File: inc/enqueue/styles.php
 * Description: Main stylesheet + footer.css enqueue.
 * Plugin: Starscream
 * Author: Eric Kowalewski
 * Last Updated: 2025-09-02 21:35 EDT
 */
if (!defined('ABSPATH')) exit;

add_action('wp_enqueue_scripts', function () {
  wp_enqueue_style('beartraxs-style', get_stylesheet_uri(), [], '1.4.29');

  $footer_path = starscream_locate('assets/css/footer.css');
  if ($footer_path && file_exists($footer_path)) {
    $ver = @filemtime($footer_path) ?: '1.4.29';
    $uri = starscream_asset_uri('assets/css/footer.css');
    wp_enqueue_style('starscream-footer', $uri, [], $ver);
  }
}, 20);

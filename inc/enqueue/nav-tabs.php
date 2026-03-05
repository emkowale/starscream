<?php
if (!defined('ABSPATH')) exit;

/**
 * Adds a stronger tab-style layer for the primary menu.
 * Loads after nav-lite so it can override layout without changing colors.
 */
add_action('wp_enqueue_scripts', function () {
  // child or parent path/URI resolution
  $path = trailingslashit(get_stylesheet_directory()) . 'assets/css/nav-tabs.css';
  $uri  = trailingslashit(get_stylesheet_directory_uri()) . 'assets/css/nav-tabs.css';
  if (!file_exists($path)) {
    $path = trailingslashit(get_template_directory()) . 'assets/css/nav-tabs.css';
    $uri  = trailingslashit(get_template_directory_uri()) . 'assets/css/nav-tabs.css';
  }
  if (file_exists($path)) {
    // Load after nav-lite; keep color-agnostic
    wp_enqueue_style(
      'starscream-nav-tabs',
      $uri,
      ['starscream-nav-lite'],
      filemtime($path)
    );
  }
}, 110);

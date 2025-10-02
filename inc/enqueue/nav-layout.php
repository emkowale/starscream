<?php
if (!defined('ABSPATH')) exit;

/**
 * Layout-only tweaks:
 * - Desktop: push .site-nav onto its own row under the logo (no DOM edits)
 * - Mobile: make the menu a full-screen overlay when open
 * Loads after nav-lite/nav-tabs so it can override them.
 */
add_action('wp_enqueue_scripts', function () {
  $path = trailingslashit(get_stylesheet_directory()) . 'assets/css/nav-layout.css';
  $uri  = trailingslashit(get_stylesheet_directory_uri()) . 'assets/css/nav-layout.css';

  if (!file_exists($path)) {
    $path = trailingslashit(get_template_directory()) . 'assets/css/nav-layout.css';
    $uri  = trailingslashit(get_template_directory_uri()) . 'assets/css/nav-layout.css';
  }

  if (file_exists($path)) {
    wp_enqueue_style(
      'starscream-nav-layout',
      $uri,
      ['starscream-nav-tabs','starscream-nav-lite','starscream-core'], // ok if some aren’t present
      filemtime($path)
    );
  }
}, 115);

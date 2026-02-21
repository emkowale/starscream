<?php
if (!defined('ABSPATH')) exit;

add_action('wp_enqueue_scripts', function () {
  // Core theme stylesheet (child preferred)
  $style = get_stylesheet_directory().'/style.css';
  if (file_exists($style)) {
    wp_enqueue_style('starscream-core', get_stylesheet_uri(), [], filemtime($style));
  }

  // Nav CSS (color-agnostic formatting only)
  $css_path = trailingslashit(get_stylesheet_directory()).'assets/css/nav-lite.css';
  $css_uri  = trailingslashit(get_stylesheet_directory_uri()).'assets/css/nav-lite.css';
  if (!file_exists($css_path)) {
    $css_path = trailingslashit(get_template_directory()).'assets/css/nav-lite.css';
    $css_uri  = trailingslashit(get_template_directory_uri()).'assets/css/nav-lite.css';
  }
  if (file_exists($css_path)) {
    wp_enqueue_style('starscream-nav-lite', $css_uri, ['starscream-core'], filemtime($css_path));
  }

  // Nav JS (inserts hamburger button; no theme edits)
  $js_path = trailingslashit(get_stylesheet_directory()).'assets/js/nav-lite.js';
  $js_uri  = trailingslashit(get_stylesheet_directory_uri()).'assets/js/nav-lite.js';
  if (!file_exists($js_path)) {
    $js_path = trailingslashit(get_template_directory()).'assets/js/nav-lite.js';
    $js_uri  = trailingslashit(get_template_directory_uri()).'assets/js/nav-lite.js';
  }
  if (file_exists($js_path)) {
    wp_enqueue_script('starscream-nav-lite', $js_uri, [], filemtime($js_path), true);
  }
}, 100);

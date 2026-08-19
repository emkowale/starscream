<?php
if (!defined('ABSPATH')) exit;

add_action('wp_enqueue_scripts', function () {
  $css_path = starscream_locate('assets/css/alerts.css');
  if ($css_path && file_exists($css_path)) {
    wp_enqueue_style(
      'starscream-alerts',
      starscream_asset_uri('assets/css/alerts.css'),
      ['starscream-base', 'starscream-buttons'],
      filemtime($css_path)
    );
  }

  $js_path = starscream_locate('assets/js/alerts.js');
  if ($js_path && file_exists($js_path)) {
    wp_enqueue_script(
      'starscream-alerts',
      starscream_asset_uri('assets/js/alerts.js'),
      [],
      filemtime($js_path),
      true
    );
  }
}, 120);

<?php
if (!defined('ABSPATH')) exit;

add_action('after_setup_theme', function () {
  add_theme_support('menus');
  register_nav_menus([
    'primary' => __('Primary Menu', 'starscream'),
    'footer'  => __('Footer Menu',  'starscream'),
  ]);
});

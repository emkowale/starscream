<?php
/*
 * File: inc/theme/supports.php
 * Description: Core theme supports (logo).
 * Plugin: Starscream
 * Author: Eric Kowalewski
 * Last Updated: 2025-09-02 21:35 EDT
 */
if (!defined('ABSPATH')) exit;

add_action('after_setup_theme', function () {
  add_theme_support('custom-logo', [
    'height'=>200,'width'=>600,'flex-width'=>true,'flex-height'=>true,
  ]);
}, 5);

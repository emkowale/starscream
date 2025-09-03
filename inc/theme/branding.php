<?php
/*
 * File: inc/theme/branding.php
 * Description: Treat theme option 'company_logo_id' as custom_logo if present.
 * Plugin: Starscream
 * Author: Eric Kowalewski
 * Last Updated: 2025-09-02 21:35 EDT
 */
if (!defined('ABSPATH')) exit;

add_filter('theme_mod_custom_logo', function ($value) {
  $company_logo=(int)get_theme_mod('company_logo_id',0);
  return $company_logo>0 ? $company_logo : $value;
});

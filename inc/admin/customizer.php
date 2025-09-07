<?php
// Orchestrator for Customizer modules (≤100 lines)
if (!defined('ABSPATH')) exit;

if (!defined('STARSCREAM_CUSTOMIZER_SECTION')) {
  define('STARSCREAM_CUSTOMIZER_SECTION', 'beartraxs_colors');
}

// Create the single section early so controls can attach reliably.
add_action('customize_register', function($wp_customize){
  $wp_customize->add_section(STARSCREAM_CUSTOMIZER_SECTION, [
    'title'    => 'Starscream Options',
    'priority' => 30,
  ]);
}, 5);

// Load submodules
starscream_require('inc/admin/customizer/helpers.php');
starscream_require('inc/admin/customizer/logo.php');
starscream_require('inc/admin/customizer/colors.php');
starscream_require('inc/admin/customizer/fonts.php');
starscream_require('inc/admin/customizer/contact_hero.php');
starscream_require('inc/admin/customizer/socials.php');
starscream_require('inc/admin/customizer/banners.php');

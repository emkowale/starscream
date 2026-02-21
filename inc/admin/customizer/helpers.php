<?php
if (!defined('ABSPATH')) exit;

/** Shared helpers for Starscream Customizer modules */
if (!function_exists('starscream_customizer_section_id')) {
  function starscream_customizer_section_id() {
    return defined('STARSCREAM_CUSTOMIZER_SECTION') ? STARSCREAM_CUSTOMIZER_SECTION : 'beartraxs_colors';
  }
}

if (!function_exists('starscream_allowed_fonts')) {
  function starscream_allowed_fonts() {
    return ['Inter','Roboto','Open Sans','Montserrat','Lato','Oswald','Raleway','Poppins','Nunito','PT Sans'];
  }
}

if (!function_exists('starscream_sanitize_font')) {
  function starscream_sanitize_font($value) {
    return in_array($value, starscream_allowed_fonts(), true) ? $value : 'Inter';
  }
}

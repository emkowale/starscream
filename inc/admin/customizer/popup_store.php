<?php
if (!defined('ABSPATH')) exit;

if (!function_exists('starscream_sanitize_popup_store_date')) {
  function starscream_sanitize_popup_store_date($value) {
    $value = sanitize_text_field($value);
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) return '';

    [$year, $month, $day] = array_map('absint', explode('-', $value));
    return checkdate($month, $day, $year) ? $value : '';
  }
}

if (!function_exists('starscream_is_popup_store_enabled')) {
  function starscream_is_popup_store_enabled($control = null) {
    if ($control instanceof WP_Customize_Control) {
      $setting = $control->manager->get_setting('popup_store_enabled');
      if ($setting) return (bool) $setting->value();
    }
    return (bool) get_theme_mod('popup_store_enabled', false);
  }
}

add_action('customize_register', function ($wp_customize) {
  $section = starscream_customizer_section_id();

  if (function_exists('starscream_add_customizer_divider')) {
    starscream_add_customizer_divider($wp_customize, $section, 'btx_divider_popup_store', 'Popup Store', 1);
  }

  $wp_customize->add_setting('popup_store_enabled', [
    'default'           => false,
    'sanitize_callback' => 'starscream_sanitize_checkbox',
  ]);
  $wp_customize->add_control('popup_store_enabled', [
    'label'    => 'Enable Popup Store',
    'section'  => $section,
    'type'     => 'checkbox',
    'priority' => 2,
  ]);

  $wp_customize->add_setting('popup_store_closing_date', [
    'default'           => '',
    'sanitize_callback' => 'starscream_sanitize_popup_store_date',
  ]);
  $wp_customize->add_control('popup_store_closing_date', [
    'label'           => 'Store closing date',
    'description'     => 'The popup store closes at 11:00 PM on this date.',
    'section'         => $section,
    'type'            => 'date',
    'priority'        => 3,
    'active_callback' => 'starscream_is_popup_store_enabled',
  ]);
}, 10);

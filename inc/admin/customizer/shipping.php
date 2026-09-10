<?php
if (!defined('ABSPATH')) exit;

if (!function_exists('starscream_sanitize_money')) {
  function starscream_sanitize_money($value) {
    $value = is_scalar($value) ? (string) $value : '0';
    $value = preg_replace('/[^0-9.]/', '', $value);
    if ($value === '' || !is_numeric($value)) return '0.00';
    return number_format((float) $value, 2, '.', '');
  }
}

add_action('customize_register', function ($wp_customize) {
  $section = starscream_customizer_section_id();

  if (function_exists('starscream_add_customizer_divider')) {
    starscream_add_customizer_divider($wp_customize, $section, 'btx_divider_shipping', 'Shipping Options', 4);
  }

  $wp_customize->add_setting('starscream_shipping_local_pickup_enabled', [
    'default'           => true,
    'sanitize_callback' => 'starscream_sanitize_checkbox',
  ]);
  $wp_customize->add_control('starscream_shipping_local_pickup_enabled', [
    'label'    => 'Enable Local Pickup',
    'section'  => $section,
    'type'     => 'checkbox',
    'priority' => 4.1,
  ]);

  $wp_customize->add_setting('starscream_shipping_free_min', [
    'default'           => '50.00',
    'sanitize_callback' => 'starscream_sanitize_money',
  ]);
  $wp_customize->add_control('starscream_shipping_free_min', [
    'label'       => 'Free Shipping Minimum',
    'description' => 'Orders at or above this subtotal get free shipping.',
    'section'     => $section,
    'type'        => 'number',
    'input_attrs' => [
      'min'  => '0',
      'step' => '0.01',
    ],
    'priority' => 4.2,
  ]);

  $wp_customize->add_setting('starscream_shipping_flat_fee', [
    'default'           => '9.00',
    'sanitize_callback' => 'starscream_sanitize_money',
  ]);
  $wp_customize->add_control('starscream_shipping_flat_fee', [
    'label'       => 'Standard Shipping Fee',
    'description' => 'Applied when the order subtotal is below the free shipping minimum.',
    'section'     => $section,
    'type'        => 'number',
    'input_attrs' => [
      'min'  => '0',
      'step' => '0.01',
    ],
    'priority' => 4.3,
  ]);
}, 10);

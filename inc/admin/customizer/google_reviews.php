<?php
if (!defined('ABSPATH')) exit;

if (!function_exists('starscream_sanitize_google_reviews_api_key')) {
  function starscream_sanitize_google_reviews_api_key($value) {
    return trim((string) sanitize_text_field((string) $value));
  }
}

if (!function_exists('starscream_sanitize_google_reviews_place_id')) {
  function starscream_sanitize_google_reviews_place_id($value) {
    $value = trim((string) sanitize_text_field((string) $value));
    $value = preg_replace('/[^A-Za-z0-9:_-]/', '', $value);
    return is_string($value) ? $value : '';
  }
}

if (!function_exists('starscream_sanitize_google_reviews_max_reviews')) {
  function starscream_sanitize_google_reviews_max_reviews($value) {
    $value = absint($value);
    if ($value < 1) return 1;
    if ($value > 12) return 12;
    return $value;
  }
}

if (!function_exists('starscream_sanitize_google_reviews_cache_minutes')) {
  function starscream_sanitize_google_reviews_cache_minutes($value) {
    $value = absint($value);
    if ($value < 5) return 5;
    if ($value > 1440) return 1440;
    return $value;
  }
}

if (!function_exists('starscream_sanitize_google_reviews_autoplay_seconds')) {
  function starscream_sanitize_google_reviews_autoplay_seconds($value) {
    $value = absint($value);
    if ($value > 20) return 20;
    return $value;
  }
}

add_action('customize_register', function ($wp_customize) {
  if (!($wp_customize instanceof WP_Customize_Manager)) return;

  $section = 'starscream_google_reviews';
  if (!$wp_customize->get_section($section)) {
    $wp_customize->add_section($section, [
      'title' => 'Google Reviews',
      'priority' => 36, // under Slider (35), above core Menus.
      'description' => 'Use shortcode [tbt-googlereviews] to render the review slider.',
    ]);
  }

  if (function_exists('starscream_add_customizer_divider')) {
    starscream_add_customizer_divider($wp_customize, $section, 'btx_divider_google_reviews_source', 'Google Source', 10);
  }

  $wp_customize->add_setting('tbt_google_reviews_api_key', [
    'default' => '',
    'sanitize_callback' => 'starscream_sanitize_google_reviews_api_key',
  ]);
  $wp_customize->add_control('tbt_google_reviews_api_key', [
    'label' => 'Google Places API Key',
    'description' => 'Required. <a href="https://developers.google.com/maps/documentation/places/web-service/get-api-key" target="_blank" rel="noopener noreferrer">How to get a Google Places API key</a>: create/select a Google Cloud project, enable Places API, create an API key in APIs &amp; Services &gt; Credentials, then apply key restrictions.',
    'section' => $section,
    'type' => 'text',
    'priority' => 20,
  ]);

  $wp_customize->add_setting('tbt_google_reviews_business_location', [
    'default' => '',
    'sanitize_callback' => 'sanitize_text_field',
  ]);
  $wp_customize->add_control('tbt_google_reviews_business_location', [
    'label' => 'Business Location',
    'description' => 'Example: "Uniform Headquarters, 2705 N Saginaw Rd, Midland, MI".',
    'section' => $section,
    'type' => 'text',
    'priority' => 21,
  ]);

  $wp_customize->add_setting('tbt_google_reviews_place_id', [
    'default' => '',
    'sanitize_callback' => 'starscream_sanitize_google_reviews_place_id',
  ]);
  $wp_customize->add_control('tbt_google_reviews_place_id', [
    'label' => 'Place ID (Optional)',
    'description' => 'Optional override. If set, this is used instead of Business Location lookup. <a href="https://developers.google.com/maps/documentation/javascript/examples/places-placeid-finder" target="_blank" rel="noopener noreferrer">Open Place ID Finder</a>.',
    'section' => $section,
    'type' => 'text',
    'priority' => 22,
  ]);

  if (function_exists('starscream_add_customizer_divider')) {
    starscream_add_customizer_divider($wp_customize, $section, 'btx_divider_google_reviews_display', 'Display', 30);
  }

  $wp_customize->add_setting('tbt_google_reviews_subheading', [
    'default' => 'Google Reviews',
    'sanitize_callback' => 'sanitize_text_field',
  ]);
  $wp_customize->add_control('tbt_google_reviews_subheading', [
    'label' => 'Subheading',
    'section' => $section,
    'type' => 'text',
    'priority' => 40,
  ]);

  $wp_customize->add_setting('tbt_google_reviews_heading', [
    'default' => 'From Our Customers',
    'sanitize_callback' => 'sanitize_text_field',
  ]);
  $wp_customize->add_control('tbt_google_reviews_heading', [
    'label' => 'Heading',
    'section' => $section,
    'type' => 'text',
    'priority' => 41,
  ]);

  $wp_customize->add_setting('tbt_google_reviews_cta_label', [
    'default' => 'Read More On Google',
    'sanitize_callback' => 'sanitize_text_field',
  ]);
  $wp_customize->add_control('tbt_google_reviews_cta_label', [
    'label' => 'CTA Label',
    'section' => $section,
    'type' => 'text',
    'priority' => 42,
  ]);

  $wp_customize->add_setting('tbt_google_reviews_max_reviews', [
    'default' => 7,
    'sanitize_callback' => 'starscream_sanitize_google_reviews_max_reviews',
  ]);
  $wp_customize->add_control('tbt_google_reviews_max_reviews', [
    'label' => 'Max 5-Star Reviews',
    'section' => $section,
    'type' => 'number',
    'input_attrs' => [
      'min' => 1,
      'max' => 12,
      'step' => 1,
    ],
    'priority' => 43,
  ]);

  $wp_customize->add_setting('tbt_google_reviews_autoplay_seconds', [
    'default' => 5,
    'sanitize_callback' => 'starscream_sanitize_google_reviews_autoplay_seconds',
  ]);
  $wp_customize->add_control('tbt_google_reviews_autoplay_seconds', [
    'label' => 'Autoplay Seconds (0 = Off)',
    'section' => $section,
    'type' => 'number',
    'input_attrs' => [
      'min' => 0,
      'max' => 20,
      'step' => 1,
    ],
    'priority' => 44,
  ]);

  if (function_exists('starscream_add_customizer_divider')) {
    starscream_add_customizer_divider($wp_customize, $section, 'btx_divider_google_reviews_cache', 'Caching', 50);
  }

  $wp_customize->add_setting('tbt_google_reviews_cache_minutes', [
    'default' => 120,
    'sanitize_callback' => 'starscream_sanitize_google_reviews_cache_minutes',
  ]);
  $wp_customize->add_control('tbt_google_reviews_cache_minutes', [
    'label' => 'Refresh Every X Minutes',
    'description' => 'How often the shortcode should refresh Google API data.',
    'section' => $section,
    'type' => 'number',
    'input_attrs' => [
      'min' => 5,
      'max' => 1440,
      'step' => 5,
    ],
    'priority' => 60,
  ]);
}, 10);

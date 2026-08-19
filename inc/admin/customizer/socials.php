<?php
if (!defined('ABSPATH')) exit;

if (!function_exists('starscream_social_icons_map')) {
  function starscream_social_icons_map() {
    return [
      'fab fa-facebook-f'     => 'Facebook',
      'fab fa-youtube'        => 'YouTube',
      'fab fa-instagram'      => 'Instagram',
      'fab fa-twitter'        => 'Twitter / X',
      'fab fa-pinterest'      => 'Pinterest',
      'fab fa-linkedin-in'    => 'LinkedIn',
      'fab fa-tiktok'         => 'TikTok',
      'fab fa-snapchat-ghost' => 'Snapchat',
      'fab fa-discord'        => 'Discord',
      'fab fa-reddit-alien'   => 'Reddit',
    ];
  }
}

if (!function_exists('starscream_sanitize_social_icon')) {
  function starscream_sanitize_social_icon($value) {
    $icons = starscream_social_icons_map();
    return array_key_exists((string) $value, $icons) ? (string) $value : '';
  }
}

if (!function_exists('starscream_sanitize_social_editor_slot')) {
  function starscream_sanitize_social_editor_slot($value) {
    $value = absint($value);
    if ($value < 1) return 1;
    if ($value > 6) return 6;
    return $value;
  }
}

if (!function_exists('starscream_is_selected_social_slot')) {
  function starscream_is_selected_social_slot($control = null, $slot = 1) {
    $slot = starscream_sanitize_social_editor_slot($slot);
    if ($control && isset($control->manager)) {
      $setting = $control->manager->get_setting('social_editor_slot');
      if ($setting) {
        return starscream_sanitize_social_editor_slot($setting->value()) === $slot;
      }
    }
    return starscream_sanitize_social_editor_slot(get_theme_mod('social_editor_slot', 1)) === $slot;
  }
}

add_action('customize_register', function ($wp_customize) {
  $section = starscream_customizer_section_id();
  $icons = starscream_social_icons_map();

  if (function_exists('starscream_add_customizer_divider')) {
    starscream_add_customizer_divider($wp_customize, $section, 'btx_divider_socials', 'Socials', 500);
  }

  $wp_customize->add_setting('social_editor_slot', [
    'default' => 1,
    'sanitize_callback' => 'starscream_sanitize_social_editor_slot',
  ]);
  $wp_customize->add_control('social_editor_slot', [
    'label' => 'Social Editor',
    'description' => 'Choose a social slot to edit.',
    'section' => $section,
    'type' => 'select',
    'choices' => [
      '1' => 'Social 1',
      '2' => 'Social 2',
      '3' => 'Social 3',
      '4' => 'Social 4',
      '5' => 'Social 5',
      '6' => 'Social 6',
    ],
    'priority' => 510,
  ]);

  for ($i = 1; $i <= 6; $i++) {
    $wp_customize->add_setting("social_icon_$i", [
      'default' => '',
      'sanitize_callback' => 'starscream_sanitize_social_icon',
    ]);
    $wp_customize->add_setting("social_url_$i", [
      'default' => '',
      'sanitize_callback' => 'esc_url_raw',
    ]);

    $base = 520 + (($i - 1) * 10);
    $slot_active = function ($control) use ($i) {
      return starscream_is_selected_social_slot($control, $i);
    };

    $wp_customize->add_control("social_icon_$i", [
      'label' => "Social Icon $i",
      'section' => $section,
      'type' => 'select',
      'choices' => $icons,
      'priority' => $base,
      'active_callback' => $slot_active,
    ]);
    $wp_customize->add_control("social_url_$i", [
      'label' => "Social URL $i",
      'section' => $section,
      'type' => 'text',
      'priority' => $base + 1,
      'active_callback' => $slot_active,
    ]);
  }
}, 10);

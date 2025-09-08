<?php
if (!defined('ABSPATH')) exit;

add_action('customize_register', function ($wp_customize) {
  $section = starscream_customizer_section_id();

  $wp_customize->add_setting('header_footer_font', [
    'default'           => 'Inter',
    'sanitize_callback' => 'starscream_sanitize_font',
  ]);

  $choices = array_combine(starscream_allowed_fonts(), starscream_allowed_fonts());

  $wp_customize->add_control('header_footer_font', [
    'label'    => 'Header & Footer Font',
    'section'  => $section,
    'type'     => 'select',
    'choices'  => $choices,
    'priority' => 50,
  ]);
}, 7);

<?php
if (!defined('ABSPATH')) exit;

add_action('customize_register', function ($wp_customize) {
  $section = starscream_customizer_section_id();

  $wp_customize->add_setting('company_logo_id', [
    'default'           => 0,
    'sanitize_callback' => 'absint',
  ]);

  if (class_exists('WP_Customize_Media_Control')) {
    $wp_customize->add_control(new WP_Customize_Media_Control($wp_customize, 'company_logo_id', [
      'label'       => 'Company Logo',
      'description' => 'Choose a logo from the Media Library.',
      'section'     => $section,
      'mime_type'   => 'image',
      'priority'    => 5,
    ]));
  } else {
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'company_logo_id', [
      'label'       => 'Company Logo',
      'description' => 'Choose a logo from the Media Library.',
      'section'     => $section,
      'priority'    => 5,
    ]));
  }
}, 5);

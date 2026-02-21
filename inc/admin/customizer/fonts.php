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

// Load the selected Google Font on the front end (and Customizer preview)
add_action('wp_enqueue_scripts', function () {
  // The user’s choice from your <select>, already sanitized by starscream_sanitize_font
  $name = get_theme_mod('header_footer_font', 'Inter');

  // Curated list → Google Fonts CSS2 family spec (weights you support)
  $map = [
    'Inter'      => 'Inter:wght@400;500;600;700',
    'Roboto'     => 'Roboto:wght@400;500;700',
    'Open Sans'  => 'Open+Sans:wght@400;600;700',
    'Montserrat' => 'Montserrat:wght@400;600;700',
    'Lato'       => 'Lato:wght@400;700',
    'Oswald'     => 'Oswald:wght@400;600;700',
    'Raleway'    => 'Raleway:wght@400;600;700',
    'Poppins'    => 'Poppins:wght@400;600;700',
    'Nunito'     => 'Nunito:wght@400;600;700',
    'PT Sans'    => 'PT+Sans:wght@400;700',
  ];

  if (isset($map[$name])) {
    $href = 'https://fonts.googleapis.com/css2?family=' . $map[$name] . '&display=swap';
    wp_enqueue_style('starscream-google-font', $href, [], null);
  }
}, 20);

// (Nice-to-have) small perf hint for Google Fonts
add_filter('wp_resource_hints', function ($urls, $rel) {
  if ($rel === 'preconnect') {
    $urls[] = ['href' => 'https://fonts.gstatic.com', 'crossorigin' => true];
    $urls[] = 'https://fonts.googleapis.com';
  }
  return $urls;
}, 10, 2);

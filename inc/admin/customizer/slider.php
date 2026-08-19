<?php
if (!defined('ABSPATH')) exit;

if (!function_exists('starscream_slider_customizer_section_id')) {
  function starscream_slider_customizer_section_id() {
    return 'starscream_slider';
  }
}

if (!function_exists('starscream_is_tbt_slider_ticker_enabled')) {
  function starscream_is_tbt_slider_ticker_enabled($control = null) {
    if ($control && isset($control->manager)) {
      $setting = $control->manager->get_setting('tbt_slider_ticker_enabled');
      if ($setting) return (bool) $setting->value();
    }
    return (bool) get_theme_mod('tbt_slider_ticker_enabled', false);
  }
}

if (!function_exists('starscream_register_tbt_slider_content_controls')) {
  function starscream_register_tbt_slider_content_controls($wp_customize, $section, $priority_base = 70, $id_suffix = '') {
    if (!($wp_customize instanceof WP_Customize_Manager)) return;
    if (!$wp_customize->get_section($section)) return;

    $controls = [
      [
        'setting' => 'tbt_slider_line_1',
        'label' => 'Slider: Line 1 (Small)',
        'type' => 'text',
        'priority' => $priority_base,
      ],
      [
        'setting' => 'tbt_slider_line_2',
        'label' => 'Slider: Line 2 (Large)',
        'type' => 'text',
        'priority' => $priority_base + 1,
      ],
      [
        'setting' => 'tbt_slider_line_3',
        'label' => 'Slider: Line 3 (Small)',
        'type' => 'text',
        'priority' => $priority_base + 2,
      ],
      [
        'setting' => 'tbt_slider_button_text',
        'label' => 'Slider: Button Text',
        'type' => 'text',
        'priority' => $priority_base + 3,
      ],
      [
        'setting' => 'tbt_slider_button_link',
        'label' => 'Slider: Button Link',
        'type' => 'text',
        'priority' => $priority_base + 4,
      ],
    ];

    foreach ($controls as $row) {
      $control_id = $row['setting'] . $id_suffix;
      $wp_customize->add_control($control_id, [
        'settings' => $row['setting'],
        'label' => $row['label'],
        'section' => $section,
        'type' => $row['type'],
        'priority' => $row['priority'],
      ]);
    }
  }
}

add_action('customize_register', function ($wp_customize) {
  if (!($wp_customize instanceof WP_Customize_Manager)) return;

  $section = starscream_slider_customizer_section_id();
  if (!$wp_customize->get_section($section)) {
    $wp_customize->add_section($section, [
      'title' => 'Slider',
      'priority' => 35,
    ]);
  }

  if (function_exists('starscream_add_customizer_divider')) {
    starscream_add_customizer_divider($wp_customize, $section, 'btx_divider_slider_ticker', 'Slider Ticker', 10);
  }

  $wp_customize->add_setting('tbt_slider_ticker_enabled', [
    'default' => false,
    'sanitize_callback' => 'starscream_sanitize_checkbox',
  ]);
  $wp_customize->add_control('tbt_slider_ticker_enabled', [
    'label' => 'Enable Slider Ticker',
    'section' => $section,
    'type' => 'checkbox',
    'priority' => 20,
  ]);

  $wp_customize->add_setting('tbt_slider_ticker_text', [
    'default' => 'Scrub Style. All Day, Every Day.',
    'sanitize_callback' => 'sanitize_text_field',
  ]);
  $wp_customize->add_control('tbt_slider_ticker_text', [
    'label' => 'Slider Ticker Text',
    'section' => $section,
    'type' => 'text',
    'priority' => 21,
    'active_callback' => 'starscream_is_tbt_slider_ticker_enabled',
  ]);

  if (function_exists('starscream_add_customizer_divider')) {
    starscream_add_customizer_divider($wp_customize, $section, 'btx_divider_slider_video', 'Slider Video', 40);
  }

  $wp_customize->add_setting('tbt_slider_video_url', [
    'default' => '',
    'sanitize_callback' => 'esc_url_raw',
  ]);
  $wp_customize->add_control('tbt_slider_video_url', [
    'label' => 'Slider Video URL',
    'description' => 'Use a vertical phone video URL. YouTube and hosted MP4 URLs are supported.',
    'section' => $section,
    'type' => 'url',
    'priority' => 50,
  ]);

  if (function_exists('starscream_add_customizer_divider')) {
    starscream_add_customizer_divider($wp_customize, $section, 'btx_divider_slider_content', 'Slider Content', 60);
  }

  $wp_customize->add_setting('tbt_slider_line_1', [
    'default' => '',
    'sanitize_callback' => 'sanitize_text_field',
  ]);
  $wp_customize->add_setting('tbt_slider_line_2', [
    'default' => '',
    'sanitize_callback' => 'sanitize_text_field',
  ]);
  $wp_customize->add_setting('tbt_slider_line_3', [
    'default' => '',
    'sanitize_callback' => 'sanitize_text_field',
  ]);
  $wp_customize->add_setting('tbt_slider_button_text', [
    'default' => '',
    'sanitize_callback' => 'sanitize_text_field',
  ]);
  $wp_customize->add_setting('tbt_slider_button_link', [
    'default' => '',
    'sanitize_callback' => 'esc_url_raw',
  ]);

  starscream_register_tbt_slider_content_controls($wp_customize, $section, 70);

  // Mirror these controls inside Starscream Options so they are available
  // in the same place as the existing Header Slider controls.
  if (function_exists('starscream_customizer_section_id')) {
    $starscream_options_section = starscream_customizer_section_id();
    if (is_string($starscream_options_section) && $starscream_options_section !== $section && $wp_customize->get_section($starscream_options_section)) {
      if (function_exists('starscream_add_customizer_divider')) {
        starscream_add_customizer_divider($wp_customize, $starscream_options_section, 'btx_divider_slider_content_mirror', 'Slider Content (Shortcode)', 340);
      }
      starscream_register_tbt_slider_content_controls($wp_customize, $starscream_options_section, 350, '_mirror');
    }
  }
}, 9);

add_action('customize_controls_print_styles', function () {
  ?>
  <style id="starscream-tbt-slider-tooltip-styles">
    .customize-control .btx-help-tip {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 16px;
      height: 16px;
      margin-left: 6px;
      border-radius: 50%;
      background: #1d2327;
      color: #fff;
      font-size: 11px;
      font-weight: 700;
      line-height: 1;
      cursor: help;
      vertical-align: middle;
      user-select: none;
    }
  </style>
  <?php
}, 35);

add_action('customize_controls_print_footer_scripts', function () {
  ?>
  <script>
    (function () {
      function addHelpTip(controlId, text) {
        var control = document.getElementById('customize-control-' + controlId);
        if (!control) return;
        var title = control.querySelector('.customize-control-title');
        if (!title) return;
        if (title.querySelector('.btx-help-tip')) return;

        var tip = document.createElement('span');
        tip.className = 'btx-help-tip';
        tip.textContent = '?';
        tip.setAttribute('title', text);
        tip.setAttribute('tabindex', '0');
        tip.setAttribute('aria-label', text);
        title.appendChild(tip);
      }

      addHelpTip('tbt_slider_video_url', 'Use a vertical phone video. ShopUHQ model size is 1080px width x 1920px height.');
    })();
  </script>
  <?php
}, 35);

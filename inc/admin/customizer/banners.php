<?php
if (!defined('ABSPATH')) exit;

if (!function_exists('starscream_header_slider_max_slides')) {
  function starscream_header_slider_max_slides() {
    return 6;
  }
}

if (!function_exists('starscream_header_slider_image_setting_ids')) {
  function starscream_header_slider_image_setting_ids() {
    static $ids = null;
    if (is_array($ids)) return $ids;

    $ids = [];
    $max = starscream_header_slider_max_slides();
    for ($i = 1; $i <= $max; $i++) {
      $ids[] = "header_slider_slide_{$i}_desktop_image_id";
      $ids[] = "header_slider_slide_{$i}_mobile_image_id";
    }
    return $ids;
  }
}

if (!function_exists('starscream_sanitize_slider_interval_seconds')) {
  function starscream_sanitize_slider_interval_seconds($value) {
    $value = absint($value);
    if ($value < 3) return 3;
    if ($value > 30) return 30;
    return $value;
  }
}

if (!function_exists('starscream_sanitize_slider_editor_slide')) {
  function starscream_sanitize_slider_editor_slide($value) {
    $value = absint($value);
    $max = starscream_header_slider_max_slides();
    if ($value < 1) return 1;
    if ($value > $max) return $max;
    return $value;
  }
}

if (!function_exists('starscream_is_header_slider_enabled')) {
  function starscream_is_header_slider_enabled($control = null) {
    if ($control && isset($control->manager)) {
      $setting = $control->manager->get_setting('header_slider_enabled');
      if ($setting) return (bool) $setting->value();
    }
    return (bool) get_theme_mod('header_slider_enabled', false);
  }
}

if (!function_exists('starscream_is_header_slider_selected_slide')) {
  function starscream_is_header_slider_selected_slide($control = null, $slide_index = 1) {
    if (!starscream_is_header_slider_enabled($control)) return false;

    $slide_index = starscream_sanitize_slider_editor_slide($slide_index);
    if ($control && isset($control->manager)) {
      $setting = $control->manager->get_setting('header_slider_editor_slide');
      if ($setting) {
        return starscream_sanitize_slider_editor_slide($setting->value()) === $slide_index;
      }
    }

    return starscream_sanitize_slider_editor_slide(get_theme_mod('header_slider_editor_slide', 1)) === $slide_index;
  }
}

add_action('customize_register', function ($wp_customize) {
  $section = starscream_customizer_section_id();
  $media = class_exists('WP_Customize_Media_Control') ? 'WP_Customize_Media_Control' : 'WP_Customize_Image_Control';
  $slide_count = starscream_header_slider_max_slides();

  if (function_exists('starscream_add_customizer_divider')) {
    starscream_add_customizer_divider($wp_customize, $section, 'btx_divider_header_slider', 'Header Slider', 250);
  }

  $slider_prio = 260;
  $wp_customize->add_setting('header_slider_enabled', [
    'default' => false,
    'sanitize_callback' => 'starscream_sanitize_checkbox',
  ]);
  $wp_customize->add_control('header_slider_enabled', [
    'label'    => 'Enable Header Slider (Home only)',
    'section'  => $section,
    'type'     => 'checkbox',
    'priority' => $slider_prio,
  ]);

  $wp_customize->add_setting('header_slider_autoplay_seconds', [
    'default' => 10,
    'sanitize_callback' => 'starscream_sanitize_slider_interval_seconds',
  ]);
  $wp_customize->add_control('header_slider_autoplay_seconds', [
    'label'    => 'Header Slider Autoplay Speed (seconds)',
    'description' => 'Each slide duration. Min 3, max 30.',
    'section'  => $section,
    'type'     => 'number',
    'priority' => $slider_prio + 1,
    'input_attrs' => [
      'min'  => 3,
      'max'  => 30,
      'step' => 1,
    ],
    'active_callback' => 'starscream_is_header_slider_enabled',
  ]);

  $wp_customize->add_setting('header_slider_editor_slide', [
    'default' => 1,
    'sanitize_callback' => 'starscream_sanitize_slider_editor_slide',
  ]);
  $slide_choices = [];
  for ($i = 1; $i <= $slide_count; $i++) {
    $slide_choices[(string) $i] = 'Slide ' . $i;
  }
  $wp_customize->add_control('header_slider_editor_slide', [
    'label' => 'Header Slider Slide Editor',
    'description' => 'Choose a slide to edit.',
    'section' => $section,
    'type' => 'select',
    'choices' => $slide_choices,
    'priority' => $slider_prio + 2,
    'active_callback' => 'starscream_is_header_slider_enabled',
  ]);

  for ($i = 1; $i <= $slide_count; $i++) {
    $base = $slider_prio + 10 + (($i - 1) * 10);
    $slide_active = function ($control) use ($i) {
      return starscream_is_header_slider_selected_slide($control, $i);
    };

    $desktop_setting = "header_slider_slide_{$i}_desktop_image_id";
    $mobile_setting  = "header_slider_slide_{$i}_mobile_image_id";
    $line_1_setting  = "header_slider_slide_{$i}_line_1";
    $line_2_setting  = "header_slider_slide_{$i}_line_2";
    $line_3_setting  = "header_slider_slide_{$i}_line_3";
    $btn_txt_setting = "header_slider_slide_{$i}_button_text";
    $btn_url_setting = "header_slider_slide_{$i}_button_url";

    $wp_customize->add_setting($desktop_setting, ['default' => 0, 'sanitize_callback' => 'absint']);
    $wp_customize->add_setting($mobile_setting,  ['default' => 0, 'sanitize_callback' => 'absint']);
    $wp_customize->add_setting($line_1_setting,  ['default' => '', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_setting($line_2_setting,  ['default' => '', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_setting($line_3_setting,  ['default' => '', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_setting($btn_txt_setting, ['default' => '', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_setting($btn_url_setting, ['default' => '', 'sanitize_callback' => 'esc_url_raw']);

    $wp_customize->add_control(new $media($wp_customize, $desktop_setting, [
      'label'    => "Header Slider Slide {$i}: Desktop Image",
      'description' => 'Slide only renders when desktop + mobile image are both selected.',
      'section'  => $section,
      'mime_type'=> 'image',
      'priority' => $base,
      'active_callback' => $slide_active,
    ]));

    $wp_customize->add_control(new $media($wp_customize, $mobile_setting, [
      'label'    => "Header Slider Slide {$i}: Mobile Image",
      'section'  => $section,
      'mime_type'=> 'image',
      'priority' => $base + 1,
      'active_callback' => $slide_active,
    ]));

    $wp_customize->add_control($line_1_setting, [
      'label'    => "Header Slider Slide {$i}: Line 1 (Small)",
      'section'  => $section,
      'type'     => 'text',
      'priority' => $base + 2,
      'active_callback' => $slide_active,
    ]);

    $wp_customize->add_control($line_2_setting, [
      'label'    => "Header Slider Slide {$i}: Line 2 (Main Heading)",
      'section'  => $section,
      'type'     => 'text',
      'priority' => $base + 3,
      'active_callback' => $slide_active,
    ]);

    $wp_customize->add_control($line_3_setting, [
      'label'    => "Header Slider Slide {$i}: Line 3 (Small)",
      'section'  => $section,
      'type'     => 'text',
      'priority' => $base + 4,
      'active_callback' => $slide_active,
    ]);

    $wp_customize->add_control($btn_txt_setting, [
      'label'    => "Header Slider Slide {$i}: Button Text",
      'section'  => $section,
      'type'     => 'text',
      'priority' => $base + 5,
      'active_callback' => $slide_active,
    ]);

    $wp_customize->add_control($btn_url_setting, [
      'label'    => "Header Slider Slide {$i}: Button Link",
      'section'  => $section,
      'type'     => 'text',
      'priority' => $base + 6,
      'active_callback' => $slide_active,
    ]);
  }

  if (function_exists('starscream_add_customizer_divider')) {
    starscream_add_customizer_divider($wp_customize, $section, 'btx_divider_footer_settings', 'Footers', 610);
  }

  $footer_prio = 620;
  $wp_customize->add_setting('home_bottom_banner_image_id', ['default' => 0, 'sanitize_callback' => 'absint']);
  $wp_customize->add_setting('home_bottom_banner_link',     ['default' => '', 'sanitize_callback' => 'esc_url_raw']);
  $wp_customize->add_setting('home_bottom_banner_alt',      ['default' => '', 'sanitize_callback' => 'sanitize_text_field']);

  $wp_customize->add_control(new $media($wp_customize, 'home_bottom_banner_image_id', [
    'label'    => 'Footer Banner Image (Home only)',
    'section'  => $section,
    'mime_type'=> 'image',
    'priority' => $footer_prio,
  ]));
  $wp_customize->add_control('home_bottom_banner_link', [
    'label'    => 'Footer Banner Link (optional)',
    'section'  => $section,
    'type'     => 'text',
    'priority' => $footer_prio + 1,
  ]);
  $wp_customize->add_control('home_bottom_banner_alt', [
    'label'    => 'Footer Banner Alt Text',
    'section'  => $section,
    'type'     => 'text',
    'priority' => $footer_prio + 2,
  ]);
}, 9);

add_action('customize_save_after', function () {
  if (!function_exists('starscream_convert_attachment_to_webp')) return;

  foreach (starscream_header_slider_image_setting_ids() as $setting_id) {
    $attachment_id = absint(get_theme_mod($setting_id, 0));
    if ($attachment_id > 0) starscream_convert_attachment_to_webp($attachment_id);
  }
}, 25);

add_action('wp_ajax_starscream_convert_slider_attachment_to_webp', function () {
  if (!current_user_can('edit_theme_options')) {
    wp_send_json_error(['message' => 'Insufficient permissions.'], 403);
  }

  check_ajax_referer('starscream_slider_webp_nonce', 'nonce');

  $attachment_id = isset($_POST['attachment_id']) ? absint($_POST['attachment_id']) : 0;
  if ($attachment_id < 1) {
    wp_send_json_error(['message' => 'Invalid attachment ID.'], 400);
  }

  if (!function_exists('starscream_convert_attachment_to_webp')) {
    wp_send_json_error(['message' => 'Conversion engine unavailable.'], 500);
  }

  $result = starscream_convert_attachment_to_webp($attachment_id);
  if (is_wp_error($result)) {
    wp_send_json_error(['message' => $result->get_error_message()], 500);
  }

  wp_send_json_success([
    'attachment_id' => $attachment_id,
    'mime' => (string) get_post_mime_type($attachment_id),
  ]);
});

add_action('customize_controls_print_styles', function () {
  ?>
  <style id="starscream-slider-tooltip-styles">
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
}, 30);

add_action('customize_controls_print_footer_scripts', function () {
  $nonce = wp_create_nonce('starscream_slider_webp_nonce');
  $max = starscream_header_slider_max_slides();
  $setting_ids = starscream_header_slider_image_setting_ids();
  ?>
  <script>
    (function () {
      var maxSlides = <?php echo (int) $max; ?>;
      var nonce = <?php echo wp_json_encode($nonce); ?>;
      var settingIds = <?php echo wp_json_encode($setting_ids); ?>;
      var ajaxEndpoint = window.ajaxurl || (typeof ajaxurl !== 'undefined' ? ajaxurl : '');
      var converted = {};

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

      function requestWebpConversion(id) {
        var attachmentId = parseInt(id || 0, 10);
        if (!attachmentId || converted[attachmentId]) return;
        if (!ajaxEndpoint) return;
        converted[attachmentId] = true;

        var payload = new URLSearchParams();
        payload.set('action', 'starscream_convert_slider_attachment_to_webp');
        payload.set('nonce', nonce);
        payload.set('attachment_id', String(attachmentId));

        fetch(ajaxEndpoint, {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
          body: payload.toString()
        }).then(function (response) {
          return response.json();
        }).then(function (json) {
          if (!json || !json.success) converted[attachmentId] = false;
        }).catch(function () {
          converted[attachmentId] = false;
        });
      }

      function bindSettingConversion(settingId) {
        if (!window.wp || !wp.customize) return;
        wp.customize(settingId, function (setting) {
          requestWebpConversion(setting.get());
          setting.bind(function (value) {
            requestWebpConversion(value);
          });
        });
      }

      for (var i = 1; i <= maxSlides; i++) {
        addHelpTip('header_slider_slide_' + i + '_mobile_image_id', 'This image must be 1065px x 1600px webp');
        addHelpTip('header_slider_slide_' + i + '_desktop_image_id', 'This image must be 6132px x 4088px webp');
      }

      settingIds.forEach(function (settingId) {
        bindSettingConversion(settingId);
      });
    })();
  </script>
  <?php
}, 30);

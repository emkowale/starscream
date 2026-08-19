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

if (class_exists('WP_Customize_Control') && !class_exists('Starscream_Customize_Slider_Guide_Control')) {
  class Starscream_Customize_Slider_Guide_Control extends WP_Customize_Control {
    public $type = 'starscream_slider_guide';

    public function render_content() {
      ?>
      <div class="starscream-slider-guide">
        <p class="starscream-slider-guide__eyebrow">Slider Image Guide</p>
        <p class="starscream-slider-guide__lead">Build every homepage slide from one bright desktop master plus one mobile crop from that same master.</p>

        <div class="starscream-slider-guide__grid">
          <div class="starscream-slider-guide__card">
            <h3>Desktop</h3>
            <p><strong>Size:</strong> 6132 x 4088 webp</p>
            <p><strong>Use:</strong> wide lifestyle image with the top quarter kept pale and uncluttered for the transparent header.</p>
          </div>

          <div class="starscream-slider-guide__card">
            <h3>Mobile</h3>
            <p><strong>Size:</strong> 1065 x 1600 webp</p>
            <p><strong>Use:</strong> a vertical crop of the same image, keeping the face and torso centered lower in the frame.</p>
          </div>
        </div>

        <div class="starscream-slider-guide__card starscream-slider-guide__card--full">
          <h3>Composition rules</h3>
          <ul>
            <li>Keep the top 20 to 25 percent very light so the dark menu items stay readable.</li>
            <li>Do not place faces, logos, or important text inside the top header zone.</li>
            <li>Because the slider uses <code>object-position: center top</code>, the image should still work when cropped from the center and pinned to the top edge.</li>
            <li>For mobile, crop from the desktop master instead of choosing a different photo unless the original image breaks down vertically.</li>
          </ul>
        </div>

        <div class="starscream-slider-guide__card starscream-slider-guide__card--full">
          <h3>Reusable prompt scaffold</h3>
          <p>Create a bright, premium ecommerce homepage hero for a medical apparel brand. Leave the top quarter clean, pale, and low contrast for a transparent header with dark navigation. Place the subjects in the lower middle of the frame. Use real-world lighting, no floating text, no logo overlays, and no busy background behind the header area. Export one desktop version at 6132 x 4088 and one mobile crop at 1065 x 1600.</p>
        </div>
      </div>
      <?php
    }
  }
}

if (class_exists('WP_Customize_Control') && !class_exists('Starscream_Customize_Slider_Helper_Control')) {
  class Starscream_Customize_Slider_Helper_Control extends WP_Customize_Control {
    public $type = 'starscream_slider_helper';

    public function render_content() {
      $max = function_exists('starscream_header_slider_max_slides') ? starscream_header_slider_max_slides() : 6;
      ?>
      <div class="starscream-slider-helper" data-max-slides="<?php echo esc_attr((string) $max); ?>">
        <div class="starscream-slider-helper__header">
          <p class="starscream-slider-helper__eyebrow">Slider Helper</p>
          <h3>Preview safe zones and generate the mobile crop from the current desktop image.</h3>
          <p class="starscream-slider-helper__lead">The desktop preview marks the light header band. The mobile preview shows the crop zone the helper will target for the current slide.</p>
        </div>

        <div class="starscream-slider-helper__toolbar">
          <div class="starscream-slider-helper__current-slide">Editing slide <strong data-slider-helper-slide-number>1</strong></div>
          <button type="button" class="button button-primary starscream-slider-helper__generate-mobile">Generate mobile crop from desktop</button>
        </div>

        <div class="starscream-slider-helper__status" aria-live="polite"></div>

        <div class="starscream-slider-helper__grid">
          <section class="starscream-slider-helper__panel">
            <div class="starscream-slider-helper__panel-head">
              <strong>Desktop preview</strong>
              <span>6132 x 4088</span>
            </div>
            <div class="starscream-slider-helper__frame starscream-slider-helper__frame--desktop">
              <div class="starscream-slider-helper__empty" data-slider-helper-empty="desktop">Choose a desktop image for this slide.</div>
              <img data-slider-helper-image="desktop" alt="" hidden>
              <div class="starscream-slider-helper__zone starscream-slider-helper__zone--desktop-top">
                <span>Keep this band pale for the transparent header</span>
              </div>
            </div>
            <p class="starscream-slider-helper__meta" data-slider-helper-meta="desktop">No desktop image selected.</p>
          </section>

          <section class="starscream-slider-helper__panel">
            <div class="starscream-slider-helper__panel-head">
              <strong>Mobile crop preview</strong>
              <span>1065 x 1600</span>
            </div>
            <div class="starscream-slider-helper__frame starscream-slider-helper__frame--mobile">
              <div class="starscream-slider-helper__empty" data-slider-helper-empty="mobile">Generate or choose a mobile image for this slide.</div>
              <img data-slider-helper-image="mobile" alt="" hidden>
              <div class="starscream-slider-helper__zone starscream-slider-helper__zone--mobile-top">
                <span>Header must stay clear</span>
              </div>
              <div class="starscream-slider-helper__zone starscream-slider-helper__zone--mobile-focus">
                <span>Keep faces and torso in this band</span>
              </div>
            </div>
            <p class="starscream-slider-helper__meta" data-slider-helper-meta="mobile">No mobile image selected.</p>
          </section>
        </div>
      </div>
      <?php
    }
  }
}

if (!function_exists('starscream_slider_mobile_crop_dimensions')) {
  function starscream_slider_mobile_crop_dimensions() {
    return [
      'width' => 1065,
      'height' => 1600,
    ];
  }
}

if (!function_exists('starscream_generate_slider_mobile_crop_attachment')) {
  function starscream_generate_slider_mobile_crop_attachment($attachment_id) {
    $attachment_id = absint($attachment_id);
    if ($attachment_id < 1) return new WP_Error('invalid_attachment', 'Choose a valid desktop image first.');
    if (get_post_type($attachment_id) !== 'attachment') return new WP_Error('invalid_attachment', 'The selected desktop image could not be found.');

    $source_file = get_attached_file($attachment_id, true);
    if (!$source_file || !file_exists($source_file)) {
      return new WP_Error('missing_attachment_file', 'The desktop image file could not be found.');
    }

    $editor = wp_get_image_editor($source_file);
    if (is_wp_error($editor)) return $editor;

    $size = $editor->get_size();
    $source_width = !empty($size['width']) ? (int) $size['width'] : 0;
    $source_height = !empty($size['height']) ? (int) $size['height'] : 0;
    if ($source_width < 1 || $source_height < 1) {
      return new WP_Error('invalid_source_size', 'The desktop image size could not be read.');
    }

    $target = starscream_slider_mobile_crop_dimensions();
    $target_width = (int) $target['width'];
    $target_height = (int) $target['height'];
    $target_ratio = $target_width / $target_height;
    $source_ratio = $source_width / $source_height;

    if ($source_ratio > $target_ratio) {
      $crop_height = $source_height;
      $crop_width = (int) round($crop_height * $target_ratio);
      $src_x = (int) max(0, floor(($source_width - $crop_width) / 2));
      $src_y = 0;
    } else {
      $crop_width = $source_width;
      $crop_height = (int) round($crop_width / $target_ratio);
      $src_x = 0;
      $available_y = max(0, $source_height - $crop_height);
      $src_y = (int) floor($available_y * 0.12);
    }

    $cropped = $editor->crop($src_x, $src_y, $crop_width, $crop_height, $target_width, $target_height);
    if (is_wp_error($cropped)) return $cropped;
    if (method_exists($editor, 'set_quality')) $editor->set_quality(90);

    $path_info = pathinfo($source_file);
    $dir = isset($path_info['dirname']) ? (string) $path_info['dirname'] : '';
    $name = isset($path_info['filename']) ? (string) $path_info['filename'] : '';
    if ($dir === '' || $name === '') {
      return new WP_Error('invalid_path', 'The desktop image path could not be prepared for cropping.');
    }

    $target_name = wp_unique_filename($dir, $name . '-mobile-crop.webp');
    $target_file = trailingslashit($dir) . $target_name;
    $saved = $editor->save($target_file, 'image/webp');
    if (is_wp_error($saved)) return $saved;
    if (empty($saved['path']) || !file_exists((string) $saved['path'])) {
      return new WP_Error('crop_save_failed', 'The cropped mobile image could not be saved.');
    }

    $upload = wp_upload_dir();
    $file_path = (string) $saved['path'];
    $relative = ltrim(str_replace((string) $upload['basedir'], '', $file_path), '/');
    $url = trailingslashit((string) $upload['baseurl']) . $relative;
    $title = trim((string) get_the_title($attachment_id));
    if ($title === '') $title = wp_basename($source_file);
    $title .= ' mobile crop';

    $new_attachment_id = wp_insert_attachment([
      'post_title' => $title,
      'post_status' => 'inherit',
      'post_mime_type' => 'image/webp',
      'guid' => $url,
    ], $file_path, 0, true);
    if (is_wp_error($new_attachment_id)) return $new_attachment_id;

    require_once ABSPATH . 'wp-admin/includes/image.php';

    $metadata = wp_generate_attachment_metadata($new_attachment_id, $file_path);
    if (!is_wp_error($metadata) && is_array($metadata)) {
      wp_update_attachment_metadata($new_attachment_id, $metadata);
    }

    $alt = trim((string) get_post_meta($attachment_id, '_wp_attachment_image_alt', true));
    if ($alt !== '') update_post_meta($new_attachment_id, '_wp_attachment_image_alt', $alt);
    update_post_meta($new_attachment_id, '_starscream_slider_generated_from', $attachment_id);

    return [
      'attachment_id' => (int) $new_attachment_id,
      'url' => (string) wp_get_attachment_image_url((int) $new_attachment_id, 'full'),
      'thumb_url' => (string) (wp_get_attachment_image_url((int) $new_attachment_id, 'medium') ?: wp_get_attachment_image_url((int) $new_attachment_id, 'full')),
      'title' => $title,
      'width' => $target_width,
      'height' => $target_height,
    ];
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

  $wp_customize->add_setting('header_slider_image_guide', [
    'default' => '',
    'sanitize_callback' => 'sanitize_text_field',
  ]);

  if (class_exists('Starscream_Customize_Slider_Guide_Control')) {
    $wp_customize->add_control(new Starscream_Customize_Slider_Guide_Control($wp_customize, 'header_slider_image_guide', [
      'section' => $section,
      'priority' => $slider_prio + 1,
      'active_callback' => 'starscream_is_header_slider_enabled',
    ]));
  }

  $wp_customize->add_setting('header_slider_helper_control', [
    'default' => '',
    'sanitize_callback' => 'sanitize_text_field',
  ]);

  if (class_exists('Starscream_Customize_Slider_Helper_Control')) {
    $wp_customize->add_control(new Starscream_Customize_Slider_Helper_Control($wp_customize, 'header_slider_helper_control', [
      'section' => $section,
      'priority' => $slider_prio + 2,
      'active_callback' => 'starscream_is_header_slider_enabled',
    ]));
  }

  $wp_customize->add_setting('header_slider_autoplay_seconds', [
    'default' => 10,
    'sanitize_callback' => 'starscream_sanitize_slider_interval_seconds',
  ]);
  $wp_customize->add_control('header_slider_autoplay_seconds', [
    'label'    => 'Header Slider Autoplay Speed (seconds)',
    'description' => 'Each slide duration. Min 3, max 30.',
    'section'  => $section,
    'type'     => 'number',
    'priority' => $slider_prio + 3,
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
    'priority' => $slider_prio + 4,
    'active_callback' => 'starscream_is_header_slider_enabled',
  ]);

  for ($i = 1; $i <= $slide_count; $i++) {
    $base = $slider_prio + 12 + (($i - 1) * 10);
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

add_action('wp_ajax_starscream_generate_slider_mobile_crop', function () {
  if (!current_user_can('edit_theme_options')) {
    wp_send_json_error(['message' => 'Insufficient permissions.'], 403);
  }

  check_ajax_referer('starscream_slider_helper_nonce', 'nonce');

  $attachment_id = isset($_POST['attachment_id']) ? absint($_POST['attachment_id']) : 0;
  $result = starscream_generate_slider_mobile_crop_attachment($attachment_id);
  if (is_wp_error($result)) {
    wp_send_json_error(['message' => $result->get_error_message()], 400);
  }

  wp_send_json_success($result);
});

add_action('customize_controls_enqueue_scripts', function () {
  if (!function_exists('starscream_locate') || !function_exists('starscream_asset_uri')) return;

  wp_enqueue_media();

  $style_path = starscream_locate('assets/css/customizer-slider-helper.css');
  if ($style_path && file_exists($style_path)) {
    wp_enqueue_style(
      'starscream-customizer-slider-helper',
      starscream_asset_uri('assets/css/customizer-slider-helper.css'),
      [],
      filemtime($style_path)
    );
  }

  $script_path = starscream_locate('assets/js/customizer-slider-helper.js');
  if ($script_path && file_exists($script_path)) {
    wp_enqueue_script(
      'starscream-customizer-slider-helper',
      starscream_asset_uri('assets/js/customizer-slider-helper.js'),
      ['customize-controls', 'jquery', 'media-models'],
      filemtime($script_path),
      true
    );

    wp_localize_script('starscream-customizer-slider-helper', 'starscreamSliderHelper', [
      'ajaxUrl' => admin_url('admin-ajax.php'),
      'nonce' => wp_create_nonce('starscream_slider_helper_nonce'),
      'maxSlides' => starscream_header_slider_max_slides(),
      'mobileTarget' => starscream_slider_mobile_crop_dimensions(),
      'messages' => [
        'missingDesktop' => 'Choose a desktop image before generating the mobile crop.',
        'generating' => 'Generating the mobile crop from the desktop image.',
        'generated' => 'Generated a new mobile crop and assigned it to the current slide.',
        'failed' => 'The mobile crop could not be created.',
      ],
    ]);
  }
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

    .customize-control-starscream_slider_guide {
      margin-top: 12px;
    }

    .starscream-slider-guide {
      display: grid;
      gap: 12px;
      padding: 14px;
      border: 1px solid rgba(15, 23, 42, 0.12);
      border-radius: 16px;
      background:
        radial-gradient(circle at top right, rgba(15, 109, 140, 0.1), transparent 32%),
        linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    }

    .starscream-slider-guide__eyebrow {
      margin: 0;
      color: #0f6d8c;
      font-size: 11px;
      font-weight: 800;
      letter-spacing: 0.08em;
      text-transform: uppercase;
    }

    .starscream-slider-guide__lead,
    .starscream-slider-guide__card p,
    .starscream-slider-guide__card li {
      margin: 0;
      color: #475569;
      font-size: 12px;
      line-height: 1.55;
    }

    .starscream-slider-guide__grid {
      display: grid;
      gap: 10px;
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .starscream-slider-guide__card {
      display: grid;
      gap: 8px;
      padding: 12px;
      border: 1px solid rgba(15, 23, 42, 0.1);
      border-radius: 12px;
      background: rgba(255, 255, 255, 0.92);
    }

    .starscream-slider-guide__card h3 {
      margin: 0;
      color: #0f172a;
      font-size: 13px;
    }

    .starscream-slider-guide__card ul {
      margin: 0;
      padding-left: 18px;
    }

    .starscream-slider-guide__card--full {
      grid-column: 1 / -1;
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

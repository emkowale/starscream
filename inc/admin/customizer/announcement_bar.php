<?php
if (!defined('ABSPATH')) exit;

if (!function_exists('starscream_is_announcement_enabled')) {
  function starscream_is_announcement_enabled($control = null) {
    if ($control instanceof WP_Customize_Control) {
      $setting = $control->manager->get_setting('announcement_bar_enabled');
      if ($setting) return (bool) $setting->value();
    }
    return (bool) get_theme_mod('announcement_bar_enabled', false);
  }
}

if (!function_exists('starscream_sanitize_announcement_editor_item')) {
  function starscream_sanitize_announcement_editor_item($value) {
    $value = absint($value);
    if ($value < 1) return 1;
    if ($value > 6) return 6;
    return $value;
  }
}

if (!function_exists('starscream_is_announcement_selected_item')) {
  function starscream_is_announcement_selected_item($control = null, $item = 1) {
    if (!starscream_is_announcement_enabled($control)) return false;

    $item = starscream_sanitize_announcement_editor_item($item);
    if ($control && isset($control->manager)) {
      $setting = $control->manager->get_setting('announcement_bar_editor_item');
      if ($setting) {
        return starscream_sanitize_announcement_editor_item($setting->value()) === $item;
      }
    }

    return starscream_sanitize_announcement_editor_item(get_theme_mod('announcement_bar_editor_item', 1)) === $item;
  }
}

if (class_exists('WP_Customize_Control') && !class_exists('Starscream_Customize_Announcement_Item_Control')) {
  class Starscream_Customize_Announcement_Item_Control extends WP_Customize_Control {
    public $type = 'starscream_announcement_item';
    public $icons = [];

    public function render_content() {
      $icons = is_array($this->icons) ? $this->icons : [];

      $selected_icon = (string) $this->value('icon');
      if (!array_key_exists($selected_icon, $icons)) $selected_icon = '';

      $text_value = (string) $this->value('text');
      ?>
      <span class="customize-control-title"><?php echo esc_html($this->label); ?></span>
      <div class="btx-announcement-item-row">
        <div class="btx-icon-picker" data-control-id="<?php echo esc_attr($this->id); ?>">
          <input type="hidden" class="btx-icon-value" <?php $this->link('icon'); ?> value="<?php echo esc_attr($selected_icon); ?>">
          <button type="button" class="button btx-icon-toggle" aria-expanded="false">
            <span class="btx-icon-preview">
              <?php
              if (isset($icons[$selected_icon]) && !empty($icons[$selected_icon]['svg'])) {
                echo '<span class="btx-icon-option-svg">' . $icons[$selected_icon]['svg'] . '</span>';
              } else {
                echo '<span class="btx-icon-none">-</span>';
              }
              ?>
            </span>
            <span class="btx-icon-caret" aria-hidden="true">▾</span>
          </button>
          <div class="btx-icon-menu" hidden>
            <div class="btx-icon-list" role="listbox" aria-label="<?php echo esc_attr($this->label . ' Icon'); ?>">
              <?php foreach ($icons as $key => $icon) : ?>
                <?php
                $is_active = ((string) $key === $selected_icon) ? ' is-active' : '';
                $aria = !empty($icon['label']) ? $icon['label'] : 'No Icon';
                ?>
                <button
                  type="button"
                  class="btx-icon-option<?php echo esc_attr($is_active); ?>"
                  data-icon-key="<?php echo esc_attr($key); ?>"
                  aria-label="<?php echo esc_attr($aria); ?>"
                  aria-selected="<?php echo ((string) $key === $selected_icon) ? 'true' : 'false'; ?>"
                >
                  <?php
                  if (!empty($icon['svg'])) {
                    echo '<span class="btx-icon-option-svg">' . $icon['svg'] . '</span>';
                  } else {
                    echo '<span class="btx-icon-none">-</span>';
                  }
                  ?>
                </button>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <input
          type="text"
          class="btx-announcement-text"
          maxlength="40"
          placeholder="Announcement text"
          <?php $this->link('text'); ?>
          value="<?php echo esc_attr($text_value); ?>"
        >
      </div>
      <?php
    }
  }
}

add_action('customize_register', function ($wp_customize) {
  $section = starscream_customizer_section_id();
  $icons   = starscream_announcement_icon_library();
  $start   = 6;

  if (function_exists('starscream_add_customizer_divider')) {
    starscream_add_customizer_divider($wp_customize, $section, 'btx_divider_announcement_bar', 'Announcement Bar', 5);
  }

  $wp_customize->add_setting('announcement_bar_enabled', [
    'default'           => false,
    'sanitize_callback' => 'starscream_sanitize_checkbox',
  ]);
  $wp_customize->add_control('announcement_bar_enabled', [
    'label'    => 'Enable Announcement Bar',
    'section'  => $section,
    'type'     => 'checkbox',
    'priority' => $start,
  ]);

  $wp_customize->add_setting('announcement_bar_bg_color', [
    'default'           => '#151515',
    'sanitize_callback' => 'sanitize_hex_color',
  ]);
  $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'announcement_bar_bg_color', [
    'label'    => 'Announcement Bar Background Color',
    'section'  => $section,
    'settings' => 'announcement_bar_bg_color',
    'priority' => $start + 1,
    'active_callback' => 'starscream_is_announcement_enabled',
  ]));

  $wp_customize->add_setting('announcement_bar_editor_item', [
    'default' => 1,
    'sanitize_callback' => 'starscream_sanitize_announcement_editor_item',
  ]);
  $wp_customize->add_control('announcement_bar_editor_item', [
    'label' => 'Announcement Editor',
    'description' => 'Choose an announcement to edit.',
    'section' => $section,
    'type' => 'select',
    'choices' => [
      '1' => 'Announcement 1',
      '2' => 'Announcement 2',
      '3' => 'Announcement 3',
      '4' => 'Announcement 4',
      '5' => 'Announcement 5',
      '6' => 'Announcement 6',
    ],
    'priority' => $start + 2,
    'active_callback' => 'starscream_is_announcement_enabled',
  ]);

  if (function_exists('starscream_add_customizer_divider')) {
    starscream_add_customizer_divider($wp_customize, $section, 'btx_divider_transparent_header', 'Transparent Header', 60);
  }
  $wp_customize->add_setting('transparent_header_enabled', [
    'default'           => false,
    'sanitize_callback' => 'starscream_sanitize_checkbox',
  ]);
  $wp_customize->add_control('transparent_header_enabled', [
    'label'    => 'Transparent Header',
    'section'  => $section,
    'type'     => 'checkbox',
    'priority' => 61,
  ]);

  for ($i = 1; $i <= 6; $i++) {
    $text_setting = "announcement_bar_item_{$i}_text";
    $icon_setting = "announcement_bar_item_{$i}_icon";
    $base         = $start + 10 + ($i - 1);

    $wp_customize->add_setting($icon_setting, [
      'default'           => '',
      'sanitize_callback' => 'starscream_sanitize_announcement_icon',
    ]);

    $wp_customize->add_setting($text_setting, [
      'default'           => '',
      'sanitize_callback' => 'starscream_sanitize_announcement_text',
    ]);

    if (class_exists('Starscream_Customize_Announcement_Item_Control')) {
      $item_active = function ($control) use ($i) {
        return starscream_is_announcement_selected_item($control, $i);
      };
      $wp_customize->add_control(new Starscream_Customize_Announcement_Item_Control($wp_customize, "announcement_bar_item_{$i}", [
        'label'    => "Announcement {$i}",
        'section'  => $section,
        'priority' => $base,
        'active_callback' => $item_active,
        'settings' => [
          'icon' => $icon_setting,
          'text' => $text_setting,
        ],
        'icons' => $icons,
      ]));
    }
  }
}, 11);

add_action('customize_controls_print_styles', function () {
  ?>
  <style id="starscream-announcement-control-styles">
    .customize-control-starscream_announcement_item .customize-control-title {
      margin-bottom: 6px;
    }
    .btx-announcement-item-row {
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .btx-icon-picker {
      position: relative;
      flex: 0 0 auto;
    }
    .btx-icon-toggle {
      display: inline-flex;
      align-items: center;
      justify-content: space-between;
      gap: 6px;
      width: 44px;
      min-height: 34px;
      padding: 0 6px;
    }
    .btx-icon-preview {
      display: inline-flex;
      align-items: center;
      justify-content: center;
    }
    .btx-icon-caret {
      font-size: 9px;
      line-height: 1;
      opacity: .8;
    }
    .btx-icon-menu {
      position: absolute;
      top: calc(100% + 4px);
      left: 0;
      z-index: 100000;
      border: 1px solid #ccd0d4;
      border-radius: 3px;
      background: #fff;
      box-shadow: 0 2px 8px rgba(0, 0, 0, .12);
      padding: 2px;
    }
    .btx-icon-list {
      display: flex;
      flex-direction: column;
      width: 42px;
      max-height: 120px;
      overflow-y: auto;
    }
    .btx-icon-option svg {
      width: 16px;
      height: 16px;
      display: block;
      fill: currentColor;
    }
    .btx-icon-option {
      width: 100%;
      min-height: 28px;
      border: 1px solid transparent;
      background: #fff;
      border-radius: 3px;
      padding: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      margin: 1px 0;
    }
    .btx-icon-option:hover,
    .btx-icon-option:focus {
      border-color: #2271b1;
      outline: 0;
    }
    .btx-icon-option.is-active {
      border-color: #2271b1;
      background: #f0f6fc;
    }
    .btx-announcement-text {
      flex: 1 1 0%;
      min-width: 120px;
      margin: 0;
    }
    .btx-icon-none {
      display: inline-block;
      font-size: 15px;
      line-height: 1;
      font-weight: 600;
    }
  </style>
  <?php
});

add_action('customize_controls_print_footer_scripts', function () {
  ?>
  <script>
    (function () {
      document.addEventListener('click', function (event) {
        var toggle = event.target.closest('.btx-icon-toggle');
        if (toggle) {
          var pickerToggle = toggle.closest('.btx-icon-picker');
          if (!pickerToggle) return;
          var menuToggle = pickerToggle.querySelector('.btx-icon-menu');
          if (!menuToggle) return;

          var currentlyOpen = toggle.getAttribute('aria-expanded') === 'true';
          document.querySelectorAll('.btx-icon-picker').forEach(function (picker) {
            var menu = picker.querySelector('.btx-icon-menu');
            var btn = picker.querySelector('.btx-icon-toggle');
            if (menu) menu.hidden = true;
            if (btn) btn.setAttribute('aria-expanded', 'false');
          });
          menuToggle.hidden = currentlyOpen;
          toggle.setAttribute('aria-expanded', currentlyOpen ? 'false' : 'true');
          return;
        }

        var option = event.target.closest('.btx-icon-option');
        if (option) {
          var picker = option.closest('.btx-icon-picker');
          if (!picker) return;

          var input = picker.querySelector('.btx-icon-value');
          var key = option.getAttribute('data-icon-key') || '';
          var preview = picker.querySelector('.btx-icon-preview');
          var menu = picker.querySelector('.btx-icon-menu');
          var toggleBtn = picker.querySelector('.btx-icon-toggle');

          if (input) {
            input.value = key;
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
          }

          if (preview) {
            var iconSvg = option.querySelector('.btx-icon-option-svg');
            preview.innerHTML = iconSvg ? iconSvg.outerHTML : '<span class="btx-icon-none">-</span>';
          }

          picker.querySelectorAll('.btx-icon-option').forEach(function (btn) {
            btn.classList.remove('is-active');
            btn.setAttribute('aria-selected', 'false');
          });
          option.classList.add('is-active');
          option.setAttribute('aria-selected', 'true');

          if (menu) menu.hidden = true;
          if (toggleBtn) toggleBtn.setAttribute('aria-expanded', 'false');
          return;
        }

        if (!event.target.closest('.btx-icon-picker')) {
          document.querySelectorAll('.btx-icon-picker').forEach(function (picker) {
            var menu = picker.querySelector('.btx-icon-menu');
            var btn = picker.querySelector('.btx-icon-toggle');
            if (menu) menu.hidden = true;
            if (btn) btn.setAttribute('aria-expanded', 'false');
          });
        }
      });

      document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') return;
        document.querySelectorAll('.btx-icon-picker').forEach(function (picker) {
          var menu = picker.querySelector('.btx-icon-menu');
          var btn = picker.querySelector('.btx-icon-toggle');
          if (menu) menu.hidden = true;
          if (btn) btn.setAttribute('aria-expanded', 'false');
        });
      });
    })();
  </script>
  <?php
});

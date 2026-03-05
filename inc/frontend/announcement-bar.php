<?php
if (!defined('ABSPATH')) exit;

if (!function_exists('starscream_get_announcement_items')) {
  function starscream_get_announcement_items() {
    $library = function_exists('starscream_announcement_icon_library')
      ? starscream_announcement_icon_library()
      : [];

    $items = [];
    for ($i = 1; $i <= 6; $i++) {
      $text = trim((string) get_theme_mod("announcement_bar_item_{$i}_text", ''));
      if ($text === '') continue;

      $icon_key = (string) get_theme_mod("announcement_bar_item_{$i}_icon", '');
      if (function_exists('starscream_sanitize_announcement_icon')) {
        $icon_key = starscream_sanitize_announcement_icon($icon_key);
      } else {
        $icon_key = '';
      }

      $items[] = [
        'text' => $text,
        'svg'  => isset($library[$icon_key]['svg']) ? $library[$icon_key]['svg'] : '',
      ];
    }

    return $items;
  }
}

if (!function_exists('starscream_render_announcement_bar')) {
  function starscream_render_announcement_bar() {
    if (is_admin()) return;
    if (!get_theme_mod('announcement_bar_enabled', false)) return;

    $items = starscream_get_announcement_items();
    if (!$items) return;

    echo '<div class="btx-announcement-bar" data-announcement-bar="1" role="region" aria-label="Announcement bar">';
    echo '<div class="btx-announcement-bar__inner">';
    echo '<div class="btx-announcement-bar__track">';

    for ($loop = 0; $loop < 2; $loop++) {
      $hidden = $loop > 0 ? ' aria-hidden="true"' : '';
      echo '<div class="btx-announcement-bar__group"' . $hidden . '>';
      foreach ($items as $item) {
        echo '<span class="btx-announcement-bar__item">';
        if (!empty($item['svg'])) {
          echo '<span class="btx-announcement-bar__icon" aria-hidden="true">' . $item['svg'] . '</span>';
        }
        echo '<span class="btx-announcement-bar__text">' . esc_html($item['text']) . '</span>';
        echo '</span>';
      }
      echo '</div>';
    }

    echo '</div>';
    echo '</div>';
    echo '</div>';
  }
}

add_action('wp_body_open', 'starscream_render_announcement_bar', 4);

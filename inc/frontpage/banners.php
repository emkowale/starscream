<?php
if (!defined('ABSPATH')) exit;

if (!function_exists('btx_is_home_like')) {
  function btx_is_home_like() {
    return is_front_page() || is_home() || (function_exists('is_shop') && is_shop());
  }
}

if (!function_exists('starscream_get_header_slider_slides')) {
  function starscream_get_header_slider_slides() {
    static $cache = null;
    if (is_array($cache)) return $cache;

    $slides = [];
    $max_slides = function_exists('starscream_header_slider_max_slides') ? starscream_header_slider_max_slides() : 6;
    for ($i = 1; $i <= $max_slides; $i++) {
      $desktop_id = (int) get_theme_mod("header_slider_slide_{$i}_desktop_image_id", 0);
      $mobile_id  = (int) get_theme_mod("header_slider_slide_{$i}_mobile_image_id", 0);
      if (!$desktop_id || !$mobile_id) continue;

      $desktop = wp_get_attachment_image_url($desktop_id, 'full');
      $mobile  = wp_get_attachment_image_url($mobile_id, 'full');
      if (!$desktop || !$mobile) continue;

      $line_1 = trim((string) get_theme_mod("header_slider_slide_{$i}_line_1", ''));
      $line_2 = trim((string) get_theme_mod("header_slider_slide_{$i}_line_2", ''));
      $line_3 = trim((string) get_theme_mod("header_slider_slide_{$i}_line_3", ''));
      $btn    = trim((string) get_theme_mod("header_slider_slide_{$i}_button_text", ''));
      $url    = trim((string) get_theme_mod("header_slider_slide_{$i}_button_url", ''));
      $alt    = trim((string) get_post_meta($desktop_id, '_wp_attachment_image_alt', true));

      if ($line_1 === '' && $line_2 === '' && $line_3 === '') {
        $line_2 = get_bloginfo('name');
      }
      if ($alt === '') {
        $alt = $line_2 !== '' ? $line_2 : get_bloginfo('name');
      }

      $slides[] = [
        'desktop' => $desktop,
        'mobile'  => $mobile,
        'line_1'  => $line_1,
        'line_2'  => $line_2,
        'line_3'  => $line_3,
        'btn'     => $btn,
        'url'     => $url,
        'alt'     => $alt,
      ];
    }

    $cache = $slides;
    return $cache;
  }
}

if (!function_exists('starscream_should_render_header_slider')) {
  function starscream_should_render_header_slider() {
    if (is_admin() || !btx_is_home_like()) return false;
    if (!get_theme_mod('header_slider_enabled', false)) return false;
    return count(starscream_get_header_slider_slides()) > 0;
  }
}

add_filter('body_class', function ($classes) {
  if (starscream_should_render_header_slider()) $classes[] = 'btx-has-header-slider';
  return $classes;
}, 25);

if (!function_exists('starscream_render_header_slider')) {
  function starscream_render_header_slider() {
    static $printed = false;
    if ($printed || !starscream_should_render_header_slider()) return;

    $slides = starscream_get_header_slider_slides();
    $total  = count($slides);
    if ($total < 1) return;

    $autoplay_seconds = absint(get_theme_mod('header_slider_autoplay_seconds', 0));
    if ($autoplay_seconds < 1) {
      $legacy_ms = absint(get_theme_mod('header_slider_autoplay_ms', 0));
      if ($legacy_ms > 0) $autoplay_seconds = (int) round($legacy_ms / 1000);
    }
    if ($autoplay_seconds < 3) $autoplay_seconds = 10;
    if ($autoplay_seconds > 30) $autoplay_seconds = 30;
    $autoplay = $autoplay_seconds * 1000;

    $printed = true;

    echo '<section class="btx-header-slider" data-btx-header-slider="1" data-autoplay-ms="' . esc_attr($autoplay) . '" role="region" aria-label="Header slider">';
    echo '<div class="btx-header-slider__viewport">';

    foreach ($slides as $idx => $slide) {
      $is_active = ($idx === 0);
      $loading   = $is_active ? 'eager' : 'lazy';
      $fetchprio = $is_active ? 'high' : 'auto';
      $slide_num = str_pad((string) ($idx + 1), 2, '0', STR_PAD_LEFT);

      echo '<article class="btx-header-slider__slide' . ($is_active ? ' is-active' : '') . '" data-slide-index="' . esc_attr((string) $idx) . '" aria-hidden="' . ($is_active ? 'false' : 'true') . '">';
      echo '<picture class="btx-header-slider__picture">';
      echo '<source media="(max-width: 767px)" srcset="' . esc_url($slide['mobile']) . '">';
      echo '<img class="btx-header-slider__img" src="' . esc_url($slide['desktop']) . '" alt="' . esc_attr($slide['alt']) . '" loading="' . esc_attr($loading) . '" fetchpriority="' . esc_attr($fetchprio) . '" decoding="async">';
      echo '</picture>';
      echo '<div class="btx-header-slider__shade" aria-hidden="true"></div>';

      echo '<div class="btx-header-slider__content">';
      if ($slide['line_1'] !== '') echo '<p class="btx-header-slider__line btx-header-slider__line--top">' . esc_html($slide['line_1']) . '</p>';
      if ($slide['line_2'] !== '') echo '<h1 class="btx-header-slider__title">' . esc_html($slide['line_2']) . '</h1>';
      if ($slide['line_3'] !== '') echo '<p class="btx-header-slider__line btx-header-slider__line--bottom">' . esc_html($slide['line_3']) . '</p>';
      if ($slide['btn'] !== '' && $slide['url'] !== '') {
        echo '<a class="btx-header-slider__cta" href="' . esc_url($slide['url']) . '">' . esc_html($slide['btn']) . '</a>';
      }
      echo '</div>';

      echo '<span class="screen-reader-text">Slide ' . esc_html($slide_num) . '</span>';
      echo '</article>';
    }

    echo '<div class="btx-header-slider__chrome">';
    echo '<div class="btx-header-slider__dots" role="tablist" aria-label="Header slider dots">';
    foreach ($slides as $idx => $slide) {
      $is_current = $idx === 0 ? 'true' : 'false';
      echo '<button type="button" class="btx-header-slider__dot' . ($idx === 0 ? ' is-active' : '') . '" role="tab" aria-selected="' . esc_attr($is_current) . '" aria-label="Go to slide ' . esc_attr((string) ($idx + 1)) . '" data-slide-target="' . esc_attr((string) $idx) . '"><span aria-hidden="true" class="btx-header-slider__dot-core"></span></button>';
    }
    echo '</div>';

    echo '<div class="btx-header-slider__meter-wrap">';
    echo '<span class="btx-header-slider__meter" aria-hidden="true">';
    echo '<svg viewBox="0 0 44 44" focusable="false" aria-hidden="true">';
    echo '<circle class="btx-header-slider__meter-track" cx="22" cy="22" r="18"></circle>';
    echo '<circle class="btx-header-slider__meter-value" cx="22" cy="22" r="18" data-slider-progress></circle>';
    echo '</svg>';
    echo '<span class="btx-header-slider__percent" data-slider-percent>0%</span>';
    echo '</span>';
    echo '</div>';
    echo '</div>';

    echo '</div>';
    echo '</section>';
  }
}

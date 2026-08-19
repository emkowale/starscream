<?php
if (!defined('ABSPATH')) exit;

if (!function_exists('starscream_get_popup_store_close_time')) {
  function starscream_get_popup_store_close_time($date = null) {
    $date = $date === null ? get_theme_mod('popup_store_closing_date', '') : $date;
    if (function_exists('starscream_sanitize_popup_store_date')) {
      $date = starscream_sanitize_popup_store_date($date);
    }
    if ($date === '') return null;

    $timezone = function_exists('wp_timezone') ? wp_timezone() : new DateTimeZone('America/Detroit');
    $close = DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $date . ' 23:00:00', $timezone);
    if (!$close) return null;

    return $close;
  }
}

if (!function_exists('starscream_is_popup_store_active')) {
  function starscream_is_popup_store_active() {
    if (is_admin()) return false;
    if (!get_theme_mod('popup_store_enabled', false)) return false;

    $close = starscream_get_popup_store_close_time();
    return $close instanceof DateTimeImmutable && $close->getTimestamp() > time();
  }
}

if (!function_exists('starscream_is_popup_store_closed')) {
  function starscream_is_popup_store_closed() {
    if (is_admin()) return false;
    if (function_exists('is_customize_preview') && is_customize_preview()) return false;
    if (!get_theme_mod('popup_store_enabled', false)) return false;

    $close = starscream_get_popup_store_close_time();
    return $close instanceof DateTimeImmutable && $close->getTimestamp() <= time();
  }
}

if (!function_exists('starscream_render_popup_store_closed_page')) {
  function starscream_render_popup_store_closed_page() {
    if (!starscream_is_popup_store_closed()) return;

    $close = starscream_get_popup_store_close_time();
    $date_label = $close instanceof DateTimeImmutable
      ? wp_date(get_option('date_format'), $close->getTimestamp())
      : '';

    status_header(200);
    nocache_headers();
    ?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title><?php echo esc_html(get_bloginfo('name') . ' - Store Closed'); ?></title>
  <style>
    html,
    body {
      width: 100%;
      min-height: 100%;
      margin: 0;
    }

    body {
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      color: #ffffff;
      background: #171717;
      font-family: system-ui, -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
      text-align: center;
    }

    .btx-store-closed {
      width: min(100% - 32px, 720px);
    }

    .btx-store-closed__title {
      margin: 0;
      font-size: clamp(3rem, 10vw, 7.5rem);
      font-weight: 800;
      line-height: .95;
      letter-spacing: 0;
      text-transform: uppercase;
    }

    .btx-store-closed__date {
      display: block;
      margin-top: .2em;
      font-size: .5em;
      line-height: 1.15;
    }
  </style>
</head>
<body>
  <main class="btx-store-closed" aria-label="Store status">
    <h1 class="btx-store-closed__title">
      Store Closed
      <span class="btx-store-closed__date">as of <?php echo esc_html($date_label); ?></span>
    </h1>
  </main>
</body>
</html>
    <?php
    exit;
  }
}

add_action('template_redirect', 'starscream_render_popup_store_closed_page', 0);

add_filter('body_class', function ($classes) {
  if (starscream_is_popup_store_active()) {
    $classes[] = 'btx-popup-store-active';
  }
  return $classes;
}, 20);

if (!function_exists('starscream_render_popup_store_banner')) {
  function starscream_render_popup_store_banner() {
    if (!starscream_is_popup_store_active()) return;

    $close = starscream_get_popup_store_close_time();
    if (!$close) return;

    $timestamp_ms = $close->getTimestamp() * 1000;
    $date_label = wp_date(get_option('date_format'), $close->getTimestamp());
    $datetime = $close->format(DateTimeInterface::ATOM);

    echo '<div class="btx-popup-store-banner" data-popup-store="1" data-popup-store-close="' . esc_attr((string) $timestamp_ms) . '" role="region" aria-label="Popup store closing countdown">';
    echo '<div class="btx-popup-store-banner__inner">';
    echo '<div class="btx-popup-store-banner__message">Store closing on <time datetime="' . esc_attr($datetime) . '">' . esc_html($date_label) . '</time></div>';
    echo '<div class="btx-popup-store-banner__countdown" data-popup-store-countdown aria-live="polite">';
    echo '<span data-popup-store-days>0</span>D <span data-popup-store-hours>00</span>:<span data-popup-store-minutes>00</span>:<span data-popup-store-seconds>00</span>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
  }
}

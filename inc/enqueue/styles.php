<?php
if (!defined('ABSPATH')) exit;

add_action('wp_enqueue_scripts', function () {
  // Keep theme stylesheet (for header comment / child overrides if any)
  wp_enqueue_style('starscream-style', get_stylesheet_uri(), [], wp_get_theme()->get('Version'));

  // Enqueue modular CSS (child can override via same paths)
  $v = wp_get_theme()->get('Version');

  wp_enqueue_style('starscream-base',    starscream_asset_uri('assets/css/base.css'),    [], $v);
  wp_enqueue_style('starscream-hero',    starscream_asset_uri('assets/css/hero.css'),    ['starscream-base'], $v);
  wp_enqueue_style('starscream-cards',   starscream_asset_uri('assets/css/cards.css'),   ['starscream-base'], $v);
  // Wishlist CSS (only enqueue if the file actually exists)
  $wishlist_path = function_exists('starscream_locate') ? starscream_locate('assets/css/wishlist.css') : '';
  if ($wishlist_path && file_exists($wishlist_path)) {
    wp_enqueue_style(
      'starscream-wishlist',
      starscream_asset_uri('assets/css/wishlist.css'),
      ['starscream-cards'],
      filemtime($wishlist_path)
    );
  }
  wp_enqueue_style('starscream-woo-typ', starscream_asset_uri('assets/css/woocommerce-typography.css'), [], $v);
  wp_enqueue_style('starscream-buttons', starscream_asset_uri('assets/css/buttons.css'), [], $v);
  wp_enqueue_style('starscream-checkout',starscream_asset_uri('assets/css/checkout.css'),['starscream-buttons'], $v);
  wp_enqueue_style('starscream-mobile',  starscream_asset_uri('assets/css/mobile-shop.css'), [], $v);
  wp_enqueue_style('starscream-logo',    starscream_asset_uri('assets/css/logo.css'),    [], $v);
  wp_enqueue_style('starscream-header',  starscream_asset_uri('assets/css/header.css'), ['starscream-base'], wp_get_theme()->get('Version'));
  $header_slider_css = starscream_locate('assets/css/header-slider.css');
  if ($header_slider_css && file_exists($header_slider_css)) {
    wp_enqueue_style(
      'starscream-header-slider',
      starscream_asset_uri('assets/css/header-slider.css'),
      ['starscream-base', 'starscream-header'],
      filemtime($header_slider_css)
    );
  }
  $tbt_slider_css = starscream_locate('assets/css/tbt-slider.css');
  if ($tbt_slider_css && file_exists($tbt_slider_css)) {
    wp_enqueue_style(
      'starscream-tbt-slider',
      starscream_asset_uri('assets/css/tbt-slider.css'),
      ['starscream-base', 'starscream-header'],
      filemtime($tbt_slider_css)
    );
  }
  $tbt_google_reviews_css = starscream_locate('assets/css/tbt-googlereviews.css');
  if ($tbt_google_reviews_css && file_exists($tbt_google_reviews_css)) {
    wp_enqueue_style(
      'starscream-tbt-googlereviews',
      starscream_asset_uri('assets/css/tbt-googlereviews.css'),
      ['starscream-base', 'starscream-header'],
      filemtime($tbt_google_reviews_css)
    );
  }
  $announcement_css = starscream_locate('assets/css/announcement-bar.css');
  if ($announcement_css && file_exists($announcement_css)) {
    wp_enqueue_style(
      'starscream-announcement-bar',
      starscream_asset_uri('assets/css/announcement-bar.css'),
      ['starscream-base'],
      filemtime($announcement_css)
    );
  }
  wp_enqueue_style('starscream-footer', starscream_asset_uri('assets/css/footer.css'), ['starscream-header'], wp_get_theme()->get('Version'));
  wp_enqueue_style('starscream-banners', starscream_asset_uri('assets/css/banners.css'), [], $v);
  wp_enqueue_style('starscream-cart', starscream_asset_uri('assets/css/cart.css'), [], $v);

  $announcement_js = starscream_locate('assets/js/announcement-bar.js');
  if ($announcement_js && file_exists($announcement_js)) {
    wp_enqueue_script(
      'starscream-announcement-bar',
      starscream_asset_uri('assets/js/announcement-bar.js'),
      [],
      filemtime($announcement_js),
      true
    );
  }

  $header_slider_js = starscream_locate('assets/js/header-slider.js');
  if ($header_slider_js && file_exists($header_slider_js)) {
    wp_enqueue_script(
      'starscream-header-slider',
      starscream_asset_uri('assets/js/header-slider.js'),
      [],
      filemtime($header_slider_js),
      true
    );
  }

  $tbt_google_reviews_js = starscream_locate('assets/js/tbt-googlereviews.js');
  if ($tbt_google_reviews_js && file_exists($tbt_google_reviews_js)) {
    wp_enqueue_script(
      'starscream-tbt-googlereviews',
      starscream_asset_uri('assets/js/tbt-googlereviews.js'),
      [],
      filemtime($tbt_google_reviews_js),
      true
    );
  }
}, 50);

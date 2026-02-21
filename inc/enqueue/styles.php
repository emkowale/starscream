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
  wp_enqueue_style('starscream-footer', starscream_asset_uri('assets/css/footer.css'), ['starscream-header'], wp_get_theme()->get('Version'));
  wp_enqueue_style('starscream-banners', starscream_asset_uri('assets/css/banners.css'), [], $v);
  wp_enqueue_style('starscream-cart', starscream_asset_uri('assets/css/cart.css'), [], $v);
}, 50);

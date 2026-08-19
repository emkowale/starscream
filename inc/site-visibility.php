<?php
if (!defined('ABSPATH')) exit;

// Force site visibility to "live" (public) and disable Woo demo store.
add_action('init', function () {
  if (get_option('blog_public') !== '1') update_option('blog_public', '1');
  if (get_option('woocommerce_demo_store') === 'yes') update_option('woocommerce_demo_store', 'no');
  if (get_option('woocommerce_coming_soon') === 'yes') update_option('woocommerce_coming_soon', 'no');
  if (get_option('woocommerce_store_pages_only') === 'yes') update_option('woocommerce_store_pages_only', 'no');
}, 1);

// Short-circuit to public even if another plugin tries to override later.
add_filter('pre_option_blog_public', function () { return '1'; });
add_filter('option_blog_public', function () { return '1'; });

// Prevent Woo from re-enabling the demo store notice.
add_filter('pre_option_woocommerce_demo_store', function () { return 'no'; });
add_filter('option_woocommerce_demo_store', function () { return 'no'; });

// Force Woo "Coming soon" mode off.
add_filter('pre_option_woocommerce_coming_soon', function () { return 'no'; });
add_filter('option_woocommerce_coming_soon', function () { return 'no'; });
add_filter('pre_option_woocommerce_store_pages_only', function () { return 'no'; });
add_filter('option_woocommerce_store_pages_only', function () { return 'no'; });

// Ensure robots output stays indexable.
add_filter('wp_robots', function ($robots) {
  $robots['noindex'] = false;
  $robots['nofollow'] = false;
  return $robots;
});

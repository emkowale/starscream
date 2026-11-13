<?php
if (!defined('ABSPATH')) exit;

/** Disable WordPress comments and pingbacks for storefront sites. */
add_filter('comments_open', '__return_false', PHP_INT_MAX);
add_filter('pings_open', '__return_false', PHP_INT_MAX);
add_filter('comments_array', '__return_empty_array', PHP_INT_MAX);
add_action('admin_init', function () {
    update_option('default_comment_status', 'closed');
    update_option('default_ping_status', 'closed');
});

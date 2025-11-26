<?php
if (!defined('ABSPATH')) exit;

// Force site visibility to "live" (public) on every load.
add_action('init', function () {
  if (get_option('blog_public') !== '1') update_option('blog_public', '1');
});

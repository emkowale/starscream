<?php
if (!defined('ABSPATH')) exit;

/**
 * Force the site timezone to America/Detroit.
 * Uses timezone_string (preferred) and clears gmt_offset to avoid conflicts.
 */
add_action('init', function () {
  $tz = get_option('timezone_string');
  if ($tz !== 'America/Detroit') {
    update_option('timezone_string', 'America/Detroit');
    delete_option('gmt_offset');
  }
});

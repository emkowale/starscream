<?php
if (!defined('ABSPATH')) exit;

/**
 * Ensure the default category is named/slugs as "General".
 * If a "general" category already exists, switch the default to it.
 */
add_action('init', function () {
  $default_id = (int) get_option('default_category', 0);

  // If a "general" category already exists, make it the default.
  $existing_general = term_exists('general', 'category');
  if ($existing_general && !is_wp_error($existing_general)) {
    $general_id = (int) $existing_general['term_id'];
    if ($general_id > 0 && $general_id !== $default_id) {
      update_option('default_category', $general_id);
      $default_id = $general_id;
    }
  }

  if ($default_id <= 0) return;

  $term = get_term($default_id, 'category');
  if (!$term || is_wp_error($term)) return;

  $args = [];
  if ($term->name !== 'General') $args['name'] = 'General';
  if ($term->slug !== 'general') $args['slug'] = 'general';

  if ($args) wp_update_term($term->term_id, 'category', $args);
}, 5);

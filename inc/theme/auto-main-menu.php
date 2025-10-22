<?php
if (!defined('ABSPATH')) exit;

/**
 * Ensure a menu named "Main Menu" exists and is assigned to the 'primary' location.
 * - No items are added.
 * - Runs on theme switch and on admin_init (admin only, cap-checked).
 */
function starscream_set_primary_empty_main_menu() {
    if (!is_admin() || !current_user_can('edit_theme_options')) return;

    // Ensure 'primary' exists as a registered location
    $registered = get_registered_nav_menus();
    if (empty($registered['primary'])) return;

    if (!function_exists('wp_create_nav_menu')) {
        require_once ABSPATH . 'wp-admin/includes/nav-menu.php';
    }

    $menu_name = 'Main Menu';
    $menu_obj  = wp_get_nav_menu_object($menu_name);
    $menu_id   = $menu_obj && !is_wp_error($menu_obj) ? (int) $menu_obj->term_id : 0;

    if ($menu_id <= 0) {
        $menu_id = (int) wp_create_nav_menu($menu_name);
        if (is_wp_error($menu_id) || $menu_id <= 0) return;
    }

    // Assign "Main Menu" to 'primary' (unconditionally, per request)
    $locations = (array) get_theme_mod('nav_menu_locations', []);
    $locations['primary'] = $menu_id;
    set_theme_mod('nav_menu_locations', $locations);
}
add_action('after_switch_theme', 'starscream_set_primary_empty_main_menu');
add_action('admin_init',        'starscream_set_primary_empty_main_menu');

<?php
if (!defined('ABSPATH')) exit;

function starscream_set_primary_empty_main_menu() {
    if (!is_admin() || !current_user_can('edit_theme_options')) return;
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
    $locations = (array) get_theme_mod('nav_menu_locations', []);
    $locations['primary'] = $menu_id;
    set_theme_mod('nav_menu_locations', $locations);
}
add_action('after_switch_theme', 'starscream_set_primary_empty_main_menu');
add_action('admin_init',        'starscream_set_primary_empty_main_menu');

function starscream_add_product_cats_to_main_menu() {
    if (!is_admin() || !current_user_can('edit_theme_options')) return;
    if (!taxonomy_exists('product_cat')) return;
    if (!function_exists('wp_create_nav_menu')) {
        require_once ABSPATH . 'wp-admin/includes/nav-menu.php';
    }
    $menu = wp_get_nav_menu_object('Main Menu');
    if (!$menu || is_wp_error($menu)) return;
    $menu_id = (int) $menu->term_id;
    if ($menu_id <= 0) return;
    $uncat = get_term_by('slug', 'uncategorized', 'product_cat');
    if ($uncat && !is_wp_error($uncat)) {
        wp_update_term((int) $uncat->term_id, 'product_cat', ['name' => 'General', 'slug' => 'general']);
    }
    $existing = [];
    $menu_items = (array) wp_get_nav_menu_items($menu_id);
    foreach ($menu_items as $item) {
        if ($item->type === 'taxonomy' && $item->object === 'product_cat') {
            $existing[] = (int) $item->object_id;
        }
    }
    $cats = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => false]);
    if (is_wp_error($cats) || count($cats) <= 1) {
        foreach ($menu_items as $item) {
            if ($item->type === 'taxonomy' && $item->object === 'product_cat') {
                wp_delete_post($item->ID, true);
            }
        }
        return;
    }

    foreach ($cats as $cat) {
        $id = (int) $cat->term_id;
        if (in_array($id, $existing, true)) continue;
        wp_update_nav_menu_item($menu_id, 0, [
            'menu-item-object-id' => $id,
            'menu-item-object'    => 'product_cat',
            'menu-item-type'      => 'taxonomy',
            'menu-item-status'    => 'publish',
        ]);
    }
}
add_action('admin_init', 'starscream_add_product_cats_to_main_menu', 12);
function starscream_filter_menu_product_cats($items, $args) {
    $loc_ok = isset($args->theme_location) && $args->theme_location === 'primary';
    $name_ok = isset($args->menu) && ((is_object($args->menu) && $args->menu->name === 'Main Menu') || $args->menu === 'Main Menu');
    if (!$loc_ok && !$name_ok) return $items;

    $cats = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => false]);
    if (is_wp_error($cats) || count($cats) > 1) return $items;

    $filtered = [];
    foreach ((array) $items as $item) {
        if ($item->type === 'taxonomy' && $item->object === 'product_cat') continue;
        $filtered[] = $item;
    }
    return $filtered;
}
add_filter('wp_nav_menu_objects', 'starscream_filter_menu_product_cats', 10, 2);

<?php
/*
 * File: inc/ensure-classic-pages.php
 * Description: On theme switch, force WooCommerce core pages to use their classic shortcodes.
 * Theme: Starscream
 * Author: Eric Kowalewski
 * Version: 1.1.0
 * Last Updated: 2025-08-28 — 19:30 EDT
 */

if (!defined('ABSPATH')) exit;

add_action('after_switch_theme', function () {

    if (!class_exists('WooCommerce')) {
        set_transient('starscream_wc_msg', 'WooCommerce not active — could not set WC pages.', 60);
        return;
    }

    // List of WooCommerce pages and shortcodes
    $pages = [
        'woocommerce_cart_page_id'      => '[woocommerce_cart]',
        'woocommerce_checkout_page_id'  => '[woocommerce_checkout]',
        'woocommerce_myaccount_page_id' => '[woocommerce_my_account]',
        // Optional: uncomment if you use order tracking
        // 'woocommerce_order_tracking_page_id' => '[woocommerce_order_tracking]',
    ];

    foreach ($pages as $option_key => $shortcode) {
        $page_id = (int) get_option($option_key);

        // If missing, try to find by title
        if (!$page_id) {
            $title = ucwords(str_replace(['woocommerce_', '_page_id'], ['', ''], $option_key));
            $page = get_page_by_title($title);
            if ($page instanceof WP_Post) {
                $page_id = $page->ID;
                update_option($option_key, $page_id);
            } else {
                // Create it
                $page_id = wp_insert_post([
                    'post_title'   => $title,
                    'post_status'  => 'publish',
                    'post_type'    => 'page',
                    'post_content' => $shortcode,
                ]);
                if ($page_id && !is_wp_error($page_id)) {
                    update_option($option_key, $page_id);
                }
            }
        }

        // Force correct shortcode into content
        if ($page_id && !is_wp_error($page_id)) {
            wp_update_post([
                'ID'           => $page_id,
                'post_content' => $shortcode,
            ]);
        }
    }

    set_transient('starscream_wc_msg', 'WooCommerce pages set to classic shortcodes.', 60);
});

// One-time notice in admin
add_action('admin_notices', function () {
    if ($msg = get_transient('starscream_wc_msg')) {
        delete_transient('starscream_wc_msg');
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($msg) . '</p></div>';
    }
});

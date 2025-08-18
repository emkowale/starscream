<?php
/**
 * Theme passthrough Cart template
 * Keeps Woo default structure but gives us a stable theme hook point.
 * Last Updated: 2025-08-13 (EDT)
 */

defined('ABSPATH') || exit;

// Optional: theme wrapper before cart
do_action('btx_cart_before');  // you can hook banners/notes here

// Load Woo’s current template so we stay in sync with Woo updates
wc_get_template( 'cart/cart.php', [], '', WC()->plugin_path() . '/templates/' );

// Optional: theme wrapper after cart
do_action('btx_cart_after');

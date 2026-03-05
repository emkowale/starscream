<?php
defined('ABSPATH') || exit;

get_header();

if ( function_exists('is_cart') && is_cart() ) {
    echo do_shortcode('[woocommerce_cart]');
} elseif ( function_exists('is_checkout') && is_checkout() ) {
    echo do_shortcode('[woocommerce_checkout]');
} elseif ( function_exists('is_account_page') && is_account_page() ) {
    echo do_shortcode('[woocommerce_my_account]');
} else {
    // Let WooCommerce render archives + single-product properly
    if ( function_exists('woocommerce_content') ) {
        woocommerce_content();
    }
}

get_footer();

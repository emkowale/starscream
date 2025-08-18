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
    // Fallback: normal page content for any other Woo page you might have
    while ( have_posts() ) :
        the_post();
        the_content();
    endwhile;
}

get_footer();

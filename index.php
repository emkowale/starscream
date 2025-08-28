<?php
/*
 * File: index.php
 * Description: Master fallback — use Woo on actual catalog/product routes; otherwise render the normal WP loop.
 * Theme: The Bear Traxs Subscription Template
 * Author: Eric Kowalewski
 * Version: 1.4.23
 * Last Updated: 2025-08-12 — 20:05 EDT
 */

defined('ABSPATH') || exit;

get_header(); ?>
<main>
<?php
$use_woo = function_exists('is_woocommerce') && ( is_shop() || is_product_taxonomy() || is_product() );

if ( $use_woo && function_exists('woocommerce_content') ) {
    woocommerce_content();
} else {
    if ( have_posts() ) {
        while ( have_posts() ) { the_post(); the_content(); }
    }
}
?>
</main>
<?php get_footer(); ?>

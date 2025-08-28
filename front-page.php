<?php
/*
 * File: front-page.php
 * Description: Front page renderer when the Shop page is set as the homepage. Shows hero (Shop only) and renders a product grid with pagination.
 * Theme: The Bear Traxs Subscription Template
 * Author: Eric Kowalewski
 * Version: 1.4.23
 * Last Updated: 2025-08-12 — 20:58 EDT
 */

defined('ABSPATH') || exit;

get_header();
?>
<main>
<?php
// ---------- Determine if this front page is the Shop ----------
$is_shop_front = function_exists('is_shop') && is_shop();

// ---------- HERO (Shop only) ----------
if ( $is_shop_front ) {
    // Pull URL from current theme mods; fall back to legacy key and parent mods
    $video = get_theme_mod('hero_video_url', '');
    if ($video === '') $video = get_theme_mod('btx_hero_video_url', '');
    if ($video === '') {
        $parent = get_option('template');
        if ($parent) {
            $mods = get_option('theme_mods_' . $parent);
            if (is_array($mods)) {
                if (!empty($mods['hero_video_url']))        $video = $mods['hero_video_url'];
                elseif (!empty($mods['btx_hero_video_url'])) $video = $mods['btx_hero_video_url'];
            }
        }
    }

    if ($video) {
        $lower = strtolower($video);
        $is_youtube = (strpos($lower,'youtube.com')!==false || strpos($lower,'youtu.be')!==false);
        $is_vimeo   = (strpos($lower,'vimeo.com')!==false);

        // Minimal inline CSS (contained here to avoid theme CSS regressions)
        echo '<style>
            .btx-hero{position:relative;margin:0 0 16px}
            .btx-hero-video{width:100%;height:auto;display:block}
            .btx-hero-embed{position:relative;width:100%;padding-top:56.25%}
            .btx-hero-embed iframe{position:absolute;inset:0;width:100%;height:100%}
        </style>';

        echo '<section class="btx-hero">';
        if ($is_youtube) {
            // Normalize YouTube to /embed/
            $src = '';
            $p = wp_parse_url($video);
            if ($p) {
                if (!empty($p['path']) && strpos($p['path'],'/embed/')!==false) {
                    $src = $video;
                } elseif (!empty($p['host']) && $p['host']==='youtu.be') {
                    $id = isset($p['path']) ? ltrim($p['path'],'/') : '';
                    if ($id) $src = 'https://www.youtube.com/embed/' . esc_attr($id);
                } else {
                    parse_str(isset($p['query'])?$p['query']:'', $q);
                    if (!empty($q['v'])) $src = 'https://www.youtube.com/embed/' . esc_attr($q['v']);
                }
            }
            if ($src) {
                $sep = (strpos($src,'?')===false)?'?':'&';
                $id  = basename($src);
                $src = $src . $sep . "rel=0&modestbranding=1&playsinline=1&autoplay=1&mute=1&loop=1&playlist={$id}";
                echo '<div class="btx-hero-embed"><iframe src="'.esc_url($src).'" frameborder="0" allow="autoplay; encrypted-media; picture-in-picture" allowfullscreen></iframe></div>';
            }
        } elseif ($is_vimeo) {
            $src = '';
            $p = wp_parse_url($video);
            if ($p && !empty($p['path'])) {
                $id = preg_replace('~^/+~','', $p['path']);
                if (preg_match('~^[0-9]+$~', $id)) {
                    $src = 'https://player.vimeo.com/video/' . esc_attr($id) . '?autoplay=1&muted=1&loop=1&title=0&byline=0&portrait=0';
                }
            }
            if ($src) {
                echo '<div class="btx-hero-embed"><iframe src="'.esc_url($src).'" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe></div>';
            }
        } else {
            // Assume direct MP4
            echo '<video class="btx-hero-video" autoplay muted loop playsinline><source src="'.esc_url($video).'" type="video/mp4"></source></video>';
        }
        echo '</section>';
    }
}

// ---------- Hide archive description (Shop only) ----------
if ( $is_shop_front ) {
    // No taxonomy/shop description block above the grid
    add_filter('woocommerce_get_the_archive_description', '__return_empty_string', 99);
}

// ---------- Render the Shop grid explicitly (don’t rely on main query) ----------
if ( $is_shop_front ) {

    // Open Woo wrappers so markup is consistent with archive-product.php
    do_action('woocommerce_before_main_content');

    if ( apply_filters('woocommerce_show_page_title', true) ) {
        echo '<h1 class="page-title">' . woocommerce_page_title(false) . '</h1>';
    }

    // Build a product query explicitly
    $paged = max(1, (int) get_query_var('paged', 0), (int) get_query_var('page', 0));
    $args  = [
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'paged'          => $paged,
        'orderby'        => 'menu_order title',
        'order'          => 'ASC',
    ];
    $loop = new WP_Query($args);

    if ( $loop->have_posts() ) {
        woocommerce_product_loop_start();
        while ( $loop->have_posts() ) {
            $loop->the_post();
            do_action('woocommerce_shop_loop');
            wc_get_template_part('content', 'product');
        }
        woocommerce_product_loop_end();

        // Basic pagination compatible with pretty permalinks
        echo '<nav class="woocommerce-pagination">';
        echo paginate_links([
            'total'   => $loop->max_num_pages,
            'current' => $paged,
        ]);
        echo '</nav>';

        wp_reset_postdata();

        do_action('woocommerce_after_shop_loop');

    } else {
        do_action('woocommerce_no_products_found');
    }

    do_action('woocommerce_after_main_content');

} else {
    // Not the Shop front: render normal page content
    if ( have_posts() ) {
        while ( have_posts() ) { the_post(); the_content(); }
    }
}
?>
</main>
<?php get_footer(); ?>


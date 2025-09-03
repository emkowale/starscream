<?php 
/*
 * File: functions.php
 * Description: Bootstrap/theme wiring. Front page → Shop, enqueue, Woo image/grid tweaks. Customizer lives in inc/admin/customizer.php.
 * Theme: The Bear Traxs Subscription Template (Starscream)
 * Author: Eric Kowalewski
 * Version: 1.4.23
 * Last Updated: 2025-08-28 — 12:58 EDT
 */

// ---------------------------------------------------------
// Load Customizer (all settings/controls + font helpers/CSS)
// ---------------------------------------------------------
$customizer_file = get_template_directory() . '/inc/admin/customizer.php';
if ( file_exists( $customizer_file ) ) {
    require_once $customizer_file;
} else {
    if ( function_exists('error_log') ) {
        error_log('[Starscream] Missing inc/admin/customizer.php');
    }
}

// ---------------------------------------------------------
// Front page = Shop renderer (no sidebar, optional hero)
// ---------------------------------------------------------
add_action('template_redirect', function () {
    if ( ! function_exists('is_shop') || ! is_shop() || ! is_front_page() ) {
        return;
    }

    // Resolve hero URL from theme mods (child/parent, legacy fallback)
    $video = get_theme_mod('hero_video_url', '');
    if ($video === '') $video = get_theme_mod('btx_hero_video_url', '');
    if ($video === '') {
        $parent = get_option('template');
        if ($parent) {
            $mods = get_option('theme_mods_' . $parent);
            if (is_array($mods)) {
                if (!empty($mods['hero_video_url']))         $video = $mods['hero_video_url'];
                elseif (!empty($mods['btx_hero_video_url'])) $video = $mods['btx_hero_video_url'];
            }
        }
    }

    get_header();
    echo "\n<!-- BTX FORCE: front-page Shop override ran (no sidebar) -->\n";

    // HERO (if URL present)
    if (!empty($video)) {
        $lower = strtolower($video);
        $is_youtube = (strpos($lower,'youtube.com')!==false || strpos($lower,'youtu.be')!==false);
        $is_vimeo   = (strpos($lower,'vimeo.com')!==false);

        echo '<section class="btx-hero">';
        if ($is_youtube) {
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
            echo '<video class="btx-hero-video" autoplay muted loop playsinline><source src="'.esc_url($video).'" type="video/mp4"></source></video>';
        }
        echo '</section>';
    }

    // Woo wrappers (no Shop H1)
    do_action('woocommerce_before_main_content');

    // Product grid with pagination
    $paged = max(1, (int) get_query_var('paged', 0), (int) get_query_var('page', 0));
    $loop  = new WP_Query([
        'post_type'   => 'product',
        'post_status' => 'publish',
        'paged'       => $paged,
    ]);

    if ( $loop->have_posts() ) {
        woocommerce_product_loop_start();
        while ( $loop->have_posts() ) {
            $loop->the_post();
            do_action('woocommerce_shop_loop');
            wc_get_template_part('content', 'product');
        }
        woocommerce_product_loop_end();

        echo '<nav class="woocommerce-pagination">';
        echo paginate_links(['total' => max(1,(int)$loop->max_num_pages), 'current' => $paged]);
        echo '</nav>';

        wp_reset_postdata();
        do_action('woocommerce_after_shop_loop');
    } else {
        do_action('woocommerce_no_products_found');
    }

    do_action('woocommerce_after_main_content');
    get_footer();
    exit;
}, 1);

// ---------------------------------------------------------
// Enqueue styles (theme + footer.css)
// ---------------------------------------------------------
add_action('wp_enqueue_scripts', function () {
    // main stylesheet (style.css)
    wp_enqueue_style('beartraxs-style', get_stylesheet_uri(), [], '1.4.23');

    // footer.css (main theme)
    $footer_path = get_template_directory() . '/assets/css/footer.css';
    if ( file_exists($footer_path) ) {
        wp_enqueue_style(
            'starscream-footer',
            get_template_directory_uri() . '/assets/css/footer.css',
            [],
            filemtime($footer_path)
        );
    }
});

// ---------------------------------------------------------
// Woo archive: remove add-to-cart button (archive tiles only)
// ---------------------------------------------------------
add_action('init', function () {
    remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
}, 20);

// ---------------------------------------------------------
// Woo image defaults (apply once) + layout helpers
// ---------------------------------------------------------
add_action('after_setup_theme', function () {
    if (get_option('btx_wc_image_defaults_set')) { return; }

    update_option('woocommerce_single_image_width',        1064);
    update_option('woocommerce_thumbnail_image_width',     1064);
    update_option('woocommerce_thumbnail_cropping',        'custom');
    update_option('woocommerce_thumbnail_cropping_custom_width',  5);
    update_option('woocommerce_thumbnail_cropping_custom_height', 7);

    update_option('btx_wc_image_defaults_set', 1);
}, 11);

/* Thumbnail aspect ratio CSS variable */
add_action('wp_head', function () {
    $crop = get_option('woocommerce_thumbnail_cropping', '1:1'); // '1:1' | 'custom' | 'uncropped'
    $ratio_css = '5 / 7';
    if ($crop === '1:1') {
        $ratio_css = '1 / 1';
    } elseif ($crop === 'custom') {
        $w = max(1, (int) get_option('woocommerce_thumbnail_cropping_custom_width', 5));
        $h = max(1, (int) get_option('woocommerce_thumbnail_cropping_custom_height', 7));
        $ratio_css = $w . ' / ' . $h;
    }
    echo '<style id="btx-woo-image-vars">:root{--btx-thumb-aspect:' . esc_html($ratio_css) . ';}</style>';
}, 99);

add_filter('body_class', function ($classes) {
    if (get_option('woocommerce_thumbnail_cropping', '1:1') === 'uncropped') {
        $classes[] = 'btx-uncropped-thumbs';
    }
    return $classes;
});

/* Image width CSS caps */
add_action('wp_head', function () {
    $single_w = (int) get_option('woocommerce_single_image_width', 600);
    $thumb_w  = (int) get_option('woocommerce_thumbnail_image_width', 300);
    if ($single_w < 200) $single_w = 200;
    if ($thumb_w  < 150) $thumb_w  = 150;
    ?>
    <style id="btx-woo-image-widths">
      :root{
        --btx-main-img-width: <?php echo $single_w; ?>px;
        --btx-thumb-img-width: <?php echo $thumb_w; ?>px;
      }
      .single-product div.product .woocommerce-product-gallery,
      .single-product div.product .images,
      .single-product div.product .product-gallery,
      .single-product div.product .product-images {
        max-width: var(--btx-main-img-width) !important;
      }
      .woocommerce ul.products li.product .btx-card-media {
        max-width: var(--btx-thumb-img-width) !important;
        margin-left: auto; margin-right: auto;
      }
      .woocommerce ul.products li.product .woocommerce-LoopProduct-link img {
        max-width: var(--btx-thumb-img-width) !important;
        width: 100%;
        height: auto;
      }
    </style>
    <?php
}, 100);

/* Make archive tiles larger; use WordPress 'large' image size */
add_filter('loop_shop_columns', fn($c) => 3, 99);
add_filter('single_product_archive_thumbnail_size', fn($s) => 'large', 99);

/* Ensure Woo uses Customizer widths for generated sizes */
add_filter('woocommerce_get_image_size_single', function($size){
  $w = (int) get_option('woocommerce_single_image_width', 600);
  return ['width' => max(200,$w), 'height' => 0, 'crop' => 0];
}, 10);

add_filter('woocommerce_get_image_size_thumbnail', function($size){
  $w = (int) get_option('woocommerce_thumbnail_image_width', 300);
  $crop_setting = get_option('woocommerce_thumbnail_cropping', '1:1');
  $crop = $crop_setting !== 'uncropped';
  $h = ($crop && $crop_setting === '1:1') ? max(150,$w) : 0;
  return ['width' => max(150,$w), 'height' => $h, 'crop' => $crop];
}, 10);

add_filter('woocommerce_get_image_size_gallery_thumbnail', fn($s) => ['width'=>100,'height'=>0,'crop'=>0], 10);

// ---------------------------------------------------------
// Woo extras include (child or parent) — non-fatal if missing
// ---------------------------------------------------------
$woo_extras = locate_template(['inc/modules/woo-extras.php'], false, false);
if ( $woo_extras && file_exists( $woo_extras ) ) {
    require_once $woo_extras;
} else {
    if ( function_exists('error_log') ) {
        error_log('[Starscream] woo-extras.php not found in child or parent; continuing without Woo extras.');
    }
}

// Enable core custom logo support (so has_custom_logo() & the_custom_logo() work)
add_action('after_setup_theme', function () {
    add_theme_support('custom-logo', [
        'height'      => 200,
        'width'       => 600,
        'flex-width'  => true,
        'flex-height' => true,
    ]);
}, 5);

// If our theme option 'company_logo_id' is set, treat it as the core custom_logo
add_filter('theme_mod_custom_logo', function ($value) {
    $company_logo = (int) get_theme_mod('company_logo_id', 0);
    return $company_logo > 0 ? $company_logo : $value;
});

require_once get_template_directory() . '/inc/ensure-classic-pages.php';


// Output the Customizer accent color as a CSS variable.
add_action('wp_head', function () {
  $accent = get_theme_mod('accent_color', '#0073aa');
  if (!preg_match('/^#([0-9a-f]{3}){1,2}$/i', $accent)) { $accent = '#0073aa'; }
  echo '<style id="btx-accent-var">:root{--btx-accent:' . esc_html($accent) . ';}</style>';
}, 101);


// ==== Woo Gallery: disable lightbox, keep zoom/slider ====
add_action('after_setup_theme', function () {
  // Make sure lightbox is OFF so clicks don’t go fullscreen
  remove_theme_support('wc-product-gallery-lightbox');
  // Keep these if you want them (both optional)
  add_theme_support('wc-product-gallery-zoom');
  add_theme_support('wc-product-gallery-slider');
}, 20);

// ==== Enqueue gallery behavior (single product only) ====
add_action('wp_enqueue_scripts', function () {
  if (!function_exists('is_product') || !is_product()) return;

  // JS
  wp_enqueue_script(
    'starscream-product-gallery',
    get_stylesheet_directory_uri() . '/assets/js/product-gallery.js',
    ['jquery'],
    '1.0.0',
    true
  );

  // CSS
  wp_enqueue_style(
    'starscream-product-gallery',
    get_stylesheet_directory_uri() . '/assets/css/product-gallery.css',
    [],
    '1.0.0'
  );
}, 20);

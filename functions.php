<?php 
/*
 * File: functions.php (forced Shop renderer, no sidebar)
 * Description: Front page = Shop → render hero (if set) + product grid. No inline CSS.
 * Theme: The Bear Traxs Subscription Template
 * Author: Eric Kowalewski
 * Version: 1.4.23
 * Last Updated: 2025-08-12 — 22:18 EDT
 */
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
    // intentionally no H1 on Shop front page

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

// Enqueue the theme stylesheet
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('beartraxs-style', get_stylesheet_uri(), [], '1.4.23');
});

// Woo archive: remove add-to-cart/Select Options button
add_action('init', function () {
    remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
}, 20);


add_action('customize_register', function ($wp_customize) {

    // Section
    $wp_customize->add_section('beartraxs_colors', [
        'title'    => 'The Bear Traxs Theme Options',
        'priority' => 30,
    ]);

    // Settings
    $wp_customize->add_setting('header_bg_color',           ['default' => '#eeeeee']);
    $wp_customize->add_setting('footer_bg_color',           ['default' => '#eeeeee']);
    $wp_customize->add_setting('header_footer_text_color',  ['default' => '#000000']);
    $wp_customize->add_setting('header_footer_font',        ['default' => 'Roboto']);
    $wp_customize->add_setting('accent_color',              ['default' => '#0073aa']);
    $wp_customize->add_setting('phone_number',              ['default' => 'xxx-xxx-xxxx' ]);
    $wp_customize->add_setting('email_address',             ['default' => 'you@exmple.com' ]);
    $wp_customize->add_setting('hero_video_url',            ['default' => '' ]);

    // Controls (explicit priorities so the order matches your spec)
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'header_bg_color', [
        'label'    => 'Header Background Color',
        'section'  => 'beartraxs_colors',
        'settings' => 'header_bg_color',
        'priority' => 10,
    ]));

    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'footer_bg_color', [
        'label'    => 'Footer Background Color',
        'section'  => 'beartraxs_colors',
        'settings' => 'footer_bg_color',
        'priority' => 20,
    ]));

    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'header_footer_text_color', [
        'label'    => 'Header & Footer Text Color',
        'section'  => 'beartraxs_colors',
        'settings' => 'header_footer_text_color',
        'priority' => 30,
    ]));

    // Font (directly under text color)
    $wp_customize->add_control('header_footer_font', [
        'label'    => 'Header & Footer Font',
        'section'  => 'beartraxs_colors',
        'type'     => 'select',
        'choices'  => [
            'Roboto'     => 'Roboto',
            'Open Sans'  => 'Open Sans',
            'Montserrat' => 'Montserrat',
            'Lato'       => 'Lato',
            'Oswald'     => 'Oswald',
            'Raleway'    => 'Raleway',
            'Poppins'    => 'Poppins',
            'Nunito'     => 'Nunito',
            'Inter'      => 'Inter',
            'PT Sans'    => 'PT Sans',
        ],
        'priority' => 40,
    ]);

    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'accent_color', [
        'label'    => 'Accent Color (Icons & Links)',
        'section'  => 'beartraxs_colors',
        'settings' => 'accent_color',
        'priority' => 50,
    ]));

    // Contact
    $wp_customize->add_control('phone_number', [
        'label'    => 'Phone Number',
        'section'  => 'beartraxs_colors',
        'type'     => 'text',
        'priority' => 60,
    ]);

    $wp_customize->add_control('email_address', [
        'label'    => 'Email Address',
        'section'  => 'beartraxs_colors',
        'type'     => 'text',
        'priority' => 70,
    ]);

    // Hero video (blank = no hero)
    $wp_customize->add_control('hero_video_url', [
        'label'    => 'Hero Video URL',
        'section'  => 'beartraxs_colors',
        'type'     => 'text',
        'priority' => 80,
    ]);

    // Socials (four slots)
    for ($i = 1; $i <= 4; $i++) {
        $base = 100 + ($i - 1) * 10;

        $wp_customize->add_setting("social_icon_$i", ['default' => '' ]);
        $wp_customize->add_setting("social_url_$i",  ['default' => '' ]);

        $wp_customize->add_control("social_icon_$i", [
            'label'    => "Social Icon $i (Font Awesome class)",
            'section'  => 'beartraxs_colors',
            'type'     => 'select',
            'choices'  => [
                'fab fa-facebook-f'     => 'Facebook',
                'fab fa-youtube'        => 'YouTube',
                'fab fa-instagram'      => 'Instagram',
                'fab fa-twitter'        => 'Twitter',
                'fab fa-pinterest'      => 'Pinterest',
                'fab fa-linkedin-in'    => 'LinkedIn',
                'fab fa-tiktok'         => 'TikTok',
                'fab fa-snapchat-ghost' => 'Snapchat',
                'fab fa-discord'        => 'Discord',
                'fab fa-reddit-alien'   => 'Reddit',
            ],
            'priority' => $base,
        ]);

        $wp_customize->add_control("social_url_$i", [
            'label'    => "Social Icon $i URL",
            'section'  => 'beartraxs_colors',
            'type'     => 'text',
            'priority' => $base + 5,
        ]);
    }
});

add_action('after_setup_theme', function () {
    // Only run once; don’t clobber user changes.
    if (get_option('btx_wc_image_defaults_set')) { return; }

    // Match your Vicki screenshot: wide, tall cards
    update_option('woocommerce_single_image_width',        1064); // product page image
    update_option('woocommerce_thumbnail_image_width',     1064); // archive/catalog
    update_option('woocommerce_thumbnail_cropping',        'custom');
    update_option('woocommerce_thumbnail_cropping_custom_width',  5);
    update_option('woocommerce_thumbnail_cropping_custom_height', 7);

    // Mark as set
    update_option('btx_wc_image_defaults_set', 1);
}, 11);

/* Use a larger image size on product archives (no regen needed) */
add_filter('single_product_archive_thumbnail_size', function ($size) {
    // 'large' exists on all WP installs (typically ~1024w)
    return 'large';
}, 99);

// Make the shop grid bigger by using 3 columns instead of Woo's ~4-column default
add_filter('loop_shop_columns', function ($cols) {
    return 3; // desktop
}, 99);

// Use a larger image size on the archive tiles (no regeneration required)
add_filter('single_product_archive_thumbnail_size', function ($size) {
    return 'large'; // WordPress' built-in 1024px size (already exists on most installs)
}, 99);

// Hide Add-to-Cart/Select Options on archives (keeps just image → title → price)
add_action('init', function () {
    remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
}, 20);

if ( ! function_exists('btx_get_selected_font_name') ) {
  function btx_get_selected_font_name() {
    // Try both setting IDs; default to Inter
    $font = get_theme_mod('btx_header_footer_font');
    if (!$font) { $font = get_theme_mod('header_footer_font'); }
    if (!$font || !is_string($font)) { $font = 'Inter'; }
    return trim($font);
  }
}

if ( ! function_exists('btx_font_stack_for') ) {
  function btx_font_stack_for( $font ) {
    // Known stacks (add more if you expose more choices in Customizer)
    $stacks = [
      'Inter'       => '"Inter", system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif',
      'Roboto'      => '"Roboto", system-ui, -apple-system, "Segoe UI", Arial, sans-serif',
      'Poppins'     => '"Poppins", system-ui, -apple-system, "Segoe UI", Arial, sans-serif',
      'Lato'        => '"Lato", system-ui, -apple-system, "Segoe UI", Arial, sans-serif',
      'Montserrat'  => '"Montserrat", system-ui, -apple-system, "Segoe UI", Arial, sans-serif',
      'Open Sans'   => '"Open Sans", system-ui, -apple-system, "Segoe UI", Arial, sans-serif',
    ];
    return isset($stacks[$font])
      ? $stacks[$font]
      : '"' . esc_attr($font) . '", system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif';
  }
}

if ( ! function_exists('btx_enqueue_selected_font') ) {
  function btx_enqueue_selected_font() {
    $font = btx_get_selected_font_name();

    // If it's a common Google font, enqueue it
    $google_fonts = ['Inter','Roboto','Poppins','Lato','Montserrat','Open Sans'];
    if ( in_array($font, $google_fonts, true) ) {
      $family = str_replace(' ', '+', $font);
      wp_enqueue_style(
        'btx-google-font-' . sanitize_title($font),
        'https://fonts.googleapis.com/css2?family=' . rawurlencode($family) . ':wght@300;400;500;600;700;800;900&display=swap',
        [],
        null
      );
    }
  }
  add_action('wp_enqueue_scripts', 'btx_enqueue_selected_font');
}

if ( ! function_exists('btx_print_font_css') ) {
  function btx_print_font_css() {
    $font       = btx_get_selected_font_name();
    $font_stack = btx_font_stack_for($font);

    // Print CSS variable + apply to header & footer (safe from plugin CSS)
    ?>
    <style id="btx-site-font-css">
      :root{ --header-footer-font: <?php echo $font_stack; ?>; }

      /* Apply to header & footer areas explicitly */
      header, .site-header, .main-header, .page-header,
      nav, .main-navigation, .topbar,
      footer, .site-footer {
        font-family: var(--header-footer-font) !important;
      }

      /* Optional: uncomment next line to apply across ENTIRE site */
      /* html, body, .woocommerce, .woocommerce * { font-family: var(--header-footer-font) !important; } */
    </style>
    <?php
  }
  add_action('wp_head', 'btx_print_font_css', 99);
}


/* -----------------------------------------------
 * Woo image settings → theme CSS (thumbnail ratio)
 * Last Updated: 2025-08-13 (EDT)
 * ----------------------------------------------- */

if ( ! function_exists('btx_output_woo_image_css') ) {
  function btx_output_woo_image_css() {
    // Woo options (WooCommerce 3.3+)
    $crop = get_option('woocommerce_thumbnail_cropping', '1:1'); // '1:1' | 'custom' | 'uncropped'
    $ratio_css = '5 / 7'; // fallback to current theme default

    if ($crop === '1:1') {
      $ratio_css = '1 / 1';
    } elseif ($crop === 'custom') {
      $w = max(1, (int) get_option('woocommerce_thumbnail_cropping_custom_width', 5));
      $h = max(1, (int) get_option('woocommerce_thumbnail_cropping_custom_height', 7));
      $ratio_css = $w . ' / ' . $h;
    } elseif ($crop === 'uncropped') {
      // handled via body class below (aspect-ratio:auto)
    }

    echo '<style id="btx-woo-image-vars">';
    echo ':root{--btx-thumb-aspect:' . esc_html($ratio_css) . ';}';
    echo '</style>';
  }
  add_action('wp_head', 'btx_output_woo_image_css', 99);
}

if ( ! function_exists('btx_woo_image_body_class') ) {
  function btx_woo_image_body_class( $classes ) {
    $crop = get_option('woocommerce_thumbnail_cropping', '1:1');
    if ($crop === 'uncropped') {
      $classes[] = 'btx-uncropped-thumbs';
    }
    return $classes;
  }
  add_filter('body_class', 'btx_woo_image_body_class');
}

/* -------------------------------------------------------------
 * Woo image widths → actual image sizes + front-end layout
 * Last Updated: 2025-08-13 (EDT)
 * ------------------------------------------------------------- */

if ( ! function_exists('btx_output_woo_image_width_css') ) {
  function btx_output_woo_image_width_css() {
    // Woo options (pixels)
    $single_w = (int) get_option('woocommerce_single_image_width', 600);      // Main image width (single product)
    $thumb_w  = (int) get_option('woocommerce_thumbnail_image_width', 300);   // Catalog thumbnail width

    // Safety floors so CSS isn't zero
    if ($single_w < 200) { $single_w = 200; }
    if ($thumb_w  < 150) { $thumb_w  = 150; }

    ?>
    <style id="btx-woo-image-widths">
      :root{
        --btx-main-img-width: <?php echo $single_w; ?>px;
        --btx-thumb-img-width: <?php echo $thumb_w; ?>px;
      }

      /* SINGLE PRODUCT: cap gallery container to "Main image width" */
      .single-product div.product .woocommerce-product-gallery {
        max-width: var(--btx-main-img-width) !important;
      }
      /* Common wrappers some themes use around the gallery */
      .single-product div.product .images,
      .single-product div.product .product-gallery,
      .single-product div.product .product-images {
        max-width: var(--btx-main-img-width) !important;
      }

      /* CATALOG: cap each tile's media box to "Thumbnail width" */
      .woocommerce ul.products li.product .btx-card-media {
        max-width: var(--btx-thumb-img-width) !important;
        margin-left: auto; margin-right: auto;
      }
      /* If your theme uses Woo default image wrapper instead of .btx-card-media */
      .woocommerce ul.products li.product .woocommerce-LoopProduct-link img {
        max-width: var(--btx-thumb-img-width) !important;
        width: 100%;
        height: auto;
      }
    </style>
    <?php
  }
  add_action('wp_head', 'btx_output_woo_image_width_css', 100);
}

/**
 * Ensure Woo uses the Customizer widths for generated image sizes,
 * even if the theme filters them.
 */
add_filter('woocommerce_get_image_size_single', function($size){
  $w = (int) get_option('woocommerce_single_image_width', 600);
  // height 0 = uncropped (Woo will respect cropping ratio separately)
  return ['width' => max(200,$w), 'height' => 0, 'crop' => 0];
}, 10);

add_filter('woocommerce_get_image_size_thumbnail', function($size){
  $w = (int) get_option('woocommerce_thumbnail_image_width', 300);
  $crop_setting = get_option('woocommerce_thumbnail_cropping', '1:1'); // '1:1' | 'custom' | 'uncropped'
  $crop = $crop_setting !== 'uncropped';

  // If square cropping, height = width; if custom, Woo handles crop ratio elsewhere; if uncropped, height 0
  $h = ($crop && $crop_setting === '1:1') ? max(150,$w) : 0;

  return ['width' => max(150,$w), 'height' => $h, 'crop' => $crop];
}, 10);

/* Also align gallery thumbnail (small thumbs under main image) */
add_filter('woocommerce_get_image_size_gallery_thumbnail', function($size){
  // Keep these small but proportional; you can tie to $thumb_w if desired
  return ['width' => 100, 'height' => 0, 'crop' => 0];
}, 10);

require_once get_stylesheet_directory() . '/inc/modules/woo-extras.php';

<?php
/*
 * File: archive-product.php
 * Description: Shop & taxonomy archives — hero on Shop only (YouTube/Vimeo/MP4). No placeholder. Hide Shop description block. Standard Woo loop + H1.
 * Theme: The Bear Traxs Subscription Template
 * Author: Eric Kowalewski
 * Version: 1.4.23
 * Last Updated: 2025-08-12 — 20:05 EDT
 */

defined('ABSPATH') || exit;

get_header();

/** HERO (SHOP ONLY) */
$is_shop = function_exists('is_shop') && is_shop();
$video = '';
if ( $is_shop ) {
    $video = trim( (string) get_theme_mod('hero_video_url', '') );
    if ($video === '') $video = trim( (string) get_theme_mod('btx_hero_video_url', '') );
}

function btx_youtube_embed($url){
    $p = wp_parse_url($url); if(!$p||empty($p['host'])) return '';
    $host = strtolower($p['host']);
    if(!empty($p['path']) && strpos($p['path'],'/embed/')!==false){ $base=$url; }
    elseif($host==='youtu.be'){ $id = isset($p['path'])?ltrim($p['path'],'/'):''; $base = $id? "https://www.youtube.com/embed/".esc_attr($id):''; }
    else { parse_str(isset($p['query'])?$p['query']:'',$q); $id = isset($q['v'])?$q['v']:''; $base = $id? "https://www.youtube.com/embed/".esc_attr($id):''; }
    if(!$base) return '';
    $sep = (strpos($base,'?')===false)?'?':'&'; $id = basename($base);
    return $base.$sep."rel=0&modestbranding=1&playsinline=1&autoplay=1&mute=1&loop=1&playlist={$id}";
}
function btx_vimeo_embed($url){
    $p = wp_parse_url($url); if(!$p||empty($p['path'])) return '';
    $id = preg_replace('~^/+~','',$p['path']); if(!preg_match('~^[0-9]+$~',$id)) return '';
    return "https://player.vimeo.com/video/".esc_attr($id)."?autoplay=1&muted=1&loop=1&title=0&byline=0&portrait=0";
}

if ( $is_shop && $video ) : ?>
  <section class="btx-hero">
    <?php
    $lower = strtolower($video);
    if (strpos($lower,'youtube.com')!==false || strpos($lower,'youtu.be')!==false) {
        if ($src = btx_youtube_embed($video)) {
            echo '<div class="btx-hero-embed"><iframe src="'.esc_url($src).'" frameborder="0" allow="autoplay; encrypted-media; picture-in-picture" allowfullscreen></iframe></div>';
        }
    } elseif (strpos($lower,'vimeo.com')!==false) {
        if ($src = btx_vimeo_embed($video)) {
            echo '<div class="btx-hero-embed"><iframe src="'.esc_url($src).'" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe></div>';
        }
    } else {
        echo '<video class="btx-hero-video" autoplay muted loop playsinline><source src="'.esc_url($video).'" type="video/mp4"></source></video>';
    }
    ?>
  </section>
<?php endif; ?>

<?php
// WOO ARCHIVE
do_action('woocommerce_before_main_content');

if ( apply_filters('woocommerce_show_page_title', true) ) {
    echo '<h1 class="page-title">'.woocommerce_page_title(false).'</h1>';
}

// Hide just the Shop page description block
if ( $is_shop ) { remove_action('woocommerce_archive_description','woocommerce_product_archive_description',10); }
do_action('woocommerce_archive_description');

if ( woocommerce_product_loop() ) {
    woocommerce_product_loop_start();
    if ( wc_get_loop_prop('total') ) {
        while ( have_posts() ) { the_post(); do_action('woocommerce_shop_loop'); wc_get_template_part('content','product'); }
    }
    woocommerce_product_loop_end();
    do_action('woocommerce_after_shop_loop');
} else {
    do_action('woocommerce_no_products_found');
}

do_action('woocommerce_after_main_content');
get_footer();
?>
<style>
.btx-hero{position:relative;margin:0 0 16px}
.btx-hero-video{width:100%;height:auto;display:block}
.btx-hero-embed{position:relative;width:100%;padding-top:56.25%}
.btx-hero-embed iframe{position:absolute;inset:0;width:100%;height:100%}
</style>

<?php
if (!defined('ABSPATH')) exit;

if (!function_exists('btx_is_home_like')) {
  function btx_is_home_like(){ return is_front_page() || is_home() || (function_exists('is_shop') && is_shop()); }
}

if (!function_exists('btx_render_banner')) {
  function btx_render_banner($pos='top'){
    if (!btx_is_home_like()) return;
    $on = get_theme_mod($pos==='top'?'home_top_banner_enable':'home_bottom_banner_enable', false);
    if (!$on) return;
    $id   = get_theme_mod($pos==='top'?'home_top_banner_image_id':'home_bottom_banner_image_id', 0);
    if (!$id) return;
    $img  = wp_get_attachment_image_src($id,'full'); if (!$img) return;
    $link = trim(get_theme_mod($pos==='top'?'home_top_banner_link':'home_bottom_banner_link',''));
    $alt  = trim(get_theme_mod($pos==='top'?'home_top_banner_alt':'home_bottom_banner_alt',''));
    $cls  = $pos==='top'?'btx-header-banner':'btx-footer-banner';
    echo '<div class="'.$cls.'" role="region" aria-label="Site banner">';
    if ($link) echo '<a class="'.$cls.'__link" href="'.esc_url($link).'">';
    echo '<img class="'.$cls.'__img" src="'.esc_url($img[0]).'" alt="'.esc_attr($alt).'" loading="lazy" decoding="async" />';
    if ($link) echo '</a>';
    echo '</div>';
  }
}

// Hooks: top under header; bottom above footer
add_action('wp_body_open', function(){ if(!is_admin()) btx_render_banner('top'); }, 5);
add_action('woocommerce_before_main_content', function(){ if(!is_admin()) btx_render_banner('top'); }, 5);
add_action('get_footer', function(){ if(!is_admin()) btx_render_banner('bottom'); }, 0);

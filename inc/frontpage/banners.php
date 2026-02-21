<?php
// inc/frontpage/banners.php — renders header/footer banners on homepage/shop
if (!defined('ABSPATH')) exit;

/* DEBUG: prove this file is loaded (remove after testing) */
add_action('wp_head', function(){ echo "\n<!-- starscream: banners.php LOADED -->\n"; }, 0);

function btx_is_home_like(){
  return is_front_page() || is_home() || (function_exists('is_shop') && is_shop());
}

function btx_render_banner($pos = 'top'){
  if (is_admin() || !btx_is_home_like()) return;

  $enable = get_theme_mod($pos === 'top' ? 'home_top_banner_enable' : 'home_bottom_banner_enable', false);
  if (!$enable) return;

  $id = (int) get_theme_mod($pos === 'top' ? 'home_top_banner_image_id' : 'home_bottom_banner_image_id', 0);
  if (!$id) return;

  $img = wp_get_attachment_image_src($id, 'full'); if (!$img) return;
  $link = trim(get_theme_mod($pos === 'top' ? 'home_top_banner_link' : 'home_bottom_banner_link', ''));
  $alt  = trim(get_theme_mod($pos === 'top' ? 'home_top_banner_alt'  : 'home_bottom_banner_alt',  ''));
  $cls  = $pos === 'top' ? 'btx-header-banner' : 'btx-footer-banner';

  echo '<div class="'.esc_attr($cls).'" role="region" aria-label="Site banner">';
  if ($link) echo '<a class="'.esc_attr($cls).'__link" href="'.esc_url($link).'">';
  echo '<img class="'.esc_attr($cls).'__img" src="'.esc_url($img[0]).'" alt="'.esc_attr($alt).'" loading="lazy" decoding="async" />';
  if ($link) echo '</a>';
  echo '</div>';
}

/* Ensure top banner prints only once even if both hooks fire (shop pages) */
function btx_render_top_once(){
  static $printed = false; if ($printed) return;
  btx_render_banner('top'); $printed = true;
}

/* Hooks */
add_action('wp_body_open', 'btx_render_top_once', 5);
add_action('woocommerce_before_main_content', 'btx_render_top_once', 5);
add_action('get_footer', function(){ btx_render_banner('bottom'); }, 0);

/* Admin-only state dump (remove after testing) */
add_action('wp_head', function () {
  if (!current_user_can('manage_options')) return;
  $is = btx_is_home_like() ? '1' : '0';
  $en = get_theme_mod('home_top_banner_enable', false) ? '1' : '0';
  $id = (int) get_theme_mod('home_top_banner_image_id', 0);
  echo "<!-- btx-banners-debug: is_home_like=$is enable_top=$en id_top=$id -->\n";
}, 1);

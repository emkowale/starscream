<?php
if (!defined('ABSPATH')) exit;

// Settings
add_action('customize_register', function($c){
  // Top
  $c->add_setting('home_top_banner_enable',['default'=>false,'sanitize_callback'=>'starscream_sanitize_checkbox']);
  $c->add_setting('home_top_banner_image_id',['default'=>0,'sanitize_callback'=>'absint']);
  $c->add_setting('home_top_banner_link',['default'=>'','sanitize_callback'=>'esc_url_raw']);
  $c->add_setting('home_top_banner_alt',['default'=>'','sanitize_callback'=>'sanitize_text_field']);
  // Bottom
  $c->add_setting('home_bottom_banner_enable',['default'=>false,'sanitize_callback'=>'starscream_sanitize_checkbox']);
  $c->add_setting('home_bottom_banner_image_id',['default'=>0,'sanitize_callback'=>'absint']);
  $c->add_setting('home_bottom_banner_link',['default'=>'','sanitize_callback'=>'esc_url_raw']);
  $c->add_setting('home_bottom_banner_alt',['default'=>'','sanitize_callback'=>'sanitize_text_field']);

  // Controls: Top
  $c->add_control('home_top_banner_enable',['label'=>'Show Header Banner (Homepage)','section'=>STARSCREAM_CUSTOMIZER_SECTION,'type'=>'checkbox','priority'=>85]);
  $c->add_control(new WP_Customize_Media_Control($c,'home_top_banner_image_id',['label'=>'Header Banner Image','section'=>STARSCREAM_CUSTOMIZER_SECTION,'mime_type'=>'image','priority'=>86]));
  $c->add_control('home_top_banner_link',['label'=>'Header Banner Link (optional)','section'=>STARSCREAM_CUSTOMIZER_SECTION,'type'=>'text','priority'=>87]);
  $c->add_control('home_top_banner_alt',['label'=>'Header Banner Alt Text (optional)','section'=>STARSCREAM_CUSTOMIZER_SECTION,'type'=>'text','priority'=>88]);

  // Controls: Bottom
  $c->add_control('home_bottom_banner_enable',['label'=>'Show Footer Banner (Homepage)','section'=>STARSCREAM_CUSTOMIZER_SECTION,'type'=>'checkbox','priority'=>95]);
  $c->add_control(new WP_Customize_Media_Control($c,'home_bottom_banner_image_id',['label'=>'Footer Banner Image','section'=>STARSCREAM_CUSTOMIZER_SECTION,'mime_type'=>'image','priority'=>96]));
  $c->add_control('home_bottom_banner_link',['label'=>'Footer Banner Link (optional)','section'=>STARSCREAM_CUSTOMIZER_SECTION,'type'=>'text','priority'=>97]);
  $c->add_control('home_bottom_banner_alt',['label'=>'Footer Banner Alt Text (optional)','section'=>STARSCREAM_CUSTOMIZER_SECTION,'type'=>'text','priority'=>98]);
});

// CSS for banner containers
if (!function_exists('btx_print_banner_css')) {
  function btx_print_banner_css(){ ?>
    <style id="btx-banner-css">
      .btx-header-banner,.btx-footer-banner{width:100vw;margin-left:calc(50% - 50vw);margin-right:calc(50% - 50vw);overflow:hidden;}
      .btx-header-banner{margin-bottom:24px}.btx-footer-banner{margin-top:24px}
      .btx-header-banner__link,.btx-footer-banner__link{display:block;width:100%;height:auto}
      .btx-header-banner__img,.btx-footer-banner__img{display:block;width:100%;height:auto;object-position:center}
      @media (max-width:768px){.btx-header-banner__img,.btx-footer-banner__img{height:200px;object-fit:cover}}
      @media (max-width:480px){.btx-header-banner__img,.btx-footer-banner__img{height:160px}}
    </style>
  <?php }
  add_action('wp_head','btx_print_banner_css',101);
}

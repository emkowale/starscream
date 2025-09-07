<?php
if (!defined('ABSPATH')) exit;

// Settings
add_action('customize_register', function($c){
  $c->add_setting('header_bg_color', ['default'=>'#eeeeee','sanitize_callback'=>'sanitize_hex_color']);
  $c->add_setting('footer_bg_color', ['default'=>'#eeeeee','sanitize_callback'=>'sanitize_hex_color']);
  $c->add_setting('header_footer_text_color', ['default'=>'#000000','sanitize_callback'=>'sanitize_hex_color']); // header text
  $c->add_setting('footer_text_color', ['default'=>'#000000','sanitize_callback'=>'sanitize_hex_color']);       // footer text

  // Controls
  $c->add_control(new WP_Customize_Color_Control($c,'header_bg_color',[
    'label'=>'Header Background Color','section'=>STARSCREAM_CUSTOMIZER_SECTION,'settings'=>'header_bg_color','priority'=>10
  ]));
  $c->add_control(new WP_Customize_Color_Control($c,'footer_bg_color',[
    'label'=>'Footer Background Color','section'=>STARSCREAM_CUSTOMIZER_SECTION,'settings'=>'footer_bg_color','priority'=>20
  ]));
  $c->add_control(new WP_Customize_Color_Control($c,'header_footer_text_color',[
    'label'=>'Header Text Color','section'=>STARSCREAM_CUSTOMIZER_SECTION,'settings'=>'header_footer_text_color','priority'=>30
  ]));
  $c->add_control(new WP_Customize_Color_Control($c,'footer_text_color',[
    'label'=>'Footer Text Color','section'=>STARSCREAM_CUSTOMIZER_SECTION,'settings'=>'footer_text_color','priority'=>31
  ]));
});

// Output CSS
if (!function_exists('btx_print_color_css')) {
  function btx_print_color_css(){
    $h = sanitize_hex_color(get_theme_mod('header_footer_text_color','#000')) ?: '#000';
    $f = sanitize_hex_color(get_theme_mod('footer_text_color',$h)) ?: $h;
    ?>
    <style id="btx-color-css">
      :root{--btx-header-text:<?php echo esc_html($h); ?>;--btx-footer-text:<?php echo esc_html($f); ?>;--btx-header-footer-text:<?php echo esc_html($h); ?>;}
      header,.site-header,.main-header,.page-header,header a,.site-header a,.main-header a,.page-header a{color:var(--btx-header-text)!important;}
      footer,.site-footer,footer a,.site-footer a{color:var(--btx-footer-text)!important;}
    </style>
    <?php
  }
  add_action('wp_head','btx_print_color_css',100);
}

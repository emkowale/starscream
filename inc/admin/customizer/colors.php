<?php
if (!defined('ABSPATH')) exit;

add_action('customize_register', function ($wp_customize) {
  $section = starscream_customizer_section_id();

  // Settings
  $wp_customize->add_setting('header_bg_color',  ['default'=>'#eeeeee','sanitize_callback'=>'sanitize_hex_color']);
  $wp_customize->add_setting('footer_bg_color',  ['default'=>'#eeeeee','sanitize_callback'=>'sanitize_hex_color']);
  $wp_customize->add_setting('header_text_color',['default'=>'#000000','sanitize_callback'=>'sanitize_hex_color']);
  $wp_customize->add_setting('footer_text_color',['default'=>'#000000','sanitize_callback'=>'sanitize_hex_color']);
  $wp_customize->add_setting('accent_color',     ['default'=>'#0073aa','sanitize_callback'=>'sanitize_hex_color']);

  // Controls
  $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'header_bg_color', [
    'label'=>'Header Background Color','section'=>$section,'settings'=>'header_bg_color','priority'=>10,
  ]));
  $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'footer_bg_color', [
    'label'=>'Footer Background Color','section'=>$section,'settings'=>'footer_bg_color','priority'=>20,
  ]));
  $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'header_text_color', [
    'label'=>'Header Text Color','section'=>$section,'settings'=>'header_text_color','priority'=>30,
  ]));
  $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'footer_text_color', [
    'label'=>'Footer Text Color','section'=>$section,'settings'=>'footer_text_color','priority'=>35,
  ]));
  $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'accent_color', [
    'label'=>'Accent Color (Icons & Links)','section'=>$section,'settings'=>'accent_color','priority'=>40,
  ]));
}, 6);

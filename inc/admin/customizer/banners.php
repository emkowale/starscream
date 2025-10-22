<?php
if (!defined('ABSPATH')) exit;

add_action('customize_register', function ($wp_customize) {
  $section = starscream_customizer_section_id();

  // TOP
  $wp_customize->add_setting('home_top_banner_image_id', ['default'=>0,'sanitize_callback'=>'absint']);
  $wp_customize->add_setting('home_top_banner_link',     ['default'=>'','sanitize_callback'=>'esc_url_raw']);
  $wp_customize->add_setting('home_top_banner_alt',      ['default'=>'','sanitize_callback'=>'sanitize_text_field']);

  // BOTTOM
  $wp_customize->add_setting('home_bottom_banner_image_id', ['default'=>0,'sanitize_callback'=>'absint']);
  $wp_customize->add_setting('home_bottom_banner_link',     ['default'=>'','sanitize_callback'=>'esc_url_raw']);
  $wp_customize->add_setting('home_bottom_banner_alt',      ['default'=>'','sanitize_callback'=>'sanitize_text_field']);

  // Controls
  $prio = 90;
  $media = class_exists('WP_Customize_Media_Control') ? 'WP_Customize_Media_Control' : 'WP_Customize_Image_Control';

  $wp_customize->add_control(new $media($wp_customize, 'home_top_banner_image_id', [
    'label'=>'Header Banner Image (Home only)','section'=>$section,'mime_type'=>'image','priority'=>$prio,
  ]));
  $wp_customize->add_control('home_top_banner_link', [
    'label'=>'Header Banner Link (optional)','section'=>$section,'type'=>'text','priority'=>$prio+1,
  ]);
  $wp_customize->add_control('home_top_banner_alt', [
    'label'=>'Header Banner Alt Text','section'=>$section,'type'=>'text','priority'=>$prio+2,
  ]);

  $wp_customize->add_control(new $media($wp_customize, 'home_bottom_banner_image_id', [
    'label'=>'Footer Banner Image (Home only)','section'=>$section,'mime_type'=>'image','priority'=>$prio+10,
  ]));
  $wp_customize->add_control('home_bottom_banner_link', [
    'label'=>'Footer Banner Link (optional)','section'=>$section,'type'=>'text','priority'=>$prio+11,
  ]);
  $wp_customize->add_control('home_bottom_banner_alt', [
    'label'=>'Footer Banner Alt Text','section'=>$section,'type'=>'text','priority'=>$prio+12,
  ]);
}, 9);

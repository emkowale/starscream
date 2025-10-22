<?php
if (!defined('ABSPATH')) exit;

add_action('customize_register', function ($wp_customize) {
  $section = starscream_customizer_section_id();

  $wp_customize->add_setting('phone_number',  ['default'=>'xxx-xxx-xxxx','sanitize_callback'=>'sanitize_text_field']);
  $wp_customize->add_setting('email_address', ['default'=>'you@example.com','sanitize_callback'=>'sanitize_text_field']);
  $wp_customize->add_setting('hero_video_url',['default'=>'','sanitize_callback'=>'esc_url_raw']);

  $wp_customize->add_control('phone_number', [
    'label'=>'Phone Number','section'=>$section,'type'=>'text','priority'=>60,
  ]);
  $wp_customize->add_control('email_address', [
    'label'=>'Email Address','section'=>$section,'type'=>'text','priority'=>70,
  ]);
  $wp_customize->add_control('hero_video_url', [
    'label'=>'Hero Video URL','section'=>$section,'type'=>'text','priority'=>80,
  ]);
}, 8);

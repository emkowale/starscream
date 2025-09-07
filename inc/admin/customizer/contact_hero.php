<?php
if (!defined('ABSPATH')) exit;

add_action('customize_register', function($c){
  $c->add_setting('phone_number',['default'=>'xxx-xxx-xxxx','sanitize_callback'=>'sanitize_text_field']);
  $c->add_setting('email_address',['default'=>'you@example.com','sanitize_callback'=>'sanitize_text_field']);
  $c->add_setting('hero_video_url',['default'=>'','sanitize_callback'=>'esc_url_raw']);

  $c->add_control('phone_number',['label'=>'Phone Number','section'=>STARSCREAM_CUSTOMIZER_SECTION,'type'=>'text','priority'=>60]);
  $c->add_control('email_address',['label'=>'Email Address','section'=>STARSCREAM_CUSTOMIZER_SECTION,'type'=>'text','priority'=>70]);
  $c->add_control('hero_video_url',['label'=>'Hero Video URL','section'=>STARSCREAM_CUSTOMIZER_SECTION,'type'=>'text','priority'=>80]);
});

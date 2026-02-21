<?php
if (!defined('ABSPATH')) exit;

add_action('customize_register', function ($wp_customize) {
  $section = starscream_customizer_section_id();
  $icons = [
    'fab fa-facebook-f'     => 'Facebook',
    'fab fa-youtube'        => 'YouTube',
    'fab fa-instagram'      => 'Instagram',
    'fab fa-twitter'        => 'Twitter / X',
    'fab fa-pinterest'      => 'Pinterest',
    'fab fa-linkedin-in'    => 'LinkedIn',
    'fab fa-tiktok'         => 'TikTok',
    'fab fa-snapchat-ghost' => 'Snapchat',
    'fab fa-discord'        => 'Discord',
    'fab fa-reddit-alien'   => 'Reddit',
  ];

  for ($i=1; $i<=4; $i++) {
    $wp_customize->add_setting("social_icon_$i", [
      'default'=>'',
      'sanitize_callback'=>function($val) use ($icons){ return array_key_exists($val,$icons)?$val:''; }
    ]);
    $wp_customize->add_setting("social_url_$i",  ['default'=>'','sanitize_callback'=>'esc_url_raw']);

    $base = 120 + ($i-1)*10;
    $wp_customize->add_control("social_icon_$i", [
      'label'=>"Social Icon $i",'section'=>$section,'type'=>'select','choices'=>$icons,'priority'=>$base,
    ]);
    $wp_customize->add_control("social_url_$i", [
      'label'=>"Social URL $i",'section'=>$section,'type'=>'text','priority'=>$base+1,
    ]);
  }
}, 10);

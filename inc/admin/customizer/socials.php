<?php
if (!defined('ABSPATH')) exit;

add_action('customize_register', function($c){
  $choices=['fab fa-facebook-f'=>'Facebook','fab fa-youtube'=>'YouTube','fab fa-instagram'=>'Instagram','fab fa-twitter'=>'Twitter','fab fa-pinterest'=>'Pinterest','fab fa-linkedin-in'=>'LinkedIn','fab fa-tiktok'=>'TikTok','fab fa-snapchat-ghost'=>'Snapchat','fab fa-discord'=>'Discord','fab fa-reddit-alien'=>'Reddit'];
  for($i=1;$i<=4;$i++){
    $base=100+($i-1)*10;
    $c->add_setting("social_icon_$i",['default'=>'','sanitize_callback'=>function($v)use($choices){return array_key_exists($v,$choices)?$v:'';}]);
    $c->add_setting("social_url_$i",['default'=>'','sanitize_callback'=>'esc_url_raw']);
    $c->add_control("social_icon_$i",['label'=>"Social Icon $i (Font Awesome)",'section'=>STARSCREAM_CUSTOMIZER_SECTION,'type'=>'select','choices'=>$choices,'priority'=>$base]);
    $c->add_control("social_url_$i",['label'=>"Social Icon $i URL",'section'=>STARSCREAM_CUSTOMIZER_SECTION,'type'=>'text','priority'=>$base+5]);
  }
});

<?php
if (!defined('ABSPATH')) exit;

add_action('customize_register', function($c){
  $c->add_setting('company_logo_id', ['default'=>0,'sanitize_callback'=>'absint']);
  if (class_exists('WP_Customize_Media_Control')) {
    $c->add_control(new WP_Customize_Media_Control($c,'company_logo_id',[
      'label'=>'Company Logo','description'=>'Choose a logo from Media Library.',
      'section'=>STARSCREAM_CUSTOMIZER_SECTION,'mime_type'=>'image','priority'=>5
    ]));
  } else {
    $c->add_control(new WP_Customize_Image_Control($c,'company_logo_id',[
      'label'=>'Company Logo','description'=>'Choose a logo from Media Library.',
      'section'=>STARSCREAM_CUSTOMIZER_SECTION,'priority'=>5
    ]));
  }
});

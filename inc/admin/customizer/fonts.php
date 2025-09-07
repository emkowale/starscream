<?php
if (!defined('ABSPATH')) exit;

// Setting + control
add_action('customize_register', function($c){
  $c->add_setting('header_footer_font', ['default'=>'Roboto','sanitize_callback'=>function($v){
    $allowed=['Roboto','Open Sans','Montserrat','Lato','Oswald','Raleway','Poppins','Nunito','Inter','PT Sans'];
    return in_array($v,$allowed,true)?$v:'Roboto';
  }]);
  $c->add_control('header_footer_font',[
    'label'=>'Header & Footer Font','section'=>STARSCREAM_CUSTOMIZER_SECTION,'type'=>'select','priority'=>40,
    'choices'=>['Roboto'=>'Roboto','Open Sans'=>'Open Sans','Montserrat'=>'Montserrat','Lato'=>'Lato','Oswald'=>'Oswald','Raleway'=>'Raleway','Poppins'=>'Poppins','Nunito'=>'Nunito','Inter'=>'Inter','PT Sans'=>'PT Sans']
  ]);
});

// Helpers + CSS
if (!function_exists('btx_get_selected_font_name')) {
  function btx_get_selected_font_name(){
    $f=get_theme_mod('btx_header_footer_font')?:get_theme_mod('header_footer_font'); return $f?:'Inter';
  }
}
if (!function_exists('btx_font_stack_for')) {
  function btx_font_stack_for($f){
    $m=['Inter'=>'"Inter",system-ui,-apple-system,"Segoe UI",Roboto,Arial,sans-serif',
        'Roboto'=>'"Roboto",system-ui,-apple-system,"Segoe UI",Arial,sans-serif',
        'Poppins'=>'"Poppins",system-ui,-apple-system,"Segoe UI",Arial,sans-serif',
        'Lato'=>'"Lato",system-ui,-apple-system,"Segoe UI",Arial,sans-serif',
        'Montserrat'=>'"Montserrat",system-ui,-apple-system,"Segoe UI",Arial,sans-serif',
        'Open Sans'=>'"Open Sans",system-ui,-apple-system,"Segoe UI",Arial,sans-serif',
        'Oswald'=>'"Oswald",system-ui,-apple-system,"Segoe UI",Arial,sans-serif',
        'Nunito'=>'"Nunito",system-ui,-apple-system,"Segoe UI",Arial,sans-serif',
        'Raleway'=>'"Raleway",system-ui,-apple-system,"Segoe UI",Arial,sans-serif',
        'PT Sans'=>'"PT Sans",system-ui,-apple-system,"Segoe UI",Arial,sans-serif'];
    return $m[$f] ?? '"'.esc_attr($f).'",system-ui,-apple-system,"Segoe UI",Roboto,Arial,sans-serif';
  }
}
if (!function_exists('btx_enqueue_selected_font')) {
  function btx_enqueue_selected_font(){
    $f=btx_get_selected_font_name(); $g=['Inter','Roboto','Poppins','Lato','Montserrat','Open Sans','Oswald','Nunito','Raleway','PT Sans'];
    if (in_array($f,$g,true)) { $fam=str_replace(' ','+',$f);
      wp_enqueue_style('btx-google-font-'.sanitize_title($f),'https://fonts.googleapis.com/css2?family='.rawurlencode($fam).':wght@300;400;500;600;700;800;900&display=swap',[],null);
    }
  }
  add_action('wp_enqueue_scripts','btx_enqueue_selected_font');
}
if (!function_exists('btx_print_font_css')) {
  function btx_print_font_css(){ $stack=btx_font_stack_for(btx_get_selected_font_name()); ?>
    <style id="btx-site-font-css">:root{--header-footer-font:<?php echo $stack; ?>;}header,.site-header,.main-header,.page-header,nav,.main-navigation,.topbar,footer,.site-footer{font-family:var(--header-footer-font)!important;}</style>
  <?php }
  add_action('wp_head','btx_print_font_css',99);
}

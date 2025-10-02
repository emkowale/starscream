(function($){
  function hideRows(ctx){
    $('.variations tr', ctx || document).each(function(){
      var $r = $(this);
      if ($r.find('.bt-should-hide').length){ $r.hide(); }
    });
  }
  function initForm($form){
    if (!$form.length) return;
    // make Woo compute variation_id from the (hidden) selects
    $form.trigger('check_variations');
    $form.on('woocommerce_update_variation_values found_variation reset_data', function(){
      hideRows(this);
    });
  }
  $(function(){
    hideRows();
    initForm($('.variations_form'));
  });
})(jQuery);

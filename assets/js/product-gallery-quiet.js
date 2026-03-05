(function($){
  $(function(){
    var $gallery = $('.woocommerce-product-gallery');

    if (!$gallery.length) return;

    // Cancel clicks on the MAIN image only (do not block thumbnails)
    $gallery.on('click', '.woocommerce-product-gallery__image a', function(e){
      // If this link is within the main slide, prevent navigation/open
      // (Woo marks main slide with this class; thumbnails are in .flex-control-thumbs)
      e.preventDefault();
      return false;
    });

    // If a theme/plugin re-enabled lightbox later, kill its triggers defensively
    $(document).on('click', '.pswp, .pswp__bg, .pswp__button--close', function(e){
      // Just in case: close immediately rather than trapping user
      try{ if(window.PhotoSwipe){ $('.pswp__button--close').trigger('click'); } }catch(_){}
      e.preventDefault();
    });
  });
})(jQuery);

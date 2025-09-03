/*
 * File: assets/js/product-gallery.js
 * Description: Prevent fullscreen; slide-in main image on thumbnail click.
 * Plugin: Starscream
 * Author: Eric Kowalewski
 * Last Updated: 2025-09-02 (EDT)
 */
jQuery(function ($) {
  var $gallery = $('.woocommerce-product-gallery');
  if (!$gallery.length) return;

  var $wrap = $gallery.find('.woocommerce-product-gallery__wrapper');
  if (!$wrap.length) return;

  $gallery.on('click', '.flex-control-thumbs li img, .woocommerce-product-gallery__thumbnail img', function () {
    $wrap.addClass('ss-anim');
    requestAnimationFrame(function(){ $wrap.addClass('ss-anim-in'); });
    setTimeout(function(){ $wrap.removeClass('ss-anim ss-anim-in'); }, 450);
  });
});

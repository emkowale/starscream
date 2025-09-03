/*
 * File: assets/js/product-gallery.js
 * Description: Prevent fullscreen; add slide-in animation on main image when a thumbnail is clicked.
 * Theme: Starscream
 * Author: Eric Kowalewski
 * Last Updated: 2025-09-02 (EDT)
 */
jQuery(function ($) {
  var $gallery = $('.woocommerce-product-gallery');
  if (!$gallery.length) return;

  // Wrapper that holds the big image slides
  var $wrap = $gallery.find('.woocommerce-product-gallery__wrapper');
  if (!$wrap.length) return;

  // Clicking a thumb should not open a lightbox (we removed lightbox support above).
  // We add a quick slide-in effect while Woo replaces the main image.
  $gallery.on('click', '.flex-control-thumbs li img, .woocommerce-product-gallery__thumbnail img', function () {
    // Start animation state
    $wrap.addClass('ss-anim');
    // Kick to next frame so the transition runs
    requestAnimationFrame(function () {
      $wrap.addClass('ss-anim-in');
    });
    // Clean up class after transition is done
    setTimeout(function () {
      $wrap.removeClass('ss-anim ss-anim-in');
    }, 450);
  });
});

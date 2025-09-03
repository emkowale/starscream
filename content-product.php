<?php
/*
 * File: woocommerce/content-product.php
 * Description: Minimal product card for archives — image, wishlist heart, title, price (no Add to Cart).
 * Theme: The Bear Traxs Subscription Template
 * Author: Eric Kowalewski
 * Version: 1.4.23
 * Last Updated: 2025-08-12 — 22:52 EDT
 */

defined('ABSPATH') || exit;

global $product;
if ( empty( $product ) || ! $product->is_visible() ) { return; }
?>

<li <?php wc_product_class('btx-product-card', $product); ?>>

  <a class="btx-card-link" href="<?php the_permalink(); ?>" aria-label="<?php the_title_attribute(); ?>">
    <div class="btx-card-media">
      <?php
      // image size is forced to 'large' by the filter in functions.php
      echo wp_get_attachment_image(
        $product->get_image_id(),
        apply_filters('single_product_archive_thumbnail_size', 'woocommerce_thumbnail'),
        false,
        ['class' => 'btx-card-img', 'loading' => 'lazy', 'decoding' => 'async']
      );
      ?>
      <div class="btx-wishlist">
        <?php echo do_shortcode('[ti_wishlist_products_counter]'); /* optional counter */ ?>
        <?php echo do_shortcode('[ti_wishlists_addtowishlist]');   /* TI Wishlist heart */ ?>
      </div>
    </div>
  </a>

  <h2 class="btx-card-title"><?php the_title(); ?></h2>

  <div class="btx-card-price">
    <?php woocommerce_template_loop_price(); ?>
  </div>

</li>
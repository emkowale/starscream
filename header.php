<?php
/** ===============================
 *  header.php — Starscream
 *  - Uses Customizer logo (company_logo_id) only
 *  - No inline CSS; styles live in style.css
 *  =============================== */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Icons -->
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<!-- ===============================
     Site Header
     =============================== -->
<header class="site-header">
  <!-- Tagline -->
  <div class="header-topline">
    <?php bloginfo('description'); ?>
  </div>

  <!-- Logo + Actions -->
  <div class="header-main">
    <div class="site-logo">
      <?php
      $logo_id = (int) get_theme_mod('company_logo_id', 0);
      if ($logo_id) {
          // Prefer attachment alt; fallback to site name
          $alt = get_post_meta($logo_id, '_wp_attachment_image_alt', true);
          if (!is_string($alt) || $alt === '') {
              $alt = get_bloginfo('name', 'display');
          }
          echo '<a class="site-logo-link" href="' . esc_url(home_url('/')) . '">';
          echo wp_get_attachment_image(
              $logo_id,
              'full',
              false,
              [
                  'class'     => 'site-logo-img',
                  'alt'       => esc_attr($alt),
                  'decoding'  => 'async',
                  'loading'   => 'eager' // keep header logo snappy
              ]
          );
          echo '</a>';
      } else {
          // Text fallback ONLY if no Customizer logo set
          echo '<a class="site-title" href="' . esc_url(home_url('/')) . '">'
             . esc_html(get_bloginfo('name', 'display'))
             . '</a>';
      }
      ?>
    </div>

    <div class="header-icons">
      <a href="/my-account/" aria-label="Account" class="header-icon-link">
        <i class="fas fa-user"></i>
      </a>
      <a href="/cart/" aria-label="Cart" class="header-icon-link">
        <i class="fas fa-shopping-cart"></i>
      </a>

      <?php if ($phone = get_theme_mod('phone_number')): ?>
        <div class="header-contact-line">
          <i class="fas fa-phone header-contact-icon"></i>
          <a class="header-contact-link"
             href="tel:<?php echo preg_replace('/[^0-9+]/', '', $phone); ?>">
            <?php echo esc_html($phone); ?>
          </a>
        </div>
      <?php endif; ?>

      <?php if ($email = get_theme_mod('email_address')): ?>
        <div class="header-contact-line">
          <i class="fas fa-envelope header-contact-icon"></i>
          <a class="header-contact-link" href="mailto:<?php echo esc_attr($email); ?>">
            <?php echo esc_html($email); ?>
          </a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</header>

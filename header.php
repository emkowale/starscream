<?php
/** ===============================
 *  header.php  — The Bear Traxs
 *  - No Woo titles/breadcrumbs here
 *  - Colors & font come from Customizer
 *  =============================== */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Icons -->
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <!-- Small hover tweak; uses your CSS variables -->
  <style>
    .header-icons a:hover,
    .footer-socials a:hover {
      color: var(--footer-text-color) !important;
    }
  </style>

  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<!-- ===============================
     Site Header
     =============================== -->
<header
  style="
    background-color: <?php echo esc_attr(get_theme_mod('header_bg_color', '#eeeeee')); ?>;
    color:            <?php echo esc_attr(get_theme_mod('header_footer_text_color', '#000000')); ?>;
    padding:10px 20px;
    font-family: <?php echo esc_attr(get_theme_mod('header_footer_font', 'Roboto')); ?>;
  "
>
  <!-- Tagline -->
  <div style="text-align:center; font-size:14px; margin-bottom:5px;">
    <?php bloginfo('description'); ?>
  </div>

  <!-- Logo + Actions -->
  <div style="display:flex; justify-content:space-between; align-items:center;">
  <div class="site-logo">
    <?php
    $company_logo_id = (int) get_theme_mod('company_logo_id', 0);

    if ( has_custom_logo() ) {
        // Output WP’s built-in markup (already wrapped in <a class="custom-logo-link">)
        echo get_custom_logo();
    } elseif ( $company_logo_id ) {
        // Fallback: render our theme option directly with the same classes
        echo '<a href="'.esc_url(home_url('/')).'" class="custom-logo-link" rel="home">'
          . wp_get_attachment_image($company_logo_id, 'full', false, [
              'class' => 'custom-logo',
              'alt'   => get_bloginfo('name'),
            ])
          . '</a>';
    } else {
        // Text fallback
        echo '<a href="'.esc_url(home_url('/')).'" class="site-title" style="text-decoration:none; color:inherit;">'
          . esc_html(get_bloginfo('name'))
          . '</a>';
    }
    ?>
  </div>



    <div class="header-icons" style="text-align:right;">
      <a href="/my-account/" aria-label="Account"
         style="margin-right:15px; color:<?php echo esc_attr(get_theme_mod('accent_color', '#0073aa')); ?>;">
        <i class="fas fa-user"></i>
      </a>
      <a href="/cart/" aria-label="Cart"
         style="margin-right:15px; color:<?php echo esc_attr(get_theme_mod('accent_color', '#0073aa')); ?>;">
        <i class="fas fa-shopping-cart"></i>
      </a>

      <?php if ($phone = get_theme_mod('phone_number')): ?>
        <div style="margin-top:5px;">
          <i class="fas fa-phone" style="color:<?php echo esc_attr(get_theme_mod('accent_color', '#0073aa')); ?>;"></i>
          <a href="tel:<?php echo preg_replace('/[^0-9+]/', '', $phone); ?>"
             style="color:<?php echo esc_attr(get_theme_mod('header_footer_text_color', '#000000')); ?>; text-decoration:none;">
            <?php echo esc_html($phone); ?>
          </a>
        </div>
      <?php endif; ?>

      <?php if ($email = get_theme_mod('email_address')): ?>
        <div>
          <i class="fas fa-envelope" style="color:<?php echo esc_attr(get_theme_mod('accent_color', '#0073aa')); ?>;"></i>
          <a href="mailto:<?php echo esc_attr($email); ?>"
             style="color:<?php echo esc_attr(get_theme_mod('header_footer_text_color', '#000000')); ?>; text-decoration:none;">
            <?php echo esc_html($email); ?>
          </a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</header>

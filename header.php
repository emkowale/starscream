<?php
/** ===============================
 *  header.php  — The Bear Traxs
 *  - No Woo titles/breadcrumbs here
 *  - Colors & font come from Customizer via CSS vars
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
<?php wp_body_open(); ?>

<!-- ===============================
     Site Header
     =============================== -->
<header class="btx-site-header">
  <!-- Tagline -->
  <div class="btx-tagline">
    <?php bloginfo('description'); ?>
  </div>

  <!-- Logo + Actions -->
  <div class="btx-header-bar">
    <div class="site-logo">
      <?php
      $logo_id = (int) get_theme_mod('custom_logo', 0);
      if ($logo_id) {
        $logo = wp_get_attachment_image($logo_id, 'full', false, [
          'class' => 'custom-logo btx-header-logo',
          'alt'   => get_bloginfo('name'),
        ]);
        echo '<a href="'.esc_url(home_url('/')).'" class="custom-logo-link" rel="home">'.$logo.'</a>';
      } else {
        echo '<a href="'.esc_url(home_url('/')).'" class="site-title">'
          . esc_html(get_bloginfo('name'))
          . '</a>';
      }
      ?>
    </div>

    <div class="header-icons">
      <a href="/my-account/" aria-label="Account"><i class="fas fa-user"></i></a>
      <a href="/cart/" aria-label="Cart"><i class="fas fa-shopping-cart"></i></a>

      <?php if ($phone = get_theme_mod('phone_number')): ?>
        <div>
          <i class="fas fa-phone"></i>
          <a href="tel:<?php echo preg_replace('/[^0-9+]/', '', $phone); ?>">
            <?php echo esc_html($phone); ?>
          </a>
        </div>
      <?php endif; ?>

      <?php if ($email = get_theme_mod('email_address')): ?>
        <div>
          <i class="fas fa-envelope"></i>
          <a href="mailto:<?php echo esc_attr($email); ?>">
            <?php echo esc_html($email); ?>
          </a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</header>

<?php
// Header banner — HOME pages only (front/blog/shop). Ignore the toggle for now.
if ( is_front_page() || is_home() || ( function_exists('is_shop') && is_shop() ) ) {
  $id = (int) get_theme_mod('home_top_banner_image_id', 0);
  if ( $id ) {
    $link = trim( get_theme_mod('home_top_banner_link', '' ) );
    $alt  = trim( get_theme_mod('home_top_banner_alt',  '' ) );
    echo '<div class="btx-header-banner" role="region" aria-label="Site banner">';
    if ( $link ) echo '<a class="btx-header-banner__link" href="'.esc_url($link).'">';
    echo wp_get_attachment_image(
      $id, 'full', false,
      ['class'=>'btx-header-banner__img','loading'=>'lazy','decoding'=>'async','alt'=>$alt]
    );
    if ( $link ) echo '</a>';
    echo '</div>';
  }
}
?>

<?php
/** ===============================
 *  footer.php — The Bear Traxs
 *  =============================== */
?>

<?php
// Footer banner — HOME pages only (front/blog/shop). Ignore the toggle for now.
if ( is_front_page() || is_home() || ( function_exists('is_shop') && is_shop() ) ) {
  $id = (int) get_theme_mod('home_bottom_banner_image_id', 0);
  if ( $id ) {
    $link = trim( get_theme_mod('home_bottom_banner_link', '' ) );
    $alt  = trim( get_theme_mod('home_bottom_banner_alt',  '' ) );
    echo '<div class="btx-footer-banner" role="region" aria-label="Site banner">';
    if ( $link ) echo '<a class="btx-footer-banner__link" href="'.esc_url($link).'">';
    echo wp_get_attachment_image(
      $id, 'full', false,
      ['class'=>'btx-footer-banner__img','loading'=>'lazy','decoding'=>'async','alt'=>$alt]
    );
    if ( $link ) echo '</a>';
    echo '</div>';
  }
}
?>


<footer class="bt-footer btx-site-footer" role="contentinfo">
  <div class="bt-footer-grid">

    <!-- Column 1: Logo -->
    <div class="bt-footer-col bt-footer-logo">
      <?php
      $logo_id = (int) get_theme_mod('custom_logo');
      if ($logo_id) {
        $logo = wp_get_attachment_image($logo_id, 'full', false, [
          'class' => 'custom-logo btx-footer-logo',
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

    <!-- Column 2: Copy -->
    <div class="bt-footer-col bt-footer-copy">
      &copy; <?php echo esc_html(get_bloginfo('name')) . ' ' . date('Y'); ?>. All Rights Reserved.
      <div class="powered-by">
        Powered by <a href="https://thebeartraxs.com" target="_blank" rel="noopener">The Bear Traxs</a>
      </div>
    </div>

    <!-- Column 3: Socials -->
    <div class="bt-footer-col bt-footer-socials footer-socials">
      <?php for ($i = 1; $i <= 4; $i++) :
        $icon = get_theme_mod("social_icon_$i");
        $url  = get_theme_mod("social_url_$i");
        if (!empty($icon) && !empty($url)) : ?>
          <a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener" style="color: var(--accent-color);">
            <i class="<?php echo esc_attr($icon); ?>"></i>
          </a>
        <?php endif;
      endfor; ?>
    </div>

  </div>

  <?php wp_footer(); ?>
</footer>
</body>
</html>

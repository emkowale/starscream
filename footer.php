<footer
  class="bt-footer"
  style="--bt-footer-bg: <?php echo esc_attr(get_theme_mod('footer_bg_color', '#eeeeee')); ?>;
         --bt-footer-text: <?php echo esc_attr(get_theme_mod('footer_text_color', '#000000')); ?>;
         --bt-accent: <?php echo esc_attr(get_theme_mod('accent_color', '#0073aa')); ?>;
         --bt-font: <?php echo esc_attr(get_theme_mod('header_footer_font', 'Roboto')); ?>;">
  <div class="bt-footer-grid">

    <!-- Column 1: Logo -->
    <div class="bt-footer-col bt-footer-logo">
      <?php $logo_id = get_theme_mod('custom_logo'); ?>
      <?php $logo_url = is_string($logo_id) || is_int($logo_id) ? wp_get_attachment_url($logo_id) : ''; ?>
      <?php if (!empty($logo_url)) : ?>
        <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?> logo">
      <?php endif; ?>
    </div>

    <!-- Column 2: Copy -->
    <div class="bt-footer-col bt-footer-copy">
      &copy; <?php echo esc_html(get_bloginfo('name')) . ' ' . date('Y'); ?>. All Rights Reserved.
      <div class="powered-by">
        Powered by <a href="https://thebeartraxs.com" target="_blank" rel="noopener">The Bear Traxs</a>
      </div>
    </div>

    <!-- Column 3: Socials -->
    <div class="bt-footer-col bt-footer-socials">
      <?php for ($i = 1; $i <= 4; $i++) : ?>
        <?php $icon = get_theme_mod("social_icon_$i"); $url = get_theme_mod("social_url_$i"); ?>
        <?php if (!empty($icon) && !empty($url)) : ?>
          <a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener">
            <i class="<?php echo esc_attr($icon); ?>"></i>
          </a>
        <?php endif; ?>
      <?php endfor; ?>
    </div>

  </div>
  <?php wp_footer(); ?>
</footer>
</body>
</html>

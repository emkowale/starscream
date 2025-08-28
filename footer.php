<footer style="background-color: <?php echo esc_attr(get_theme_mod('footer_bg_color', '#eeeeee')); ?>;
                color: <?php echo esc_attr(get_theme_mod('footer_text_color', '#000000')); ?>;
                padding: 20px; text-align: center; font-family: <?php echo esc_attr(get_theme_mod("header_footer_font", "Roboto")); ?>;">
  <div style="display: flex; justify-content: space-between; align-items: center;">
    <div class="footer-logo">
      <?php $logo_id = get_theme_mod('custom_logo'); ?>
      <?php $logo_url = is_string($logo_id) || is_int($logo_id) ? wp_get_attachment_url($logo_id) : ''; ?>
      <?php if (!empty($logo_url)): ?>
        <img src="<?php echo esc_url($logo_url); ?>" style="height: 100px; width: auto;">
      <?php endif; ?>
    </div>
      <div class="footer-copy">
        &copy; <?php echo esc_html(get_bloginfo('name')) . ' ' . date('Y'); ?>. All Rights Reserved.
        <div class="powered-by">
          Powered by <a href="https://thebeartraxs.com" target="_blank" rel="noopener">The Bear Traxs</a>
        </div>
      </div>
  </div>
  <div style="margin-top: 15px;">
    <?php for ($i = 1; $i <= 4; $i++): ?>
      <?php $icon = get_theme_mod("social_icon_$i"); $url = get_theme_mod("social_url_$i"); ?>
      <?php if (!empty($icon) && !empty($url)): ?>
        <a href="<?php echo esc_url($url); ?>" target="_blank" style="margin: 0 10px; text-decoration: none;">
          <i class="<?php echo esc_attr($icon); ?>" style="font-size: 24px; color: <?php echo esc_attr(get_theme_mod('accent_color', '#0073aa')); ?>;"></i>
        </a>
      <?php endif; ?>
    <?php endfor; ?>
  </div>
<?php wp_footer(); ?>
</footer>
</body>
</html>
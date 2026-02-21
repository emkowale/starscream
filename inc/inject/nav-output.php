<?php
if (!defined('ABSPATH')) exit;

/**
 * Output a minimal nav immediately after <body>.
 * It is VISIBLE by default so you always see a menu even if JS is blocked.
 * JS will relocate it into .btx-header-bar when available.
 */
add_action('wp_body_open', function () {
  ?>
  <div id="starscream-injected-nav">
    <nav id="primary-menu" class="site-nav" role="navigation"
         aria-label="<?php echo esc_attr__('Primary', 'starscream'); ?>">
      <?php
      wp_nav_menu([
        'theme_location' => 'primary',
        'container'      => false,
        'menu_class'     => 'menu',
        'fallback_cb'    => 'wp_page_menu',
        'depth'          => 2,
      ]);
      ?>
    </nav>
  </div>
  <?php
}, 5);

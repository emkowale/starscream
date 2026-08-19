<?php
if (!defined('ABSPATH')) exit;

if (!function_exists('starscream_tbt_slider_get_video_url')) {
  function starscream_tbt_slider_get_video_url() {
    return trim((string) get_theme_mod('tbt_slider_video_url', ''));
  }
}

if (!function_exists('starscream_tbt_slider_get_ticker_text')) {
  function starscream_tbt_slider_get_ticker_text() {
    $text = trim((string) get_theme_mod('tbt_slider_ticker_text', 'Scrub Style. All Day, Every Day.'));
    if ($text === '') $text = trim((string) get_bloginfo('name'));
    return $text;
  }
}

if (!function_exists('starscream_tbt_slider_get_youtube_embed_url')) {
  function starscream_tbt_slider_get_youtube_embed_url($url) {
    $url = trim((string) $url);
    if ($url === '') return '';

    $parts = wp_parse_url($url);
    if (!is_array($parts) || empty($parts['host'])) return '';

    $host = strtolower((string) $parts['host']);
    $path = isset($parts['path']) ? trim((string) $parts['path'], '/') : '';
    $video_id = '';

    if (strpos($host, 'youtu.be') !== false) {
      $pieces = explode('/', $path);
      $video_id = isset($pieces[0]) ? (string) $pieces[0] : '';
    } elseif (strpos($host, 'youtube.com') !== false || strpos($host, 'youtube-nocookie.com') !== false) {
      if (strpos($path, 'embed/') === 0) {
        $pieces = explode('/', substr($path, 6));
        $video_id = isset($pieces[0]) ? (string) $pieces[0] : '';
      } elseif (strpos($path, 'shorts/') === 0) {
        $pieces = explode('/', substr($path, 7));
        $video_id = isset($pieces[0]) ? (string) $pieces[0] : '';
      } else {
        $query = [];
        parse_str(isset($parts['query']) ? (string) $parts['query'] : '', $query);
        if (!empty($query['v'])) $video_id = (string) $query['v'];
      }
    }

    $video_id = preg_replace('~[^A-Za-z0-9_-]~', '', $video_id);
    if ($video_id === '') return '';

    $params = [
      'autoplay' => '1',
      'mute' => '1',
      'loop' => '1',
      'playlist' => $video_id,
      'playsinline' => '1',
      'controls' => '0',
      'rel' => '0',
      'modestbranding' => '1',
    ];

    return 'https://www.youtube.com/embed/' . rawurlencode($video_id) . '?' . http_build_query($params);
  }
}

if (!function_exists('starscream_render_tbt_slider_shortcode')) {
  function starscream_render_tbt_slider_shortcode($atts = [], $content = null, $shortcode_tag = '') {
    $video_url = starscream_tbt_slider_get_video_url();
    $ticker_enabled = (bool) get_theme_mod('tbt_slider_ticker_enabled', false);
    $ticker_text = starscream_tbt_slider_get_ticker_text();
    $line_1 = trim((string) get_theme_mod('tbt_slider_line_1', ''));
    $line_2 = trim((string) get_theme_mod('tbt_slider_line_2', ''));
    $line_3 = trim((string) get_theme_mod('tbt_slider_line_3', ''));
    $button_text = trim((string) get_theme_mod('tbt_slider_button_text', ''));
    $button_link = trim((string) get_theme_mod('tbt_slider_button_link', ''));
    $has_content = ($line_1 !== '' || $line_2 !== '' || $line_3 !== '' || ($button_text !== '' && $button_link !== ''));

    if (!$ticker_enabled && $video_url === '') return '';

    $youtube_embed = starscream_tbt_slider_get_youtube_embed_url($video_url);
    $is_mp4 = (bool) preg_match('~\\.mp4($|\\?)~i', $video_url);

    ob_start();
    ?>
    <div class="btx-tbt-slider" data-btx-tbt-slider="1">
      <?php if ($ticker_enabled && $ticker_text !== ''): ?>
        <section class="shopify-section section-scrolling-text btx-tbt-slider__ticker" role="region" aria-label="Slider ticker">
          <div class="row full-width-row-full">
            <div class="scrolling-text scrolling-text-uppercase--false section-spacing section-spacing--disable-top section-spacing--disable-bottom height-auto heading-font btx-tbt-slider__scrolling-text" style="--color-bg:#0171bb;--color-text:#ffffff;--overlay-color-rgb:0,0,0;--overlay-opacity:0;">
              <div class="scrolling-text--inner direction-left btx-tbt-slider__scrolling-inner" style="--marquee-speed: 30s;">
                <?php for ($g = 0; $g < 2; $g++): ?>
                  <div>
                    <?php for ($i = 0; $i < 3; $i++): ?>
                      <div class="scrolling-text--item outline-text--true"><span><?php echo esc_html($ticker_text); ?></span></div>
                    <?php endfor; ?>
                  </div>
                <?php endfor; ?>
              </div>
            </div>
          </div>
        </section>
      <?php endif; ?>

      <?php if ($video_url !== '' && ($youtube_embed !== '' || $is_mp4)): ?>
        <section class="shopify-section section-slideshow btx-tbt-slider__video" role="region" aria-label="Slider video">
          <div class="row full-width-row">
            <div class="slideshow main-slideshow mobile-height-500 desktop-height-650 section-spacing section-spacing--disable-top section-spacing--disable-bottom">
              <div class="slideshow__slide slideshow__slide--index-1 mobile-height-500 desktop-height-650 is-selected">
                <div class="slideshow__slide-video-bg btx-tbt-slider__video-bg" data-provider="<?php echo $youtube_embed !== '' ? 'youtube' : 'hosted'; ?>">
                  <?php if ($youtube_embed !== ''): ?>
                    <iframe
                      src="<?php echo esc_url($youtube_embed); ?>"
                      title="Slider video"
                      frameborder="0"
                      allow="autoplay; encrypted-media; picture-in-picture"
                      allowfullscreen>
                    </iframe>
                  <?php else: ?>
                    <video autoplay muted loop playsinline preload="metadata">
                      <source src="<?php echo esc_url($video_url); ?>" type="video/mp4">
                    </video>
                  <?php endif; ?>
                </div>
                <div class="slideshow__slide-overlay btx-tbt-slider__video-overlay" aria-hidden="true"></div>
                <?php if ($has_content): ?>
                  <div class="btx-tbt-slider__content">
                    <?php if ($line_1 !== ''): ?>
                      <p class="btx-tbt-slider__line btx-tbt-slider__line--top"><?php echo esc_html($line_1); ?></p>
                    <?php endif; ?>
                    <?php if ($line_2 !== ''): ?>
                      <h2 class="btx-tbt-slider__title"><?php echo esc_html($line_2); ?></h2>
                    <?php endif; ?>
                    <?php if ($line_3 !== ''): ?>
                      <p class="btx-tbt-slider__line btx-tbt-slider__line--bottom"><?php echo esc_html($line_3); ?></p>
                    <?php endif; ?>
                    <?php if ($button_text !== '' && $button_link !== ''): ?>
                      <a class="btx-tbt-slider__cta" href="<?php echo esc_url($button_link); ?>"><?php echo esc_html($button_text); ?></a>
                    <?php endif; ?>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </section>
      <?php endif; ?>
    </div>
    <?php
    return trim((string) ob_get_clean());
  }
}

add_shortcode('tbt-slider', 'starscream_render_tbt_slider_shortcode');

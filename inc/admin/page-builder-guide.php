<?php
if (!defined('ABSPATH')) exit;

if (!function_exists('starscream_page_builder_guide_action')) {
  function starscream_page_builder_guide_action() {
    return 'starscream_page_builder_guide';
  }
}

if (!function_exists('starscream_page_builder_guide_url')) {
  function starscream_page_builder_guide_url($args = []) {
    $args = is_array($args) ? $args : [];
    $args['action'] = starscream_page_builder_guide_action();
    return add_query_arg($args, admin_url('admin-post.php'));
  }
}

if (!function_exists('starscream_page_builder_guide_asset_url')) {
  function starscream_page_builder_guide_asset_url($relpath) {
    if (!function_exists('starscream_locate') || !function_exists('starscream_asset_uri')) return '';

    $path = starscream_locate($relpath);
    if (!$path || !file_exists($path)) return '';

    $version = filemtime($path);
    if (!$version) {
      $version = wp_get_theme()->get('Version');
    }

    return add_query_arg('ver', rawurlencode((string) $version), starscream_asset_uri($relpath));
  }
}

add_action('admin_init', function () {
  if (!is_admin()) return;
  if (!current_user_can('edit_theme_options')) return;

  $page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
  if ($page !== 'starscream-page-builder-guide') return;

  wp_safe_redirect(starscream_page_builder_guide_url());
  exit;
});

add_action('admin_post_' . 'starscream_page_builder_guide', function () {
  if (!current_user_can('edit_theme_options')) {
    wp_die(esc_html__('You do not have permission to access this guide.', 'starscream'));
  }

  $site_css = starscream_page_builder_guide_asset_url('assets/css/site.css');
  $pages_css = starscream_page_builder_guide_asset_url('assets/css/pages.css');
  $guide_css = starscream_page_builder_guide_asset_url('assets/css/admin-page-builder-guide.css');
  $guide_js = starscream_page_builder_guide_asset_url('assets/js/admin-page-builder-guide.js');

  nocache_headers();
  status_header(200);
  ?>
  <!doctype html>
  <html <?php language_attributes(); ?> class="starscream-guide-frame">
    <head>
      <meta charset="<?php bloginfo('charset'); ?>">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <title><?php echo esc_html(get_bloginfo('name') . ' | Page Builder Guide'); ?></title>
      <?php if ($site_css !== '') : ?>
        <link rel="stylesheet" href="<?php echo esc_url($site_css); ?>">
      <?php endif; ?>
      <?php if ($pages_css !== '') : ?>
        <link rel="stylesheet" href="<?php echo esc_url($pages_css); ?>">
      <?php endif; ?>
      <?php if ($guide_css !== '') : ?>
        <link rel="stylesheet" href="<?php echo esc_url($guide_css); ?>">
      <?php endif; ?>
    </head>
    <body class="starscream-guide-frame">
      <?php starscream_render_page_builder_guide(); ?>
      <?php if ($guide_js !== '') : ?>
        <script src="<?php echo esc_url($guide_js); ?>"></script>
      <?php endif; ?>
    </body>
  </html>
  <?php
  exit;
});

if (!function_exists('starscream_page_builder_guide_reference_rows')) {
  function starscream_page_builder_guide_reference_rows() {
    return [
      ['site-page-shell', 'Outer wrapper for the whole page', 'Use this once per page, then add your own page prefix class beside it.'],
      ['site-section', 'Standard vertical spacing for a section', 'Add `site-section--tight` for smaller spacing or `site-section--soft` for a soft background.'],
      ['site-container', 'Centers content and caps line length', 'Use inside each section.'],
      ['site-page-intro', 'Eyebrow + heading + lead block', 'Best for centered intros above sections or pages.'],
      ['site-rich-text', 'Readable text column', 'Use for mission statements, paragraphs, and lists.'],
      ['site-grid site-grid--2/3/4', 'Responsive grid layouts', 'The grid collapses automatically on smaller screens.'],
      ['site-card / site-card--feature', 'Surface block for features and summaries', 'Use `site-card__title` and `site-card__body` for consistent typography.'],
      ['site-media-frame', 'Framed media container', 'Drop an image, video, or iframe inside.'],
      ['site-hero / site-hero--dark', 'Large headline section', 'Good for hero panels and quote blocks.'],
      ['site-checklist', 'Styled bullet checklist', 'Ideal for benefits, services, and process lists.'],
      ['site-stat-band', 'Three-up statistics band', 'Use strong text for numbers and span for labels.'],
      ['site-cta-band', 'Closing call-to-action block', 'Pair with `site-cta-band__actions` and the button classes.'],
      ['site-btn site-btn--primary', 'Primary action button', 'Use for the main click target in a section.'],
      ['site-btn site-btn--secondary', 'Secondary action button', 'Use as the alternate action beside the primary button.'],
    ];
  }
}

if (!function_exists('starscream_page_builder_guide_patterns')) {
  function starscream_page_builder_guide_patterns() {
    return [
      [
        'id' => 'shell',
        'title' => 'Start With a Page Shell',
        'description' => 'Most landing pages should begin with one page wrapper, one page-specific prefix, and a small scoped style block for any custom layout helpers.',
        'note' => 'Keep custom selectors prefixed to one page class like `.company-page__split` so they never leak into other pages.',
        'preview' => <<<'HTML'
<section class="site-section site-section--tight">
  <div class="site-container">
    <header class="site-page-intro">
      <p class="site-page-intro__eyebrow">Page Eyebrow</p>
      <h1 class="site-page-intro__title">Build a page from reusable Starscream classes</h1>
      <p class="site-page-intro__lead">The theme handles spacing, typography, cards, buttons, grids, and section rhythm for you.</p>
    </header>
  </div>
</section>
HTML,
        'code' => <<<'HTML'
<style>
.company-page{
  --company-page-navy:#0b3042;
  --company-page-blue:#11708f;
}

.company-page__split{
  display:grid;
  gap:clamp(1.25rem,3vw,2.5rem);
  align-items:center;
}

@media (min-width:900px){
  .company-page__split{
    grid-template-columns:minmax(0,1fr) minmax(0,1fr);
  }
}
</style>

<div class="site-page-shell company-page">
  <section class="site-section site-section--tight">
    <div class="site-container">
      <header class="site-page-intro">
        <p class="site-page-intro__eyebrow">Page Eyebrow</p>
        <h1 class="site-page-intro__title">Page headline</h1>
        <p class="site-page-intro__lead">Short supporting paragraph.</p>
      </header>
    </div>
  </section>
</div>
HTML,
      ],
      [
        'id' => 'hero-split',
        'title' => 'Hero Split Layout',
        'description' => 'This is the same basic pattern used on the About Us page: a text-heavy hero or intro panel paired with framed media.',
        'note' => 'Use your page-specific split helper for the two-column behavior. The theme already handles the card, hero, and text styling.',
        'preview' => <<<'HTML'
<section class="site-section">
  <div class="site-container">
    <div class="starscream-guide-split">
      <div class="site-hero">
        <div class="site-hero__inner">
          <p class="site-page-intro__eyebrow">Hero Split</p>
          <h2 class="site-hero__title">Pair strong copy with a single image.</h2>
          <p class="site-hero__lead">Use this for welcome sections, mission statements, or service overviews that need a visual anchor.</p>
          <div class="site-cta-band__actions">
            <a class="site-btn site-btn--primary" href="#">Primary Action</a>
            <a class="site-btn site-btn--secondary" href="#">Secondary Action</a>
          </div>
        </div>
      </div>
      <figure class="site-media-frame">
        <div class="starscream-guide-placeholder">Image / Video</div>
      </figure>
    </div>
  </div>
</section>
HTML,
        'code' => <<<'HTML'
<section class="site-section">
  <div class="site-container">
    <div class="company-page__split">
      <div class="site-hero">
        <div class="site-hero__inner">
          <p class="site-page-intro__eyebrow">Hero Split</p>
          <h2 class="site-hero__title">Pair strong copy with a single image.</h2>
          <p class="site-hero__lead">Use this for welcome sections, mission statements, or service overviews.</p>
          <div class="site-cta-band__actions">
            <a class="site-btn site-btn--primary" href="/shop/">Primary Action</a>
            <a class="site-btn site-btn--secondary" href="/contact-us/">Secondary Action</a>
          </div>
        </div>
      </div>

      <figure class="site-media-frame">
        <img
          src="https://images.unsplash.com/photo-1619450565660-2a93ba8603fd?auto=format&fit=crop&w=1400&q=80"
          alt="Describe the image"
          loading="lazy"
        >
      </figure>
    </div>
  </div>
</section>
HTML,
      ],
      [
        'id' => 'cards',
        'title' => 'Feature Card Grid',
        'description' => 'Use cards for services, reasons to choose you, benefits, or program highlights. The grid classes handle the responsive columns.',
        'note' => 'Stick to short headlines and concise body copy so all cards stay visually balanced.',
        'preview' => <<<'HTML'
<section class="site-section site-section--soft">
  <div class="site-container">
    <header class="site-page-intro">
      <p class="site-page-intro__eyebrow">Feature Grid</p>
      <h2 class="site-page-intro__title">Show three or four key points.</h2>
    </header>
    <div class="site-grid site-grid--3">
      <article class="site-card site-card--feature">
        <h3 class="site-card__title">Feature One</h3>
        <p class="site-card__body">Short explanation of what makes this offer useful.</p>
      </article>
      <article class="site-card site-card--feature">
        <h3 class="site-card__title">Feature Two</h3>
        <p class="site-card__body">A second supporting message with the same content length.</p>
      </article>
      <article class="site-card site-card--feature">
        <h3 class="site-card__title">Feature Three</h3>
        <p class="site-card__body">Keep the structure identical so the cards read as one system.</p>
      </article>
    </div>
  </div>
</section>
HTML,
        'code' => <<<'HTML'
<section class="site-section site-section--soft">
  <div class="site-container">
    <header class="site-page-intro">
      <p class="site-page-intro__eyebrow">Feature Grid</p>
      <h2 class="site-page-intro__title">Show three or four key points.</h2>
    </header>

    <div class="site-grid site-grid--3">
      <article class="site-card site-card--feature">
        <h3 class="site-card__title">Feature One</h3>
        <p class="site-card__body">Short explanation of what makes this offer useful.</p>
      </article>

      <article class="site-card site-card--feature">
        <h3 class="site-card__title">Feature Two</h3>
        <p class="site-card__body">A second supporting message with the same content length.</p>
      </article>

      <article class="site-card site-card--feature">
        <h3 class="site-card__title">Feature Three</h3>
        <p class="site-card__body">Keep the structure identical so the cards read as one system.</p>
      </article>
    </div>
  </div>
</section>
HTML,
      ],
      [
        'id' => 'checklist',
        'title' => 'Checklist + Media Split',
        'description' => 'This pattern works well for “why choose us,” service summaries, and capability sections that need a scannable list.',
        'note' => 'Put the checklist inside `.site-rich-text` so paragraphs and list spacing remain consistent.',
        'preview' => <<<'HTML'
<section class="site-section">
  <div class="site-container">
    <div class="starscream-guide-split">
      <div class="site-rich-text">
        <p class="site-page-intro__eyebrow">Checklist Layout</p>
        <h2 class="site-page-intro__title">Use a list when visitors need fast scanning.</h2>
        <p>Keep the intro short, then move into a checklist of clear benefits or service details.</p>
        <ul class="site-checklist">
          <li>Consistent visual treatment across marketing pages</li>
          <li>Works for services, benefits, and process steps</li>
          <li>Stacks cleanly on mobile without extra work</li>
        </ul>
      </div>
      <figure class="site-media-frame">
        <div class="starscream-guide-placeholder">Support Image</div>
      </figure>
    </div>
  </div>
</section>
HTML,
        'code' => <<<'HTML'
<section class="site-section">
  <div class="site-container">
    <div class="company-page__split">
      <div class="site-rich-text">
        <p class="site-page-intro__eyebrow">Checklist Layout</p>
        <h2 class="site-page-intro__title">Use a list when visitors need fast scanning.</h2>
        <p>Keep the intro short, then move into a checklist of clear benefits or service details.</p>

        <ul class="site-checklist">
          <li>Consistent visual treatment across marketing pages</li>
          <li>Works for services, benefits, and process steps</li>
          <li>Stacks cleanly on mobile without extra work</li>
        </ul>
      </div>

      <figure class="site-media-frame">
        <img
          src="https://images.unsplash.com/photo-1770134223774-13b735e29201?auto=format&fit=crop&w=1200&q=80"
          alt="Describe the image"
          loading="lazy"
        >
      </figure>
    </div>
  </div>
</section>
HTML,
      ],
      [
        'id' => 'stats',
        'title' => 'Statistics Band',
        'description' => 'Use this for milestones, counts, service reach, or other quick trust signals. The theme already handles the spacing and responsive stacking.',
        'note' => 'Keep the number in `strong` and the label in `span` so the typography matches the rest of the system.',
        'preview' => <<<'HTML'
<section class="site-section site-section--soft">
  <div class="site-container">
    <header class="site-page-intro">
      <p class="site-page-intro__eyebrow">Stats Band</p>
      <h2 class="site-page-intro__title">Quick proof points for the page.</h2>
    </header>
    <div class="site-stat-band">
      <div class="site-stat-band__item">
        <strong>10,000+</strong>
        <span>Orders fulfilled</span>
      </div>
      <div class="site-stat-band__item">
        <strong>Hundreds</strong>
        <span>Healthcare teams served</span>
      </div>
      <div class="site-stat-band__item">
        <strong>Fast</strong>
        <span>Turnaround on core customization services</span>
      </div>
    </div>
  </div>
</section>
HTML,
        'code' => <<<'HTML'
<section class="site-section site-section--soft">
  <div class="site-container">
    <header class="site-page-intro">
      <p class="site-page-intro__eyebrow">Stats Band</p>
      <h2 class="site-page-intro__title">Quick proof points for the page.</h2>
    </header>

    <div class="site-stat-band">
      <div class="site-stat-band__item">
        <strong>10,000+</strong>
        <span>Orders fulfilled</span>
      </div>

      <div class="site-stat-band__item">
        <strong>Hundreds</strong>
        <span>Healthcare teams served</span>
      </div>

      <div class="site-stat-band__item">
        <strong>Fast</strong>
        <span>Turnaround on core customization services</span>
      </div>
    </div>
  </div>
</section>
HTML,
      ],
      [
        'id' => 'cta',
        'title' => 'Closing CTA Band',
        'description' => 'Finish the page with one clear action block. This keeps the final section structured without needing a custom page layout.',
        'note' => 'Use this near the bottom of the page after features, lists, and supporting proof.',
        'preview' => <<<'HTML'
<section class="site-section">
  <div class="site-container">
    <div class="site-cta-band">
      <h2 class="site-card__title">Ready for the next step?</h2>
      <p class="site-rich-text">Wrap the page with one concise message and a focused action row.</p>
      <div class="site-cta-band__actions">
        <a class="site-btn site-btn--primary" href="#">Primary Action</a>
        <a class="site-btn site-btn--secondary" href="#">Secondary Action</a>
      </div>
    </div>
  </div>
</section>
HTML,
        'code' => <<<'HTML'
<section class="site-section">
  <div class="site-container">
    <div class="site-cta-band">
      <h2 class="site-card__title">Ready for the next step?</h2>
      <p class="site-rich-text">Wrap the page with one concise message and a focused action row.</p>

      <div class="site-cta-band__actions">
        <a class="site-btn site-btn--primary" href="/shop/">Primary Action</a>
        <a class="site-btn site-btn--secondary" href="/contact-us/">Secondary Action</a>
      </div>
    </div>
  </div>
</section>
HTML,
      ],
      [
        'id' => 'full-page',
        'title' => 'Full Starter Based on the About Us Formula',
        'description' => 'Use this when you want a complete scaffold you can paste in, then replace the copy, images, and page prefix.',
        'note' => 'Paste large HTML in a Custom HTML block or the Classic editor Text tab. Avoid the visual editor for snippets like this.',
        'preview' => <<<'HTML'
<div class="starscream-guide-outline">
  <div class="starscream-guide-outline__item">1. Page shell + scoped style block</div>
  <div class="starscream-guide-outline__item">2. Hero split section</div>
  <div class="starscream-guide-outline__item">3. Mission / content split</div>
  <div class="starscream-guide-outline__item">4. Soft section with cards or gallery</div>
  <div class="starscream-guide-outline__item">5. Closing CTA band</div>
</div>
HTML,
        'code' => <<<'HTML'
<style>
.company-page{
  --company-page-navy:#0b3042;
  --company-page-blue:#11708f;
  --company-page-soft:#eef7fb;
}

.company-page__split,
.company-page__cta{
  display:grid;
  gap:clamp(1.25rem,3vw,2.5rem);
  align-items:center;
}

.company-page__media img,
.company-page__gallery img{
  display:block;
  width:100%;
  height:100%;
  object-fit:cover;
}

.company-page__media img{
  aspect-ratio:5/4;
}

.company-page__gallery{
  display:grid;
  gap:var(--site-grid-gap);
}

.company-page__gallery img{
  aspect-ratio:4/3;
}

@media (min-width:900px){
  .company-page__split,
  .company-page__cta{
    grid-template-columns:minmax(0,1fr) minmax(0,1fr);
  }

  .company-page__gallery{
    grid-template-columns:repeat(2,minmax(0,1fr));
  }
}
</style>

<div class="site-page-shell company-page">
  <section class="site-section site-section--tight">
    <div class="site-container">
      <div class="company-page__split">
        <div class="site-hero">
          <div class="site-hero__inner">
            <p class="site-page-intro__eyebrow">Page Eyebrow</p>
            <h1 class="site-hero__title">Welcome headline for the page</h1>
            <p class="site-hero__lead">Introduce the company, service, or program in one tight paragraph.</p>
          </div>
        </div>

        <figure class="site-media-frame company-page__media">
          <img
            src="https://images.unsplash.com/photo-1619450565660-2a93ba8603fd?auto=format&fit=crop&w=1400&q=80"
            alt="Describe the image"
            loading="lazy"
          >
        </figure>
      </div>
    </div>
  </section>

  <section class="site-section">
    <div class="site-container">
      <div class="company-page__split">
        <div class="site-rich-text">
          <p class="site-page-intro__eyebrow">Our Mission</p>
          <h2 class="site-page-intro__title">Explain the purpose of the business or program.</h2>
          <p>Use one to three paragraphs here for the main story.</p>
          <p>This is the same content rhythm used on the live About Us page.</p>
        </div>

        <figure class="site-media-frame company-page__media">
          <img
            src="https://images.unsplash.com/photo-1770134223774-13b735e29201?auto=format&fit=crop&w=1200&q=80"
            alt="Describe the image"
            loading="lazy"
          >
        </figure>
      </div>
    </div>
  </section>

  <section class="site-section site-section--soft">
    <div class="site-container">
      <header class="site-page-intro">
        <p class="site-page-intro__eyebrow">What We Support</p>
        <h2 class="site-page-intro__title">Show services, categories, or capabilities.</h2>
      </header>

      <div class="company-page__split">
        <div class="site-card">
          <h3 class="site-card__title">What We Support</h3>
          <ul class="site-checklist">
            <li>Service or capability one</li>
            <li>Service or capability two</li>
            <li>Service or capability three</li>
            <li>Service or capability four</li>
          </ul>
        </div>

        <div class="company-page__gallery">
          <figure class="site-media-frame">
            <img
              src="https://images.unsplash.com/photo-1517120026326-d87759a7b63b?auto=format&fit=crop&w=1400&q=80"
              alt="Describe the image"
              loading="lazy"
            >
          </figure>

          <figure class="site-media-frame">
            <img
              src="https://images.unsplash.com/photo-1761234852472-85aeea9c3eac?auto=format&fit=crop&w=1200&q=80"
              alt="Describe the image"
              loading="lazy"
            >
          </figure>
        </div>
      </div>
    </div>
  </section>

  <section class="site-section">
    <div class="site-container">
      <header class="site-page-intro">
        <p class="site-page-intro__eyebrow">Why Choose Us</p>
        <h2 class="site-page-intro__title">Summarize the strongest reasons to work with you.</h2>
      </header>

      <div class="site-grid site-grid--3">
        <article class="site-card site-card--feature">
          <h3 class="site-card__title">Reason One</h3>
          <p class="site-card__body">Short supporting copy.</p>
        </article>

        <article class="site-card site-card--feature">
          <h3 class="site-card__title">Reason Two</h3>
          <p class="site-card__body">Short supporting copy.</p>
        </article>

        <article class="site-card site-card--feature">
          <h3 class="site-card__title">Reason Three</h3>
          <p class="site-card__body">Short supporting copy.</p>
        </article>
      </div>
    </div>
  </section>

  <section class="site-section site-section--tight">
    <div class="site-container">
      <div class="site-cta-band">
        <h2 class="site-card__title">Close with one direct CTA.</h2>
        <p class="site-rich-text">This is where you send the visitor to shop, contact, book, or request a quote.</p>
        <div class="site-cta-band__actions">
          <a class="site-btn site-btn--primary" href="/shop/">Primary Action</a>
          <a class="site-btn site-btn--secondary" href="/contact-us/">Secondary Action</a>
        </div>
      </div>
    </div>
  </section>
</div>
HTML,
      ],
    ];
  }
}

if (!function_exists('starscream_page_builder_guide_render_pattern')) {
  function starscream_page_builder_guide_render_pattern($pattern) {
    $id = isset($pattern['id']) ? sanitize_html_class($pattern['id']) : 'pattern';
    $title = isset($pattern['title']) ? $pattern['title'] : '';
    $description = isset($pattern['description']) ? $pattern['description'] : '';
    $note = isset($pattern['note']) ? $pattern['note'] : '';
    $preview = isset($pattern['preview']) ? $pattern['preview'] : '';
    $code = isset($pattern['code']) ? trim($pattern['code']) : '';
    $textarea_id = 'starscream-guide-snippet-' . $id;
    $rows = max(8, min(36, substr_count($code, "\n") + 2));
    ?>
    <section id="<?php echo esc_attr($id); ?>" class="starscream-guide-section">
      <div class="starscream-guide-section__head">
        <div>
          <h2><?php echo esc_html($title); ?></h2>
          <p><?php echo esc_html($description); ?></p>
        </div>
        <?php if ($note !== '') : ?>
          <p class="starscream-guide-section__note"><?php echo esc_html($note); ?></p>
        <?php endif; ?>
      </div>

      <div class="starscream-guide-pattern">
        <div class="starscream-guide-pattern__panel starscream-guide-pattern__panel--preview">
          <div class="starscream-guide-preview site-page-shell">
            <?php echo $preview; ?>
          </div>
        </div>

        <div class="starscream-guide-pattern__panel starscream-guide-pattern__panel--code">
          <div class="starscream-guide-code__bar">
            <strong>Copyable snippet</strong>
            <button type="button" class="button button-secondary" data-copy-target="<?php echo esc_attr($textarea_id); ?>">Copy</button>
          </div>
          <textarea
            readonly
            spellcheck="false"
            class="starscream-guide-code"
            id="<?php echo esc_attr($textarea_id); ?>"
            rows="<?php echo esc_attr((string) $rows); ?>"
          ><?php echo esc_textarea($code); ?></textarea>
        </div>
      </div>
    </section>
    <?php
  }
}

if (!function_exists('starscream_render_page_builder_guide')) {
  function starscream_render_page_builder_guide() {
    if (!current_user_can('edit_theme_options')) {
      wp_die(esc_html__('You do not have permission to access this page.', 'starscream'));
    }

    $reference_rows = starscream_page_builder_guide_reference_rows();
    $patterns = starscream_page_builder_guide_patterns();
    ?>
    <div class="wrap starscream-guide-page">
      <div class="starscream-guide">
        <aside class="starscream-guide__rail">
          <div class="starscream-guide__rail-card">
            <p class="starscream-guide__eyebrow">Starscream</p>
            <h1>Page Builder Guide</h1>
            <p>Use the same layout system that powers pages like <code>/about-us/</code>, without reinventing the CSS every time.</p>
          </div>

          <nav class="starscream-guide__toc" aria-label="Guide sections">
            <a href="#workflow">Workflow</a>
            <a href="#reference">Core classes</a>
            <?php foreach ($patterns as $pattern) : ?>
              <a href="#<?php echo esc_attr(sanitize_html_class($pattern['id'])); ?>"><?php echo esc_html($pattern['title']); ?></a>
            <?php endforeach; ?>
          </nav>
        </aside>

        <main class="starscream-guide__main">
          <section class="starscream-guide-hero">
            <div class="starscream-guide-hero__copy">
              <p class="starscream-guide__eyebrow">Design faster</p>
              <h2>Build new pages from the existing system, not from scratch.</h2>
              <p>The live <code>About Us</code> page uses a repeatable formula: page shell, section wrappers, split layouts, cards, checklist blocks, and a closing CTA. This guide gives you those pieces with copyable markup.</p>
            </div>
            <div class="starscream-guide-hero__meta">
              <div class="starscream-guide-stat">
                <strong>Theme primitives</strong>
                <span><code>site-section</code>, <code>site-grid</code>, <code>site-card</code>, <code>site-hero</code>, <code>site-checklist</code></span>
              </div>
              <div class="starscream-guide-stat">
                <strong>Best editor mode</strong>
                <span>Custom HTML block or the Classic editor Text tab for full page scaffolds</span>
              </div>
              <div class="starscream-guide-stat">
                <strong>Custom CSS rule</strong>
                <span>Prefix page-specific helpers to one wrapper class such as <code>.company-page__split</code></span>
              </div>
            </div>
          </section>

          <section id="workflow" class="starscream-guide-section">
            <div class="starscream-guide-section__head">
              <div>
                <h2>Workflow</h2>
                <p>Use this sequence when building a new landing page or rewriting an existing one.</p>
              </div>
            </div>

            <div class="starscream-guide-steps">
              <article class="starscream-guide-step">
                <strong>1. Start with one page wrapper</strong>
                <p>Wrap the whole page in <code>site-page-shell</code> and add one page-specific class like <code>company-page</code>.</p>
              </article>
              <article class="starscream-guide-step">
                <strong>2. Build with section primitives</strong>
                <p>Stack sections using <code>site-section</code>, <code>site-container</code>, <code>site-grid</code>, <code>site-card</code>, and <code>site-hero</code>.</p>
              </article>
              <article class="starscream-guide-step">
                <strong>3. Add custom layout helpers only when needed</strong>
                <p>Create small scoped classes like <code>company-page__split</code> or <code>company-page__gallery</code> for layout behavior unique to that page.</p>
              </article>
              <article class="starscream-guide-step">
                <strong>4. Paste in code-friendly editor mode</strong>
                <p>Use a Custom HTML block or the Classic editor Text tab so WordPress does not mangle the markup with extra paragraphs and breaks.</p>
              </article>
            </div>
          </section>

          <section id="reference" class="starscream-guide-section">
            <div class="starscream-guide-section__head">
              <div>
                <h2>Core Class Reference</h2>
                <p>These are the building blocks you should reach for first before inventing anything new.</p>
              </div>
            </div>

            <div class="starscream-guide-table-wrap">
              <table class="widefat fixed striped starscream-guide-table">
                <thead>
                  <tr>
                    <th>Class</th>
                    <th>Use</th>
                    <th>Notes</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($reference_rows as $row) : ?>
                    <tr>
                      <td><code><?php echo esc_html($row[0]); ?></code></td>
                      <td><?php echo esc_html($row[1]); ?></td>
                      <td><?php echo esc_html($row[2]); ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </section>

          <?php foreach ($patterns as $pattern) : ?>
            <?php starscream_page_builder_guide_render_pattern($pattern); ?>
          <?php endforeach; ?>
        </main>
      </div>
    </div>
    <?php
  }
}

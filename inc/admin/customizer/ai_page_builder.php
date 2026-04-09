<?php
if (!defined('ABSPATH')) exit;

if (!function_exists('starscream_sanitize_ai_page_builder_api_key')) {
  function starscream_sanitize_ai_page_builder_api_key($value) {
    return trim((string) sanitize_text_field((string) $value));
  }
}

if (!function_exists('starscream_sanitize_ai_page_builder_model')) {
  function starscream_sanitize_ai_page_builder_model($value) {
    $value = trim((string) sanitize_text_field((string) $value));
    $value = preg_replace('/[^A-Za-z0-9._:-]/', '', $value);
    if (!is_string($value) || $value === '') return 'gpt-5.1';
    return substr($value, 0, 100);
  }
}

if (!function_exists('starscream_ai_page_builder_api_key')) {
  function starscream_ai_page_builder_api_key($override = '') {
    $override = starscream_sanitize_ai_page_builder_api_key($override);
    if ($override !== '') return $override;

    if (defined('OPENAI_API_KEY')) {
      $constant = trim((string) OPENAI_API_KEY);
      if ($constant !== '') return $constant;
    }

    return starscream_sanitize_ai_page_builder_api_key((string) get_theme_mod('starscream_ai_page_builder_api_key', ''));
  }
}

if (!function_exists('starscream_ai_page_builder_model')) {
  function starscream_ai_page_builder_model($override = '') {
    $override = trim((string) $override);
    if ($override !== '') return starscream_sanitize_ai_page_builder_model($override);

    $stored = trim((string) get_theme_mod('starscream_ai_page_builder_model', 'gpt-5.1'));
    if ($stored !== '') return starscream_sanitize_ai_page_builder_model($stored);

    return 'gpt-5.1';
  }
}

if (class_exists('WP_Customize_Control') && !class_exists('Starscream_Customize_AI_Page_Builder_Control')) {
  class Starscream_Customize_AI_Page_Builder_Control extends WP_Customize_Control {
    public $type = 'starscream_ai_page_builder';

    public function render_content() {
      $pages = function_exists('starscream_ai_page_builder_available_pages') ? starscream_ai_page_builder_available_pages() : [];
      $guide_url = function_exists('starscream_page_builder_guide_url') ? starscream_page_builder_guide_url() : '';
      ?>
      <div class="starscream-ai-page-builder" data-control-id="<?php echo esc_attr((string) $this->id); ?>">
        <p class="description">
          Build a new page or overwrite an existing one using the Starscream page system, client notes, relevant media library images, and live web research.
        </p>

        <?php if ($guide_url !== '') : ?>
          <div class="starscream-ai-page-builder__field">
            <div class="starscream-ai-page-builder__guide">
              <div class="starscream-ai-page-builder__guide-copy">
                <span class="starscream-ai-page-builder__label">Page Builder Guide</span>
                <p class="description">Open the Starscream layout guide in a frame without leaving the AI Page Builder.</p>
              </div>
              <a
                class="button button-secondary starscream-ai-page-builder__open-guide"
                href="<?php echo esc_url($guide_url); ?>"
                target="_blank"
                rel="noopener"
              >Open guide</a>
            </div>
          </div>
        <?php endif; ?>

        <div class="starscream-ai-page-builder__field">
          <span class="starscream-ai-page-builder__label">Target</span>
          <div class="starscream-ai-page-builder__mode">
            <label>
              <input type="radio" name="<?php echo esc_attr((string) $this->id); ?>-mode" value="new" checked>
              <span>Create new page</span>
            </label>
            <label>
              <input type="radio" name="<?php echo esc_attr((string) $this->id); ?>-mode" value="existing">
              <span>Overwrite existing page</span>
            </label>
          </div>
        </div>

        <div class="starscream-ai-page-builder__field starscream-ai-page-builder__field--new">
          <label class="starscream-ai-page-builder__label" for="<?php echo esc_attr((string) $this->id); ?>-new-title">New page title</label>
          <input type="text" class="regular-text starscream-ai-page-builder__new-title" id="<?php echo esc_attr((string) $this->id); ?>-new-title" placeholder="About Us">
          <p class="description">New pages are created as drafts so you can review them before publishing.</p>
        </div>

        <div class="starscream-ai-page-builder__field starscream-ai-page-builder__field--existing" hidden>
          <label class="starscream-ai-page-builder__label" for="<?php echo esc_attr((string) $this->id); ?>-existing-page">Existing page</label>
          <select class="starscream-ai-page-builder__existing-page" id="<?php echo esc_attr((string) $this->id); ?>-existing-page">
            <option value="">Select a page</option>
            <?php foreach ($pages as $page) : ?>
              <option
                value="<?php echo (int) $page['id']; ?>"
                data-title="<?php echo esc_attr($page['title']); ?>"
                data-url="<?php echo esc_attr($page['url']); ?>"
              ><?php echo esc_html($page['title']); ?></option>
            <?php endforeach; ?>
          </select>
          <div class="notice notice-warning inline starscream-ai-page-builder__warning" hidden>
            <p><strong>Warning:</strong> building into an existing page will overwrite that page's current content.</p>
          </div>
        </div>

        <div class="starscream-ai-page-builder__field">
          <label class="starscream-ai-page-builder__label" for="<?php echo esc_attr((string) $this->id); ?>-brief">Client brief / raw content</label>
          <textarea
            class="starscream-ai-page-builder__brief"
            id="<?php echo esc_attr((string) $this->id); ?>-brief"
            rows="8"
            placeholder="Paste the client notes, service details, talking points, differentiators, location info, rough copy, or anything else that should shape the page."
          ></textarea>
          <p class="description">The builder uses this brief plus web research to draft the page structure and copy.</p>
        </div>

        <div class="starscream-ai-page-builder__field">
          <span class="starscream-ai-page-builder__label">Client images</span>
          <div class="starscream-ai-page-builder__media-actions">
            <button type="button" class="button starscream-ai-page-builder__select-images">Choose or upload images</button>
            <button type="button" class="button-link-delete starscream-ai-page-builder__clear-images" hidden>Clear</button>
          </div>
          <input type="hidden" class="starscream-ai-page-builder__image-ids" value="">
          <div class="starscream-ai-page-builder__image-list" hidden></div>
          <p class="description">Pick multiple client images here. The builder may also pull additional semantically relevant images from the media library when they fit the page.</p>
        </div>

        <div class="starscream-ai-page-builder__field starscream-ai-page-builder__field--actions">
          <button type="button" class="button button-primary starscream-ai-page-builder__build">Build page</button>
        </div>

        <div class="starscream-ai-page-builder__result" aria-live="polite"></div>

        <div class="starscream-ai-page-builder__tool-divider" role="separator" aria-label="AI Image Replacer">
          <span>AI Image Replacer</span>
        </div>

        <p class="description">
          Pick an existing page and let AI suggest better-fitting media library images for the image slots already on that page.
        </p>

        <div class="starscream-ai-page-builder__field">
          <label class="starscream-ai-page-builder__label" for="<?php echo esc_attr((string) $this->id); ?>-replace-page">Page to update</label>
          <select class="starscream-ai-page-builder__replace-page" id="<?php echo esc_attr((string) $this->id); ?>-replace-page">
            <option value="">Select a page</option>
            <?php foreach ($pages as $page) : ?>
              <option
                value="<?php echo (int) $page['id']; ?>"
                data-title="<?php echo esc_attr($page['title']); ?>"
                data-url="<?php echo esc_attr($page['url']); ?>"
              ><?php echo esc_html($page['title']); ?></option>
            <?php endforeach; ?>
          </select>
          <p class="description">This reads the page content, inspects the current images, and proposes media library replacements before anything is changed.</p>
        </div>

        <div class="starscream-ai-page-builder__field starscream-ai-page-builder__field--actions">
          <button type="button" class="button starscream-ai-page-builder__suggest-replacements">Find replacements</button>
          <button type="button" class="button button-primary starscream-ai-page-builder__apply-replacements" disabled hidden>Apply replacements</button>
        </div>

        <input type="hidden" class="starscream-ai-page-builder__replacements-json" value="">
        <div class="starscream-ai-page-builder__replace-result" aria-live="polite"></div>
        <div class="starscream-ai-page-builder__suggestions" hidden></div>

        <div class="starscream-ai-page-builder__tool-divider" role="separator" aria-label="Media Library Fixer">
          <span>Media Library Fixer</span>
        </div>

        <p class="description">
          Resize oversized media-library images to the site standard for Starscream pages. Bumblebee original art is skipped, while Bumblebee mockups are capped separately for ecommerce use.
        </p>

        <div class="starscream-ai-page-builder__field">
          <span class="starscream-ai-page-builder__label">Standards</span>
          <div class="starscream-ai-page-builder__standards">
            <span><strong>Landscape:</strong> 2560 x 2048 max</span>
            <span><strong>Portrait:</strong> 2048 x 2560 max</span>
            <span><strong>Square:</strong> 2000 x 2000 max</span>
            <span><strong>Bumblebee mockups:</strong> 500 x 500 max</span>
            <span><strong>Bumblebee original art:</strong> never modified</span>
          </div>
        </div>

        <div class="starscream-ai-page-builder__field starscream-ai-page-builder__field--actions">
          <button type="button" class="button starscream-ai-page-builder__fix-media-library">Fix media library images</button>
        </div>

        <div class="starscream-ai-page-builder__fix-result" aria-live="polite"></div>

        <div class="starscream-ai-page-builder__modal" hidden>
          <div class="starscream-ai-page-builder__modal-card">
            <span class="spinner is-active"></span>
            <strong>AI page builder is working...</strong>
            <p class="starscream-ai-page-builder__modal-message">Researching the client and drafting the page.</p>
          </div>
        </div>
      </div>
      <?php
    }
  }
}

if (!function_exists('starscream_ai_page_builder_available_pages')) {
  function starscream_ai_page_builder_available_pages() {
    static $pages = null;
    if (is_array($pages)) return $pages;

    $pages = [];
    foreach (get_pages([
      'sort_column' => 'post_title',
      'sort_order' => 'asc',
      'post_status' => ['publish', 'draft', 'pending', 'private', 'future'],
    ]) as $page) {
      $title = trim((string) $page->post_title);
      if ($title === '') $title = '(Untitled page)';
      $pages[] = [
        'id' => (int) $page->ID,
        'title' => $title,
        'url' => (string) get_permalink($page),
      ];
    }

    return $pages;
  }
}

if (!function_exists('starscream_ai_page_builder_stopwords')) {
  function starscream_ai_page_builder_stopwords() {
    return [
      'a','about','after','all','also','an','and','any','are','as','at','be','because','been','before','but',
      'by','can','do','each','for','from','get','has','have','how','if','in','into','is','it','its','just',
      'may','more','new','not','of','on','or','our','out','page','should','so','that','the','their','them',
      'they','this','to','up','use','using','we','what','when','where','which','who','why','with','your',
    ];
  }
}

if (!function_exists('starscream_ai_page_builder_keywords')) {
  function starscream_ai_page_builder_keywords($title, $brief) {
    $text = strtolower((string) $title . ' ' . (string) $brief);
    $text = preg_replace('/[^a-z0-9\s-]/', ' ', $text);
    $parts = preg_split('/\s+/', (string) $text, -1, PREG_SPLIT_NO_EMPTY);
    if (!is_array($parts)) return [];

    $stopwords = array_flip(starscream_ai_page_builder_stopwords());
    $counts = [];
    foreach ($parts as $part) {
      if (strlen($part) < 3) continue;
      if (isset($stopwords[$part])) continue;
      if (!isset($counts[$part])) $counts[$part] = 0;
      $counts[$part]++;
    }

    arsort($counts);
    return array_slice(array_keys($counts), 0, 14);
  }
}

if (!function_exists('starscream_ai_page_builder_attachment_meta')) {
  function starscream_ai_page_builder_attachment_meta($attachment_id) {
    $attachment_id = absint($attachment_id);
    if ($attachment_id < 1) return null;
    if (get_post_type($attachment_id) !== 'attachment') return null;

    $url = wp_get_attachment_image_url($attachment_id, 'full');
    if (!$url) return null;

    $title = trim((string) get_the_title($attachment_id));
    $alt = trim((string) get_post_meta($attachment_id, '_wp_attachment_image_alt', true));
    $caption = trim((string) wp_get_attachment_caption($attachment_id));
    $description = trim((string) get_post_field('post_content', $attachment_id));
    $mime = (string) get_post_mime_type($attachment_id);
    $filename = (string) wp_basename((string) get_attached_file($attachment_id));
    $thumb = wp_get_attachment_image_url($attachment_id, 'medium');
    $metadata = wp_get_attachment_metadata($attachment_id);
    $width = is_array($metadata) && !empty($metadata['width']) ? (int) $metadata['width'] : 0;
    $height = is_array($metadata) && !empty($metadata['height']) ? (int) $metadata['height'] : 0;
    $orientation = 'square';
    if ($width > 0 && $height > 0) {
      if ($width > $height) $orientation = 'landscape';
      if ($height > $width) $orientation = 'portrait';
    }

    return [
      'attachment_id' => $attachment_id,
      'url' => (string) $url,
      'thumb_url' => $thumb ? (string) $thumb : (string) $url,
      'title' => $title,
      'alt' => $alt,
      'caption' => $caption,
      'description' => $description,
      'filename' => $filename,
      'mime' => $mime,
      'width' => $width,
      'height' => $height,
      'orientation' => $orientation,
    ];
  }
}

if (!function_exists('starscream_ai_page_builder_media_fix_standards')) {
  function starscream_ai_page_builder_media_fix_standards() {
    return [
      'landscape' => [
        'label' => 'Landscape',
        'max_width' => 2560,
        'max_height' => 2048,
      ],
      'portrait' => [
        'label' => 'Portrait',
        'max_width' => 2048,
        'max_height' => 2560,
      ],
      'square' => [
        'label' => 'Square',
        'max_width' => 2000,
        'max_height' => 2000,
      ],
      'bumblebee_mockup' => [
        'label' => 'Bumblebee mockup',
        'max_width' => 500,
        'max_height' => 500,
      ],
    ];
  }
}

if (!function_exists('starscream_ai_page_builder_orientation_for_dimensions')) {
  function starscream_ai_page_builder_orientation_for_dimensions($width, $height) {
    $width = (int) $width;
    $height = (int) $height;
    if ($width < 1 || $height < 1) return 'square';

    $ratio = $width / $height;
    if (abs($ratio - 1) <= 0.08) return 'square';

    return $ratio > 1 ? 'landscape' : 'portrait';
  }
}

if (!function_exists('starscream_ai_page_builder_bumblebee_original_art_ids')) {
  function starscream_ai_page_builder_bumblebee_original_art_ids() {
    static $ids = null;
    if (is_array($ids)) return $ids;

    global $wpdb;

    $ids = [];
    $meta_rows = $wpdb->get_results(
      "SELECT meta_key, meta_value FROM {$wpdb->postmeta} WHERE meta_key LIKE 'Original Art %'",
      ARRAY_A
    );

    foreach ((array) $meta_rows as $row) {
      $meta_key = isset($row['meta_key']) ? (string) $row['meta_key'] : '';
      $meta_value = isset($row['meta_value']) ? trim((string) $row['meta_value']) : '';
      if ($meta_value === '') continue;

      if (strpos($meta_key, 'Original Art ID ') === 0) {
        $attachment_id = absint($meta_value);
        if ($attachment_id > 0) $ids[$attachment_id] = true;
        continue;
      }

      $attachment_id = starscream_ai_page_builder_attachment_id_from_url($meta_value);
      if ($attachment_id > 0) $ids[$attachment_id] = true;
    }

    return $ids;
  }
}

if (!function_exists('starscream_ai_page_builder_bumblebee_mockup_attachment_ids')) {
  function starscream_ai_page_builder_bumblebee_mockup_attachment_ids() {
    static $ids = null;
    if (is_array($ids)) return $ids;

    global $wpdb;

    $ids = [];
    $product_ids = $wpdb->get_col(
      "SELECT DISTINCT post_id FROM {$wpdb->postmeta} WHERE meta_key IN ('Site Slug', 'Company Name', 'Production')"
    );

    $product_ids = array_values(array_filter(array_map('absint', (array) $product_ids)));
    if (empty($product_ids)) return $ids;

    $parent_ids_sql = implode(',', $product_ids);
    $variation_ids = $wpdb->get_col(
      "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'product_variation' AND post_parent IN ({$parent_ids_sql})"
    );

    $post_ids = array_values(array_unique(array_filter(array_merge($product_ids, array_map('absint', (array) $variation_ids)))));
    if (empty($post_ids)) return $ids;

    $post_ids_sql = implode(',', $post_ids);
    $thumb_rows = $wpdb->get_col(
      "SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_thumbnail_id' AND post_id IN ({$post_ids_sql})"
    );

    foreach ((array) $thumb_rows as $thumb_id) {
      $attachment_id = absint($thumb_id);
      if ($attachment_id > 0) $ids[$attachment_id] = true;
    }

    return $ids;
  }
}

if (!function_exists('starscream_ai_page_builder_media_fix_profile_for_attachment')) {
  function starscream_ai_page_builder_media_fix_profile_for_attachment($attachment_id, $width, $height) {
    $attachment_id = absint($attachment_id);
    $original_art_ids = starscream_ai_page_builder_bumblebee_original_art_ids();
    if ($attachment_id > 0 && isset($original_art_ids[$attachment_id])) {
      return [
        'skip' => true,
        'reason' => 'bumblebee_original_art',
      ];
    }

    $orientation = starscream_ai_page_builder_orientation_for_dimensions($width, $height);
    $profile_key = $orientation;
    $mockup_ids = starscream_ai_page_builder_bumblebee_mockup_attachment_ids();
    if ($attachment_id > 0 && isset($mockup_ids[$attachment_id])) {
      $profile_key = 'bumblebee_mockup';
    }

    $standards = starscream_ai_page_builder_media_fix_standards();
    if (empty($standards[$profile_key])) {
      return [
        'skip' => true,
        'reason' => 'unsupported_profile',
      ];
    }

    return [
      'skip' => false,
      'profile_key' => $profile_key,
      'profile' => $standards[$profile_key],
      'orientation' => $orientation,
    ];
  }
}

if (!function_exists('starscream_ai_page_builder_fix_attachment')) {
  function starscream_ai_page_builder_fix_attachment($attachment_id) {
    $attachment_id = absint($attachment_id);
    if ($attachment_id < 1) {
      return ['status' => 'error', 'reason' => 'invalid_attachment'];
    }

    $mime = strtolower((string) get_post_mime_type($attachment_id));
    if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
      return ['status' => 'skipped', 'reason' => 'unsupported_type'];
    }

    $file = (string) get_attached_file($attachment_id, true);
    if ($file === '' || !file_exists($file)) {
      return ['status' => 'error', 'reason' => 'missing_file'];
    }

    $size = @getimagesize($file);
    if (!is_array($size) || empty($size[0]) || empty($size[1])) {
      return ['status' => 'error', 'reason' => 'invalid_image'];
    }

    $width = (int) $size[0];
    $height = (int) $size[1];
    $profile = starscream_ai_page_builder_media_fix_profile_for_attachment($attachment_id, $width, $height);
    if (!empty($profile['skip'])) {
      return ['status' => 'skipped', 'reason' => (string) ($profile['reason'] ?? 'skipped')];
    }

    $profile_data = isset($profile['profile']) && is_array($profile['profile']) ? $profile['profile'] : [];
    $max_width = isset($profile_data['max_width']) ? (int) $profile_data['max_width'] : 0;
    $max_height = isset($profile_data['max_height']) ? (int) $profile_data['max_height'] : 0;
    if ($max_width < 1 || $max_height < 1) {
      return ['status' => 'error', 'reason' => 'invalid_profile'];
    }

    if ($width <= $max_width && $height <= $max_height) {
      return [
        'status' => 'unchanged',
        'profile_key' => (string) ($profile['profile_key'] ?? ''),
      ];
    }

    if (!function_exists('wp_get_image_editor')) {
      require_once ABSPATH . 'wp-admin/includes/image.php';
    }

    $editor = wp_get_image_editor($file);
    if (is_wp_error($editor)) {
      return ['status' => 'error', 'reason' => 'editor_unavailable'];
    }

    $resized = $editor->resize($max_width, $max_height, false);
    if (is_wp_error($resized)) {
      return ['status' => 'error', 'reason' => 'resize_failed'];
    }

    $saved = $editor->save($file);
    if (is_wp_error($saved)) {
      return ['status' => 'error', 'reason' => 'save_failed'];
    }

    require_once ABSPATH . 'wp-admin/includes/image.php';
    $metadata = wp_generate_attachment_metadata($attachment_id, $file);
    if (!is_wp_error($metadata) && is_array($metadata) && !empty($metadata)) {
      wp_update_attachment_metadata($attachment_id, $metadata);
    }

    clean_post_cache($attachment_id);

    return [
      'status' => 'updated',
      'profile_key' => (string) ($profile['profile_key'] ?? ''),
    ];
  }
}

if (!function_exists('starscream_ai_page_builder_fix_media_library_batch')) {
  function starscream_ai_page_builder_fix_media_library_batch($args) {
    global $wpdb;

    $cursor = isset($args['cursor']) ? absint($args['cursor']) : 0;
    $batch_size = 20;

    $attachment_ids = $wpdb->get_col($wpdb->prepare(
      "SELECT ID FROM {$wpdb->posts}
       WHERE post_type = 'attachment'
         AND post_status = 'inherit'
         AND post_mime_type LIKE 'image/%%'
         AND ID > %d
       ORDER BY ID ASC
       LIMIT %d",
      $cursor,
      $batch_size
    ));

    $counts = [
      'processed' => 0,
      'updated' => 0,
      'updated_mockups' => 0,
      'unchanged' => 0,
      'skipped_original_art' => 0,
      'skipped_unsupported' => 0,
      'errors' => 0,
    ];

    $next_cursor = $cursor;
    foreach ((array) $attachment_ids as $attachment_id_raw) {
      $attachment_id = absint($attachment_id_raw);
      if ($attachment_id < 1) continue;

      $counts['processed']++;
      $next_cursor = $attachment_id;
      $result = starscream_ai_page_builder_fix_attachment($attachment_id);
      $status = isset($result['status']) ? (string) $result['status'] : 'error';
      $reason = isset($result['reason']) ? (string) $result['reason'] : '';
      $profile_key = isset($result['profile_key']) ? (string) $result['profile_key'] : '';

      if ($status === 'updated') {
        $counts['updated']++;
        if ($profile_key === 'bumblebee_mockup') $counts['updated_mockups']++;
        continue;
      }

      if ($status === 'unchanged') {
        $counts['unchanged']++;
        continue;
      }

      if ($status === 'skipped') {
        if ($reason === 'bumblebee_original_art') {
          $counts['skipped_original_art']++;
        } else {
          $counts['skipped_unsupported']++;
        }
        continue;
      }

      $counts['errors']++;
    }

    return [
      'done' => empty($attachment_ids),
      'next_cursor' => $next_cursor,
      'counts' => $counts,
      'standards' => starscream_ai_page_builder_media_fix_standards(),
    ];
  }
}

if (!function_exists('starscream_ai_page_builder_attachment_analysis_path')) {
  function starscream_ai_page_builder_attachment_analysis_path($attachment_id) {
    $attachment_id = absint($attachment_id);
    if ($attachment_id < 1) return '';

    $original = (string) get_attached_file($attachment_id);
    if ($original === '' || !file_exists($original)) return '';

    $meta = wp_get_attachment_metadata($attachment_id);
    $dir = dirname($original);
    if (is_array($meta) && !empty($meta['sizes']) && is_array($meta['sizes'])) {
      foreach (['medium_large', 'large', 'medium', 'thumbnail'] as $size_name) {
        if (empty($meta['sizes'][$size_name]['file'])) continue;
        $candidate = $dir . '/' . ltrim((string) $meta['sizes'][$size_name]['file'], '/');
        if (file_exists($candidate)) return $candidate;
      }
    }

    return $original;
  }
}

if (!function_exists('starscream_ai_page_builder_attachment_data_url')) {
  function starscream_ai_page_builder_attachment_data_url($attachment_id) {
    $attachment_id = absint($attachment_id);
    if ($attachment_id < 1) return '';

    $mime = strtolower((string) get_post_mime_type($attachment_id));
    if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'image/gif'], true)) return '';

    $path = starscream_ai_page_builder_attachment_analysis_path($attachment_id);
    if ($path === '' || !is_readable($path)) return '';

    $bytes = @file_get_contents($path);
    if ($bytes === false || $bytes === '') return '';
    if (strlen($bytes) > 1500000) return '';

    return 'data:' . $mime . ';base64,' . base64_encode($bytes);
  }
}

if (!function_exists('starscream_ai_page_builder_attachment_score')) {
  function starscream_ai_page_builder_attachment_score($meta, $keywords) {
    if (!is_array($meta) || empty($keywords)) return 0;

    $haystack = strtolower(implode(' ', [
      isset($meta['title']) ? (string) $meta['title'] : '',
      isset($meta['alt']) ? (string) $meta['alt'] : '',
      isset($meta['caption']) ? (string) $meta['caption'] : '',
      isset($meta['description']) ? (string) $meta['description'] : '',
      isset($meta['filename']) ? (string) $meta['filename'] : '',
    ]));

    $score = 0;
    foreach ($keywords as $keyword) {
      $keyword = strtolower((string) $keyword);
      if ($keyword === '') continue;
      if (strpos($haystack, $keyword) !== false) $score += 3;
      if (preg_match('/\b' . preg_quote($keyword, '/') . '\b/', $haystack)) $score += 2;
    }

    return $score;
  }
}

if (!function_exists('starscream_ai_page_builder_attachment_quality_bonus')) {
  function starscream_ai_page_builder_attachment_quality_bonus($meta) {
    if (!is_array($meta)) return 0;

    $haystack = strtolower(implode(' ', [
      isset($meta['title']) ? (string) $meta['title'] : '',
      isset($meta['alt']) ? (string) $meta['alt'] : '',
      isset($meta['caption']) ? (string) $meta['caption'] : '',
      isset($meta['description']) ? (string) $meta['description'] : '',
      isset($meta['filename']) ? (string) $meta['filename'] : '',
    ]));

    $bonus = 0;
    if (preg_match('/\b(logo|placeholder|favicon|icon)\b/', $haystack)) $bonus -= 220;
    if (preg_match('/\bcropped\b/', $haystack)) $bonus -= 120;
    if (preg_match('/\b(slide|hero|banner)\b/', $haystack)) $bonus += 40;
    if (preg_match('/\bmobile\b/', $haystack)) $bonus -= 60;
    if (!empty($meta['alt']) || !empty($meta['caption']) || !empty($meta['description'])) $bonus += 8;

    $width = isset($meta['width']) ? (int) $meta['width'] : 0;
    $height = isset($meta['height']) ? (int) $meta['height'] : 0;
    if ($width >= 1400 || $height >= 1400) $bonus += 6;
    if ($width >= 1000 && $height >= 700) $bonus += 4;

    return $bonus;
  }
}

if (!function_exists('starscream_ai_page_builder_media_candidate_allowed')) {
  function starscream_ai_page_builder_media_candidate_allowed($meta) {
    if (!is_array($meta)) return false;
    return starscream_ai_page_builder_attachment_quality_bonus($meta) > -150;
  }
}

if (!function_exists('starscream_ai_page_builder_attachment_id_from_url')) {
  function starscream_ai_page_builder_attachment_id_from_url($url) {
    $url = trim((string) $url);
    if ($url === '') return 0;

    $clean = strtok($url, '?#');
    if (!is_string($clean) || $clean === '') return 0;

    $candidates = [$clean];
    $resized = preg_replace('/-\d+x\d+(?=\.[^.]+$)/', '', $clean);
    if (is_string($resized) && $resized !== '' && $resized !== $clean) $candidates[] = $resized;

    foreach ($candidates as $candidate) {
      $attachment_id = attachment_url_to_postid($candidate);
      if ($attachment_id > 0) return (int) $attachment_id;
    }

    return 0;
  }
}

if (!function_exists('starscream_ai_page_builder_used_theme_attachment_ids')) {
  function starscream_ai_page_builder_used_theme_attachment_ids() {
    $used = [];

    if (function_exists('starscream_header_slider_image_setting_ids')) {
      foreach ((array) starscream_header_slider_image_setting_ids() as $setting_id) {
        $attachment_id = absint(get_theme_mod((string) $setting_id, 0));
        if ($attachment_id > 0) $used[$attachment_id] = true;
      }
    }

    foreach ((array) get_theme_mods() as $key => $value) {
      if (!is_scalar($value)) continue;
      $key = (string) $key;
      if (!preg_match('/(?:^|_)(?:image|desktop_image|mobile_image|logo)_id$/', $key)) continue;
      $attachment_id = absint($value);
      if ($attachment_id > 0) $used[$attachment_id] = true;
    }

    return array_map('intval', array_keys($used));
  }
}

if (!function_exists('starscream_ai_page_builder_used_content_attachment_ids')) {
  function starscream_ai_page_builder_used_content_attachment_ids() {
    $used = [];
    $post_types = array_diff(
      array_values((array) get_post_types(['public' => true], 'names')),
      ['attachment']
    );

    if (empty($post_types)) return [];

    $posts = get_posts([
      'post_type' => $post_types,
      'post_status' => 'publish',
      'posts_per_page' => -1,
      'fields' => 'ids',
      'orderby' => 'ID',
      'order' => 'ASC',
      'no_found_rows' => true,
      'update_post_meta_cache' => false,
      'update_post_term_cache' => false,
    ]);

    foreach ((array) $posts as $post_id) {
      $post_id = absint($post_id);
      if ($post_id < 1) continue;

      $thumbnail_id = get_post_thumbnail_id($post_id);
      if ($thumbnail_id > 0) $used[(int) $thumbnail_id] = true;

      $content = (string) get_post_field('post_content', $post_id);
      if ($content === '') continue;

      if (preg_match_all('/wp-image-([0-9]+)/', $content, $class_matches)) {
        foreach ((array) ($class_matches[1] ?? []) as $image_id) {
          $image_id = absint($image_id);
          if ($image_id > 0) $used[$image_id] = true;
        }
      }

      if (preg_match_all('/<img[^>]+src=(["\'])(.*?)\1/i', $content, $img_matches)) {
        foreach ((array) ($img_matches[2] ?? []) as $url) {
          $attachment_id = starscream_ai_page_builder_attachment_id_from_url((string) $url);
          if ($attachment_id > 0) $used[$attachment_id] = true;
        }
      }

      if (preg_match_all('/url\((["\']?)(https?:\/\/[^)"\']+)\1\)/i', $content, $bg_matches)) {
        foreach ((array) ($bg_matches[2] ?? []) as $url) {
          $attachment_id = starscream_ai_page_builder_attachment_id_from_url((string) $url);
          if ($attachment_id > 0) $used[$attachment_id] = true;
        }
      }
    }

    return array_map('intval', array_keys($used));
  }
}

if (!function_exists('starscream_ai_page_builder_used_site_attachment_ids')) {
  function starscream_ai_page_builder_used_site_attachment_ids() {
    static $used = null;
    if (is_array($used)) return $used;

    $used = array_values(array_unique(array_merge(
      starscream_ai_page_builder_used_theme_attachment_ids(),
      starscream_ai_page_builder_used_content_attachment_ids()
    )));

    return $used;
  }
}

if (!function_exists('starscream_ai_page_builder_media_candidates')) {
  function starscream_ai_page_builder_media_candidates($selected_ids, $title, $brief, $options = []) {
    $selected_ids = array_values(array_unique(array_filter(array_map('absint', (array) $selected_ids))));
    $options = is_array($options) ? $options : [];
    $exclude_used_site_images = !empty($options['exclude_used_site_images']);
    $used_site_ids = $exclude_used_site_images ? array_flip(array_map('absint', starscream_ai_page_builder_used_site_attachment_ids())) : [];
    $keywords = starscream_ai_page_builder_keywords($title, $brief);
    $candidates = [];

    foreach ($selected_ids as $attachment_id) {
      $meta = starscream_ai_page_builder_attachment_meta($attachment_id);
      if (!$meta) continue;
      $meta['source'] = 'user_selected';
      $meta['selected_by_user'] = true;
      $meta['score'] = 1000 + starscream_ai_page_builder_attachment_score($meta, $keywords) + starscream_ai_page_builder_attachment_quality_bonus($meta);
      $candidates[$attachment_id] = $meta;
    }

    $attachments = get_posts([
      'post_type' => 'attachment',
      'post_status' => 'inherit',
      'post_mime_type' => 'image',
      'posts_per_page' => 220,
      'orderby' => 'date',
      'order' => 'DESC',
      'post__not_in' => $selected_ids,
      'fields' => 'ids',
    ]);

    foreach ((array) $attachments as $attachment_id) {
      if ($exclude_used_site_images && isset($used_site_ids[(int) $attachment_id])) continue;
      $meta = starscream_ai_page_builder_attachment_meta((int) $attachment_id);
      if (!$meta) continue;
      if (!starscream_ai_page_builder_media_candidate_allowed($meta)) continue;
      $meta['source'] = 'media_library';
      $meta['selected_by_user'] = false;
      $meta['score'] = starscream_ai_page_builder_attachment_score($meta, $keywords) + starscream_ai_page_builder_attachment_quality_bonus($meta);
      $candidates[(int) $attachment_id] = $meta;
    }

    uasort($candidates, function ($a, $b) {
      $score_a = isset($a['score']) ? (int) $a['score'] : 0;
      $score_b = isset($b['score']) ? (int) $b['score'] : 0;
      if ($score_a === $score_b) {
        $id_a = isset($a['attachment_id']) ? (int) $a['attachment_id'] : 0;
        $id_b = isset($b['attachment_id']) ? (int) $b['attachment_id'] : 0;
        return $id_b <=> $id_a;
      }
      return $score_b <=> $score_a;
    });

    $filtered = [];
    foreach ($candidates as $candidate) {
      if (!empty($candidate['selected_by_user']) || (int) $candidate['score'] > 0 || count($filtered) < 6) {
        $filtered[] = $candidate;
      }
      if (count($filtered) >= 14) break;
    }

    return $filtered;
  }
}

if (!function_exists('starscream_ai_page_builder_visual_candidates')) {
  function starscream_ai_page_builder_visual_candidates($candidates) {
    $visuals = [];

    foreach ((array) $candidates as $candidate) {
      if (!empty($candidate['selected_by_user'])) $visuals[] = $candidate;
      if (count($visuals) >= 6) return array_slice($visuals, 0, 6);
    }

    foreach ((array) $candidates as $candidate) {
      if (count($visuals) >= 6) break;
      if (!empty($candidate['selected_by_user'])) continue;
      $visuals[] = $candidate;
    }

    return array_slice($visuals, 0, 6);
  }
}

if (!function_exists('starscream_ai_page_builder_slug_prefix')) {
  function starscream_ai_page_builder_slug_prefix($slug) {
    $slug = sanitize_title((string) $slug);
    if ($slug === '') $slug = 'ai-page';
    if (preg_match('/^[0-9]/', $slug)) $slug = 'page-' . $slug;
    return substr($slug, 0, 36);
  }
}

if (!function_exists('starscream_ai_page_builder_page_archetype')) {
  function starscream_ai_page_builder_page_archetype($title) {
    $title = strtolower((string) $title);
    if (strpos($title, 'about') !== false || strpos($title, 'our story') !== false || strpos($title, 'company') !== false) {
      return 'about-us page';
    }
    if (strpos($title, 'service') !== false || strpos($title, 'program') !== false || strpos($title, 'solution') !== false) {
      return 'service or program page';
    }
    if (strpos($title, 'contact') !== false || strpos($title, 'location') !== false) {
      return 'contact or location page';
    }
    if (strpos($title, 'team') !== false || strpos($title, 'staff') !== false) {
      return 'team page';
    }
    return 'marketing landing page';
  }
}

if (!function_exists('starscream_ai_page_builder_existing_page_context')) {
  function starscream_ai_page_builder_existing_page_context($page_id) {
    $page_id = absint($page_id);
    if ($page_id < 1) return '';

    $post = get_post($page_id);
    if (!($post instanceof WP_Post) || $post->post_type !== 'page') return '';

    $content = trim((string) wp_strip_all_tags((string) $post->post_content));
    if ($content === '') return '';
    return substr($content, 0, 5000);
  }
}

if (!function_exists('starscream_ai_page_builder_trimmed_sources')) {
  function starscream_ai_page_builder_trimmed_sources($sources) {
    $urls = [];
    foreach ((array) $sources as $source) {
      $url = '';
      if (is_string($source)) $url = trim($source);
      if (is_array($source) && isset($source['url'])) $url = trim((string) $source['url']);
      if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) continue;
      $urls[$url] = $url;
    }
    return array_values($urls);
  }
}

if (!function_exists('starscream_ai_page_builder_google_location_context')) {
  function starscream_ai_page_builder_google_location_context() {
    return [
      'business_location' => trim((string) get_theme_mod('tbt_google_reviews_business_location', '')),
      'place_id' => trim((string) get_theme_mod('tbt_google_reviews_place_id', '')),
    ];
  }
}

if (!function_exists('starscream_ai_page_builder_extract_output_text')) {
  function starscream_ai_page_builder_extract_output_text($response_body) {
    if (!is_array($response_body)) return '';

    if (!empty($response_body['output_text']) && is_string($response_body['output_text'])) {
      return trim($response_body['output_text']);
    }

    $chunks = [];
    foreach ((array) ($response_body['output'] ?? []) as $item) {
      if (($item['type'] ?? '') !== 'message') continue;
      foreach ((array) ($item['content'] ?? []) as $content) {
        if (($content['type'] ?? '') === 'output_text' && isset($content['text']) && is_string($content['text'])) {
          $chunks[] = $content['text'];
        }
      }
    }

    return trim(implode("\n\n", $chunks));
  }
}

if (!function_exists('starscream_ai_page_builder_extract_search_sources')) {
  function starscream_ai_page_builder_extract_search_sources($response_body) {
    if (!is_array($response_body)) return [];

    $sources = [];
    foreach ((array) ($response_body['output'] ?? []) as $item) {
      if (($item['type'] ?? '') !== 'web_search_call') continue;
      $sources = array_merge($sources, (array) (($item['action']['sources'] ?? [])));
    }

    return starscream_ai_page_builder_trimmed_sources($sources);
  }
}

if (!function_exists('starscream_ai_page_builder_sanitize_generated_html')) {
  function starscream_ai_page_builder_sanitize_generated_html($html) {
    $html = trim((string) $html);
    if ($html === '') return '';

    $html = preg_replace('#<script\b[^>]*>.*?</script>#is', '', $html);
    $html = preg_replace('/\son[a-z]+\s*=\s*(".*?"|\'.*?\'|[^\s>]+)/i', '', (string) $html);
    $html = preg_replace('/(href|src)\s*=\s*([\"\'])\s*javascript:[^\"\']*\2/i', '$1="#"', (string) $html);

    return trim((string) $html);
  }
}

if (!function_exists('starscream_ai_page_builder_normalize_text_snippet')) {
  function starscream_ai_page_builder_normalize_text_snippet($text, $limit = 320) {
    $text = trim(preg_replace('/\s+/', ' ', (string) $text));
    if ($text === '') return '';

    $limit = max(80, (int) $limit);
    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
      if (mb_strlen($text) <= $limit) return $text;
      return rtrim((string) mb_substr($text, 0, $limit - 1)) . '...';
    }

    if (strlen($text) <= $limit) return $text;
    return rtrim(substr($text, 0, $limit - 1)) . '...';
  }
}

if (!function_exists('starscream_ai_page_builder_dom_fragment')) {
  function starscream_ai_page_builder_dom_fragment($html) {
    $html = trim((string) $html);
    if ($html === '') return null;

    $dom = new DOMDocument('1.0', 'UTF-8');
    $previous = libxml_use_internal_errors(true);
    $wrapped = '<!DOCTYPE html><html><body><div id="starscream-ai-page-root">' . $html . '</div></body></html>';
    $loaded = $dom->loadHTML('<?xml encoding="utf-8" ?>' . $wrapped);
    libxml_clear_errors();
    libxml_use_internal_errors($previous);

    if (!$loaded) return null;

    $xpath = new DOMXPath($dom);
    $root = $xpath->query('//*[@id="starscream-ai-page-root"]')->item(0);
    if (!($root instanceof DOMNode)) return null;

    return [
      'dom' => $dom,
      'xpath' => $xpath,
      'root' => $root,
    ];
  }
}

if (!function_exists('starscream_ai_page_builder_dom_inner_html')) {
  function starscream_ai_page_builder_dom_inner_html($node) {
    if (!($node instanceof DOMNode) || !($node->ownerDocument instanceof DOMDocument)) return '';

    $html = '';
    foreach ($node->childNodes as $child) {
      $html .= $node->ownerDocument->saveHTML($child);
    }

    return $html;
  }
}

if (!function_exists('starscream_ai_page_builder_image_context')) {
  function starscream_ai_page_builder_image_context($img, $xpath) {
    if (!($img instanceof DOMNode) || !($xpath instanceof DOMXPath)) {
      return [
        'heading' => '',
        'text' => '',
        'container_class' => '',
      ];
    }

    $container = $xpath->query('ancestor::*[self::section or self::article or self::figure or contains(concat(" ", normalize-space(@class), " "), " site-section ")][1]', $img)->item(0);
    if (!($container instanceof DOMNode)) $container = $img->parentNode;

    $heading = '';
    if ($container instanceof DOMNode) {
      $heading_node = $xpath->query('.//h1|.//h2|.//h3', $container)->item(0);
      if ($heading_node instanceof DOMNode) {
        $heading = starscream_ai_page_builder_normalize_text_snippet($heading_node->textContent, 120);
      }
    }

    $text = $container instanceof DOMNode ? starscream_ai_page_builder_normalize_text_snippet($container->textContent, 320) : '';
    $container_class = '';
    if ($container instanceof DOMElement && $container->hasAttribute('class')) {
      $container_class = trim((string) $container->getAttribute('class'));
    }

    return [
      'heading' => $heading,
      'text' => $text,
      'container_class' => $container_class,
    ];
  }
}

if (!function_exists('starscream_ai_page_builder_page_images')) {
  function starscream_ai_page_builder_page_images($page_id) {
    $page_id = absint($page_id);
    if ($page_id < 1) return [];

    $post = get_post($page_id);
    if (!($post instanceof WP_Post) || $post->post_type !== 'page') return [];

    $fragment = starscream_ai_page_builder_dom_fragment((string) $post->post_content);
    if (!is_array($fragment)) return [];

    $dom = $fragment['dom'];
    $xpath = $fragment['xpath'];
    $root = $fragment['root'];
    if (!($dom instanceof DOMDocument) || !($xpath instanceof DOMXPath) || !($root instanceof DOMNode)) return [];

    $site_host = strtolower((string) wp_parse_url(home_url('/'), PHP_URL_HOST));
    $images = [];
    $nodes = $xpath->query('.//img', $root);

    foreach ($nodes as $index => $node) {
      if (!($node instanceof DOMElement)) continue;

      $src = trim((string) $node->getAttribute('src'));
      if ($src === '') continue;

      $alt = trim((string) $node->getAttribute('alt'));
      $context = starscream_ai_page_builder_image_context($node, $xpath);
      $current_host = strtolower((string) wp_parse_url($src, PHP_URL_HOST));
      $is_external = $current_host !== '' && $site_host !== '' && $current_host !== $site_host;
      $label = $context['heading'];
      if ($label === '') $label = $alt;
      if ($label === '') $label = 'Image slot ' . ((int) $index + 1);

      $images[] = [
        'slot_key' => 'slot-' . ((int) $index + 1) . '-' . substr(sha1($src . '|' . $index), 0, 12),
        'index' => (int) $index,
        'label' => $label,
        'url' => $src,
        'alt' => $alt,
        'context_heading' => isset($context['heading']) ? (string) $context['heading'] : '',
        'context_text' => isset($context['text']) ? (string) $context['text'] : '',
        'container_class' => isset($context['container_class']) ? (string) $context['container_class'] : '',
        'is_external' => $is_external,
      ];
    }

    return $images;
  }
}

if (!function_exists('starscream_ai_page_builder_slot_candidate_score')) {
  function starscream_ai_page_builder_slot_candidate_score($slot, $candidate, $page_title = '') {
    if (!is_array($slot) || !is_array($candidate)) return -999999;

    $score = isset($candidate['score']) ? (int) $candidate['score'] : 0;
    $orientation = isset($candidate['orientation']) ? (string) $candidate['orientation'] : 'square';
    $title_text = strtolower((string) $page_title . ' ' . (string) ($slot['label'] ?? '') . ' ' . (string) ($slot['context_heading'] ?? '') . ' ' . (string) ($slot['context_text'] ?? ''));

    if ((int) ($slot['index'] ?? 0) === 0) {
      if ($orientation === 'landscape') $score += 24;
      if ($orientation === 'portrait') $score -= 8;
    } else {
      if ($orientation === 'portrait') $score += 6;
      if ($orientation === 'landscape') $score += 4;
    }

    if (strpos($title_text, 'about') !== false || strpos($title_text, 'team') !== false || strpos($title_text, 'story') !== false || strpos($title_text, 'mission') !== false) {
      $filename = strtolower((string) ($candidate['filename'] ?? '') . ' ' . (string) ($candidate['title'] ?? ''));
      if (strpos($filename, 'slide') !== false) $score += 14;
    }

    return $score;
  }
}

if (!function_exists('starscream_ai_page_builder_fallback_replacements')) {
  function starscream_ai_page_builder_fallback_replacements($slots, $media_candidates, $page_title, $existing_suggestions = []) {
    $slots = is_array($slots) ? $slots : [];
    $media_candidates = is_array($media_candidates) ? $media_candidates : [];
    if (empty($slots) || empty($media_candidates)) return [];

    $used_slot_keys = [];
    $used_attachment_ids = [];
    foreach ((array) $existing_suggestions as $suggestion) {
      $slot_key = isset($suggestion['slot_key']) ? (string) $suggestion['slot_key'] : '';
      $attachment_id = isset($suggestion['replacement']['attachment_id']) ? (int) $suggestion['replacement']['attachment_id'] : 0;
      if ($slot_key !== '') $used_slot_keys[$slot_key] = true;
      if ($attachment_id > 0) $used_attachment_ids[$attachment_id] = true;
    }

    $fallback = [];
    foreach ($slots as $slot) {
      $slot_key = isset($slot['slot_key']) ? (string) $slot['slot_key'] : '';
      if ($slot_key === '' || isset($used_slot_keys[$slot_key])) continue;

      $best = null;
      $best_score = -999999;
      foreach ($media_candidates as $candidate) {
        $attachment_id = isset($candidate['attachment_id']) ? (int) $candidate['attachment_id'] : 0;
        if ($attachment_id < 1 || isset($used_attachment_ids[$attachment_id])) continue;
        if (!starscream_ai_page_builder_media_candidate_allowed($candidate)) continue;

        $candidate_score = starscream_ai_page_builder_slot_candidate_score($slot, $candidate, $page_title);
        if ($candidate_score <= $best_score) continue;
        $best = $candidate;
        $best_score = $candidate_score;
      }

      if (!is_array($best)) continue;

      $attachment_id = isset($best['attachment_id']) ? (int) $best['attachment_id'] : 0;
      if ($attachment_id < 1) continue;

      $used_attachment_ids[$attachment_id] = true;
      $used_slot_keys[$slot_key] = true;

      $replacement_alt = trim((string) ($best['alt'] ?? ''));
      if ($replacement_alt === '') $replacement_alt = trim((string) ($best['title'] ?? ''));

      $fallback[] = [
        'slot_key' => $slot_key,
        'current' => [
          'label' => isset($slot['label']) ? (string) $slot['label'] : '',
          'url' => isset($slot['url']) ? (string) $slot['url'] : '',
          'alt' => isset($slot['alt']) ? (string) $slot['alt'] : '',
          'context_heading' => isset($slot['context_heading']) ? (string) $slot['context_heading'] : '',
          'context_text' => isset($slot['context_text']) ? (string) $slot['context_text'] : '',
          'is_external' => !empty($slot['is_external']),
        ],
        'replacement' => [
          'attachment_id' => $attachment_id,
          'url' => isset($best['url']) ? (string) $best['url'] : '',
          'thumb_url' => isset($best['thumb_url']) ? (string) $best['thumb_url'] : '',
          'title' => isset($best['title']) ? (string) $best['title'] : '',
          'alt' => $replacement_alt,
          'source' => isset($best['source']) ? (string) $best['source'] : 'media_library',
        ],
        'reason' => 'Fallback match based on page context, image position, and the best available media-library candidates.',
      ];
    }

    return $fallback;
  }
}

if (!function_exists('starscream_ai_page_builder_image_replacement_payload')) {
  function starscream_ai_page_builder_image_replacement_payload($args) {
    $page_title = isset($args['page_title']) ? trim((string) $args['page_title']) : 'Untitled Page';
    $page_url = isset($args['page_url']) ? trim((string) $args['page_url']) : '';
    $page_context = isset($args['page_context']) ? trim((string) $args['page_context']) : '';
    $slots = isset($args['slots']) && is_array($args['slots']) ? $args['slots'] : [];
    $media_candidates = isset($args['media_candidates']) && is_array($args['media_candidates']) ? $args['media_candidates'] : [];

    $slot_summary = [];
    foreach ($slots as $slot) {
      $slot_summary[] = [
        'slot_key' => isset($slot['slot_key']) ? (string) $slot['slot_key'] : '',
        'label' => isset($slot['label']) ? (string) $slot['label'] : '',
        'current_url' => isset($slot['url']) ? (string) $slot['url'] : '',
        'current_alt' => isset($slot['alt']) ? (string) $slot['alt'] : '',
        'context_heading' => isset($slot['context_heading']) ? (string) $slot['context_heading'] : '',
        'context_text' => isset($slot['context_text']) ? (string) $slot['context_text'] : '',
        'container_class' => isset($slot['container_class']) ? (string) $slot['container_class'] : '',
      ];
    }

    $candidate_summary = [];
    foreach ($media_candidates as $candidate) {
      $candidate_summary[] = [
        'attachment_id' => isset($candidate['attachment_id']) ? (int) $candidate['attachment_id'] : 0,
        'url' => isset($candidate['url']) ? (string) $candidate['url'] : '',
        'title' => isset($candidate['title']) ? (string) $candidate['title'] : '',
        'alt' => isset($candidate['alt']) ? (string) $candidate['alt'] : '',
        'caption' => isset($candidate['caption']) ? (string) $candidate['caption'] : '',
        'description' => isset($candidate['description']) ? (string) $candidate['description'] : '',
        'filename' => isset($candidate['filename']) ? (string) $candidate['filename'] : '',
      ];
    }

    $instructions = implode("\n", [
      'You are choosing replacement media library images for an existing WordPress page in the Starscream theme.',
      'Return only JSON matching the provided schema.',
      'Choose the best-fitting attachment_id for each page image slot from the provided media candidates.',
      'Consider the section heading, surrounding copy, and the kind of image already occupying that slot.',
      'Prefer lifestyle, team, and environment photography over isolated product shots for about-us, team, and story content.',
      'Avoid logos, graphics, or irrelevant products unless the slot context clearly calls for them.',
      'Use each candidate attachment at most once unless there is no other reasonable option.',
      'If no candidate fits a slot, return attachment_id 0 for that slot and explain why.',
      'Write proposed alt text that accurately describes the chosen image in the context of the slot.',
      'Do not invent URLs or attachment IDs. Only use the provided media candidates.',
    ]);

    $content = [
      [
        'type' => 'input_text',
        'text' => implode("\n\n", [
          'Page title: ' . $page_title,
          'Page URL: ' . ($page_url !== '' ? $page_url : 'n/a'),
          'Page text context:' . "\n" . ($page_context !== '' ? $page_context : 'No additional text context available.'),
          'Page image slots JSON:' . "\n" . wp_json_encode($slot_summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
          'Media candidates JSON:' . "\n" . wp_json_encode($candidate_summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
        ]),
      ],
    ];

    foreach (array_slice($media_candidates, 0, 10) as $candidate) {
      $content[] = [
        'type' => 'input_text',
        'text' => 'Candidate image: ' . wp_json_encode([
          'attachment_id' => isset($candidate['attachment_id']) ? (int) $candidate['attachment_id'] : 0,
          'url' => isset($candidate['url']) ? (string) $candidate['url'] : '',
          'title' => isset($candidate['title']) ? (string) $candidate['title'] : '',
          'alt' => isset($candidate['alt']) ? (string) $candidate['alt'] : '',
          'caption' => isset($candidate['caption']) ? (string) $candidate['caption'] : '',
        ], JSON_UNESCAPED_SLASHES),
      ];

      $image_input = starscream_ai_page_builder_attachment_data_url(isset($candidate['attachment_id']) ? (int) $candidate['attachment_id'] : 0);
      if ($image_input === '') continue;

      $content[] = [
        'type' => 'input_image',
        'image_url' => $image_input,
        'detail' => 'high',
      ];
    }

    return [
      'instructions' => $instructions,
      'input' => [
        [
          'role' => 'user',
          'content' => $content,
        ],
      ],
      'temperature' => 0.2,
      'max_output_tokens' => 4500,
      'text' => [
        'format' => [
          'type' => 'json_schema',
          'name' => 'starscream_ai_image_replacement_output',
          'strict' => true,
          'schema' => [
            'type' => 'object',
            'additionalProperties' => false,
            'properties' => [
              'summary' => [
                'type' => 'string',
              ],
              'replacements' => [
                'type' => 'array',
                'items' => [
                  'type' => 'object',
                  'additionalProperties' => false,
                  'properties' => [
                    'slot_key' => [
                      'type' => 'string',
                    ],
                    'attachment_id' => [
                      'type' => 'integer',
                    ],
                    'alt' => [
                      'type' => 'string',
                    ],
                    'reason' => [
                      'type' => 'string',
                    ],
                  ],
                  'required' => ['slot_key', 'attachment_id', 'alt', 'reason'],
                ],
              ],
            ],
            'required' => ['summary', 'replacements'],
          ],
        ],
      ],
    ];
  }
}

if (!function_exists('starscream_ai_page_builder_suggest_page_replacements')) {
  function starscream_ai_page_builder_suggest_page_replacements($request) {
    $page_id = isset($request['page_id']) ? absint($request['page_id']) : 0;
    if ($page_id < 1) return new WP_Error('missing_page', 'Choose a page to scan for image replacements.');
    if (!current_user_can('edit_post', $page_id)) return new WP_Error('forbidden', 'You do not have permission to edit that page.');

    $post = get_post($page_id);
    if (!($post instanceof WP_Post) || $post->post_type !== 'page') {
      return new WP_Error('invalid_page', 'The selected page could not be found.');
    }

    $slots = starscream_ai_page_builder_page_images($page_id);
    if (empty($slots)) {
      return new WP_Error('no_images', 'That page does not contain any replaceable <img> tags in its page content.');
    }

    $page_title = trim((string) $post->post_title);
    if ($page_title === '') $page_title = 'Untitled Page';
    $page_context = starscream_ai_page_builder_existing_page_context($page_id);
    $context_brief = implode("\n\n", array_map(function ($slot) {
      return implode("\n", array_filter([
        isset($slot['label']) ? (string) $slot['label'] : '',
        isset($slot['context_heading']) ? (string) $slot['context_heading'] : '',
        isset($slot['context_text']) ? (string) $slot['context_text'] : '',
      ]));
    }, $slots));

    $media_candidates = starscream_ai_page_builder_media_candidates([], $page_title, $page_context . "\n\n" . $context_brief, [
      'exclude_used_site_images' => true,
    ]);
    if (empty($media_candidates)) {
      return new WP_Error('no_media_candidates', 'No unused media library candidates were found.');
    }

    $fallback_suggestions = starscream_ai_page_builder_fallback_replacements($slots, $media_candidates, $page_title);
    $api_key = starscream_ai_page_builder_api_key(isset($request['api_key_override']) ? $request['api_key_override'] : '');
    $model = starscream_ai_page_builder_model(isset($request['model_override']) ? $request['model_override'] : '');

    if ($api_key === '') {
      if (!empty($fallback_suggestions)) {
        return [
          'page_id' => $page_id,
          'page_title' => $page_title,
          'edit_url' => (string) get_edit_post_link($page_id, 'raw'),
          'view_url' => (string) get_permalink($page_id),
          'summary' => 'Prepared fallback media library replacements without AI analysis.',
          'suggestions' => $fallback_suggestions,
        ];
      }
      return new WP_Error('missing_api_key', 'Add an OpenAI API key before asking AI to replace page images.');
    }

    $payload = starscream_ai_page_builder_image_replacement_payload([
      'page_title' => $page_title,
      'page_url' => (string) get_permalink($post),
      'page_context' => $page_context,
      'slots' => $slots,
      'media_candidates' => $media_candidates,
    ]);
    $payload['model'] = $model;

    @set_time_limit(180);

    $response = wp_remote_post('https://api.openai.com/v1/responses', [
      'timeout' => 180,
      'headers' => [
        'Authorization' => 'Bearer ' . $api_key,
        'Content-Type' => 'application/json',
      ],
      'body' => wp_json_encode($payload),
    ]);

    if (is_wp_error($response)) {
      if (!empty($fallback_suggestions)) {
        return [
          'page_id' => $page_id,
          'page_title' => $page_title,
          'edit_url' => (string) get_edit_post_link($page_id, 'raw'),
          'view_url' => (string) get_permalink($page_id),
          'summary' => 'Prepared fallback media library replacements because AI analysis was unavailable.',
          'suggestions' => $fallback_suggestions,
        ];
      }
      return new WP_Error('api_request_failed', $response->get_error_message());
    }

    $status_code = (int) wp_remote_retrieve_response_code($response);
    $body_raw = (string) wp_remote_retrieve_body($response);
    $body = json_decode($body_raw, true);

    if ($status_code < 200 || $status_code >= 300) {
      $message = 'OpenAI request failed.';
      if (is_array($body) && !empty($body['error']['message'])) $message = (string) $body['error']['message'];
      if (!empty($fallback_suggestions)) {
        return [
          'page_id' => $page_id,
          'page_title' => $page_title,
          'edit_url' => (string) get_edit_post_link($page_id, 'raw'),
          'view_url' => (string) get_permalink($page_id),
          'summary' => 'Prepared fallback media library replacements because AI analysis was unavailable.',
          'suggestions' => $fallback_suggestions,
        ];
      }
      return new WP_Error('api_http_error', $message);
    }

    $output_text = starscream_ai_page_builder_extract_output_text(is_array($body) ? $body : []);
    if ($output_text === '') {
      if (!empty($fallback_suggestions)) {
        return [
          'page_id' => $page_id,
          'page_title' => $page_title,
          'edit_url' => (string) get_edit_post_link($page_id, 'raw'),
          'view_url' => (string) get_permalink($page_id),
          'summary' => 'Prepared fallback media library replacements because AI analysis returned no usable output.',
          'suggestions' => $fallback_suggestions,
        ];
      }
      return new WP_Error('empty_ai_output', 'The AI image replacer returned an empty response.');
    }

    $parsed = json_decode($output_text, true);
    if (!is_array($parsed)) {
      if (!empty($fallback_suggestions)) {
        return [
          'page_id' => $page_id,
          'page_title' => $page_title,
          'edit_url' => (string) get_edit_post_link($page_id, 'raw'),
          'view_url' => (string) get_permalink($page_id),
          'summary' => 'Prepared fallback media library replacements because AI analysis returned invalid output.',
          'suggestions' => $fallback_suggestions,
        ];
      }
      return new WP_Error('invalid_ai_output', 'The AI image replacer returned invalid JSON.');
    }

    $slot_lookup = [];
    foreach ($slots as $slot) {
      $slot_key = isset($slot['slot_key']) ? (string) $slot['slot_key'] : '';
      if ($slot_key === '') continue;
      $slot_lookup[$slot_key] = $slot;
    }

    $candidate_lookup = [];
    foreach ($media_candidates as $candidate) {
      $attachment_id = isset($candidate['attachment_id']) ? (int) $candidate['attachment_id'] : 0;
      if ($attachment_id < 1) continue;
      $candidate_lookup[$attachment_id] = $candidate;
    }

    $used_attachment_ids = [];
    $suggestions = [];
    foreach ((array) ($parsed['replacements'] ?? []) as $item) {
      $slot_key = isset($item['slot_key']) ? trim((string) $item['slot_key']) : '';
      $attachment_id = isset($item['attachment_id']) ? absint($item['attachment_id']) : 0;
      if ($slot_key === '' || !isset($slot_lookup[$slot_key])) continue;
      if ($attachment_id < 1 || !isset($candidate_lookup[$attachment_id])) continue;
      if (isset($used_attachment_ids[$attachment_id])) continue;

      $candidate = $candidate_lookup[$attachment_id];
      $slot = $slot_lookup[$slot_key];
      $used_attachment_ids[$attachment_id] = true;

      $proposed_alt = trim((string) ($item['alt'] ?? ''));
      if ($proposed_alt === '') {
        $proposed_alt = trim((string) ($candidate['alt'] ?? ''));
      }
      if ($proposed_alt === '') {
        $proposed_alt = trim((string) ($candidate['title'] ?? ''));
      }

      $suggestions[] = [
        'slot_key' => $slot_key,
        'current' => [
          'label' => isset($slot['label']) ? (string) $slot['label'] : '',
          'url' => isset($slot['url']) ? (string) $slot['url'] : '',
          'alt' => isset($slot['alt']) ? (string) $slot['alt'] : '',
          'context_heading' => isset($slot['context_heading']) ? (string) $slot['context_heading'] : '',
          'context_text' => isset($slot['context_text']) ? (string) $slot['context_text'] : '',
          'is_external' => !empty($slot['is_external']),
        ],
        'replacement' => [
          'attachment_id' => $attachment_id,
          'url' => isset($candidate['url']) ? (string) $candidate['url'] : '',
          'thumb_url' => isset($candidate['thumb_url']) ? (string) $candidate['thumb_url'] : '',
          'title' => isset($candidate['title']) ? (string) $candidate['title'] : '',
          'alt' => $proposed_alt,
          'source' => isset($candidate['source']) ? (string) $candidate['source'] : 'media_library',
        ],
        'reason' => trim((string) ($item['reason'] ?? '')),
      ];
    }

    if (count($suggestions) < count($slots)) {
      $suggestions = array_merge(
        $suggestions,
        starscream_ai_page_builder_fallback_replacements($slots, $media_candidates, $page_title, $suggestions)
      );
    }

    if (empty($suggestions)) {
      return new WP_Error('no_replacements', 'No usable media library replacements were found for that page.');
    }

    $summary = trim((string) ($parsed['summary'] ?? ''));
    if ($summary === '') {
      $summary = 'Prepared media library replacements for the page images.';
    }
    if (count($suggestions) < count($slots)) {
      $summary .= ' Some slots used fallback matching because the AI did not return a strong pick for every image.';
    }

    return [
      'page_id' => $page_id,
      'page_title' => $page_title,
      'edit_url' => (string) get_edit_post_link($page_id, 'raw'),
      'view_url' => (string) get_permalink($page_id),
      'summary' => $summary,
      'suggestions' => $suggestions,
    ];
  }
}

if (!function_exists('starscream_ai_page_builder_apply_page_replacements')) {
  function starscream_ai_page_builder_apply_page_replacements($request) {
    $page_id = isset($request['page_id']) ? absint($request['page_id']) : 0;
    if ($page_id < 1) return new WP_Error('missing_page', 'Choose a page before applying image replacements.');
    if (!current_user_can('edit_post', $page_id)) return new WP_Error('forbidden', 'You do not have permission to edit that page.');

    $post = get_post($page_id);
    if (!($post instanceof WP_Post) || $post->post_type !== 'page') {
      return new WP_Error('invalid_page', 'The selected page could not be found.');
    }

    $replacements = json_decode((string) wp_unslash($request['replacements_json'] ?? ''), true);
    if (!is_array($replacements) || empty($replacements)) {
      return new WP_Error('missing_replacements', 'No image replacements were supplied.');
    }

    $fragment = starscream_ai_page_builder_dom_fragment((string) $post->post_content);
    if (!is_array($fragment)) {
      return new WP_Error('invalid_content', 'The page content could not be parsed for image replacement.');
    }

    $dom = $fragment['dom'];
    $xpath = $fragment['xpath'];
    $root = $fragment['root'];
    if (!($dom instanceof DOMDocument) || !($xpath instanceof DOMXPath) || !($root instanceof DOMNode)) {
      return new WP_Error('invalid_content', 'The page content could not be parsed for image replacement.');
    }

    $replacement_lookup = [];
    foreach ($replacements as $replacement) {
      $slot_key = isset($replacement['slot_key']) ? trim((string) $replacement['slot_key']) : '';
      $attachment_id = isset($replacement['replacement']['attachment_id']) ? absint($replacement['replacement']['attachment_id']) : 0;
      if ($slot_key === '' || $attachment_id < 1) continue;

      $url = wp_get_attachment_image_url($attachment_id, 'full');
      if (!$url) continue;

      $alt = trim((string) ($replacement['replacement']['alt'] ?? ''));
      if ($alt === '') $alt = trim((string) get_post_meta($attachment_id, '_wp_attachment_image_alt', true));
      if ($alt === '') $alt = trim((string) get_the_title($attachment_id));

      $replacement_lookup[$slot_key] = [
        'attachment_id' => $attachment_id,
        'url' => (string) $url,
        'alt' => $alt,
      ];
    }

    if (empty($replacement_lookup)) {
      return new WP_Error('missing_replacements', 'No valid replacement images were available to apply.');
    }

    $updated_count = 0;
    $nodes = $xpath->query('.//img', $root);
    foreach ($nodes as $index => $node) {
      if (!($node instanceof DOMElement)) continue;

      $src = trim((string) $node->getAttribute('src'));
      if ($src === '') continue;

      $slot_key = 'slot-' . ((int) $index + 1) . '-' . substr(sha1($src . '|' . $index), 0, 12);
      if (!isset($replacement_lookup[$slot_key])) continue;

      $replacement = $replacement_lookup[$slot_key];
      $node->setAttribute('src', $replacement['url']);
      $node->setAttribute('alt', $replacement['alt']);
      if ($node->hasAttribute('srcset')) $node->removeAttribute('srcset');
      if ($node->hasAttribute('sizes')) $node->removeAttribute('sizes');
      $updated_count++;
    }

    if ($updated_count < 1) {
      return new WP_Error('no_changes', 'The requested replacements did not match the current page images.');
    }

    $updated_html = starscream_ai_page_builder_dom_inner_html($root);
    if (trim($updated_html) === '') {
      return new WP_Error('empty_content', 'The updated page content was empty after replacing images.');
    }

    $saved_id = wp_update_post(wp_slash([
      'ID' => $page_id,
      'post_content' => $updated_html,
    ]), true, false);

    if (is_wp_error($saved_id)) {
      return $saved_id;
    }

    $page_title = trim((string) $post->post_title);
    if ($page_title === '') $page_title = 'Untitled Page';

    return [
      'page_id' => $page_id,
      'page_title' => $page_title,
      'updated_count' => $updated_count,
      'edit_url' => (string) get_edit_post_link($page_id, 'raw'),
      'view_url' => (string) get_permalink($page_id),
    ];
  }
}

if (!function_exists('starscream_ai_page_builder_request_payload')) {
  function starscream_ai_page_builder_request_payload($args) {
    $page_title = isset($args['page_title']) ? trim((string) $args['page_title']) : 'Untitled Page';
    $page_slug = isset($args['page_slug']) ? trim((string) $args['page_slug']) : 'ai-page';
    $page_prefix = starscream_ai_page_builder_slug_prefix($page_slug);
    $page_archetype = starscream_ai_page_builder_page_archetype($page_title);
    $brief = isset($args['brief']) ? trim((string) $args['brief']) : '';
    $site_name = trim((string) get_bloginfo('name'));
    $site_url = home_url('/');
    $existing_page_url = isset($args['existing_page_url']) ? trim((string) $args['existing_page_url']) : '';
    $existing_page_context = isset($args['existing_page_context']) ? trim((string) $args['existing_page_context']) : '';
    $media_candidates = isset($args['media_candidates']) && is_array($args['media_candidates']) ? $args['media_candidates'] : [];
    $visual_candidates = starscream_ai_page_builder_visual_candidates($media_candidates);
    $google_location = starscream_ai_page_builder_google_location_context();

    $candidate_summary = [];
    foreach ($media_candidates as $candidate) {
      $candidate_summary[] = [
        'attachment_id' => isset($candidate['attachment_id']) ? (int) $candidate['attachment_id'] : 0,
        'source' => isset($candidate['source']) ? (string) $candidate['source'] : 'media_library',
        'selected_by_user' => !empty($candidate['selected_by_user']),
        'url' => isset($candidate['url']) ? (string) $candidate['url'] : '',
        'title' => isset($candidate['title']) ? (string) $candidate['title'] : '',
        'alt' => isset($candidate['alt']) ? (string) $candidate['alt'] : '',
        'caption' => isset($candidate['caption']) ? (string) $candidate['caption'] : '',
        'description' => isset($candidate['description']) ? (string) $candidate['description'] : '',
      ];
    }

    $instructions = implode("\n", [
      'You are building page body HTML for a WordPress page in the Starscream theme.',
      'Return only JSON that matches the provided schema.',
      'Use the Starscream content system: site-page-shell, site-section, site-section--tight, site-section--soft, site-container, site-page-intro, site-rich-text, site-grid, site-grid--2/3/4, site-card, site-card--feature, site-card__title, site-card__body, site-media-frame, site-hero, site-hero--dark, site-hero__inner, site-hero__title, site-hero__lead, site-stat-band, site-stat-band__item, site-cta-band, site-cta-band__actions, site-checklist, site-btn site-btn--primary, and site-btn site-btn--secondary.',
      'Always start page_content_html with a <style> block followed by a <div class="site-page-shell ' . $page_prefix . '"> wrapper.',
      'Use only a few page-scoped helper classes prefixed with .' . $page_prefix . '__ for layout, aspect ratios, and section-specific helpers.',
      'Prefer clamp() spacing and use a 900px breakpoint for custom two-column split layouts.',
      'Do not output markdown, code fences, scripts, forms, inline JS, SVG markup, or placeholder lorem ipsum.',
      'Use only candidate images from the provided image list. Never invent image URLs. Use no more than 6 images.',
      'Match images to the copy semantically. If no candidate image fits a section well, omit the image rather than forcing a mismatch.',
      'Write clear, conversion-ready copy based on the client brief and web research. Prefer official site facts and reliable sources.',
      'If facts are uncertain, stay generic instead of fabricating details.',
      'For an about-us page archetype, use a modern about page flow: intro hero, mission/story split, capabilities/support section, differentiators or proof, and a closing CTA.',
      'For a service or program page archetype, use a hero, what-it-is section, benefits/services grid, proof or FAQ-style support, and a CTA.',
      'If the brief, existing page, web research, or stored business location provides a specific real-world address or showroom/store/office location, include a location/contact section.',
      'When a precise physical location is confirmed, include exactly one embedded Google map in that location/contact section using a plain iframe src like https://www.google.com/maps?output=embed&q=... built from the best confirmed address. Do not use the JavaScript Maps API. Do not include a map if the location is vague or unconfirmed.',
      'Use one H1 only. Use H2/H3 for supporting sections. Keep the HTML production-ready.',
      'Use alt text that accurately describes the image in the context of the section where it appears.',
      'The page should harmonize with the active Starscream theme settings and existing design system rather than inventing a brand-new visual language.',
    ]);

    $context = implode("\n\n", [
      'Page title: ' . $page_title,
      'Page archetype: ' . $page_archetype,
      'Suggested page class prefix: ' . $page_prefix,
      'Site name: ' . $site_name,
      'Official website: ' . $site_url,
      'Existing page URL: ' . ($existing_page_url !== '' ? $existing_page_url : 'n/a'),
      'Stored Google business location: ' . (!empty($google_location['business_location']) ? (string) $google_location['business_location'] : 'n/a'),
      'Stored Google Place ID: ' . (!empty($google_location['place_id']) ? (string) $google_location['place_id'] : 'n/a'),
      'Client brief / raw content:' . "\n" . ($brief !== '' ? $brief : 'No brief supplied. Use the title, official website, and reputable web sources to infer the best structure and copy.'),
      'Existing page context:' . "\n" . ($existing_page_context !== '' ? $existing_page_context : 'None supplied.'),
      'Candidate images JSON:' . "\n" . wp_json_encode($candidate_summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
      'Research instructions:' . "\n" . 'Use web search to find reliable information about the organization implied by the title and brief. Prioritize the official website, official profiles, and reputable sources. Build the page using the strongest confirmed details.',
    ]);

    $content = [
      [
        'type' => 'input_text',
        'text' => $context,
      ],
    ];

    $visual_index = 0;
    foreach ($visual_candidates as $candidate) {
      $visual_index++;
      $content[] = [
        'type' => 'input_text',
        'text' => 'Visual candidate ' . $visual_index . ': ' . wp_json_encode([
          'attachment_id' => isset($candidate['attachment_id']) ? (int) $candidate['attachment_id'] : 0,
          'source' => isset($candidate['source']) ? (string) $candidate['source'] : 'media_library',
          'selected_by_user' => !empty($candidate['selected_by_user']),
          'url' => isset($candidate['url']) ? (string) $candidate['url'] : '',
          'title' => isset($candidate['title']) ? (string) $candidate['title'] : '',
          'alt' => isset($candidate['alt']) ? (string) $candidate['alt'] : '',
          'caption' => isset($candidate['caption']) ? (string) $candidate['caption'] : '',
        ], JSON_UNESCAPED_SLASHES),
      ];
      if (!empty($candidate['url'])) {
        $image_input = starscream_ai_page_builder_attachment_data_url(isset($candidate['attachment_id']) ? (int) $candidate['attachment_id'] : 0);
        if ($image_input === '') continue;
        $content[] = [
          'type' => 'input_image',
          'image_url' => $image_input,
          'detail' => 'high',
        ];
      }
    }

    return [
      'instructions' => $instructions,
      'input' => [
        [
          'role' => 'user',
          'content' => $content,
        ],
      ],
      'tools' => [
        [
          'type' => 'web_search',
          'search_context_size' => 'high',
          'user_location' => [
            'type' => 'approximate',
            'country' => 'US',
          ],
        ],
      ],
      'include' => ['web_search_call.action.sources'],
      'temperature' => 0.7,
      'max_output_tokens' => 9000,
      'text' => [
        'format' => [
          'type' => 'json_schema',
          'name' => 'starscream_ai_page_builder_output',
          'strict' => true,
          'schema' => [
            'type' => 'object',
            'additionalProperties' => false,
            'properties' => [
              'summary' => [
                'type' => 'string',
              ],
              'page_content_html' => [
                'type' => 'string',
              ],
              'source_urls' => [
                'type' => 'array',
                'items' => [
                  'type' => 'string',
                ],
              ],
              'image_usage_notes' => [
                'type' => 'array',
                'items' => [
                  'type' => 'object',
                  'additionalProperties' => false,
                  'properties' => [
                    'image_url' => [
                      'type' => 'string',
                    ],
                    'placement' => [
                      'type' => 'string',
                    ],
                    'reason' => [
                      'type' => 'string',
                    ],
                  ],
                  'required' => ['image_url', 'placement', 'reason'],
                ],
              ],
            ],
            'required' => ['summary', 'page_content_html', 'source_urls', 'image_usage_notes'],
          ],
        ],
      ],
    ];
  }
}

if (!function_exists('starscream_ai_page_builder_generate_page')) {
  function starscream_ai_page_builder_generate_page($request) {
    $api_key = starscream_ai_page_builder_api_key(isset($request['api_key_override']) ? $request['api_key_override'] : '');
    if ($api_key === '') {
      return new WP_Error('missing_api_key', 'Add an OpenAI API key before building a page.');
    }

    $model = starscream_ai_page_builder_model(isset($request['model_override']) ? $request['model_override'] : '');
    $mode = isset($request['mode']) && $request['mode'] === 'existing' ? 'existing' : 'new';
    $page_id = isset($request['page_id']) ? absint($request['page_id']) : 0;
    $new_title = isset($request['new_title']) ? trim((string) wp_unslash($request['new_title'])) : '';
    $brief = isset($request['brief']) ? trim((string) wp_unslash($request['brief'])) : '';
    $selected_ids = isset($request['image_ids']) ? array_map('absint', explode(',', (string) $request['image_ids'])) : [];
    $selected_ids = array_values(array_filter($selected_ids));

    if ($mode === 'existing') {
      if ($page_id < 1) return new WP_Error('missing_page', 'Choose an existing page to overwrite.');
      if (!current_user_can('edit_post', $page_id)) return new WP_Error('forbidden', 'You do not have permission to edit that page.');
      $post = get_post($page_id);
      if (!($post instanceof WP_Post) || $post->post_type !== 'page') return new WP_Error('invalid_page', 'The selected page could not be found.');
      $page_title = trim((string) $post->post_title);
      if ($page_title === '') $page_title = 'Untitled Page';
      $page_slug = (string) $post->post_name;
      $existing_page_url = (string) get_permalink($post);
      $existing_page_context = starscream_ai_page_builder_existing_page_context($page_id);
      $target_status = (string) $post->post_status;
    } else {
      if ($new_title === '') return new WP_Error('missing_title', 'Enter a title for the new page.');
      $page_title = $new_title;
      $page_slug = sanitize_title($new_title);
      $existing_page_url = '';
      $existing_page_context = '';
      $target_status = 'draft';
    }

    $media_candidates = starscream_ai_page_builder_media_candidates($selected_ids, $page_title, $brief);
    $payload = starscream_ai_page_builder_request_payload([
      'page_title' => $page_title,
      'page_slug' => $page_slug,
      'brief' => $brief,
      'existing_page_url' => $existing_page_url,
      'existing_page_context' => $existing_page_context,
      'media_candidates' => $media_candidates,
    ]);
    $payload['model'] = $model;

    @set_time_limit(180);

    $response = wp_remote_post('https://api.openai.com/v1/responses', [
      'timeout' => 180,
      'headers' => [
        'Authorization' => 'Bearer ' . $api_key,
        'Content-Type' => 'application/json',
      ],
      'body' => wp_json_encode($payload),
    ]);

    if (is_wp_error($response)) {
      return new WP_Error('api_request_failed', $response->get_error_message());
    }

    $status_code = (int) wp_remote_retrieve_response_code($response);
    $body_raw = (string) wp_remote_retrieve_body($response);
    $body = json_decode($body_raw, true);

    if ($status_code < 200 || $status_code >= 300) {
      $message = 'OpenAI request failed.';
      if (is_array($body) && !empty($body['error']['message'])) $message = (string) $body['error']['message'];
      return new WP_Error('api_http_error', $message);
    }

    $output_text = starscream_ai_page_builder_extract_output_text(is_array($body) ? $body : []);
    if ($output_text === '') {
      return new WP_Error('empty_ai_output', 'The AI builder returned an empty response.');
    }

    $parsed = json_decode($output_text, true);
    if (!is_array($parsed)) {
      return new WP_Error('invalid_ai_output', 'The AI builder returned invalid JSON.');
    }

    $generated_html = isset($parsed['page_content_html']) ? starscream_ai_page_builder_sanitize_generated_html((string) $parsed['page_content_html']) : '';
    if ($generated_html === '') {
      return new WP_Error('missing_html', 'The AI builder did not return page HTML.');
    }

    $sources = starscream_ai_page_builder_trimmed_sources(isset($parsed['source_urls']) ? (array) $parsed['source_urls'] : []);
    $sources = array_values(array_unique(array_merge($sources, starscream_ai_page_builder_extract_search_sources(is_array($body) ? $body : []))));

    $postarr = [
      'post_type' => 'page',
      'post_title' => $page_title,
      'post_content' => $generated_html,
      'post_status' => $target_status,
    ];

    if ($mode === 'existing') {
      $postarr['ID'] = $page_id;
      $saved_id = wp_update_post(wp_slash($postarr), true, false);
    } else {
      $postarr['post_name'] = sanitize_title($page_title);
      $saved_id = wp_insert_post(wp_slash($postarr), true, false);
    }

    if (is_wp_error($saved_id)) {
      return $saved_id;
    }

    return [
      'page_id' => (int) $saved_id,
      'page_title' => $page_title,
      'mode' => $mode,
      'summary' => isset($parsed['summary']) ? trim((string) $parsed['summary']) : '',
      'view_url' => (string) get_permalink((int) $saved_id),
      'edit_url' => (string) get_edit_post_link((int) $saved_id, 'raw'),
      'source_urls' => $sources,
      'image_usage_notes' => isset($parsed['image_usage_notes']) && is_array($parsed['image_usage_notes']) ? $parsed['image_usage_notes'] : [],
    ];
  }
}

add_action('customize_register', function ($wp_customize) {
  if (!($wp_customize instanceof WP_Customize_Manager)) return;

  $section = 'starscream_ai_page_builder';
  if (!$wp_customize->get_section($section)) {
    $wp_customize->add_section($section, [
      'title' => 'AI Page Builder',
      'priority' => 37,
      'description' => 'Generate page content with Starscream layout classes, web research, and media library images.',
    ]);
  }

  if (function_exists('starscream_add_customizer_divider')) {
    starscream_add_customizer_divider($wp_customize, $section, 'btx_divider_ai_page_builder_connection', 'Connection', 10);
  }

  $wp_customize->add_setting('starscream_ai_page_builder_api_key', [
    'default' => '',
    'sanitize_callback' => 'starscream_sanitize_ai_page_builder_api_key',
  ]);
  $wp_customize->add_control('starscream_ai_page_builder_api_key', [
    'label' => 'OpenAI API Key',
    'description' => 'Used to research and generate pages. Prefer defining OPENAI_API_KEY in wp-config.php; any value entered here can still be used immediately by the builder.',
    'section' => $section,
    'type' => 'password',
    'priority' => 20,
  ]);

  $wp_customize->add_setting('starscream_ai_page_builder_model', [
    'default' => 'gpt-5.1',
    'sanitize_callback' => 'starscream_sanitize_ai_page_builder_model',
  ]);
  $wp_customize->add_control('starscream_ai_page_builder_model', [
    'label' => 'OpenAI Model',
    'description' => 'Default is gpt-5.1. Change this only if you need a different Responses API model for your account or workflow.',
    'section' => $section,
    'type' => 'text',
    'priority' => 21,
  ]);

  if (function_exists('starscream_add_customizer_divider')) {
    starscream_add_customizer_divider($wp_customize, $section, 'btx_divider_ai_page_builder_builder', 'Builder', 30);
  }

  $wp_customize->add_setting('starscream_ai_page_builder_control', [
    'default' => '',
    'sanitize_callback' => 'sanitize_text_field',
  ]);

  if (class_exists('Starscream_Customize_AI_Page_Builder_Control')) {
    $wp_customize->add_control(new Starscream_Customize_AI_Page_Builder_Control($wp_customize, 'starscream_ai_page_builder_control', [
      'section' => $section,
      'priority' => 40,
    ]));
  }
}, 10);

add_action('customize_controls_enqueue_scripts', function () {
  if (!function_exists('starscream_locate') || !function_exists('starscream_asset_uri')) return;

  wp_enqueue_media();

  $style_path = starscream_locate('assets/css/customizer-ai-page-builder.css');
  if ($style_path && file_exists($style_path)) {
    wp_enqueue_style(
      'starscream-customizer-ai-page-builder',
      starscream_asset_uri('assets/css/customizer-ai-page-builder.css'),
      [],
      filemtime($style_path)
    );
  }

  $script_path = starscream_locate('assets/js/customizer-ai-page-builder.js');
  if ($script_path && file_exists($script_path)) {
    wp_enqueue_script(
      'starscream-customizer-ai-page-builder',
      starscream_asset_uri('assets/js/customizer-ai-page-builder.js'),
      ['customize-controls', 'jquery'],
      filemtime($script_path),
      true
    );

    wp_localize_script('starscream-customizer-ai-page-builder', 'starscreamAiPageBuilder', [
      'ajaxUrl' => admin_url('admin-ajax.php'),
      'nonce' => wp_create_nonce('starscream_ai_page_builder_nonce'),
      'apiKeyControlId' => 'customize-control-starscream_ai_page_builder_api_key',
      'modelControlId' => 'customize-control-starscream_ai_page_builder_model',
      'guideUrl' => function_exists('starscream_page_builder_guide_url') ? starscream_page_builder_guide_url() : '',
      'messages' => [
        'overwriteConfirm' => 'This will overwrite the selected page content. Continue?',
        'replaceConfirm' => 'This will update the selected page with the suggested media library images. Continue?',
        'fixConfirm' => 'This will resize oversized media-library images to the site standard. Bumblebee original art will be skipped. Continue?',
        'missingExistingPage' => 'Choose the page you want to overwrite.',
        'missingNewTitle' => 'Enter a title for the new page.',
        'missingReplacePage' => 'Choose the page you want to scan for image replacements.',
        'missingReplacementSuggestions' => 'Find replacement suggestions before applying them.',
        'building' => [
          'Researching the client and scanning the web for reliable details.',
          'Planning the page structure with the Starscream layout system.',
          'Matching client and media library images to the right sections.',
          'Writing the final WordPress page HTML.',
        ],
        'suggesting' => [
          'Reading the current page and locating each image slot.',
          'Scanning the media library for likely matches.',
          'Comparing the page context against candidate photography.',
          'Preparing replacement suggestions.',
        ],
        'applying' => [
          'Replacing the selected page images.',
          'Writing the updated page content back into WordPress.',
        ],
        'fixing' => [
          'Scanning the media library for oversized images.',
          'Skipping protected Bumblebee original art.',
          'Resizing standard images to the site caps.',
          'Refreshing WordPress image metadata.',
        ],
      ],
    ]);
  }
});

add_action('wp_ajax_starscream_ai_page_builder_generate', function () {
  if (!current_user_can('edit_theme_options')) {
    wp_send_json_error(['message' => 'Insufficient permissions.'], 403);
  }

  check_ajax_referer('starscream_ai_page_builder_nonce', 'nonce');

  $result = starscream_ai_page_builder_generate_page($_POST);
  if (is_wp_error($result)) {
    wp_send_json_error(['message' => $result->get_error_message()], 400);
  }

  wp_send_json_success($result);
});

add_action('wp_ajax_starscream_ai_page_builder_suggest_replacements', function () {
  if (!current_user_can('edit_theme_options')) {
    wp_send_json_error(['message' => 'Insufficient permissions.'], 403);
  }

  check_ajax_referer('starscream_ai_page_builder_nonce', 'nonce');

  $result = starscream_ai_page_builder_suggest_page_replacements($_POST);
  if (is_wp_error($result)) {
    wp_send_json_error(['message' => $result->get_error_message()], 400);
  }

  wp_send_json_success($result);
});

add_action('wp_ajax_starscream_ai_page_builder_apply_replacements', function () {
  if (!current_user_can('edit_theme_options')) {
    wp_send_json_error(['message' => 'Insufficient permissions.'], 403);
  }

  check_ajax_referer('starscream_ai_page_builder_nonce', 'nonce');

  $result = starscream_ai_page_builder_apply_page_replacements($_POST);
  if (is_wp_error($result)) {
    wp_send_json_error(['message' => $result->get_error_message()], 400);
  }

  wp_send_json_success($result);
});

add_action('wp_ajax_starscream_ai_page_builder_fix_media_library', function () {
  if (!current_user_can('edit_theme_options')) {
    wp_send_json_error(['message' => 'Insufficient permissions.'], 403);
  }

  check_ajax_referer('starscream_ai_page_builder_nonce', 'nonce');

  $result = starscream_ai_page_builder_fix_media_library_batch($_POST);
  if (is_wp_error($result)) {
    wp_send_json_error(['message' => $result->get_error_message()], 400);
  }

  wp_send_json_success($result);
});

<?php
if (!defined('ABSPATH')) exit;

if (!function_exists('starscream_convert_local_image_to_webp_file')) {
  function starscream_convert_local_image_to_webp_file($source_file) {
    $source_file = (string) $source_file;
    if ($source_file === '' || !file_exists($source_file)) {
      return new WP_Error('btx_missing_source', 'Source image file is missing.');
    }

    $editor = wp_get_image_editor($source_file);
    if (is_wp_error($editor)) return $editor;

    $path_info = pathinfo($source_file);
    $dir = isset($path_info['dirname']) ? $path_info['dirname'] : '';
    $name = isset($path_info['filename']) ? $path_info['filename'] : '';
    if ($dir === '' || $name === '') {
      return new WP_Error('btx_bad_path', 'Image path is invalid.');
    }

    $target_name = wp_unique_filename($dir, $name . '.webp');
    $target_file = trailingslashit($dir) . $target_name;

    $saved = $editor->save($target_file, 'image/webp');
    if (is_wp_error($saved)) return $saved;
    if (empty($saved['path']) || !file_exists($saved['path'])) {
      return new WP_Error('btx_webp_save_failed', 'WebP conversion failed.');
    }

    return $saved;
  }
}

if (!function_exists('starscream_delete_old_attachment_image_files')) {
  function starscream_delete_old_attachment_image_files($source_file, $old_meta, $keep_file = '') {
    $to_delete = [];
    $source_file = (string) $source_file;
    $keep_file = (string) $keep_file;

    if ($source_file !== '' && file_exists($source_file) && $source_file !== $keep_file) {
      $to_delete[] = $source_file;
    }

    if (is_array($old_meta) && !empty($old_meta['sizes']) && $source_file !== '') {
      $base_dir = trailingslashit(pathinfo($source_file, PATHINFO_DIRNAME));
      foreach ((array) $old_meta['sizes'] as $size_row) {
        if (empty($size_row['file'])) continue;
        $size_file = $base_dir . $size_row['file'];
        if (file_exists($size_file) && $size_file !== $keep_file) {
          $to_delete[] = $size_file;
        }
      }
    }

    foreach (array_unique($to_delete) as $file_path) {
      wp_delete_file($file_path);
    }
  }
}

if (!function_exists('starscream_convert_attachment_to_webp')) {
  function starscream_convert_attachment_to_webp($attachment_id) {
    $attachment_id = absint($attachment_id);
    if ($attachment_id < 1) return new WP_Error('btx_bad_attachment', 'Invalid attachment ID.');

    $mime = (string) get_post_mime_type($attachment_id);
    if ($mime === 'image/webp') return $attachment_id;
    if (!in_array($mime, ['image/jpeg', 'image/png'], true)) return $attachment_id;

    $source_file = get_attached_file($attachment_id, true);
    if (!$source_file || !file_exists($source_file)) {
      return new WP_Error('btx_missing_attachment_file', 'Attachment file not found.');
    }

    $old_meta = wp_get_attachment_metadata($attachment_id);
    $converted = starscream_convert_local_image_to_webp_file($source_file);
    if (is_wp_error($converted)) return $converted;

    $new_file = (string) $converted['path'];
    if ($new_file === '' || !file_exists($new_file)) {
      return new WP_Error('btx_missing_webp_file', 'Converted WebP file missing.');
    }

    update_attached_file($attachment_id, $new_file);
    wp_update_post([
      'ID' => $attachment_id,
      'post_mime_type' => 'image/webp',
    ]);

    $new_meta = wp_generate_attachment_metadata($attachment_id, $new_file);
    if (!is_wp_error($new_meta) && is_array($new_meta)) {
      wp_update_attachment_metadata($attachment_id, $new_meta);
    }

    starscream_delete_old_attachment_image_files($source_file, $old_meta, $new_file);
    return $attachment_id;
  }
}

add_filter('wp_handle_upload', function ($upload) {
  if (!is_array($upload) || empty($upload['file']) || empty($upload['type'])) return $upload;

  $type = strtolower((string) $upload['type']);
  if (!in_array($type, ['image/jpeg', 'image/png'], true)) return $upload;

  $source_file = (string) $upload['file'];
  if (!file_exists($source_file)) return $upload;

  $converted = starscream_convert_local_image_to_webp_file($source_file);
  if (is_wp_error($converted) || empty($converted['path'])) return $upload;

  $new_file = (string) $converted['path'];
  if (!file_exists($new_file)) return $upload;

  wp_delete_file($source_file);

  $upload['file'] = $new_file;
  $upload['type'] = 'image/webp';
  if (!empty($upload['url'])) {
    $upload['url'] = trailingslashit(dirname((string) $upload['url'])) . basename($new_file);
  }

  return $upload;
}, 20);

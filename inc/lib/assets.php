<?php
/*
 * File: inc/lib/assets.php
 * Description: Child/parent asset resolver.
 * Plugin: Starscream
 * Author: Eric Kowalewski
 * Last Updated: 2025-09-02 21:35 EDT
 */
if (!defined('ABSPATH')) exit;

function starscream_asset_uri($relpath){
  $child_file = trailingslashit(get_stylesheet_directory()) . ltrim($relpath,'/');
  if (file_exists($child_file)) {
    return trailingslashit(get_stylesheet_directory_uri()) . ltrim($relpath,'/');
  }
  return trailingslashit(get_template_directory_uri()) . ltrim($relpath,'/');
}

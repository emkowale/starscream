<?php
/*
 * Plugin Name: Starscream Theme Bootstrap
 * Description: Minimal loader that wires modular includes.
 * Author: Eric Kowalewski
 * Version: 1.4.29
 * Last Updated: 2025-09-02 21:35 EDT
 */

if (!defined('ABSPATH')) exit;

/** Locate (child first, then parent) */
function starscream_locate($rel){
  $child = trailingslashit(get_stylesheet_directory()) . ltrim($rel,'/');
  if (file_exists($child)) return $child;
  return trailingslashit(get_template_directory()) . ltrim($rel,'/');
}

/** Require-safe */
function starscream_require($rel){
  $p = starscream_locate($rel);
  if (file_exists($p)) require_once $p;
  else if (function_exists('error_log')) error_log("[Starscream] Missing: {$rel}");
}

/** Libs */
starscream_require('inc/lib/assets.php');        // asset URI helper

/** Admin / Customizer */
starscream_require('inc/admin/customizer.php');  // existing file

/** Front page → Shop (hero split) */
starscream_require('inc/frontpage/hero.php');
starscream_require('inc/frontpage/shop-override.php');

/** Enqueues & CSS vars */
starscream_require('inc/enqueue/styles.php');
starscream_require('inc/enqueue/gallery.php');
starscream_require('inc/enqueue/vars.php');

/** WooCommerce tweaks */
starscream_require('inc/woo/archive-tweaks.php');
starscream_require('inc/woo/image-sizes.php');

/** Theme supports & branding */
starscream_require('inc/theme/supports.php');
starscream_require('inc/theme/branding.php');

/** Optional modules (non-fatal) */
starscream_require('inc/ensure-classic-pages.php');     // existing
starscream_require('inc/modules/woo-extras.php');       // optional

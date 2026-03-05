<?php
if (!defined('ABSPATH')) exit;
/**
 * Starscream Woo Variations (fixed)
 * - Keep native selects so Woo JS sets variation_id
 * - Auto-select default/first option
 * - Hide rows when only one choice or attribute is internal
 * - If exactly one purchasable variation exists, wire it & hide picker
 */

/* Prefer a sensible selection if none chosen */
add_filter('woocommerce_dropdown_variation_attribute_options_args', function ($args) {
  if (empty($args['product']) || empty($args['attribute'])) return $args;
  $p = $args['product']; if (!$p instanceof WC_Product_Variable) return $args;

  $selected = isset($args['selected']) ? (string)$args['selected'] : '';
  $options  = isset($args['options'])  ? (array)$args['options']  : [];

  if ($selected === '' && !empty($options)) {
    $tax = preg_replace('/^attribute_/', '', (string)$args['attribute']);
    $def = (string) $p->get_variation_default_attribute($tax);
    $args['selected'] = ($def !== '') ? $def : (string) reset($options);
  }
  return $args;
}, 10);

/* Keep the <select>; just mark rows we should hide */
add_filter('woocommerce_dropdown_variation_attribute_options_html', function ($html, $args) {
  if (empty($args['product']) || empty($args['attribute'])) return $html;
  $p = $args['product']; if (!$p instanceof WC_Product_Variable) return $html;

  $tax     = preg_replace('/^attribute_/', '', (string)$args['attribute']);
  $always  = apply_filters('starscream/woo/always_hide_attributes', ['pa_quality']);
  $options = isset($args['options']) ? array_map('strval', (array)$args['options']) : [];
  $hide    = in_array($tax, $always, true) || count(array_unique($options)) <= 1;

  return $hide ? $html . '<span class="bt-should-hide" data-attr="'.esc_attr($tax).'"></span>' : $html;
}, 11, 2); // <-- accept 2 args

/* If exactly one purchasable variation, wire it + hide picker */
add_action('woocommerce_before_add_to_cart_button', function () {
  global $product;
  if (!$product instanceof WC_Product_Variable) return;

  $vars = array_values($product->get_available_variations());
  if (count($vars) !== 1) return;
  $v = $vars[0]; if (empty($v['is_in_stock'])) return;

  echo '<input type="hidden" class="variation_id" name="variation_id" value="'.esc_attr($v['variation_id']).'">';
  if (!empty($v['attributes']) && is_array($v['attributes'])) {
    foreach ($v['attributes'] as $name => $val) {
      echo '<input type="hidden" name="'.esc_attr($name).'" value="'.esc_attr($val).'">';
    }
  }
  echo '<style>.variations_form .variations{display:none!important}</style>';
}, 9);

/* JS: hide marked rows and trigger variation check */
add_action('wp_enqueue_scripts', function () {
  if (!is_product()) return;
  $path = trailingslashit(get_stylesheet_directory()).'assets/js/woo-variations-lite.js';
  $uri  = trailingslashit(get_stylesheet_directory_uri()).'assets/js/woo-variations-lite.js';
  if (!file_exists($path)) { $path = trailingslashit(get_template_directory()).'assets/js/woo-variations-lite.js';
                             $uri  = trailingslashit(get_template_directory_uri()).'assets/js/woo-variations-lite.js'; }
  if (file_exists($path)) wp_enqueue_script('starscream-woo-variations', $uri, ['jquery'], filemtime($path), true);
}, 20);

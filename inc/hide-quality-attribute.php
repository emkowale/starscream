<?php
/*
 * File: inc/hide-quality-attribute.php
 * Theme: Starscream
 * Description: Force-hide the "Quality" attribute everywhere customer-facing.
 * Author: Eric Kowalewski
 * Author URI: https://thebeartraxs.com
 * Last Updated: 2025-10-05 17:45 EDT
 */

if (!defined('ABSPATH')) exit;

// Helper to detect the Quality label/keys in different shapes
if (!function_exists('starscream_is_quality_label')) {
    function starscream_is_quality_label($label) {
        if (empty($label)) return false;
        $s = strtolower(wp_strip_all_tags((string) $label));
        return in_array($s, ['quality','pa_quality','attribute_pa_quality','attribute_quality'], true);
    }
}

// Cart / Mini-cart / Checkout line-item meta
add_filter('woocommerce_get_item_data', function ($item_data, $cart_item) {
    if (empty($item_data)) return $item_data;
    $out = [];
    foreach ($item_data as $row) {
        $key   = $row['key']         ?? '';
        $name  = $row['name']        ?? '';
        $dkey  = $row['display_key'] ?? '';
        if (starscream_is_quality_label($key) || starscream_is_quality_label($name) || starscream_is_quality_label($dkey)) {
            continue;
        }
        $out[] = $row;
    }
    return $out;
}, 100, 2);

// Order emails & My Account → Orders
add_filter('woocommerce_order_item_get_formatted_meta_data', function ($formatted_meta, $item) {
    if (empty($formatted_meta)) return $formatted_meta;
    $out = [];
    foreach ($formatted_meta as $meta) {
        $key  = isset($meta->key) ? $meta->key : '';
        $dkey = isset($meta->display_key) ? $meta->display_key : '';
        if (starscream_is_quality_label($key) || starscream_is_quality_label($dkey)) {
            continue;
        }
        $out[] = $meta;
    }
    return $out;
}, 100, 2);

// Product page "Additional information" tab
add_filter('woocommerce_product_get_attributes', function ($attributes) {
    foreach ($attributes as $k => $attr) {
        if (is_object($attr) && method_exists($attr, 'get_name')) {
            if (starscream_is_quality_label($attr->get_name()) && method_exists($attr, 'set_visible')) {
                $attr->set_visible(false);
                $attributes[$k] = $attr;
            }
        } elseif (is_array($attr)) {
            $name = $attr['name'] ?? '';
            if (starscream_is_quality_label($name)) {
                $attr['is_visible'] = false;
                $attributes[$k] = $attr;
            }
        }
    }
    return $attributes;
}, 99, 2);

// Defense-in-depth CSS in case a template bypasses filters or cache serves old HTML
add_action('wp_head', function () {
    echo '<style id="starscream-hide-quality">
    .woocommerce td.product-name dl.variation dt[class*="variation-Quality"],
    .woocommerce td.product-name dl.variation dd[class*="variation-Quality"],
    .woocommerce td.product-name dl.variation dt[class*="variation-quality"],
    .woocommerce td.product-name dl.variation dd[class*="variation-quality"],
    .woocommerce-mini-cart-item dl.variation dt[class*="variation-Quality"],
    .woocommerce-mini-cart-item dl.variation dd[class*="variation-Quality"],
    .woocommerce-mini-cart-item dl.variation dt[class*="variation-quality"],
    .woocommerce-mini-cart-item dl.variation dd[class*="variation-quality"]{display:none !important;}
    </style>';
}, 99);

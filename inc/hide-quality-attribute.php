<?php
/*
 * File: inc/hide-quality-attribute.php
 * Theme: Starscream
 * Description: Hide the "Quality" attribute everywhere customer-facing (cart/mini-cart/checkout, emails, My Account orders, product Additional information tab).
 * Author: Eric Kowalewski
 * Author URI: https://thebeartraxs.com
 * Last Updated: 2025-10-05 17:00 EDT
 */

if (!defined('ABSPATH')) exit;

// 1) Cart / Mini-cart / Checkout line-item meta
add_filter('woocommerce_get_item_data', function ($item_data, $cart_item) {
    if (empty($item_data)) return $item_data;
    $filtered = array_filter($item_data, function ($row) {
        $name = isset($row['name']) ? strtolower($row['name']) : '';
        return $name !== 'quality';
    });
    return array_values($filtered);
}, 10, 2);

// 2) Order emails & My Account → Orders (formatted order item meta)
add_filter('woocommerce_order_item_get_formatted_meta_data', function ($formatted_meta, $item) {
    $filtered = array_filter($formatted_meta, function ($meta) {
        $key  = isset($meta->key) ? strtolower($meta->key) : '';
        $dkey = isset($meta->display_key) ? strtolower($meta->display_key) : '';
        if (in_array($key, ['pa_quality','attribute_pa_quality','attribute_quality'], true)) return false;
        if ($dkey === 'quality') return false;
        return true;
    });
    return array_values($filtered);
}, 10, 2);

// 3) Product page "Additional information" tab (attribute table)
add_filter('woocommerce_product_get_attributes', function ($attributes) {
    foreach ($attributes as $key => $attribute) {
        // Taxonomy-backed attribute (e.g., pa_quality)
        if (is_object($attribute) && method_exists($attribute, 'get_name')) {
            $name = strtolower($attribute->get_name());
            if ($name === 'pa_quality' || $name === 'quality') {
                if (method_exists($attribute, 'set_visible')) {
                    $attribute->set_visible(false);
                    $attributes[$key] = $attribute;
                }
            }
        }
        // Array-style attribute (older/edge setups)
        if (is_array($attribute)) {
            $name = isset($attribute['name']) ? strtolower($attribute['name']) : '';
            if ($name === 'pa_quality' || $name === 'quality') {
                $attribute['is_visible'] = false;
                $attributes[$key] = $attribute;
            }
        }
    }
    return $attributes;
}, 10, 1);

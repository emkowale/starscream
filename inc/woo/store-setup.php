<?php
if (!defined('ABSPATH')) exit;

// Core store defaults
add_action('init', function () {
  update_option('woocommerce_store_address', '7555 Midland Road');
  update_option('woocommerce_store_address_2', '');
  update_option('woocommerce_store_city', 'Freeland');
  update_option('woocommerce_default_country', 'US:MI');
  update_option('woocommerce_store_postcode', '48623');
  update_option('woocommerce_allowed_countries', 'specific');
  update_option('woocommerce_specific_allowed_countries', ['US']);
  update_option('woocommerce_ship_to_countries', 'selling');
  update_option('woocommerce_calc_taxes', 'yes');
  update_option('blog_public', '1');
});

// Seed Michigan-only tax rate
add_action('init', function () {
  if (!class_exists('WC_Tax')) return;
  global $wpdb;
  $table = $wpdb->prefix . 'woocommerce_tax_rates';
  // Remove any US state tax rates except Michigan to keep taxes MI-only.
  $wpdb->query($wpdb->prepare(
    "DELETE FROM $table WHERE tax_rate_country=%s AND tax_rate_state <> %s",
    'US',
    'MI'
  ));

  $data = [
    'tax_rate_country' => 'US',
    'tax_rate_state' => 'MI',
    'tax_rate' => '6',
    'tax_rate_name' => 'Michigan Sales Tax',
    'tax_rate_priority' => 1,
    'tax_rate_compound' => 0,
    'tax_rate_shipping' => 0, // Shipping not taxed when itemized in MI
    'tax_rate_order' => 0,
    'tax_rate_class' => '',
  ];

  $existing = $wpdb->get_var($wpdb->prepare(
    "SELECT tax_rate_id FROM $table WHERE tax_rate_country=%s AND tax_rate_state=%s LIMIT 1",
    'US',
    'MI'
  ));

  if ($existing) {
    $wpdb->update($table, $data, ['tax_rate_id' => $existing]);
  } else {
    $wpdb->insert($table, $data);
  }

  update_option('starscream_tax_seeded_mi_only', 1);
  delete_option('starscream_tax_seeded');
}, 20);

// Shipping: USA zone with unconditional free shipping and local pickup.
add_action('woocommerce_init', function () {
  if (!class_exists('WC_Shipping_Zones')) return;
  $zones = WC_Shipping_Zones::get_zones();
  $zone_id = null;
  foreach ($zones as $z) { if ($z['zone_name'] === 'USA') { $zone_id = $z['zone_id']; break; } }
  if (!$zone_id) {
    $zone = new WC_Shipping_Zone();
    $zone->set_zone_name('USA');
    $zone_id = $zone->save();
  } else {
    $zone = new WC_Shipping_Zone($zone_id);
  }
  $zone->set_locations([['code' => 'US', 'type' => 'country']]);
  $zone->save();

  // Keep one free-shipping instance and one local-pickup instance.
  $methods_all = $zone->get_shipping_methods(false);
  $free = $pickup = null;
  foreach ($methods_all as $instance_id => $m) {
    if (!is_object($m)) continue;
    if ($m->id === 'flat_rate') { $zone->delete_shipping_method($instance_id); continue; }
    if ($m->id === 'free_shipping') {
      if ($free) { $zone->delete_shipping_method($instance_id); continue; }
      $free = $m;
      continue;
    }
    if ($m->id === 'local_pickup') {
      if ($pickup) { $zone->delete_shipping_method($instance_id); continue; }
      $pickup = $m;
    }
  }

  if (!$free) {
    $zone->add_shipping_method('free_shipping');
    foreach ($zone->get_shipping_methods(true) as $m) {
      if (is_object($m) && $m->id === 'free_shipping') { $free = $m; break; }
    }
  }
  if ($free) {
    $free->update_option('title', 'Free shipping');
    $free->update_option('requires', '');
    $free->update_option('min_amount', '0');
  }

  if (!$pickup) {
    $zone->add_shipping_method('local_pickup');
    foreach ($zone->get_shipping_methods(true) as $m) {
      if (is_object($m) && $m->id === 'local_pickup') { $pickup = $m; break; }
    }
  }
  if ($pickup) {
    $pickup->update_option('title', 'Local pickup');
    $pickup->update_option('cost', '0');
    $pickup->update_option('tax_status', 'none');
  }
}, 25);

add_filter('woocommerce_package_rates', function ($rates, $package) {
  $filtered = [];
  $has_free = false;
  $has_pickup = false;

  foreach ($rates as $key => $rate) {
    if (!is_object($rate)) continue;

    $method_id = method_exists($rate, 'get_method_id') ? $rate->get_method_id() : ($rate->method_id ?? '');

    if ($method_id === 'free_shipping') {
      if ($has_free) continue;
      $has_free = true;
      $rate->label = 'Free shipping';
      $rate->cost = 0;
      if (is_array($rate->taxes)) {
        foreach ($rate->taxes as $tax_key => $tax_amount) {
          $rate->taxes[$tax_key] = 0;
        }
      }
      $filtered[$key] = $rate;
      continue;
    }

    if ($method_id === 'local_pickup') {
      if ($has_pickup) continue;
      $has_pickup = true;
      $rate->label = 'Local pickup';
      $rate->cost = 0;
      if (is_array($rate->taxes)) {
        foreach ($rate->taxes as $tax_key => $tax_amount) {
          $rate->taxes[$tax_key] = 0;
        }
      }
      $filtered[$key] = $rate;
    }
  }

  // Fallback for stores still carrying an old zero-cost flat-rate instance.
  if (!$has_free) {
    foreach ($rates as $key => $rate) {
      if (!is_object($rate)) continue;
      $method_id = method_exists($rate, 'get_method_id') ? $rate->get_method_id() : ($rate->method_id ?? '');
      $cost = method_exists($rate, 'get_cost') ? (float) $rate->get_cost() : (float) ($rate->cost ?? 0);
      if ($method_id !== 'flat_rate' || $cost > 0) continue;

      $rate->label = 'Free shipping';
      $rate->cost = 0;
      if (is_array($rate->taxes)) {
        foreach ($rate->taxes as $tax_key => $tax_amount) {
          $rate->taxes[$tax_key] = 0;
        }
      }
      $filtered = [$key => $rate] + $filtered;
      break;
    }
  }

  return $filtered;
}, 10, 2);

add_filter('woocommerce_cart_shipping_method_full_label', function ($label, $method) {
  if (!is_object($method)) return $label;

  $method_id = method_exists($method, 'get_method_id') ? $method->get_method_id() : ($method->method_id ?? '');
  if ($method_id === 'free_shipping') return 'Free shipping';
  if ($method_id === 'local_pickup') return 'Local pickup';

  return $label;
}, 10, 2);

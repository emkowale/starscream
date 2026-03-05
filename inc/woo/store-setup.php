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

// Shipping: USA zone with unconditional free shipping.
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

  // Dedupe methods so only one flat + one free remain.
  $methods_all = $zone->get_shipping_methods(false);
  $flat = $free = null;
  foreach ($methods_all as $instance_id => $m) {
    if (is_object($m) && $m->id === 'flat_rate') {
      if ($flat) { $zone->delete_shipping_method($instance_id); continue; }
      $flat = $m;
    }
    if (is_object($m) && $m->id === 'free_shipping') {
      if ($free) { $zone->delete_shipping_method($instance_id); continue; }
      $free = $m;
    }
  }

  if (!$flat) { $zone->add_shipping_method('flat_rate'); foreach ($zone->get_shipping_methods(true) as $m) { if (is_object($m) && $m->id === 'flat_rate') { $flat = $m; break; } } }
  if ($flat) { $flat->update_option('title', 'Flat rate: FREE SHIPPING'); $flat->update_option('cost', '0'); $flat->update_option('tax_status', 'none'); }
  if (!$free) { $zone->add_shipping_method('free_shipping'); foreach ($zone->get_shipping_methods(true) as $m) { if (is_object($m) && $m->id === 'free_shipping') { $free = $m; break; } } }
  if ($free) { $free->update_option('requires', ''); $free->update_option('min_amount', '0'); }
}, 25);

add_filter('woocommerce_package_rates', function ($rates, $package) {
  $forced_label = 'Flat rate: FREE SHIPPING';

  // Force all returned shipping options to free with a consistent label.
  foreach ($rates as $key => $rate) {
    $rates[$key]->label = $forced_label;
    $rates[$key]->cost = 0;
    if (is_array($rates[$key]->taxes)) {
      foreach ($rates[$key]->taxes as $tax_key => $tax_amount) {
        $rates[$key]->taxes[$tax_key] = 0;
      }
    }
  }
  return $rates;
}, 10, 2);

add_filter('woocommerce_cart_shipping_method_full_label', function ($label, $method) {
  return 'Flat rate: FREE SHIPPING';
}, 10, 2);

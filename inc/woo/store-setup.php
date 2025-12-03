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
    'tax_rate_shipping' => 1,
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

// Shipping: USA zone with flat/free + $2 when cart < $10
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
  if ($flat) { $flat->update_option('title', 'Flat Rate'); $flat->update_option('cost', '10'); $flat->update_option('tax_status', 'none'); }
  if (!$free) { $zone->add_shipping_method('free_shipping'); foreach ($zone->get_shipping_methods(true) as $m) { if (is_object($m) && $m->id === 'free_shipping') { $free = $m; break; } } }
  if ($free) { $free->update_option('requires', 'either'); $free->update_option('min_amount', '100'); }
}, 25);

add_filter('woocommerce_package_rates', function ($rates, $package) {
  foreach ($rates as $r) { if ($r->method_id === 'free_shipping') return array_filter($rates, fn($x) => $x->method_id === 'free_shipping'); }
  $cart_total = 0;
  if (WC()->cart && !is_admin()) {
    // Use original subtotal before coupons (no shipping) to determine threshold.
    $cart_total = max(0, WC()->cart->get_subtotal());
  }
  foreach ($rates as $key => $rate) {
    if ($rate->method_id !== 'flat_rate') continue;
    $rates[$key]->cost = ($cart_total < 10) ? 2 : 10;
  }
  return $rates;
}, 10, 2);

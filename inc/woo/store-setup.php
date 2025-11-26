<?php
if (!defined('ABSPATH')) exit;

// Core store defaults
add_action('init', function () {
  update_option('woocommerce_store_address', '901 Waterway Pl. Suite B');
  update_option('woocommerce_store_address_2', '');
  update_option('woocommerce_store_city', 'Longwood');
  update_option('woocommerce_default_country', 'US:FL');
  update_option('woocommerce_store_postcode', '32750');
  update_option('woocommerce_allowed_countries', 'specific');
  update_option('woocommerce_specific_allowed_countries', ['US']);
  update_option('woocommerce_ship_to_countries', 'selling');
  update_option('woocommerce_calc_taxes', 'yes');
  update_option('blog_public', '1');
});

// Seed US tax rates once
add_action('init', function () {
  if (get_option('starscream_tax_seeded')) return;
  if (!class_exists('WC_Tax')) return;
  global $wpdb;
  $table = $wpdb->prefix . 'woocommerce_tax_rates';
  $rates = [
    ['AK','0'],['AL','4'],['AR','6.5'],['AZ','5.6'],['CA','7.25'],['CO','2.9'],['CT','6.35'],['DC','6'],
    ['DE','0'],['FL','6'],['GA','4'],['HI','4'],['IA','6'],['ID','6'],['IL','6.25'],['IN','7'],
    ['KS','6.5'],['KY','6'],['LA','4.45'],['MA','6.25'],['MD','6'],['ME','5.5'],['MI','6'],['MN','6.88'],
    ['MO','4.23'],['MS','7'],['MT','0'],['NC','4.75'],['ND','5'],['NE','5.5'],['NH','0'],['NJ','6.63'],
    ['NM','5.13'],['NV','4.6'],['NY','4'],['OH','5.75'],['OK','4.5'],['OR','0'],['PA','6'],['RI','7'],
    ['SC','6'],['SD','4.5'],['TN','7'],['TX','6.25'],['UT','4.7'],['VA','4.3'],['VT','6'],['WA','6.5'],
    ['WI','5'],['WV','6'],['WY','4'],
  ];
  foreach ($rates as [$state, $rate]) {
    $exists = $wpdb->get_var($wpdb->prepare("SELECT tax_rate_id FROM $table WHERE tax_rate_country='US' AND tax_rate_state=%s LIMIT 1", $state));
    if ($exists) continue;
    $wpdb->insert($table, [
      'tax_rate_country' => 'US',
      'tax_rate_state' => $state,
      'tax_rate' => $rate,
      'tax_rate_name' => 'Sales Tax',
      'tax_rate_priority' => 1,
      'tax_rate_compound' => 0,
      'tax_rate_shipping' => 1,
      'tax_rate_order' => 0,
      'tax_rate_class' => '',
    ]);
  }
  update_option('starscream_tax_seeded', 1);
}, 20);

// Shipping: USA zone with flat/free + $2 under $10
add_action('init', function () {
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
  $methods = $zone->get_shipping_methods(true);
  $flat = $free = null;
  foreach ($methods as $m) { if ($m->method_id === 'flat_rate') $flat = $m; if ($m->method_id === 'free_shipping') $free = $m; }
  if (!$flat) { $zone->add_shipping_method('flat_rate'); foreach ($zone->get_shipping_methods(true) as $m) { if ($m->method_id === 'flat_rate') { $flat = $m; break; } } }
  if ($flat) { $flat->update_option('title', 'Flat Rate'); $flat->update_option('cost', '10'); $flat->update_option('tax_status', 'none'); }
  if (!$free) { $zone->add_shipping_method('free_shipping'); foreach ($zone->get_shipping_methods(true) as $m) { if ($m->method_id === 'free_shipping') { $free = $m; break; } } }
  if ($free) { $free->update_option('requires', 'either'); $free->update_option('min_amount', '100'); }
}, 25);

add_filter('woocommerce_package_rates', function ($rates, $package) {
  foreach ($rates as $r) { if ($r->method_id === 'free_shipping') return array_filter($rates, fn($x) => $x->method_id === 'free_shipping'); }
  $subtotal = (WC()->cart && !is_admin()) ? WC()->cart->get_displayed_subtotal() : 0;
  if ($subtotal < 10) foreach ($rates as $key => $rate) if ($rate->method_id === 'flat_rate') $rates[$key]->cost = 2;
  return $rates;
}, 10, 2);

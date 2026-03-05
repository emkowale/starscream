<?php
if (!defined('ABSPATH')) exit;

/**
 * Disable all WooCommerce customer-facing emails.
 * Removes any email class flagged as a customer email, covering core + extensions.
 */
add_filter('woocommerce_email_classes', function ($emails) {
  foreach ($emails as $id => $email) {
    if (is_object($email) && method_exists($email, 'is_customer_email') && $email->is_customer_email()) {
      unset($emails[$id]);
    }
  }
  return $emails;
}, 20);

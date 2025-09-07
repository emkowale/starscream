<?php
if (!defined('ABSPATH')) exit;

if (!function_exists('starscream_sanitize_checkbox')) {
  function starscream_sanitize_checkbox($v){
    return (isset($v) && (true === $v || '1' === $v || 1 === $v)) ? true : false;
  }
}

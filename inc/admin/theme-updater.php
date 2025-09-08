<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('Starscream_Theme_Updater')) {
class Starscream_Theme_Updater {
  const OWNER='emkowale';
  const REPO='starscream';
  const RAW_STYLE='https://raw.githubusercontent.com/emkowale/starscream/main/style.css';
  const CACHE='starscream_update_data';
  const TTL=10800; // 3h cache

  /** Always use the actual parent theme directory on this site */
  protected static function slug(){
    return (string) get_template();
  }

  static function boot(){
    add_filter('pre_set_site_transient_update_themes',[__CLASS__,'inject'],10,1);
    add_filter('themes_api',[__CLASS__,'details'],10,3);
    add_filter('upgrader_source_selection',[__CLASS__,'rename'],10,4);
    add_action('load-update-core.php',[__CLASS__,'bust']);
    add_action('upgrader_process_complete',[__CLASS__,'bust_after'],10,2);
  }

  static function bust(){ delete_site_transient(self::CACHE); }
  static function bust_after($u,$e){ if(!empty($e['type']) && $e['type']==='theme') self::bust(); }

  protected static function ua(){
    return ['headers'=>['User-Agent'=>'WP-Starscream-Updater'],'timeout'=>12];
  }

  /** Fallback: read Version from raw style.css in main and build codeload ZIP */
  protected static function from_raw(){
    $r = wp_remote_get(self::RAW_STYLE, self::ua());
    if (is_wp_error($r) || wp_remote_retrieve_response_code($r)!==200) return null;
    $css = wp_remote_retrieve_body($r);
    if (!preg_match('/^\s*Version:\s*([0-9][0-9a-zA-Z\.\-\+_]*)/mi', $css, $m)) return null;
    $ver = trim($m[1]);
    $tag = 'v'.$ver;
    $zip = "https://codeload.github.com/".self::OWNER."/".self::REPO."/zip/refs/tags/".$tag;
    $url = "https://github.com/".self::OWNER."/".self::REPO."/releases/tag/".$tag;
    return ['version'=>$ver,'zip'=>$zip,'url'=>$url];
  }

  /** Prefer: latest GitHub Release; use asset .zip if present, else codeload ZIP */
  protected static function from_github(){
    $r = wp_remote_get("https://api.github.com/repos/".self::OWNER."/".self::REPO."/releases/latest", self::ua());
    if (is_wp_error($r) || wp_remote_retrieve_response_code($r)!==200) return null;
    $b = json_decode(wp_remote_retrieve_body($r), true);
    if (!is_array($b) || empty($b['tag_name'])) return null;

    $tag = (string)$b['tag_name'];
    $ver = ltrim($tag, 'vV');
    $zip = '';
    if (!empty($b['assets']) && is_array($b['assets'])){
      foreach ($b['assets'] as $a){
        $dl = $a['browser_download_url'] ?? '';
        $nm = $a['name'] ?? '';
        if ($dl && preg_match('/\.zip$/i', $nm)) { $zip = $dl; break; }
      }
    }
    if (!$zip) $zip = "https://codeload.github.com/".self::OWNER."/".self::REPO."/zip/refs/tags/".$tag;
    $url = $b['html_url'] ?? ("https://github.com/".self::OWNER."/".self::REPO."/releases/tag/".$tag);

    return ['version'=>$ver,'zip'=>$zip,'url'=>$url];
  }

  protected static function latest(){
    if ($c = get_site_transient(self::CACHE)) return $c;
    // Prefer a Release; fall back to raw
    $d = self::from_github();
    if (!$d) $d = self::from_raw();
    if ($d) set_site_transient(self::CACHE, $d, self::TTL);
    return $d;
  }

  /** Inject update info into WP's theme update transient */
  static function inject($tr){
    $slug  = self::slug();
    $theme = wp_get_theme($slug);
    if (!$theme->exists()) return $tr;

    $cur = (string)$theme->get('Version');
    if (!$cur) return $tr;

    $d = self::latest();
    if (!$d) return $tr;

    if (version_compare($d['version'], $cur, '>')){
      if (!is_object($tr)) $tr = new stdClass();
      $tr->response[$slug] = [
        'theme'       => $slug,
        'new_version' => $d['version'],
        'package'     => $d['zip'],
        'url'         => $d['url'],
      ];
    }
    return $tr;
  }

  /** Theme details modal in the Themes screen */
  static function details($res, $action, $args){
    $slug = self::slug();
    if ($action !== 'theme_information' || empty($args->slug) || $args->slug !== $slug) return $res;
    $d = self::latest(); if (!$d) return $res;

    $t = wp_get_theme($slug);
    return (object)[
      'name'          => $t->get('Name') ?: 'Starscream',
      'slug'          => $slug,
      'version'       => $d['version'],
      'homepage'      => $d['url'],
      'download_link' => $d['zip'],
      'sections'      => ['description' => 'Updates served from GitHub releases/tags (public).']
    ];
  }

  /** Rename extracted folder (e.g., starscream-1.4.37) -> real theme slug */
  static function rename($src, $remote, $upgrader, $extra){
    $slug = self::slug();
    $is = (isset($extra['theme']) && $extra['theme'] === $slug)
       || (isset($extra['themes']) && in_array($slug, (array)$extra['themes'], true));
    if (!$is) return $src;
    if (basename($src) === $slug) return $src;

    $dest = trailingslashit(dirname($src)).$slug;
    return @rename($src, $dest) ? $dest : $src;
  }
}
Starscream_Theme_Updater::boot();
}

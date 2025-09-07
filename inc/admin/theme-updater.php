  <?php
  /*
  * Public GitHub updater for Starscream (no tokens needed).
  * Requires: style.css has "Update URI: https://github.com/emkowale/starscream"
  */
  if (!defined('ABSPATH')) exit;

  if (!class_exists('Starscream_Theme_Updater')) {
  class Starscream_Theme_Updater {
    const OWNER='emkowale';
    const REPO='starscream';
    const SLUG='starscream';
    const TTL=21600; // 6h cache
    const TKEY='starscream_update_data';

    static function boot(){
      add_filter('pre_set_site_transient_update_themes',[__CLASS__,'check'],10,1);
      add_filter('themes_api',[__CLASS__,'info'],10,3);
      add_filter('upgrader_source_selection',[__CLASS__,'rename'],10,4);
    }

    protected static function latest(){
      // Prefer site-wide transient (MS safe)
      if ($c = get_site_transient(self::TKEY)) return $c;

      $ua = [
        'headers' => ['User-Agent' => 'WordPress/' . get_bloginfo('version') . '; ' . home_url('/')],
        'timeout' => 10,
      ];

      // 1) Try the latest Release (non-draft, non-prerelease)
      $r = wp_remote_get("https://api.github.com/repos/".self::OWNER."/".self::REPO."/releases/latest", $ua);
      if (!is_wp_error($r) && wp_remote_retrieve_response_code($r) === 200) {
        $b = json_decode(wp_remote_retrieve_body($r), true);
        if (is_array($b) && !empty($b['tag_name'])) {
          $tag = (string)$b['tag_name'];
          $ver = ltrim($tag, 'vV');
          $url = $b['html_url'] ?? ("https://github.com/".self::OWNER."/".self::REPO."/releases/tag/".$tag);

          // Prefer a release asset ZIP that already unzips to starscream/
          $zip = '';
          if (!empty($b['assets']) && is_array($b['assets'])) {
            foreach ($b['assets'] as $a) {
              $name = $a['name'] ?? '';
              $dl   = $a['browser_download_url'] ?? '';
              if ($dl && preg_match('/\.zip$/i', $name)) {
                // prefer assets that look like your packaged theme
                if (stripos($name, self::SLUG) !== false) { $zip = $dl; break; }
                $zip = $zip ?: $dl;
              }
            }
          }

          // Fallback to codeload tag ZIP if no asset
          if (!$zip) {
            $zip = "https://codeload.github.com/".self::OWNER."/".self::REPO."/zip/refs/tags/".$tag;
          }

          $d = ['version'=>$ver,'tag'=>$tag,'zip'=>$zip,'url'=>$url];
          set_site_transient(self::TKEY, $d, self::TTL);
          return $d;
        }
      }

      // 2) Fallback: first tag if releases aren't available
      $t = wp_remote_get("https://api.github.com/repos/".self::OWNER."/".self::REPO."/tags", $ua);
      if (!is_wp_error($t) && wp_remote_retrieve_response_code($t) === 200) {
        $arr = json_decode(wp_remote_retrieve_body($t), true);
        if (is_array($arr) && !empty($arr[0]['name'])) {
          $tag = (string)$arr[0]['name'];
          $ver = ltrim($tag, 'vV');
          $zip = "https://codeload.github.com/".self::OWNER."/".self::REPO."/zip/refs/tags/".$tag;
          $url = "https://github.com/".self::OWNER."/".self::REPO."/tree/".$tag;
          $d = ['version'=>$ver,'tag'=>$tag,'zip'=>$zip,'url'=>$url];
          set_site_transient(self::TKEY, $d, self::TTL);
          return $d;
        }
      }

      return null;
    }


    static function check($tr){
      $th = wp_get_theme( get_template() ); // always the parent theme (Starscream)
      if(!$th->exists()) return $tr;
      $cur=(string)$th->get('Version'); if(!$cur) return $tr;
      $l=self::latest(); if(!$l) return $tr;
      if (version_compare($l['version'],$cur,'>')){
        $tr=is_object($tr)?$tr:new stdClass();
        $tr->response[self::SLUG]=[
          'theme'=>self::SLUG,
          'new_version'=>$l['version'],
          'url'=>$l['url'],
          'package'=>$l['zip'],
        ];
      }
      return $tr;
    }

    static function info($res,$action,$args){
      if ($action!=='theme_information'||empty($args->slug)||$args->slug!==self::SLUG) return $res;
      $l=self::latest(); if(!$l) return $res;
      return (object)[
        'name'=>'Starscream','slug'=>self::SLUG,'version'=>$l['version'],
        'author'=>'<a href="https://thebeartraxs.com">Eric Kowalewski</a>',
        'homepage'=>$l['url'],'download_link'=>$l['zip'],
        'sections'=>['description'=>'Updates served from GitHub releases/tags (public).']
      ];
    }

    // Rename extracted codeload folder (starscream-TAG) -> starscream
    static function rename($src,$remote,$upgrader,$extra){
      $is=(isset($extra['theme'])&&$extra['theme']===self::SLUG) || (isset($extra['themes'])&&in_array(self::SLUG,(array)$extra['themes'],true));
      if(!$is) return $src;
      if (basename($src)===self::SLUG) return $src;
      $dest=trailingslashit(dirname($src)).self::SLUG;
      return @rename($src,$dest)?$dest:$src;
    }
  }
  Starscream_Theme_Updater::boot();
  }

  // Bust cache when core update screen loads ("Check again") and after upgrades
  add_action('load-update-core.php', function(){ delete_transient(Starscream_Theme_Updater::TKEY); });
  add_action('upgrader_process_complete', function($upgrader,$extra){
  if (!empty($extra['type']) && $extra['type']==='theme') {
      delete_transient(Starscream_Theme_Updater::TKEY);
    }
  }, 10, 2);

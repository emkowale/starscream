<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('Starscream_Theme_Updater')) {
class Starscream_Theme_Updater {
  const SLUG='starscream'; const OWNER='emkowale'; const REPO='starscream';
  const RAW_STYLE='https://raw.githubusercontent.com/emkowale/starscream/main/style.css';
  const CACHE='starscream_update_data'; const TTL=10800; // 3h

  static function boot(){
    add_filter('pre_set_site_transient_update_themes',[__CLASS__,'inject'],10,1);
    add_filter('themes_api',[__CLASS__,'details'],10,3);
    add_action('load-update-core.php',[__CLASS__,'bust']);
    add_action('upgrader_process_complete',[__CLASS__,'bust_after'],10,2);
  }
  static function bust(){ delete_site_transient(self::CACHE); }
  static function bust_after($u,$e){ if(!empty($e['type'])&&$e['type']==='theme') self::bust(); }

  protected static function ua(){ return ['headers'=>['User-Agent'=>'WordPress/'.get_bloginfo('version').'; '.home_url('/')],'timeout'=>12]; }

  protected static function from_raw(){
    $r=wp_remote_get(self::RAW_STYLE,self::ua());
    if(is_wp_error($r)||wp_remote_retrieve_response_code($r)!==200) return null;
    $css=wp_remote_retrieve_body($r);
    if(!preg_match('/^\s*Version:\s*([0-9][0-9a-zA-Z\.\-\+_]*)/mi',$css,$m)) return null;
    $ver=trim($m[1]); $tag='v'.$ver;
    $zip="https://github.com/".self::OWNER."/".self::REPO."/releases/download/$tag/".self::SLUG."-$tag.zip";
    $url="https://github.com/".self::OWNER."/".self::REPO."/releases/tag/$tag";
    return ['version'=>$ver,'zip'=>$zip,'url'=>$url];
  }

  protected static function from_github(){
    $r=wp_remote_get("https://api.github.com/repos/".self::OWNER."/".self::REPO."/releases/latest",self::ua());
    if(is_wp_error($r)||wp_remote_retrieve_response_code($r)!==200) return null;
    $b=json_decode(wp_remote_retrieve_body($r),true); if(!is_array($b)||empty($b['tag_name'])) return null;
    $tag=$b['tag_name']; $ver=ltrim($tag,'vV'); $zip='';
    if(!empty($b['assets'])) foreach($b['assets'] as $a){ $dl=$a['browser_download_url']??''; $nm=$a['name']??''; if($dl && preg_match('/\.zip$/i',$nm)){ $zip=$dl; if(stripos($nm,self::SLUG)!==false) break; } }
    if(!$zip) $zip="https://codeload.github.com/".self::OWNER."/".self::REPO."/zip/refs/tags/$tag";
    return ['version'=>$ver,'zip'=>$zip,'url'=>$b['html_url']??''];
  }

  protected static function latest(){
    if($c=get_site_transient(self::CACHE)) return $c;
    $d=self::from_raw(); if(!$d) $d=self::from_github();
    if($d) set_site_transient(self::CACHE,$d,self::TTL);
    return $d;
  }

  static function inject($tr){
    $theme=wp_get_theme(self::SLUG); if(!$theme->exists()) return $tr;
    $cur=(string)$theme->get('Version'); if(!$cur) return $tr;
    $d=self::latest(); if(!$d) return $tr;
    if(version_compare($d['version'],$cur,'>')){
      if(!is_object($tr)) $tr=new stdClass();
      $tr->response[self::SLUG]=['theme'=>self::SLUG,'new_version'=>$d['version'],'package'=>$d['zip'],'url'=>$d['url']];
    }
    return $tr;
  }

  static function details($res,$action,$args){
    if($action!=='theme_information'||empty($args->slug)||$args->slug!==self::SLUG) return $res;
    $d=self::latest(); if(!$d) return $res;
    $t=wp_get_theme(self::SLUG);
    return (object)['name'=>$t->get('Name')?:'Starscream','slug'=>self::SLUG,'version'=>$d['version'],'homepage'=>$d['url'],'download_link'=>$d['zip'],'sections'=>['description'=>'Starscream updates']];
  }
}
Starscream_Theme_Updater::boot();
}

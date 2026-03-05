<?php
/*
 * File: inc/frontpage/hero.php
 * Description: Helpers to render hero video on Shop front page.
 * Plugin: Starscream
 * Author: Eric Kowalewski
 * Last Updated: 2025-09-02 21:35 EDT
 */
if (!defined('ABSPATH')) exit;

function starscream_get_hero_url(){
  $video = get_theme_mod('hero_video_url', '');
  if ($video === '') $video = get_theme_mod('btx_hero_video_url', '');
  if ($video !== '') return $video;

  $parent = get_option('template');
  if ($parent) {
    $mods = get_option('theme_mods_'.$parent);
    if (is_array($mods)) {
      if (!empty($mods['hero_video_url'])) return $mods['hero_video_url'];
      if (!empty($mods['btx_hero_video_url'])) return $mods['btx_hero_video_url'];
    }
  }
  return '';
}

function starscream_render_hero($url){
  if (!$url) return;
  $lower = strtolower($url);
  $is_yt = (strpos($lower,'youtube.com')!==false || strpos($lower,'youtu.be')!==false);
  $is_vi = (strpos($lower,'vimeo.com')!==false);

  echo '<section class="btx-hero">';
  if ($is_yt){
    $src=''; $p=wp_parse_url($url);
    if ($p){
      if (!empty($p['path']) && strpos($p['path'],'/embed/')!==false) $src=$url;
      elseif (!empty($p['host']) && $p['host']==='youtu.be'){
        $id = isset($p['path'])?ltrim($p['path'],'/'):''; if ($id) $src='https://www.youtube.com/embed/'.esc_attr($id);
      } else {
        parse_str(isset($p['query'])?$p['query']:'',$q);
        if (!empty($q['v'])) $src='https://www.youtube.com/embed/'.esc_attr($q['v']);
      }
    }
    if ($src){
      $sep = (strpos($src,'?')===false)?'?':'&';
      $id  = basename($src);
      $src = $src.$sep."rel=0&modestbranding=1&playsinline=1&autoplay=1&mute=1&loop=1&playlist={$id}";
      echo '<div class="btx-hero-embed"><iframe src="'.esc_url($src).'" frameborder="0" allow="autoplay; encrypted-media; picture-in-picture" allowfullscreen></iframe></div>';
    }
  } elseif ($is_vi){
    $src=''; $p=wp_parse_url($url);
    if ($p && !empty($p['path'])){
      $id = preg_replace('~^/+~','',$p['path']);
      if (preg_match('~^[0-9]+$~',$id)) $src='https://player.vimeo.com/video/'.esc_attr($id).'?autoplay=1&muted=1&loop=1&title=0&byline=0&portrait=0';
    }
    if ($src){
      echo '<div class="btx-hero-embed"><iframe src="'.esc_url($src).'" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe></div>';
    }
  } else {
    echo '<video class="btx-hero-video" autoplay muted loop playsinline><source src="'.esc_url($url).'" type="video/mp4"></source></video>';
  }
  echo '</section>';
}

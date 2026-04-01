<?php
if (!defined('ABSPATH')) exit;

if (!function_exists('starscream_google_reviews_clean_text')) {
  function starscream_google_reviews_clean_text($value, $max_len = 600) {
    $value = trim((string) wp_strip_all_tags((string) $value));
    $value = preg_replace('/\s+/', ' ', (string) $value);
    if (!is_string($value)) $value = '';
    if ($max_len > 0) {
      if (function_exists('mb_substr')) return mb_substr($value, 0, $max_len);
      return substr($value, 0, $max_len);
    }
    return $value;
  }
}

if (!function_exists('starscream_google_reviews_request_json')) {
  function starscream_google_reviews_request_json($url) {
    $response = wp_remote_get($url, [
      'timeout' => 15,
      'redirection' => 3,
    ]);
    if (is_wp_error($response)) return $response;

    $code = (int) wp_remote_retrieve_response_code($response);
    if ($code < 200 || $code > 299) {
      return new WP_Error('google_reviews_http', 'Google API HTTP error: ' . $code);
    }

    $body = wp_remote_retrieve_body($response);
    $json = json_decode((string) $body, true);
    if (!is_array($json)) {
      return new WP_Error('google_reviews_json', 'Google API returned invalid JSON.');
    }

    return $json;
  }
}

if (!function_exists('starscream_google_reviews_resolve_place')) {
  function starscream_google_reviews_resolve_place($api_key, $business_location) {
    $business_location = trim((string) $business_location);
    if ($business_location === '') {
      return new WP_Error('google_reviews_no_location', 'Google Reviews business location is empty.');
    }

    $url = add_query_arg([
      'input' => $business_location,
      'inputtype' => 'textquery',
      'fields' => 'place_id,name',
      'key' => $api_key,
    ], 'https://maps.googleapis.com/maps/api/place/findplacefromtext/json');

    $json = starscream_google_reviews_request_json($url);
    if (is_wp_error($json)) return $json;

    $status = strtoupper((string) ($json['status'] ?? ''));
    if ($status !== 'OK') {
      $message = starscream_google_reviews_clean_text((string) ($json['error_message'] ?? ''), 240);
      if ($status === 'ZERO_RESULTS') {
        return new WP_Error('google_reviews_no_place', 'Google place not found for the configured business location.');
      }
      return new WP_Error('google_reviews_find_status', 'Google Find Place failed: ' . ($message !== '' ? $message : $status));
    }

    $candidate = $json['candidates'][0] ?? null;
    if (!is_array($candidate) || empty($candidate['place_id'])) {
      return new WP_Error('google_reviews_no_place_id', 'Google place_id was not returned.');
    }

    return [
      'place_id' => sanitize_text_field((string) $candidate['place_id']),
      'place_name' => sanitize_text_field((string) ($candidate['name'] ?? '')),
    ];
  }
}

if (!function_exists('starscream_google_reviews_fetch_place_details')) {
  function starscream_google_reviews_fetch_place_details($api_key, $place_id, $reviews_sort = '') {
    $place_id = trim((string) $place_id);
    if ($place_id === '') {
      return new WP_Error('google_reviews_empty_place_id', 'Google place_id is empty.');
    }

    $args = [
      'place_id' => $place_id,
      'fields' => 'name,url,reviews,rating,user_ratings_total,formatted_address',
      'key' => $api_key,
    ];
    $reviews_sort = trim((string) $reviews_sort);
    if ($reviews_sort !== '') $args['reviews_sort'] = $reviews_sort;

    $url = add_query_arg($args, 'https://maps.googleapis.com/maps/api/place/details/json');

    $json = starscream_google_reviews_request_json($url);
    if (is_wp_error($json)) return $json;

    $status = strtoupper((string) ($json['status'] ?? ''));
    if ($status !== 'OK') {
      $message = starscream_google_reviews_clean_text((string) ($json['error_message'] ?? ''), 240);
      return new WP_Error('google_reviews_details_status', 'Google Place Details failed: ' . ($message !== '' ? $message : $status));
    }

    if (!isset($json['result']) || !is_array($json['result'])) {
      return new WP_Error('google_reviews_details_missing', 'Google Place Details did not include a valid result object.');
    }

    return $json['result'];
  }
}

if (!function_exists('starscream_google_reviews_fetch_place_details_new_api')) {
  function starscream_google_reviews_fetch_place_details_new_api($api_key, $place_id) {
    $place_id = trim((string) $place_id);
    if ($place_id === '') {
      return new WP_Error('google_reviews_new_empty_place_id', 'Google place_id is empty.');
    }

    $url = 'https://places.googleapis.com/v1/places/' . rawurlencode($place_id);
    $response = wp_remote_get($url, [
      'timeout' => 15,
      'redirection' => 3,
      'headers' => [
        'X-Goog-Api-Key' => $api_key,
        'X-Goog-FieldMask' => 'id,displayName.text,formattedAddress,googleMapsUri,rating,userRatingCount,reviews.rating,reviews.relativePublishTimeDescription,reviews.publishTime,reviews.text.text,reviews.authorAttribution.displayName,reviews.authorAttribution.uri',
      ],
    ]);
    if (is_wp_error($response)) return $response;

    $code = (int) wp_remote_retrieve_response_code($response);
    if ($code < 200 || $code > 299) {
      return new WP_Error('google_reviews_new_http', 'Google Places API (new) HTTP error: ' . $code);
    }

    $body = wp_remote_retrieve_body($response);
    $json = json_decode((string) $body, true);
    if (!is_array($json)) {
      return new WP_Error('google_reviews_new_json', 'Google Places API (new) returned invalid JSON.');
    }

    if (isset($json['error']) && is_array($json['error'])) {
      $message = starscream_google_reviews_clean_text((string) ($json['error']['message'] ?? ''), 240);
      return new WP_Error('google_reviews_new_error', 'Google Places API (new) error: ' . ($message !== '' ? $message : 'Unknown error'));
    }

    $raw_reviews = isset($json['reviews']) && is_array($json['reviews']) ? $json['reviews'] : [];
    $reviews = [];
    foreach ($raw_reviews as $row) {
      if (!is_array($row)) continue;
      $publish_time = isset($row['publishTime']) ? strtotime((string) $row['publishTime']) : 0;
      $reviews[] = [
        'author_name' => starscream_google_reviews_clean_text((string) ($row['authorAttribution']['displayName'] ?? 'Google User'), 80),
        'author_url' => esc_url_raw((string) ($row['authorAttribution']['uri'] ?? '')),
        'rating' => (int) ($row['rating'] ?? 0),
        'text' => starscream_google_reviews_clean_text((string) ($row['text']['text'] ?? ''), 700),
        'relative_time_description' => starscream_google_reviews_clean_text((string) ($row['relativePublishTimeDescription'] ?? ''), 80),
        'time' => $publish_time ? (int) $publish_time : 0,
      ];
    }

    return [
      'name' => starscream_google_reviews_clean_text((string) ($json['displayName']['text'] ?? ''), 120),
      'formatted_address' => starscream_google_reviews_clean_text((string) ($json['formattedAddress'] ?? ''), 160),
      'url' => esc_url_raw((string) ($json['googleMapsUri'] ?? '')),
      'rating' => (float) ($json['rating'] ?? 0),
      'user_ratings_total' => (int) ($json['userRatingCount'] ?? 0),
      'reviews' => $reviews,
    ];
  }
}

if (!function_exists('starscream_google_reviews_text_search_candidates')) {
  function starscream_google_reviews_text_search_candidates($api_key, $query, $limit = 3) {
    $query = trim((string) $query);
    if ($query === '') return [];

    $url = add_query_arg([
      'query' => $query,
      'key' => $api_key,
    ], 'https://maps.googleapis.com/maps/api/place/textsearch/json');

    $json = starscream_google_reviews_request_json($url);
    if (is_wp_error($json)) return [];

    $status = strtoupper((string) ($json['status'] ?? ''));
    if ($status !== 'OK' && $status !== 'ZERO_RESULTS') return [];

    $rows = isset($json['results']) && is_array($json['results']) ? $json['results'] : [];
    $candidates = [];
    foreach ($rows as $row) {
      if (!is_array($row) || empty($row['place_id'])) continue;

      $types = isset($row['types']) && is_array($row['types']) ? $row['types'] : [];
      $is_place = in_array('establishment', $types, true) || in_array('point_of_interest', $types, true) || in_array('store', $types, true);
      if (!$is_place) continue;

      $candidates[] = [
        'place_id' => sanitize_text_field((string) $row['place_id']),
        'place_name' => sanitize_text_field((string) ($row['name'] ?? '')),
        'score' => (int) ($row['user_ratings_total'] ?? 0),
      ];
    }

    if (count($candidates) > 1) {
      usort($candidates, function ($a, $b) {
        return ((int) ($b['score'] ?? 0)) <=> ((int) ($a['score'] ?? 0));
      });
    }

    if ($limit < 1) $limit = 1;
    return array_slice($candidates, 0, $limit);
  }
}

if (!function_exists('starscream_google_reviews_build_queries')) {
  function starscream_google_reviews_build_queries($location) {
    $location = starscream_google_reviews_clean_text((string) $location, 240);
    if ($location === '') return [];

    $queries = [$location];
    $parts = array_values(array_filter(array_map('trim', explode(',', $location))));
    if (count($parts) >= 3) {
      $queries[] = implode(', ', array_slice($parts, 1));
    }
    if (count($parts) >= 4) {
      $queries[] = $parts[0] . ', ' . implode(', ', array_slice($parts, -2));
    }
    if (count($parts) >= 2) {
      $queries[] = implode(', ', array_slice($parts, 0, 2));
    }

    $seen = [];
    $unique = [];
    foreach ($queries as $q) {
      $key = strtolower(trim((string) $q));
      if ($key === '' || isset($seen[$key])) continue;
      $seen[$key] = true;
      $unique[] = trim((string) $q);
    }
    return $unique;
  }
}

if (!function_exists('starscream_google_reviews_extract_five_star_reviews')) {
  function starscream_google_reviews_extract_five_star_reviews($reviews, $max_reviews = 7) {
    if (!is_array($reviews)) return [];
    if ($max_reviews < 1) $max_reviews = 1;

    $result = [];
    foreach ($reviews as $row) {
      if (!is_array($row)) continue;
      if ((int) ($row['rating'] ?? 0) !== 5) continue;

      $text = starscream_google_reviews_clean_text((string) ($row['text'] ?? ''), 700);
      if ($text === '') $text = '5-star Google review.';

      $result[] = [
        'author_name' => starscream_google_reviews_clean_text((string) ($row['author_name'] ?? 'Google User'), 80),
        'author_url' => esc_url_raw((string) ($row['author_url'] ?? '')),
        'text' => $text,
        'rating' => 5,
        'relative_time' => starscream_google_reviews_clean_text((string) ($row['relative_time_description'] ?? ''), 80),
      ];

      if (count($result) >= $max_reviews) break;
    }

    return $result;
  }
}

if (!function_exists('starscream_google_reviews_merge_reviews')) {
  function starscream_google_reviews_merge_reviews($primary, $secondary) {
    $primary = is_array($primary) ? $primary : [];
    $secondary = is_array($secondary) ? $secondary : [];

    $seen = [];
    $merged = [];
    foreach ([$primary, $secondary] as $list) {
      foreach ($list as $row) {
        if (!is_array($row)) continue;
        $key = strtolower(trim((string) ($row['author_name'] ?? '')))
          . '|' . trim((string) ($row['time'] ?? ''))
          . '|' . (string) ((int) ($row['rating'] ?? 0))
          . '|' . strtolower(trim((string) ($row['text'] ?? '')));
        if ($key === '|' || isset($seen[$key])) continue;
        $seen[$key] = true;
        $merged[] = $row;
      }
    }

    return $merged;
  }
}

if (!function_exists('starscream_google_reviews_fetch_candidate_snapshot')) {
  function starscream_google_reviews_fetch_candidate_snapshot($api_key, $candidate, $max_reviews) {
    if (!is_array($candidate) || empty($candidate['place_id'])) {
      return new WP_Error('google_reviews_invalid_candidate', 'Google Reviews candidate is missing a place_id.');
    }

    $place_id = trim((string) $candidate['place_id']);
    if ($place_id === '') {
      return new WP_Error('google_reviews_invalid_candidate', 'Google Reviews candidate is missing a place_id.');
    }

    $details_primary = starscream_google_reviews_fetch_place_details($api_key, $place_id, 'most_relevant');
    $details_new_api = starscream_google_reviews_fetch_place_details_new_api($api_key, $place_id);

    if (is_wp_error($details_primary) && is_wp_error($details_new_api)) {
      return new WP_Error('google_reviews_no_details', 'Unable to fetch Google Place Details for the configured location.');
    }

    $details_base = !is_wp_error($details_primary) ? $details_primary : $details_new_api;
    $reviews_primary = (!is_wp_error($details_primary) && isset($details_primary['reviews']) && is_array($details_primary['reviews'])) ? $details_primary['reviews'] : [];
    $reviews_pool = $reviews_primary;

    if (!is_wp_error($details_primary) && count($reviews_primary) < $max_reviews) {
      $details_newest = starscream_google_reviews_fetch_place_details($api_key, $place_id, 'newest');
      if (!is_wp_error($details_newest) && isset($details_newest['reviews']) && is_array($details_newest['reviews'])) {
        $reviews_pool = starscream_google_reviews_merge_reviews($reviews_pool, $details_newest['reviews']);
      }
    }

    if (!is_wp_error($details_primary) && count($reviews_pool) < $max_reviews) {
      $details_default = starscream_google_reviews_fetch_place_details($api_key, $place_id, '');
      if (!is_wp_error($details_default) && isset($details_default['reviews']) && is_array($details_default['reviews'])) {
        $reviews_pool = starscream_google_reviews_merge_reviews($reviews_pool, $details_default['reviews']);
      }
    }

    if (!is_wp_error($details_new_api) && isset($details_new_api['reviews']) && is_array($details_new_api['reviews'])) {
      $reviews_pool = starscream_google_reviews_merge_reviews($reviews_pool, $details_new_api['reviews']);
    }

    return [
      'place_id' => $place_id,
      'details' => $details_base,
      'reviews' => starscream_google_reviews_extract_five_star_reviews($reviews_pool, $max_reviews),
      'review_pool' => $reviews_pool,
      'user_ratings_total' => (int) ($details_base['user_ratings_total'] ?? 0),
    ];
  }
}

if (!function_exists('starscream_google_reviews_get_data')) {
  function starscream_google_reviews_get_data() {
    $api_key = starscream_google_reviews_clean_text((string) get_theme_mod('tbt_google_reviews_api_key', ''), 240);
    $location = starscream_google_reviews_clean_text((string) get_theme_mod('tbt_google_reviews_business_location', ''), 240);
    $place_id_override = starscream_google_reviews_clean_text((string) get_theme_mod('tbt_google_reviews_place_id', ''), 240);

    $max_reviews = absint(get_theme_mod('tbt_google_reviews_max_reviews', 7));
    if ($max_reviews < 1) $max_reviews = 1;
    if ($max_reviews > 12) $max_reviews = 12;

    $cache_minutes = absint(get_theme_mod('tbt_google_reviews_cache_minutes', 120));
    if ($cache_minutes < 5) $cache_minutes = 5;
    if ($cache_minutes > 1440) $cache_minutes = 1440;

    if ($api_key === '') {
      return new WP_Error('google_reviews_missing_key', 'Google Reviews API key is not set.');
    }
    if ($place_id_override === '' && $location === '') {
      return new WP_Error('google_reviews_missing_location', 'Google Reviews business location is not set.');
    }

    $cache_key = 'btx_greviews_' . md5(implode('|', [
      'v6',
      $api_key,
      $location,
      $place_id_override,
      (string) $max_reviews,
    ]));
    $cached = get_transient($cache_key);
    if (is_array($cached) && isset($cached['reviews']) && is_array($cached['reviews'])) {
      return $cached;
    }

    $selected_details = null;
    $selected_reviews = [];
    $selected_place_id = '';
    $selected_pool = [];
    $selected_rating_total = 0;

    if ($place_id_override !== '') {
      $snapshot = starscream_google_reviews_fetch_candidate_snapshot($api_key, [
        'place_id' => $place_id_override,
        'place_name' => '',
      ], $max_reviews);
      if (is_wp_error($snapshot)) {
        return $snapshot;
      }

      $selected_details = $snapshot['details'];
      $selected_reviews = $snapshot['reviews'];
      $selected_place_id = (string) $snapshot['place_id'];
      $selected_pool = $snapshot['review_pool'];
      $selected_rating_total = (int) $snapshot['user_ratings_total'];
    } else {
      $snapshot = null;
      if ($location !== '') {
        $resolved_place = starscream_google_reviews_resolve_place($api_key, $location);
        if (!is_wp_error($resolved_place)) {
          $snapshot = starscream_google_reviews_fetch_candidate_snapshot($api_key, $resolved_place, $max_reviews);
        }
      }

      if (is_array($snapshot) && isset($snapshot['details']) && is_array($snapshot['details'])) {
        $selected_details = $snapshot['details'];
        $selected_reviews = $snapshot['reviews'];
        $selected_place_id = (string) $snapshot['place_id'];
        $selected_pool = $snapshot['review_pool'];
        $selected_rating_total = (int) $snapshot['user_ratings_total'];
      } else {
        $candidates = [];
        foreach (starscream_google_reviews_build_queries($location) as $query) {
          $search_candidates = starscream_google_reviews_text_search_candidates($api_key, $query, 3);
          foreach ($search_candidates as $candidate) {
            $candidates[] = $candidate;
          }
        }

        if (!$candidates) {
          return new WP_Error('google_reviews_no_place_candidates', 'No valid Google place candidates were returned for this location.');
        }

        $unique_candidates = [];
        foreach ($candidates as $candidate) {
          if (!is_array($candidate) || empty($candidate['place_id'])) continue;
          $pid = (string) $candidate['place_id'];
          if (isset($unique_candidates[$pid])) continue;
          $unique_candidates[$pid] = [
            'place_id' => $pid,
            'place_name' => sanitize_text_field((string) ($candidate['place_name'] ?? '')),
          ];
        }
        $candidates = array_values($unique_candidates);
        if (!$candidates) {
          return new WP_Error('google_reviews_no_place_candidates', 'No valid Google place candidates were returned for this location.');
        }

        $best_five = -1;
        $best_sample = -1;
        $best_ratings_total = -1;
        foreach ($candidates as $candidate) {
          $snapshot = starscream_google_reviews_fetch_candidate_snapshot($api_key, $candidate, $max_reviews);
          if (is_wp_error($snapshot)) continue;

          $five_count = count($snapshot['reviews']);
          $sample_count = count($snapshot['review_pool']);
          $candidate_ratings_total = (int) $snapshot['user_ratings_total'];

          if (
            $five_count > $best_five
            || ($five_count === $best_five && $sample_count > $best_sample)
            || ($five_count === $best_five && $sample_count === $best_sample && $candidate_ratings_total > $best_ratings_total)
          ) {
            $best_five = $five_count;
            $best_sample = $sample_count;
            $best_ratings_total = $candidate_ratings_total;
            $selected_details = $snapshot['details'];
            $selected_reviews = $snapshot['reviews'];
            $selected_place_id = (string) $snapshot['place_id'];
            $selected_pool = $snapshot['review_pool'];
            $selected_rating_total = $candidate_ratings_total;
          }
        }
      }
    }

    if (!is_array($selected_details)) {
      return new WP_Error('google_reviews_no_details', 'Unable to fetch Google Place Details for the configured location.');
    }

    $data = [
      'place_id' => $selected_place_id,
      'place_name' => starscream_google_reviews_clean_text((string) ($selected_details['name'] ?? ''), 120),
      'place_address' => starscream_google_reviews_clean_text((string) ($selected_details['formatted_address'] ?? ''), 160),
      'place_url' => esc_url_raw((string) ($selected_details['url'] ?? '')),
      'reviews' => $selected_reviews,
      'debug_user_ratings_total' => $selected_rating_total,
      'debug_sample_total' => is_array($selected_pool) ? count($selected_pool) : 0,
      'debug_sample_five' => is_array($selected_pool)
        ? count(array_filter($selected_pool, function ($row) { return is_array($row) && ((int) ($row['rating'] ?? 0) === 5); }))
        : 0,
    ];

    set_transient($cache_key, $data, $cache_minutes * MINUTE_IN_SECONDS);

    return $data;
  }
}

if (!function_exists('starscream_google_reviews_admin_notice')) {
  function starscream_google_reviews_admin_notice($message) {
    if (!current_user_can('edit_theme_options')) return '';
    return '<div class="btx-google-reviews btx-google-reviews--notice"><p>' . esc_html((string) $message) . '</p></div>';
  }
}

if (!function_exists('starscream_render_tbt_google_reviews_shortcode')) {
  function starscream_render_tbt_google_reviews_shortcode($atts = [], $content = null, $shortcode_tag = '') {
    $data = starscream_google_reviews_get_data();
    if (is_wp_error($data)) {
      return starscream_google_reviews_admin_notice($data->get_error_message());
    }

    $reviews = isset($data['reviews']) && is_array($data['reviews']) ? $data['reviews'] : [];
    if (!$reviews) {
      $place_name = starscream_google_reviews_clean_text((string) ($data['place_name'] ?? ''), 120);
      $place_address = starscream_google_reviews_clean_text((string) ($data['place_address'] ?? ''), 160);
      $detail = $place_name !== '' ? (' for "' . $place_name . '"') : '';
      if ($place_address !== '') $detail .= ' at "' . $place_address . '"';
      $sample_total = (int) ($data['debug_sample_total'] ?? 0);
      $sample_five = (int) ($data['debug_sample_five'] ?? 0);
      $ratings_total = (int) ($data['debug_user_ratings_total'] ?? 0);
      $place_id = starscream_google_reviews_clean_text((string) ($data['place_id'] ?? ''), 240);
      $diagnostic = ' Sampled reviews: ' . $sample_total . '. 5-star in sample: ' . $sample_five . '.';
      $diagnostic .= ' Total ratings on place: ' . $ratings_total . '.';
      if ($place_id !== '') $diagnostic .= ' Place ID used: ' . $place_id . '.';
      return starscream_google_reviews_admin_notice('Google returned no 5-star reviews' . $detail . '.' . $diagnostic . ' Try setting an exact Place ID for this business.');
    }

    $heading = starscream_google_reviews_clean_text((string) get_theme_mod('tbt_google_reviews_heading', 'From Our Customers'), 80);
    $subheading = starscream_google_reviews_clean_text((string) get_theme_mod('tbt_google_reviews_subheading', 'Google Reviews'), 80);
    $cta_label = starscream_google_reviews_clean_text((string) get_theme_mod('tbt_google_reviews_cta_label', 'Read More On Google'), 50);
    $place_url = esc_url((string) ($data['place_url'] ?? ''));

    $autoplay_seconds = absint(get_theme_mod('tbt_google_reviews_autoplay_seconds', 5));
    if ($autoplay_seconds > 20) $autoplay_seconds = 20;
    $autoplay_ms = $autoplay_seconds > 0 ? $autoplay_seconds * 1000 : 0;

    ob_start();
    ?>
    <div class="btx-google-reviews" data-btx-google-reviews="1" data-autoplay-ms="<?php echo esc_attr((string) $autoplay_ms); ?>">
      <section class="shopify-section section-testimonials" role="region" aria-label="Google reviews">
        <div class="row full-width-row-full">
          <div class="small-12 columns">
            <div class="testimonials section-spacing-padding section-spacing--disable-top" style="--color-bg:rgba(0,0,0,0); --color-text:#151515; --dot-speed: <?php echo esc_attr((string) max($autoplay_seconds, 1)); ?>s;">
              <div class="testimonials__inner text-large">
                <?php if ($subheading !== ''): ?>
                  <p class="subheading btx-google-reviews__subheading"><?php echo esc_html($subheading); ?></p>
                <?php endif; ?>
                <?php if ($heading !== ''): ?>
                  <h3 class="btx-google-reviews__heading"><?php echo esc_html($heading); ?></h3>
                <?php endif; ?>

                <div class="testimonials__carousel carousel custom-dots" data-autoplay="<?php echo esc_attr((string) $autoplay_ms); ?>" data-dots="<?php echo count($reviews) > 1 ? 'true' : 'false'; ?>" data-align="center" data-dot-style="dots">
                  <?php foreach ($reviews as $index => $review): ?>
                    <?php $active = $index === 0; ?>
                    <div class="testimonials__testimonial carousel__slide<?php echo $active ? ' is-active' : ''; ?>" data-review-index="<?php echo esc_attr((string) $index); ?>" aria-hidden="<?php echo $active ? 'false' : 'true'; ?>">
                      <div class="testimonials__testimonial-inner">
                        <div class="star-rating" style="--star-rating: 5;"></div>
                        <p><?php echo esc_html((string) $review['text']); ?></p>
                        <div class="testimonials__author">
                          <?php if (!empty($review['author_url'])): ?>
                            <a href="<?php echo esc_url((string) $review['author_url']); ?>" target="_blank" rel="noopener noreferrer nofollow">
                              <?php echo esc_html((string) $review['author_name']); ?>
                            </a>
                          <?php else: ?>
                            <?php echo esc_html((string) $review['author_name']); ?>
                          <?php endif; ?>
                        </div>
                        <?php if (!empty($review['relative_time'])): ?>
                          <div class="btx-google-reviews__time"><?php echo esc_html((string) $review['relative_time']); ?></div>
                        <?php endif; ?>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>

                <?php if (count($reviews) > 1): ?>
                  <ol class="flickity-page-dots dot-style--dots" role="tablist" aria-label="Review navigation">
                    <?php foreach ($reviews as $index => $review): ?>
                      <?php $selected = $index === 0; ?>
                      <li class="dot<?php echo $selected ? ' is-selected' : ''; ?>" role="presentation">
                        <button
                          type="button"
                          class="btx-google-reviews__dot<?php echo $selected ? ' is-active' : ''; ?>"
                          role="tab"
                          aria-selected="<?php echo $selected ? 'true' : 'false'; ?>"
                          aria-label="<?php echo esc_attr('Go to review ' . ($index + 1)); ?>"
                          data-review-dot="<?php echo esc_attr((string) $index); ?>">
                        </button>
                      </li>
                    <?php endforeach; ?>
                  </ol>
                <?php endif; ?>

                <?php if ($place_url !== '' && $cta_label !== ''): ?>
                  <p class="btx-google-reviews__cta-wrap">
                    <a class="btx-google-reviews__cta" href="<?php echo esc_url($place_url); ?>" target="_blank" rel="noopener noreferrer">
                      <?php echo esc_html($cta_label); ?>
                    </a>
                  </p>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
    <?php
    return trim((string) ob_get_clean());
  }
}

add_shortcode('tbt-googlereviews', 'starscream_render_tbt_google_reviews_shortcode');

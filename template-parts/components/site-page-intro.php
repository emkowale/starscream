<?php
/**
 * Reusable page intro component.
 *
 * Usage:
 * get_template_part('template-parts/components/site-page-intro', null, [
 *   'eyebrow' => 'About',
 *   'title'   => get_the_title(),
 *   'lead'    => 'Short supporting paragraph.',
 * ]);
 */
if (!defined('ABSPATH')) exit;

$eyebrow = isset($args['eyebrow']) ? trim((string) $args['eyebrow']) : '';
$title   = isset($args['title']) ? trim((string) $args['title']) : '';
$lead    = isset($args['lead']) ? trim((string) $args['lead']) : '';

if ($title === '') {
  $title = get_the_title();
}
?>
<header class="site-page-intro site-container">
  <?php if ($eyebrow !== '') : ?>
    <p class="site-page-intro__eyebrow"><?php echo esc_html($eyebrow); ?></p>
  <?php endif; ?>

  <h1 class="site-page-intro__title"><?php echo esc_html($title); ?></h1>

  <?php if ($lead !== '') : ?>
    <p class="site-page-intro__lead"><?php echo esc_html($lead); ?></p>
  <?php endif; ?>
</header>

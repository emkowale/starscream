<?php
if (!defined('ABSPATH')) exit;

/** Shared helpers for Starscream Customizer modules */
if (!function_exists('starscream_customizer_section_id')) {
  function starscream_customizer_section_id() {
    return defined('STARSCREAM_CUSTOMIZER_SECTION') ? STARSCREAM_CUSTOMIZER_SECTION : 'beartraxs_colors';
  }
}

if (!function_exists('starscream_allowed_fonts')) {
  function starscream_allowed_fonts() {
    return ['Inter','Roboto','Open Sans','Montserrat','Lato','Oswald','Raleway','Poppins','Nunito','PT Sans'];
  }
}

if (!function_exists('starscream_sanitize_font')) {
  function starscream_sanitize_font($value) {
    return in_array($value, starscream_allowed_fonts(), true) ? $value : 'Inter';
  }
}

if (!function_exists('starscream_sanitize_checkbox')) {
  function starscream_sanitize_checkbox($value) {
    return $value === true || $value === '1' || $value === 1;
  }
}

if (class_exists('WP_Customize_Control') && !class_exists('Starscream_Customize_Section_Divider_Control')) {
  class Starscream_Customize_Section_Divider_Control extends WP_Customize_Control {
    public $type = 'starscream_section_divider';

    public function render_content() {
      ?>
      <div class="btx-customizer-divider" role="separator" aria-label="<?php echo esc_attr((string) $this->label); ?>">
        <span class="btx-customizer-divider__label"><?php echo esc_html((string) $this->label); ?></span>
      </div>
      <?php
    }
  }
}

if (!function_exists('starscream_add_customizer_divider')) {
  function starscream_add_customizer_divider($wp_customize, $section, $id, $label, $priority) {
    if (!($wp_customize instanceof WP_Customize_Manager)) return;
    if (!class_exists('Starscream_Customize_Section_Divider_Control')) return;

    $wp_customize->add_setting($id, [
      'default' => '',
      'sanitize_callback' => 'sanitize_text_field',
    ]);

    $wp_customize->add_control(new Starscream_Customize_Section_Divider_Control($wp_customize, $id, [
      'label' => $label,
      'section' => $section,
      'priority' => $priority,
    ]));
  }
}

add_action('customize_controls_print_styles', function () {
  ?>
  <style id="starscream-customizer-divider-styles">
    .customize-control-starscream_section_divider {
      margin-top: 14px;
      margin-bottom: 8px;
      padding-top: 12px;
      border-top: 4px solid #1d2327;
    }
    .btx-customizer-divider__label {
      display: block;
      font-size: 12px;
      font-weight: 700;
      letter-spacing: .04em;
      text-transform: uppercase;
      color: #1d2327;
    }
  </style>
  <?php
}, 5);

if (!function_exists('starscream_announcement_icon_library')) {
  function starscream_announcement_icon_library() {
    return [
      '' => [
        'label' => 'No Icon',
        'svg'   => '',
      ],
      'bi_truck' => [
        'label' => 'Truck',
        'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-truck" viewBox="0 0 16 16"><path d="M0 1.5A1.5 1.5 0 0 1 1.5 0h9A1.5 1.5 0 0 1 12 1.5V3h1.405a1.5 1.5 0 0 1 1.425 1.026l1.07 3.269a.5.5 0 0 1 .025.155v2.3a1.5 1.5 0 0 1-1.5 1.5H14a2 2 0 1 1-4 0H6a2 2 0 1 1-4 0h-.5A1.5 1.5 0 0 1 0 9.75zm1.5-.5a.5.5 0 0 0-.5.5v8.25a.5.5 0 0 0 .5.5H2a2 2 0 1 1 4 0h4a2 2 0 1 1 4 0h.425a.5.5 0 0 0 .5-.5V8h-3.5A1.5 1.5 0 0 1 9.925 6.5V1zm10.5 3H10.925v2.5a.5.5 0 0 0 .5.5h3.11zM4 14.25a1 1 0 1 0 0-2 1 1 0 0 0 0 2m8 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2"/></svg>',
      ],
      'bi_gift' => [
        'label' => 'Gift',
        'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-gift" viewBox="0 0 16 16"><path d="M3 2.5a1.5 1.5 0 0 1 3 0c0 .535-.195.857-.465 1.175A9 9 0 0 1 5.41 4.5H3zM2 4v1h5V2.5a2.5 2.5 0 0 0-5 0V4Zm13 0V2.5a2.5 2.5 0 0 0-5 0V5h5V4Zm-1.535-.325c.27-.318.465-.64.465-1.175a1.5 1.5 0 1 0-3 0h2.41a9 9 0 0 0 .125-.825"/><path d="M15 6H1v3h14z"/><path d="M1 9v5.5A1.5 1.5 0 0 0 2.5 16H7V9zm8 7h4.5a1.5 1.5 0 0 0 1.5-1.5V9H9z"/></svg>',
      ],
      'bi_shield_check' => [
        'label' => 'Shield Check',
        'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-shield-check" viewBox="0 0 16 16"><path d="M5.338 1.59a61 61 0 0 0-2.837 1.38C1.439 3.537 1 4.52 1 5.5c0 2.667.87 4.607 1.553 5.834a13 13 0 0 0 2.043 2.528c.28.28.553.52.797.716.24.192.451.343.616.445.165-.102.377-.253.617-.445.243-.195.516-.436.797-.716a13 13 0 0 0 2.043-2.528C10.13 10.107 11 8.167 11 5.5c0-.98-.44-1.963-1.501-2.53A61 61 0 0 0 6.662 1.59a1.7 1.7 0 0 0-1.324 0"/><path d="M10.854 5.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 7.793l2.646-2.647a.5.5 0 0 1 .708 0"/></svg>',
      ],
      'bi_patch_check' => [
        'label' => 'Patch Check',
        'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-patch-check" viewBox="0 0 16 16"><path d="M10.067.87a2.89 2.89 0 0 0-2.188 0l-.468.186a2.89 2.89 0 0 1-1.897.06l-.5-.15a2.89 2.89 0 0 0-2.023.39L2.562 1.6a2.89 2.89 0 0 1-1.711.545l-.554.001a2.89 2.89 0 0 0-1.558 3.845l.196.495a2.89 2.89 0 0 1 0 2.04l-.196.495a2.89 2.89 0 0 0 1.558 3.845l.554.001a2.89 2.89 0 0 1 1.711.545l.43.243a2.89 2.89 0 0 0 2.023.39l.5-.15a2.89 2.89 0 0 1 1.897.06l.468.186a2.89 2.89 0 0 0 2.188 0l.468-.186a2.89 2.89 0 0 1 1.897-.06l.5.15a2.89 2.89 0 0 0 2.023-.39l.43-.243a2.89 2.89 0 0 1 1.711-.545l.554-.001a2.89 2.89 0 0 0 1.558-3.845l-.196-.495a2.89 2.89 0 0 1 0-2.04l.196-.495a2.89 2.89 0 0 0-1.558-3.845l-.554-.001a2.89 2.89 0 0 1-1.711-.545l-.43-.243a2.89 2.89 0 0 0-2.023-.39l-.5.15a2.89 2.89 0 0 1-1.897-.06z"/><path d="M10.854 5.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 7.793l2.646-2.647a.5.5 0 0 1 .708 0"/></svg>',
      ],
      'bi_cash_coin' => [
        'label' => 'Cash Coin',
        'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-cash-coin" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M11 15a4 4 0 1 0 0-8 4 4 0 0 0 0 8m0 1a5 5 0 1 0 0-10 5 5 0 0 0 0 10"/><path d="M13 10.5a2 2 0 0 1-1.5 1.936v.564a.5.5 0 0 1-1 0v-.564A2 2 0 0 1 9 10.5a.5.5 0 0 1 1 0 1 1 0 1 0 1-1 2 2 0 0 1-.5-3.936V5a.5.5 0 0 1 1 0v.564A2 2 0 0 1 13 7.5a.5.5 0 0 1-1 0 1 1 0 1 0-1 1 2 2 0 0 1 2 2"/><path d="M1 4a.5.5 0 0 1 .5-.5h3A.5.5 0 0 1 5 4v1h.5a.5.5 0 0 1 0 1h-.5v1h.5a.5.5 0 0 1 0 1h-.5v1h.5a.5.5 0 0 1 0 1h-.5v1a.5.5 0 0 1-.5.5h-3A.5.5 0 0 1 1 12zm3 1H2v1h2zm0 2H2v1h2zm0 2H2v1h2zm0 2H2v1h2z"/></svg>',
      ],
      'bi_percent' => [
        'label' => 'Percent',
        'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-percent" viewBox="0 0 16 16"><path d="M13.442 2.558a.5.5 0 0 1 0 .707l-10 10a.5.5 0 0 1-.708-.707l10-10a.5.5 0 0 1 .708 0"/><path d="M6.5 2.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5m-1.5 2.5a1.5 1.5 0 1 1 3 0 1.5 1.5 0 0 1-3 0m6 4a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5m-1.5 2.5a1.5 1.5 0 1 1 3 0 1.5 1.5 0 0 1-3 0"/></svg>',
      ],
      'bi_bell' => [
        'label' => 'Bell',
        'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-bell" viewBox="0 0 16 16"><path d="M8 16a2 2 0 0 0 1.985-1.75h-3.97A2 2 0 0 0 8 16M8 1.918a1 1 0 0 0-.9.55A5 5 0 0 0 3 7.022V10.5l-.87 1.088A1.5 1.5 0 0 0 3.3 14h9.4a1.5 1.5 0 0 0 1.17-2.412L13 10.5V7.022a5 5 0 0 0-4.1-4.554 1 1 0 0 0-.9-.55M5 7.022a3 3 0 1 1 6 0V11a1 1 0 0 0 .219.625l1.012 1.264a.5.5 0 0 1-.39.811H4.159a.5.5 0 0 1-.39-.81L4.78 11.624A1 1 0 0 0 5 11z"/></svg>',
      ],
      'bi_telephone' => [
        'label' => 'Telephone',
        'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-telephone" viewBox="0 0 16 16"><path d="M3.654 1.328a.68.68 0 0 1 .737-.137l2.522.945a.68.68 0 0 1 .411.786l-.452 2.264a.68.68 0 0 1-.288.423l-1.093.73q.397.794 1.207 1.605.81.81 1.605 1.207l.73-1.093a.68.68 0 0 1 .423-.288l2.264-.452a.68.68 0 0 1 .786.411l.945 2.522a.68.68 0 0 1-.137.737l-1.145 1.145a2 2 0 0 1-2.616.186 19 19 0 0 1-2.932-2.488A19 19 0 0 1 2.332 5.41a2 2 0 0 1 .186-2.616z"/></svg>',
      ],
      'bi_tag' => [
        'label' => 'Tag',
        'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-tag" viewBox="0 0 16 16"><path d="M6 0a1 1 0 0 0-.707.293L.586 5A2 2 0 0 0 0 6.414V10a1 1 0 0 0 1 1h3.586A2 2 0 0 0 6 10.414L10.707 5.707A1 1 0 0 0 11 5V1a1 1 0 0 0-1-1zM4.586 9a1 1 0 0 1-.707.293H1V6.414a1 1 0 0 1 .293-.707L5.414 1.586A.5.5 0 0 1 5.768 1.5H9.5v3.732a.5.5 0 0 1-.146.354z"/><path d="M8 0h3a1 1 0 0 1 1 1v3.5a.5.5 0 0 0 1 0V1a2 2 0 0 0-2-2H8a.5.5 0 0 0 0 1"/><path d="M10.707 5.707 6 10.414A2 2 0 0 1 4.586 11H1a1 1 0 0 0 0 2h3.586A3 3 0 0 0 7 12.121l4.707-4.707a.5.5 0 0 0-.707-.707"/></svg>',
      ],
      'bi_cart' => [
        'label' => 'Cart',
        'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-cart" viewBox="0 0 16 16"><path d="M0 1.5A.5.5 0 0 1 .5 1h1.11a.5.5 0 0 1 .485.379L2.89 4.5H14.5a.5.5 0 0 1 .49.598l-1.5 8A.5.5 0 0 1 13 13.5H4a.5.5 0 0 1-.491-.402L1.61 2H.5a.5.5 0 0 1-.5-.5M3.102 5.5l1.313 7h8.17l1.313-7z"/><path d="M5 12a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5m7 2.5a2.5 2.5 0 1 1 5 0 2.5 2.5 0 0 1-5 0"/></svg>',
      ],
      'bi_bag' => [
        'label' => 'Bag',
        'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-bag" viewBox="0 0 16 16"><path d="M8 1.5a2.5 2.5 0 0 1 2.455 2.03h-4.91A2.5 2.5 0 0 1 8 1.5M4.5 4.5a1 1 0 0 0-1 1V14a1 1 0 0 0 1 1h7a1 1 0 0 0 1-1V5.5a1 1 0 0 0-1-1h-7zM8 0a4 4 0 0 0-3.92 3.5H3.5A2.5 2.5 0 0 0 1 6v8a2.5 2.5 0 0 0 2.5 2.5h7A2.5 2.5 0 0 0 13 14V6a2.5 2.5 0 0 0-2.5-2.5h-.58A4 4 0 0 0 8 0"/><path d="M8 6.5a.75.75 0 0 1 .75.75V8a.75.75 0 0 1-1.5 0v-.75A.75.75 0 0 1 8 6.5"/></svg>',
      ],
      'bi_lightning_charge' => [
        'label' => 'Lightning',
        'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-lightning-charge" viewBox="0 0 16 16"><path d="M11.251.019a.65.65 0 0 1 .624.105.65.65 0 0 1 .214.597l-.578 3.469h2.07a.65.65 0 0 1 .468 1.107L8.113 11.46a.65.65 0 0 1-1.084-.374l-.371-3.348h-2.12a.65.65 0 0 1-.48-1.088L10.155.22a.65.65 0 0 1 1.096-.2M6.06 6.438h1.326a.65.65 0 0 1 .646.578l.187 1.683 3.8-3.808h-1.28a.65.65 0 0 1-.642-.757l.29-1.739z"/></svg>',
      ],
      'bi_megaphone' => [
        'label' => 'Megaphone',
        'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-megaphone" viewBox="0 0 16 16"><path d="M13 2.5a1.5 1.5 0 0 0-2.326-1.257L4.818 4.884A1.5 1.5 0 0 0 3.5 4h-2A1.5 1.5 0 0 0 0 5.5v5A1.5 1.5 0 0 0 1.5 12h.655c.273.66.748 1.544 1.48 2.388A4.5 4.5 0 0 0 7 16a.5.5 0 0 0 .5-.5v-4.663l3.174 1.92A1.5 1.5 0 0 0 13 11.5zm-1 0v9a.5.5 0 0 1-.758.429L5.5 8.456v-2.91l5.742-3.474A.5.5 0 0 1 12 2.5M4.5 9.06v1.952c-.423-.332-.79-.742-1.076-1.22A.5.5 0 0 0 3 9.5h-.655a.5.5 0 0 1-.5-.5v-2A.5.5 0 0 1 2.345 6H3a.5.5 0 0 0 .424-.292A4.5 4.5 0 0 1 4.5 4.488z"/></svg>',
      ],
      'bi_clock' => [
        'label' => 'Clock',
        'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-clock" viewBox="0 0 16 16"><path d="M8 3.5a.5.5 0 0 1 .5.5v4.25l3.5 2.1a.5.5 0 1 1-.5.9l-3.75-2.25A.5.5 0 0 1 7.5 8.5V4a.5.5 0 0 1 .5-.5"/><path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m0-1A7 7 0 1 1 8 1a7 7 0 0 1 0 14"/></svg>',
      ],
      'bi_chat_dots' => [
        'label' => 'Chat Dots',
        'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-chat-dots" viewBox="0 0 16 16"><path d="M2 1a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h4.586a1 1 0 0 1 .707.293l2.134 2.134a.5.5 0 0 0 .853-.354V11H14a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1zm0-1h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H11v1.573a1.5 1.5 0 0 1-2.56 1.06L6.293 12H2a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2"/><path d="M3 6.5a1.5 1.5 0 1 1 3 0 1.5 1.5 0 0 1-3 0m4 0a1.5 1.5 0 1 1 3 0 1.5 1.5 0 0 1-3 0m4 0a1.5 1.5 0 1 1 3 0 1.5 1.5 0 0 1-3 0"/></svg>',
      ],
      'bi_geo_alt' => [
        'label' => 'Geo Pin',
        'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-geo-alt" viewBox="0 0 16 16"><path d="M12.166 8.94c-.524 2.318-2.757 4.328-4.16 5.639-.566.528-1.275.528-1.841 0-1.403-1.31-3.636-3.32-4.16-5.638A6.5 6.5 0 1 1 12.165 8.94z"/><path d="M8 8a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3"/></svg>',
      ],
      'bi_calendar_check' => [
        'label' => 'Calendar Check',
        'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-calendar-check" viewBox="0 0 16 16"><path d="M10.854 7.146a.5.5 0 0 0-.708 0L7.5 9.793 6.354 8.646a.5.5 0 1 0-.708.708l1.5 1.5a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0 0-.708"/><path d="M3.5 0a.5.5 0 0 0-1 0v1H1A1 1 0 0 0 0 2v13a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1h-1.5V0a.5.5 0 0 0-1 0v1h-9zm11.5 4H1v11h14z"/></svg>',
      ],
      'bi_info_circle' => [
        'label' => 'Info Circle',
        'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-info-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/><path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 .877-.252 1.02-.598l.088-.416c.066-.246.19-.287.414-.287h.287l.082-.38-.45-.083c-.294-.07-.352-.176-.288-.469l.738-3.468c.194-.897-.105-1.319-.808-1.319-.545 0-.877.252-1.02.598l-.088.416c-.066.246-.19.287-.414.287h-.287zM8 4.5a1 1 0 1 0 0-2 1 1 0 0 0 0 2"/></svg>',
      ],
      'bi_exclamation_circle' => [
        'label' => 'Exclamation Circle',
        'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-exclamation-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/><path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0M7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.55.55 0 0 1-1.1 0z"/></svg>',
      ],
      'bi_award' => [
        'label' => 'Award',
        'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-award" viewBox="0 0 16 16"><path d="M9.669.864 8 0 6.331.864 4.5.541l-.422 1.816L2.72 3.528l.995 1.557-.995 1.557 1.359 1.171.422 1.816 1.831-.323L8 10.17l1.669-.864 1.831.323.422-1.816 1.359-1.171-.995-1.557.995-1.557-1.359-1.171-.422-1.816z"/><path d="M8 8.99a3.99 3.99 0 0 1-1.835-.444L4.5 13.5A.5.5 0 0 0 5 14h2.001a.5.5 0 0 1 .415.223l1.285 1.927a.5.5 0 0 0 .832 0l1.285-1.927A.5.5 0 0 1 11.999 14H14a.5.5 0 0 0 .5-.5l-1.665-4.954A3.99 3.99 0 0 1 8 8.99"/></svg>',
      ],
      'bi_check_circle' => [
        'label' => 'Check Circle',
        'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-check-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/><path d="m10.854 5.646-3.708 3.708L5.354 7.56a.5.5 0 1 0-.708.708l2.146 2.147a.5.5 0 0 0 .708 0l4.354-4.354a.5.5 0 0 0-.708-.708"/></svg>',
      ],
      'bi_star' => [
        'label' => 'Star',
        'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-star" viewBox="0 0 16 16"><path d="M2.866 14.85c-.078.444.36.791.746.593L8 13.187l4.389 2.256c.386.198.824-.149.746-.592l-.83-4.73 3.523-3.356c.33-.314.158-.888-.283-.95l-4.898-.696L8.465.792a.52.52 0 0 0-.93 0L5.354 5.12l-4.898.696c-.441.062-.613.636-.283.95l3.523 3.356z"/></svg>',
      ],
    ];
  }
}

if (!function_exists('starscream_announcement_icon_choices')) {
  function starscream_announcement_icon_choices() {
    $choices = [];
    foreach (starscream_announcement_icon_library() as $key => $row) {
      $choices[$key] = $row['label'];
    }
    return $choices;
  }
}

if (!function_exists('starscream_sanitize_announcement_icon')) {
  function starscream_sanitize_announcement_icon($value) {
    $choices = starscream_announcement_icon_choices();
    return array_key_exists($value, $choices) ? $value : '';
  }
}

if (!function_exists('starscream_sanitize_announcement_text')) {
  function starscream_sanitize_announcement_text($value) {
    $value = sanitize_text_field($value);
    if (function_exists('mb_substr')) return mb_substr($value, 0, 40);
    return substr($value, 0, 40);
  }
}

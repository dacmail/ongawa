<?php

namespace Roots\Sage\Extras;

use Roots\Sage\Setup;

/**
 * Add <body> classes
 */
function body_class($classes) {
  // Add page slug if it doesn't exist
  if (is_single() || is_page() && !is_front_page()) {
    if (!in_array(basename(get_permalink()), $classes)) {
      $classes[] = basename(get_permalink());
    }
  }

  // Add class if sidebar is active
  if (Setup\display_sidebar()) {
    $classes[] = 'sidebar-primary';
  }

  return $classes;
}
add_filter('body_class', __NAMESPACE__ . '\\body_class');

/**
 * Clean up the_excerpt()
 */
function excerpt_more() {
  return ' &hellip;';
}
add_filter('excerpt_more', __NAMESPACE__ . '\\excerpt_more');


function ungrynerd_svg($svg) {
  $output = '';
  if (empty($svg)) {
    return;
  }
  $svg_file_path = \get_template_directory() . "/dist/images/" . $svg . ".svg";
  ob_start();
  include($svg_file_path);
  $output .= ob_get_clean();
  return $output;
}

add_action( 'widgets_init', function(){
  register_widget( 'UN_Newsletter_Widget' );
});


function ungrynerd_shortcode_button($atts, $content = '') {
  extract(shortcode_atts(array(
    'href' => '',
    'target' => '',
  ), $atts ) );
  return '<a class="btn btn-simple" href="' . esc_attr($href) . '" target="' . esc_attr($target) . '">' . esc_html($content) . '</a>';
}

add_shortcode('boton', __NAMESPACE__ . '\\ungrynerd_shortcode_button');

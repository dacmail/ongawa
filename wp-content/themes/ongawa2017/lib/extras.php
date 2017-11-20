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



/* DOCUMENTS POST TYPE */
add_action('init',  __NAMESPACE__ . '\ugnrynerd_doc_post_type');
function ugnrynerd_doc_post_type()  {
  $labels = array(
    'name' => __('Publicaciones', 'ungrynerd'),
    'singular_name' => __('Publicación', 'ungrynerd'),
    'add_new' => __('Añadir Publicación', 'ungrynerd'),
    'add_new_item' => __('Añadir Publicación', 'ungrynerd'),
    'edit_item' => __('Editar Publicación', 'ungrynerd'),
    'new_item' => __('Nuevo Publicación', 'ungrynerd'),
    'view_item' => __('Ver Publicaciones', 'ungrynerd'),
    'search_items' => __('Buscar Publicaciones', 'ungrynerd'),
    'not_found' =>  __('No se han encontrado Publicaciones ', 'ungrynerd'),
    'not_found_in_trash' => __('No hay Publicaciones en la papelera', 'ungrynerd'),
    'parent_item_colon' => ''
  );

  $args = array(
    'labels' => $labels,
    'public' => true,
    'publicly_queryable' => true,
    'show_ui' => true,
    'query_var' => true,
    'capability_type' => 'post',
    'show_in_nav_menus' => false,
    'hierarchical' => false,
    'exclude_from_search' => false,
    'menu_position' => 5,
    'rewrite' => array( 'slug' => 'publicaciones' ),
    'taxonomies' => array('un_cat'),
    'has_archive' => true,
    'supports' => array('title', 'editor', 'thumbnail')
  );
  register_post_type('un_doc',$args);
}

function ungrynerd_doc_taxonomies() {
    register_taxonomy("un_cat",
    array("un_doc"),
    array(
        "hierarchical" => true,
        "label" => esc_html__( "Categorización", 'ungrynerd'),
        "singular_label" => esc_html__( "Categoría", 'ungrynerd'),
        "rewrite" => array( 'slug' => 'archivado', 'hierarchical' => true),
        'show_in_nav_menus' => false,
        )
    );
}
add_action( 'init', __NAMESPACE__ . '\ungrynerd_doc_taxonomies', 0);


/* DOCUMENTS POST TYPE */
add_action('init',  __NAMESPACE__ . '\ugnrynerd_country_post_type');
function ugnrynerd_country_post_type()  {
  $labels = array(
    'name' => __('Paises', 'ungrynerd'),
    'singular_name' => __('País', 'ungrynerd'),
    'add_new' => __('Añadir País', 'ungrynerd'),
    'add_new_item' => __('Añadir País', 'ungrynerd'),
    'edit_item' => __('Editar País', 'ungrynerd'),
    'new_item' => __('Nuevo País', 'ungrynerd'),
    'view_item' => __('Ver Paises', 'ungrynerd'),
    'search_items' => __('Buscar Paises', 'ungrynerd'),
    'not_found' =>  __('No se han encontrado Paises ', 'ungrynerd'),
    'not_found_in_trash' => __('No hay Paises en la papelera', 'ungrynerd'),
    'parent_item_colon' => ''
  );

  $args = array(
    'labels' => $labels,
    'public' => true,
    'publicly_queryable' => true,
    'show_ui' => true,
    'query_var' => true,
    'capability_type' => 'post',
    'show_in_nav_menus' => false,
    'hierarchical' => false,
    'exclude_from_search' => false,
    'menu_position' => 5,
    'rewrite' => array( 'slug' => 'paises' ),
    'taxonomies' => array(),
    'has_archive' => true,
    'supports' => array('title', 'editor', 'thumbnail')
  );
  register_post_type('un_country',$args);
}

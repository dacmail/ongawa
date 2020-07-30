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
    $classes[] = 'sidebar-`imary';
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


function get_term_sticky_posts() {
	if (!is_category()) return false;

	$stickies = get_option( 'sticky_posts' );

	if (!$stickies) return false;

	$current_object = get_queried_object_id();

	$args = [
			'nopaging' => true,
			'post__in' => $stickies,
			'cat' => $current_object,
			'ignore_sticky_posts' => 1,
			'fields' => 'ids'
	];
	$q = get_posts( $args );

	return $q;
}

add_action( 'pre_get_posts', function ($q) {
  if (!is_admin() && $q->is_main_query() && $q->is_category()) {
    if ( function_exists(__NAMESPACE__ . '\get_term_sticky_posts' ) ) {
			$stickies = get_term_sticky_posts();

			if ( $stickies ) {
				$q->set('post__not_in', $stickies);

				if (!$q->is_paged()) {
					add_filter( 'the_posts', function ( $posts ) use ( $stickies ) {
						$term_stickies = get_posts( ['post__in' => $stickies, 'nopaging' => true]);
						$posts = array_merge( $term_stickies, $posts );
						return $posts;
					}, 10, 1 );
				}
			}
    }
  }

  if (!is_admin() && $q->is_main_query() && ($q->is_post_type_archive('un_doc') || $q->is_tax('un_cat'))) {
    $q->set('posts_per_page', -1);
  }
});

add_filter('acf/settings/save_json', function ($path) {
  $path = get_stylesheet_directory() . '/assets/acf-json';
  return $path;
});

add_filter('acf/settings/load_json', function ($paths) {
  unset($paths[0]);
  $paths[] = get_stylesheet_directory() . '/assets/acf-json';
  return $paths;
});


add_action('acf/init', function (){
  if (function_exists('acf_register_block_type')) {
    acf_register_block_type(array(
      'name'              => 'page_header',
      'title'             => __('Cabecera'),
      'render_template'   => 'templates/blocks/page_header.php',
      'mode'              => 'auto',
      'category'          => 'layout',
      'icon'              => 'welcome-view-site',
      'supports'          => array('align' => 'false'),
      'keywords'          => array('header', 'cabecera'),
    ));

    acf_register_block_type(array(
      'name'              => 'page_img_buttons',
      'title'             => __('Botones imágen'),
      'render_template'   => 'templates/blocks/page_img_buttons.php',
      'mode'              => 'auto',
      'category'          => 'layout',
      'icon'              => 'format-image',
      'supports'          => array('align' => 'false'),
      'keywords'          => array('botones', 'button', 'imagen'),
    ));

    acf_register_block_type(array(
      'name'              => 'featured',
      'title'             => __('Destacado: Texto e imagen'),
      'render_template'   => 'templates/blocks/block-featured.php',
      'mode'              => 'auto',
      'category'          => 'layout',
      'icon'              => 'tide',
      'supports'          => array('align' => 'false'),
      'keywords'          => array('destacado', 'featured', 'imagen', 'texto', 'introduccion'),
    ));

    acf_register_block_type(array(
      'name'              => 'banner-cta',
      'title'             => __('Banner con CTA'),
      'render_template'   => 'templates/blocks/cta.php',
      'mode'              => 'auto',
      'category'          => 'layout',
      'icon'              => 'admin-post',
      'supports'          => array('align' => 'false'),
      'keywords'          => array('banner', 'cta', 'imagen', 'texto', 'boton'),
    ));

    acf_register_block_type(array(
      'name'              => 'counter',
      'title'             => __('Cifras'),
      'render_template'   => 'templates/blocks/counter.php',
      'mode'              => 'auto',
      'category'          => 'layout',
      'icon'              => 'chart-line',
      'supports'          => array('align' => 'false'),
      'keywords'          => array('counter', 'contador', 'cifra', 'numero'),
    ));
  }
});

<?php
/**
 * ministar functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package ministar
 */

if ( ! defined( '_S_VERSION' ) ) {
	// Replace the version number of the theme on each release.
	define( '_S_VERSION', '1.0.0' );
}

if( function_exists('acf_add_options_page') ) {
	
	acf_add_options_page();
	
}

if ( ! function_exists( 'ministar_setup' ) ) :
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 */
	function ministar_setup() {
		/*
		 * Make theme available for translation.
		 * Translations can be filed in the /languages/ directory.
		 * If you're building a theme based on ministar, use a find and replace
		 * to change 'ministar' to the name of your theme in all the template files.
		 */
		load_theme_textdomain( 'ministar', get_template_directory() . '/languages' );

		// Add default posts and comments RSS feed links to head.
		add_theme_support( 'automatic-feed-links' );

		/*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it for us.
		 */
		add_theme_support( 'title-tag' );

		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		 */
		add_theme_support( 'post-thumbnails' );

		// This theme uses wp_nav_menu() in one location.

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support(
			'html5',
			array(
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
				'style',
				'script',
			)
		);

		// Set up the WordPress core custom background feature.
		add_theme_support(
			'custom-background',
			apply_filters(
				'ministar_custom_background_args',
				array(
					'default-color' => 'ffffff',
					'default-image' => '',
				)
			)
		);

		// Add theme support for selective refresh for widgets.
		add_theme_support( 'customize-selective-refresh-widgets' );

		/**
		 * Add support for core custom logo.
		 *
		 * @link https://codex.wordpress.org/Theme_Logo
		 */
		add_theme_support(
			'custom-logo',
			array(
				'height'      => 250,
				'width'       => 250,
				'flex-width'  => true,
				'flex-height' => true,
			)
		);
	}
endif;
add_action( 'after_setup_theme', 'ministar_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function ministar_content_width() {
	// This variable is intended to be overruled from themes.
	// Open WPCS issue: {@link https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards/issues/1043}.
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$GLOBALS['content_width'] = apply_filters( 'ministar_content_width', 640 );
}
add_action( 'after_setup_theme', 'ministar_content_width', 0 );

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function ministar_widgets_init() {
	/**
	 * FOOTER
	 */
	// register_sidebar(array(
	// 	'name'          => 'Footer widget 1',
	// 	'id'            => 'footer_w1',
	// 	'before_widget' => '<div class="footer-content-item">',
	// 	'after_widget'  => '</div>',
	// 	'before_title'  => '<span class="footer-item-title">',
	// 	'after_title'   => '</span>',
	// ));
}
add_action( 'widgets_init', 'ministar_widgets_init' );

/**
 * Enqueue scripts and styles.
 */
function ministar_scripts() {
	wp_enqueue_style( 'ministar-style-reset', get_template_directory_uri() . '/css/reset.css');
	wp_enqueue_style( 'ministar-style-loader', get_template_directory_uri() . '/css/loadingScreen.css');

	wp_register_style( 'bootstrap-css', 'https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/css/bootstrap.min.css' );
	wp_enqueue_style( 'bootstrap-css' );

	wp_register_style( 'vue-sweet-alert-css', 'https://cdn.jsdelivr.net/npm/sweetalert2@9.17.1/dist/sweetalert2.min.css' );
	wp_enqueue_style( 'vue-sweet-alert-css' );

	wp_enqueue_style( 'ministar-style-main', get_template_directory_uri() . '/css/main.css');
	wp_enqueue_style( 'ministar-style-media', get_template_directory_uri() . '/css/media.css');
	wp_enqueue_style( 'ministar-style-fonts', get_template_directory_uri() . '/css/fonts.css');

	wp_register_style( 'font-awsome-css', 'https://use.fontawesome.com/releases/v5.0.13/css/all.css' );
	wp_enqueue_style( 'font-awsome-css' );
	
	wp_enqueue_script( 'ministar-promise-js', get_template_directory_uri() . '/js/polyfills/Promise.js');
	wp_enqueue_script( 'ministar-event-target-js', get_template_directory_uri() . '/js/polyfills/EventTarget.js');
	wp_enqueue_script( 'ministar-abort-controller-js', get_template_directory_uri() . '/js/polyfills/AbortController.js');
	wp_enqueue_script( 'ministar-fetch-js', get_template_directory_uri() . '/js/polyfills/fetch.js');
	wp_enqueue_script( 'ministar-heir-js', get_template_directory_uri() . '/js/polyfills/heir.js');
	wp_enqueue_script( 'ministar-events-js', get_template_directory_uri() . '/js/polyfills/EventEmitter.js');
	
	// wp_deregister_script( 'jquery-core' );
    // wp_register_script( 'jquery-core', "https://code.jquery.com/jquery-3.1.1.min.js", array(), '3.1.1' );
    // wp_deregister_script( 'jquery-migrate' );
	// wp_register_script( 'jquery-migrate', "https://code.jquery.com/jquery-migrate-3.0.0.min.js", array(), '3.0.0' );
	// wp_enqueue_script( 'jquery' );

	wp_register_script('vue', 'https://cdn.jsdelivr.net/npm/vue/dist/vue.js');
	wp_enqueue_script( 'vue' );
	//wp_enqueue_script('vue-input-mask', get_template_directory_uri() . '/js/vue-input-mask.js');

	wp_register_script('sweet-alert', "https://cdn.jsdelivr.net/npm/sweetalert2@9.17.1/dist/sweetalert2.min.js");
	wp_enqueue_script( 'sweet-alert' );
	
	wp_enqueue_script( 'ministar-inputmask-js', get_template_directory_uri() . '/js/inputmask.js');
	wp_enqueue_script( 'ministar-utils-js', get_template_directory_uri() . '/js/utils.js');
	
	wp_enqueue_script( 'ministar-api-js', get_template_directory_uri() . '/js/API.js');
	wp_localize_script( 'ministar-api-js', 'wp_ajax', 
		array(
			'url' => admin_url('admin-ajax.php')
		)
	);
}
add_action( 'wp_enqueue_scripts', 'ministar_scripts' );

/**
 * My menu functions
 */
function ministar_add_additional_class_on_li($classes, $item, $args) {
    if(isset($args->item_class)) {
		if($args->item_class === '')
			$classes = [];
		else
			$classes[] = $args->item_class;
    }
    return $classes;
}
add_filter('nav_menu_css_class', 'ministar_add_additional_class_on_li', 1, 3);

function ministar_init() {
	/**
	 * Post Type: Менеджеры.
	 */

	$labels = [
		"name" => __( "Менеджеры", "ministar" ),
		"singular_name" => __( "Менеджер", "ministar" ),
		"menu_name" => __( "Менеджеры", "ministar" ),
		"all_items" => __( "Все менеджеры", "ministar" ),
		"add_new" => __( "Добавить нового", "ministar" ),
		"add_new_item" => __( "Добавить нового менеджера", "ministar" ),
		"edit_item" => __( "Редактировать менеджера", "ministar" ),
		"new_item" => __( "Новый менеджер", "ministar" ),
		"view_item" => __( "Посмотреть", "ministar" ),
		"view_items" => __( "Посмотреть менеджеров", "ministar" ),
		"search_items" => __( "Найти менеджера", "ministar" ),
		"not_found" => __( "Менеджер не найден", "ministar" ),
		"not_found_in_trash" => __( "Менеджер не найден в корзине", "ministar" ),
		"parent" => __( "Родительский:", "ministar" ),
		"featured_image" => __( "Главное изображение", "ministar" ),
		"set_featured_image" => __( "Установить главное изображение", "ministar" ),
		"remove_featured_image" => __( "Удалить главное изображение", "ministar" ),
		"use_featured_image" => __( "Использовать главное изображение", "ministar" ),
		"archives" => __( "Архивы", "ministar" ),
		"insert_into_item" => __( "Вставить на страницу", "ministar" ),
		"uploaded_to_this_item" => __( "Загружено на страницу", "ministar" ),
		"filter_items_list" => __( "Фильтровать список менеджеров", "ministar" ),
		"items_list_navigation" => __( "Навигация по списку", "ministar" ),
		"items_list" => __( "Список менеджеров", "ministar" ),
		"attributes" => __( "Атрибуты менеджера", "ministar" ),
		"item_published" => __( "Менеджер опубликован", "ministar" ),
		"item_published_privately" => __( "Менеджер опубликован приватно", "ministar" ),
		"item_reverted_to_draft" => __( "Менеджер переведё в черновик", "ministar" ),
		"item_scheduled" => __( "Менеджер запланирован", "ministar" ),
		"item_updated" => __( "Менеджер обновлён", "ministar" ),
		"parent_item_colon" => __( "Родительский:", "ministar" ),
	];

	$args = [
		"label" => __( "Менеджеры", "ministar" ),
		"labels" => $labels,
		"description" => "",
		"public" => true,
		"publicly_queryable" => true,
		"show_ui" => true,
		"show_in_rest" => true,
		"rest_base" => "",
		"rest_controller_class" => "WP_REST_Posts_Controller",
		"has_archive" => false,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"delete_with_user" => false,
		"exclude_from_search" => false,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"rewrite" => [ "slug" => "managers", "with_front" => true ],
		"query_var" => true,
		"menu_icon" => "dashicons-welcome-view-site",
		"supports" => [ "title", "custom-fields" ]
	];

	register_post_type( "managers", $args );
	

	// register_nav_menus(
	// 	array(
	// 		'header-menu' => __( 'Header Menu' )
	// 	)
	// );
}
add_action( 'init', 'ministar_init' );

/**
 * Add meta fields count
 */
add_filter( 'postmeta_form_limit', 'meta_limit_increase' );
function meta_limit_increase( $limit ) {
    return 100;
}

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * Customizer additions.
 */
//require get_template_directory() . '/inc/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
if ( defined( 'JETPACK__VERSION' ) ) {
	require get_template_directory() . '/inc/jetpack.php';
}

/**
 * TGM Plugin.
 */
require get_template_directory() . '/tgm/ministar.php';


/**
 * Custom shorts in cf7
 */
add_filter( 'wpcf7_form_elements', 'do_shortcode' );

/**
 * Shortcodes
 */

function short_acf_options( $atts ) {
	$attrs = shortcode_atts( array(
		'group' => '',
		'field' => '',
	), $atts);

	if($attr["group"] === ''){
		return get_field($attrs["field"], 'options');
	}else{
		$group = get_field($attrs["group"], 'options');
		return $group[$attrs["field"]];
	}
}
add_shortcode( 'acf_options', 'short_acf_options' );


/**
 * API
 */
require_once get_template_directory() . '/API/main.php';


/**
 * acf field creation
 */
if( function_exists('acf_add_local_field_group') ){

	acf_add_local_field_group(array(
		'key' => 'group_5f2e924058eb5',
		'title' => 'Поля менеджера',
		'fields' => array(
			array(
				'key' => 'field_5f33ac1f6415c',
				'label' => 'Логин',
				'name' => 'login',
				'type' => 'text',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '30',
					'class' => '',
					'id' => '',
				),
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'maxlength' => '',
			),
			array(
				'key' => 'field_5f2e942e6b8d7',
				'label' => 'Пароль аккаунта',
				'name' => 'password',
				'type' => 'text',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '30',
					'class' => '',
					'id' => '',
				),
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'maxlength' => '',
			),
		),
		'location' => array(
			array(
				array(
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'managers',
				),
			),
		),
		'menu_order' => 0,
		'position' => 'normal',
		'style' => 'default',
		'label_placement' => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen' => '',
		'active' => true,
		'description' => '',
	));
}

/**
 * manager fields validation
 */

define( 'ACF_CUSTOM_INCLUDED_FIELD', 'acfvpdkfi2983uedaaAAA' );
 
function my_edit_form_after_editor( $post ){
  	print("<input type='hidden' name='acf[".ACF_CUSTOM_INCLUDED_FIELD."][post_ID]' value='$post->ID'/>");
}
add_action( 'edit_form_after_editor', 'my_edit_form_after_editor' );

add_filter('acf/validate_value/name=login', 'require_unique', 10, 4);
function require_unique($valid, $value, $field, $input) {
	if (!$valid) {
		return $valid;
	}

	$post_id = $_POST['acf'][ACF_CUSTOM_INCLUDED_FIELD]['post_ID'];
	
	$args = array(
		'post_type' => 'managers',
		'posts_per_page' => 1,
		'post_status' => 'publish, draft, trash',
		'post__not_in' => array($post_id),
		'meta_query' => array(
			array(
				'key' => $field['name'],
				'value' => $value
			)
		)
	);
	$query = new WP_Query($args);
	if (count($query->posts)){
		$valid = 'Логин менеджера должен быть уникальным';
	}
	return $valid;
}
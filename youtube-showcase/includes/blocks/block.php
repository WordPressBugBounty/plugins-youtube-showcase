<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function yts_register_block() {

	// Safety Check: Exit if the block registry class doesn't exist
	if ( ! class_exists( 'WP_Block_Type_Registry' ) ) {
		return;
	}

	wp_register_script(
		'yts-block',
		plugins_url( 'block.js', __FILE__ ),
		array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-i18n', 'wp-server-side-render'),
		filemtime( __DIR__ . '/block.js' )
	);

	// 2. Register ALL your CSS files first
	wp_register_style( 'yts-main', plugins_url( 'css/main.css', __FILE__ ) );
	wp_register_style( 'yts-grid', plugins_url( 'css/grid.css', __FILE__ ) );
	$dir_url = YOUTUBE_SHOWCASE_PLUGIN_URL;
	wp_register_style('youtube-showcase-allview-css', $dir_url . '/assets/css/allview.css', '', YOUTUBE_SHOWCASE_VERSION);
	wp_register_style('wpas-css', $dir_url . 'assets/ext/wpas/wpas.min.css', '', YOUTUBE_SHOWCASE_VERSION);
	wp_register_style('emd-pagination', $dir_url . 'assets/css/emd-pagination.min.css', '', YOUTUBE_SHOWCASE_VERSION);

	wp_set_script_translations(
		'yts-block',
		'youtube-showcase',
		plugin_dir_path( __FILE__ ) . 'languages'
	);


	register_block_type( 'youtube-showcase/main', array(
		'editor_script'   => 'yts-block',
		'render_callback' => 'yts_render_block',
		'style'           => Array('youtube-showcase-allview-css','wpas-css','emd-pagination'),
		'attributes'      => array(
			'type' => array(
				'type'    => 'string',
				'default' => 'gallery',
			),
			'filter' => array(
				'type'    => 'string',
				'default' => '',
			),
			'category' => array(
				'type'    => 'string',
				'default' => '',
			),
			'tag' => array(
				'type'    => 'string',
				'default' => '',
			),
			'orderby' => array(
				'type'    => 'string',
				'default' => 'date',
			),
			'order' => array(
				'type'    => 'string',
				'default' => 'DESC',
			),
			'records_per_page' => array(
				'type'    => 'integer',
				'default' => 8,
			),
			'featured' => array(
				'type'    => 'boolean',
				'default' => false,
			),
		),
	) );
}
add_action( 'init', 'yts_register_block' );


function yts_render_block( $attributes ) {
	if ( empty( $attributes['type'] ) ) {
		return '';
	}

	// 1. Sanitize all attributes
	$type     = esc_attr( $attributes['type'] );
	$featured = $attributes['featured'] ? '1' : '0';
	$category = ! empty( $attributes['category'] ) ? esc_attr( $attributes['category'] ) : '';
	$tag = ! empty( $attributes['tag'] ) ? esc_attr( $attributes['tag'] ) : '';
	$orderby  = esc_attr( $attributes['orderby'] );
	$order    = esc_attr( $attributes['order'] );
	$records_per_page = ! empty( $attributes['records_per_page'] ) ? intval ( $attributes['records_per_page'] ) : '';
	// Inside yts_render_block
	$records_per_page = intval( $attributes['records_per_page'] );

	// Security/Performance Cap: Never allow more than 50
	if ( $records_per_page > 50 ) {
		$records_per_page = 50;
	} elseif ( $records_per_page < 1 ) {
		$records_per_page = 8; // Default fallback
	}	
	$filter = "";

	if(!empty($category)){
		$filter  .= "tax::category::is::" . $category . ";";
	}
	if(!empty($tag)){
		$filter  .= "tax::post_tag::is::" . $tag . ";";
	}
	if(!empty($orderby)){
		$filter  .= "misc::orderby::is::" . $orderby . ";";
	}
	if(!empty($order)){
		$filter  .= "misc::order::is::" . $order . ";";
	}
	if(!empty($records_per_page)){
		$filter  .= "misc::posts_per_page::is::" . $records_per_page . ";";
	}
	if($featured == 1){
		$filter .= "attr::emd_video_featured::is::1;";
	}
	$output = '';
	switch ( $attributes['type'] ) {

	case 'gallery':
		$output = do_shortcode(
			'[video_gallery filter="' . $filter . '"]'
		);
		break;

	case 'grid':
		$output = do_shortcode(
			'[video_grid filter="' . $filter . '"]'
		);
		break;

	}

	if ( trim( wp_strip_all_tags( $output ) ) === '' ) {
		$total_videos = wp_count_posts( 'emd_video' )->publish;
		if($total_videos > 0 && (is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) )) {
			// Define inline styles as a string for cleaner PHP
			$container_style = 'padding: 30px; text-align: center; background: #f8f9fa; border: 2px dashed #e2e8f0; border-radius: 12px; margin: 20px 0; font-family: sans-serif;';
			$text_style      = 'color: #64748b; font-size: 16px; margin: 0; font-style: italic;';
			$link_style      = 'display: inline-block; margin-top: 10px; color: #2271b1; font-weight: 600; text-decoration: underline; cursor: pointer; font-style: normal;';

			return sprintf(
				'<div class="yts-no-results-found" style="%s">
				<p style="%s">%s</p>
				<a href="#" class="yts-reset-link" style="%s">%s</a>
				</div>',
			$container_style,
				$text_style,
				__( 'No videos match your current filters.', 'youtube-showcase' ),
				$link_style,
				__( 'Clear all filters', 'youtube-showcase' )
			);
		}
		// No visible content
		return "";

	}
	return $output;
}
add_action( 'enqueue_block_editor_assets', function() {
	// The editor needs both so it can switch types instantly without a flicker
	wp_enqueue_style('youtube-showcase-allview-css');
	wp_enqueue_style('wpas-css');
	wp_enqueue_style('emd-pagination');
} );

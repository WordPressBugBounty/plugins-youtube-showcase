<?php
/**
 * Widget Functions
 *
 * @package     EMD
 * @copyright   Copyright (c) 2014,  Emarket Design
 * @since       1.0
 */
if (!defined('ABSPATH')) exit;
add_action('wp_ajax_emd_get_widg_pagenum', 'emd_get_widg_pagenum');
add_action('wp_ajax_nopriv_emd_get_widg_pagenum', 'emd_get_widg_pagenum');

function emd_get_widg_pagenum(){
	$response = false;
	$pageno = isset($_GET['pageno']) ? (int) $_GET['pageno'] : 1;
	$div_id = isset($_GET['div_id']) ? sanitize_text_field($_GET['div_id']) : '';
	$myapp = isset($_GET['app']) ? sanitize_text_field($_GET['app']) : '';
	if(!empty($div_id)){
		$widg_list = get_option($myapp . '_widg_list', Array());
		$widg_arr = explode("-",$div_id);
		$class_to_instantiate = isset($widg_arr[1]) ? sanitize_text_field($widg_arr[1]) : '';
		if(!empty($widg_list) && in_array($class_to_instantiate, $widg_list)) {
			$pids = Array();
			$widget_settings = get_option('widget_' . $class_to_instantiate, Array());
			if(!empty($widget_settings) && isset($widg_arr[2]) && !empty($widget_settings[$widg_arr[2]])){
				$mywidg = new $class_to_instantiate();
				$count = $widget_settings[$widg_arr[2]]['count'];
				$args['has_pages'] = $widget_settings[$widg_arr[2]]['pagination'];
				$args['posts_per_page'] = $widget_settings[$widg_arr[2]]['count_per_page'];
				$args['pagination_size'] = $widget_settings[$widg_arr[2]]['pagination_size'];
				$front_ents = emd_find_limitby('frontend', $myapp);
				if(!empty($front_ents) && in_array($mywidg->class,$front_ents) && $mywidg->type != 'integration'){
					$pids = apply_filters('emd_limit_by', $pids, $myapp, $mywidg->class,'frontend');
				}
				$args['filter'] = $mywidg->filter;
				$args['has_pages'] = true;
				$args['class'] = $mywidg->class;
				$args['cname'] = get_class($mywidg);
				$args['app'] = $myapp;
				$args['query_args'] = $mywidg->query_args;
				$args['query_args']['paged'] = $pageno;
				$widg_layout = Emd_Widget::get_ent_widget_layout($count, $pids,$args);
				if ($widg_layout) {
					echo '<input type="hidden" id="emd_app" value="' . esc_attr($myapp) . '">';
					echo wp_kses_post($mywidg->header);
					echo wp_kses_post($widg_layout);
					echo wp_kses_post($mywidg->footer);
					die();
				}
			}
		}
	}
	echo false;
	die();
}

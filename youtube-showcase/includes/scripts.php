<?php
/**
 * Enqueue Scripts Functions
 *
 * @package YOUTUBE_SHOWCASE
 * @since WPAS 4.0
 */
if (!defined('ABSPATH')) exit;
add_action('admin_enqueue_scripts', 'youtube_showcase_load_admin_enq');
/**
 * Enqueue style and js for each admin entity pages and settings
 *
 * @since WPAS 4.0
 * @param string $hook
 *
 */
function youtube_showcase_load_admin_enq($hook) {
	global $typenow;
	$dir_url = YOUTUBE_SHOWCASE_PLUGIN_URL;
	do_action('emd_ext_admin_enq', 'youtube_showcase', $hook);
	$min_trigger = get_option('youtube_showcase_show_rateme_plugin_min', 0);
	$tracking_optin = get_option('youtube_showcase_tracking_optin', 0);
	if (-1 !== intval($tracking_optin) || - 1 !== intval($min_trigger)) {
		wp_enqueue_style('emd-plugin-rateme-css', $dir_url . 'assets/css/emd-plugin-rateme.css');
		wp_enqueue_script('emd-plugin-rateme-js', $dir_url . 'assets/js/emd-plugin-rateme.js');
	}
	if ($hook == 'widgets.php') {
		wp_enqueue_script('emd-widg-js', $dir_url . 'assets/js/emd-widget-admin.js', array() , '', true);
		return;
	}
	if ($hook == 'edit-tags.php') {
		return;
	}
	if (isset($_GET['page']) && $_GET['page'] == 'youtube_showcase_settings') {
		wp_enqueue_script('accordion');
		wp_enqueue_style('codemirror-css', $dir_url . 'assets/ext/codemirror/codemirror.min.css');
		wp_enqueue_script('codemirror-js', $dir_url . 'assets/ext/codemirror/codemirror.min.js', array() , '', true);
		wp_enqueue_script('codemirror-css-js', $dir_url . 'assets/ext/codemirror/css.min.js', array() , '', true);
		wp_enqueue_script('codemirror-jvs-js', $dir_url . 'assets/ext/codemirror/javascript.min.js', array() , '', true);
		return;
	} else if (isset($_GET['page']) && in_array($_GET['page'], Array(
		'youtube_showcase_notify'
	))) {
		wp_enqueue_script('accordion');
		return;
	} else if (isset($_GET['page']) && in_array($_GET['page'], Array(
		'youtube_showcase_glossary'
	))) {
		wp_enqueue_script('accordion');
		return;
	} else if (isset($_GET['page']) && $_GET['page'] == 'youtube_showcase') {
		wp_enqueue_style('lazyYT-css', $dir_url . 'assets/ext/lazyyt/lazyYT.min.css');
		wp_enqueue_script('lazyYT-js', $dir_url . 'assets/ext/lazyyt/lazyYT.min.js');
		wp_enqueue_script('getting-started-js', $dir_url . 'assets/js/getting-started.js');
		return;
	} else if (isset($_GET['page']) && in_array($_GET['page'], Array(
		'youtube_showcase_store',
		'youtube_showcase_support'
	))) {
		wp_enqueue_style('admin-tabs', $dir_url . 'assets/css/admin-store.css');
		return;
	} else if (isset($_GET['page']) && $_GET['page'] == 'youtube_showcase_licenses') {
		wp_enqueue_style('admin-css', $dir_url . 'assets/css/emd-admin.min.css');
		return;
	} else if (isset($_GET['page']) && $_GET['page'] == 'youtube_showcase_shortcodes') {
		wp_enqueue_script('emd-copy-js', $dir_url . 'assets/js/emd-copy.js', array(
			'clipboard'
		) , '');
		return;
	}
	if (in_array($typenow, Array(
		'emd_video'
	))) {
		$theme_changer_enq = 1;
		$sing_enq = 0;
		$tab_enq = 0;
		if ($hook == 'post.php' || $hook == 'post-new.php') {
			$unique_vars['msg'] = __('Please enter a unique value.', 'youtube-showcase');
			$unique_vars['reqtxt'] = __('required', 'youtube-showcase');
			$unique_vars['app_name'] = 'youtube_showcase';
			$unique_vars['nonce'] = wp_create_nonce('emd_form');
			$ent_list = get_option('youtube_showcase_ent_list');
			if (!empty($ent_list[$typenow])) {
				$unique_vars['keys'] = $ent_list[$typenow]['unique_keys'];
				if (!empty($ent_list[$typenow]['req_blt'])) {
					$unique_vars['req_blt_tax'] = $ent_list[$typenow]['req_blt'];
				}
			}
			$tax_list = get_option('youtube_showcase_tax_list');
			if (!empty($tax_list[$typenow])) {
				foreach ($tax_list[$typenow] as $txn_name => $txn_val) {
					if ($txn_val['required'] == 1) {
						$unique_vars['req_blt_tax'][$txn_name] = Array(
							'hier' => $txn_val['hier'],
							'type' => $txn_val['type'],
							'label' => $txn_val['label'] . ' ' . __('Taxonomy', 'youtube-showcase')
						);
					}
				}
			}
			$rel_list = get_option('youtube_showcase_rel_list');
			if (!empty($rel_list)) {
				foreach ($rel_list as $rel_name => $rel_val) {
					if ($rel_val['required'] == 1) {
						$rel_name = preg_replace('/^rel_/', '', $rel_name);
						if (($rel_val['show'] == 'any' || $rel_val['show'] == 'from') && $rel_val['from'] == $typenow) {
							$unique_vars['req_blt_tax']['p2p-from-' . $rel_name] = Array(
								'type' => 'rel',
								'label' => $rel_val['from_title'] . ' ' . __('Relationship', 'youtube-showcase')
							);
						} elseif ($rel_val['show'] == 'to' && $rel_val['to'] == $typenow) {
							$unique_vars['req_blt_tax']['p2p-to-' . $rel_name] = Array(
								'type' => 'rel',
								'label' => $rel_val['to_title'] . ' ' . __('Relationship', 'youtube-showcase')
							);
						}
					}
				}
			}
			wp_enqueue_script('unique_validate-js', $dir_url . 'assets/js/unique_validate.js', array(
				'jquery',
				'jquery-validate'
			) , YOUTUBE_SHOWCASE_VERSION, true);
			wp_localize_script("unique_validate-js", 'unique_vars', $unique_vars);
		} elseif ($hook == 'edit.php') {
			wp_enqueue_style('youtube-showcase-allview-css', YOUTUBE_SHOWCASE_PLUGIN_URL . '/assets/css/allview.css');
			emd_lite_admin_enq_files('youtube_showcase', $hook);
		}
		switch ($typenow) {
			case 'emd_video':
			break;
		}
	}
}
add_action('wp_enqueue_scripts', 'youtube_showcase_frontend_scripts');
/**
 * Enqueue style and js for each frontend entity pages and components
 *
 * @since WPAS 4.0
 *
 */
function youtube_showcase_frontend_scripts() {
	$dir_url = YOUTUBE_SHOWCASE_PLUGIN_URL;
	wp_register_style('emd-pagination', $dir_url . 'assets/css/emd-pagination.min.css', '', YOUTUBE_SHOWCASE_VERSION);
	wp_register_script('emd-widg-paging-js', $dir_url . 'assets/js/emd-widg-paging.js', '', YOUTUBE_SHOWCASE_VERSION, true);
	wp_register_style('youtube-showcase-allview-css', $dir_url . '/assets/css/allview.css', '', YOUTUBE_SHOWCASE_VERSION);
	$grid_vars = Array();
	$local_vars['ajax_url'] = admin_url('admin-ajax.php');
	$wpas_shc_list = get_option('youtube_showcase_shc_list');
	wp_register_style('wpas-css', $dir_url . 'assets/ext/wpas/wpas.min.css', '', YOUTUBE_SHOWCASE_VERSION);
	wp_register_script('wpas-js', $dir_url . 'assets/ext/wpas/wpas.min.js', array(
		'jquery'
	) , YOUTUBE_SHOWCASE_VERSION);
	wp_register_script('video-gallery-js', $dir_url . 'assets/js/video-gallery.js', '', YOUTUBE_SHOWCASE_VERSION, true);
	wp_register_script('ytscjs', $dir_url . 'assets/js/ytscjs.js', '', YOUTUBE_SHOWCASE_VERSION, true);
	wp_register_script('ytsc-js', $dir_url . 'assets/js/ytsc.js', '', YOUTUBE_SHOWCASE_VERSION, true);
	do_action('emd_localize_scripts', 'youtube_showcase');
	if (is_single() && get_post_type() == 'emd_video') {
		youtube_showcase_enq_bootstrap();
		do_action('emd_enqueue_sat_view', 'youtube-showcase', 'emd_video', 'single');
		wp_enqueue_style('youtube-showcase-allview-css');
		youtube_showcase_enq_custom_css_js();
		return;
	}
}
function youtube_showcase_enq_bootstrap($type = '') {
	$misc_settings = get_option('youtube_showcase_misc_settings');
	if ($type == 'css' || $type == '') {
		if (empty($misc_settings) || (isset($misc_settings['disable_bs_css']) && $misc_settings['disable_bs_css'] == 0)) {
			wp_enqueue_style('wpas-css');
		}
	}
	if ($type == 'js' || $type == '') {
		if (empty($misc_settings) || (isset($misc_settings['disable_bs_js']) && $misc_settings['disable_bs_js'] == 0)) {
			wp_enqueue_script('wpas-js');
		}
	}
}
/**
 * Enqueue custom css if set in settings tool tab
 *
 * @since WPAS 5.3
 *
 */
function youtube_showcase_enq_custom_css_js() {
	$tools = get_option('youtube_showcase_tools');
	if (!empty($tools['custom_css'])) {
		$url = home_url();
		if (is_ssl()) {
			$url = home_url('/', 'https');
		}
		wp_register_style('youtube-showcase-custom', false);
		wp_enqueue_style('youtube-showcase-custom');
		wp_add_inline_style('youtube-showcase-custom', $tools['custom_css']);
	}
	if (!empty($tools['custom_js'])) {
		$url = home_url();
		if (is_ssl()) {
			$url = home_url('/', 'https');
		}
		wp_register_script('youtube-showcase-custom', false);
		wp_enqueue_script('youtube-showcase-custom');
		wp_add_inline_script('youtube-showcase-custom', $tools['custom_js']);
	}
}
/**
 * Enqueue if allview css is not enqueued
 *
 * @since WPAS 4.5
 *
 */
function youtube_showcase_enq_allview() {
	if (!wp_style_is('youtube-showcase-allview-css', 'enqueued')) {
		wp_enqueue_style('youtube-showcase-allview-css');
	}
}
add_action('admin_print_footer_scripts', 'youtube_showcase_edit_next_prev_button');
function youtube_showcase_edit_next_prev_button() {
	$screen = get_current_screen();
	$supported_types = array(
		'emd_video'
	);
	if (strpos($screen->parent_file, 'edit.php') !== false && in_array($screen->id, $supported_types) && in_array($screen->post_type, $supported_types) && $screen->action != 'add') {
		$next_post = get_previous_post();
		$previous_post = get_next_post();
		$next_post_id = 0;
		if ($next_post && $next_post->ID) {
			$next_post_id = $next_post->ID;
		}
		$previous_post_id = 0;
		if ($previous_post && $previous_post->ID) {
			$previous_post_id = $previous_post->ID;
		}
?>
                <script>
                if(window.jQuery) {
                        jQuery(document).ready(function($) {
                                $(window).load(function() { 
                                <?php if ($next_post_id) { ?>
                                        var is_next_post_available = true;
                                <?php
		} else { ?>
                                        var is_next_post_available = false;
                                <?php
		}
		if ($previous_post_id) { ?>
                                        var is_prev_post_available = true;
                                <?php
		} else { ?>
                                        var is_prev_post_available = false;
                                <?php
		}
		if ($screen->is_block_editor) { ?>
                                                if(is_prev_post_available && is_next_post_available){
                                                        $('.edit-post-header__settings').prepend('<a href="<?php echo get_edit_post_link($previous_post_id) ?>" class="prev-post components-button editor-post-preview is-button is-primary is-large">&larr; <?php esc_html_e('Previous', 'youtube-showcase') ?></a><a href="<?php echo get_edit_post_link($next_post_id) ?>" class="next-post components-button editor-post-preview is-button is-primary is-large"><?php esc_html_e('Next', 'youtube-showcase') ?> &rarr;</a>');
                                                }else if(is_prev_post_available && !is_next_post_available){
                                                        $('.edit-post-header__settings').prepend('<a href="<?php echo get_edit_post_link($previous_post_id) ?>" class="prev-post components-button editor-post-preview is-button is-primary is-large">&larr; <?php esc_html_e('Previous', 'youtube-showcase') ?></a>');
                                                }else if(is_next_post_available && !is_prev_post_available){
                                                        $('.edit-post-header__settings').prepend('<a href="<?php echo get_edit_post_link($next_post_id) ?>" class="next-post components-button editor-post-preview is-button is-primary is-large"><?php esc_html_e('Next', 'youtube-showcase') ?> &rarr;</a>');
                                                }
                                        <?php
		} else { ?>
                                                if(is_prev_post_available && is_next_post_available){
                                                        $('.wrap .page-title-action').after('<a href="<?php echo get_edit_post_link($previous_post_id) ?>" class="prev-post page-title-action">&larr; <?php esc_html_e('Previous', 'youtube-showcase') ?></a><a href="<?php echo get_edit_post_link($next_post_id) ?>" class="next-post page-title-action"><?php esc_html_e('Next', 'youtube-showcase') ?> &rarr;</a>');
                                                }else if(is_prev_post_available && !is_next_post_available){
                                                        $('.wrap .page-title-action').after('<a href="<?php echo get_edit_post_link($previous_post_id) ?>" class="prev-post page-title-action">&larr; <?php esc_html_e('Previous', 'youtube-showcase') ?></a>');
                                                }else if(is_next_post_available && !is_prev_post_available){
                                                        $('.wrap .page-title-action').after('<a href="<?php echo get_edit_post_link($next_post_id) ?>" class="next-post page-title-action"><?php esc_html_e('Next', 'youtube-showcase') ?> &rarr;</a>');
                                                }
                                        <?php
		} ?>                                                      
                                });
                        });
                }
                </script>
<?php
	}
}
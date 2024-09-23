<?php
/**
 * Form Settings Functions
 *
 */
if (!defined('ABSPATH')) exit;

require_once 'emd-form-frontend.php';
require_once 'emd-form-settings.php';
require_once 'emd-form-functions.php';

add_action('emd_ext_set_conf','emd_form_builder_lite_install');

function emd_form_builder_lite_install($app){
	$app = str_replace('-','_',$app);
	$shc_list = get_option($app . '_shc_list',Array());
	if(!empty($shc_list['has_form_lite'])){
		emd_form_builder_lite_save_forms_data($app);
	}
}

add_action('emd_ext_init','emd_form_lite_old_forms');

function emd_form_lite_old_forms($app){
	$shc_list = get_option($app . '_shc_list',Array());
	if(!empty($shc_list['forms']) && !empty($shc_list['has_form_lite'])){
		foreach($shc_list['forms'] as $fkey => $fval){
			add_shortcode($fkey,'emd_old_forms_lite_shc');
		}
	}
}

function emd_old_forms_lite_shc($atts,$content,$tag){
	if(!empty($tag)){
		$fid = get_option('emd_form_id_' . $tag,0);
		if(!empty($fid)){
			if(!empty($atts['set'])){
				return do_shortcode("[emd_form id='" . $fid . "' set=\"" . $atts['set'] . "\"]");
			}
			else {
				return do_shortcode("[emd_form id='" . $fid . "']");
			}
		}
	}
}

if (!is_admin() || (defined('DOING_AJAX') && DOING_AJAX)) {
	add_filter('posts_where', 'emd_form_lite_builtin_posts_where', 10, 2);
}
add_action('emd_ext_admin_enq', 'emd_form_builder_lite_admin_enq', 10, 2);

function emd_form_builder_lite_admin_enq($app,$hook){
	if(preg_match('/page_' . $app . '_forms$/',$hook)){
		$shc_list = get_option($app . '_shc_list',Array());
		if(empty($shc_list['has_form_lite'])){
			return;
		}
		$dir_url = constant(strtoupper($app) . "_PLUGIN_URL");
		$builder_vars = Array();
		if(!empty($_GET['edit']) && $_GET['edit'] == 'layout'){
			wp_enqueue_script('jquery-ui-sortable');
			wp_enqueue_script('jquery-ui-draggable');
			wp_enqueue_style('form-builder-css', $dir_url . 'includes/emd-form-builder-lite/css/emd-form-builder.min.css');
			$builder_vars['ajax_url'] = admin_url('admin-ajax.php');
			$builder_vars['exit_url'] = admin_url('admin.php?page=' . sanitize_text_field($_GET['page']));
			$builder_vars['nonce'] = wp_create_nonce('emd_form');
			wp_enqueue_script('form-builder-js', $dir_url . 'includes/emd-form-builder-lite/js/emd-form-builder.js');
			wp_localize_script("form-builder-js", 'builder_vars', $builder_vars);
		}
		elseif(!empty($_GET['edit']) && $_GET['edit'] == 'settings'){
			wp_enqueue_style('form-builder-css', $dir_url . 'includes/emd-form-builder-lite/css/emd-form-builder.min.css');
			wp_enqueue_style('jq-css', $dir_url . 'assets/css/smoothness-jquery-ui.css');
			wp_enqueue_script('jquery-ui-timepicker', $dir_url . 'assets/ext/emd-meta-box/js/jqueryui/jquery-ui-timepicker-addon.js', array(
						'jquery-ui-datepicker',
						'jquery-ui-slider'
						) , constant(strtoupper($app) . '_VERSION'), true);
			wp_enqueue_style('jquery-ui-timepicker', $dir_url . 'assets/ext/emd-meta-box/css/jqueryui/jquery-ui-timepicker-addon.css');
			wp_enqueue_style('wpas-select2', $dir_url . 'assets/ext/bselect24/select2.min.css');
			wp_enqueue_script('wpas-select2-js', $dir_url . 'assets/ext/bselect24/select2.full.min.js');
			wp_enqueue_script('form-settings-js', $dir_url . 'includes/emd-form-builder-lite/js/emd-form-settings.js');
			return;
		}
		else {
			wp_enqueue_script('emd-copy-js', $dir_url . 'assets/js/emd-copy.js', array('clipboard') , '');
		}
	}
}


//change class install to save all forms in wp_posts with emd_form ptype
function emd_form_builder_lite_save_forms_data($app){
	$shc_list = get_option($app . '_shc_list');
	if(!empty($shc_list['forms'])){
		$post_forms = get_posts(Array('post_type'=>'emd_form','post_status'=>'publish','posts_per_page' => '-1'));
		$saved_forms = Array();
		if(!empty($post_forms)){
			foreach($post_forms as $myp_form){
				$fcontent = json_decode($myp_form->post_content,true);
				if($app == $fcontent['app']){
					$saved_forms[$fcontent['name']] = $fcontent;
				}
			}
		}
		$rel_list = get_option($app . '_rel_list', Array());
		$rel_list = apply_filters('emd_ext_form_rels',$rel_list,$app);
		$attr_list = get_option($app . '_attr_list', Array());
		$ent_list = get_option($app . '_ent_list', Array());
		$txn_list = get_option($app . '_tax_list', Array());
		$glob_list = get_option($app . '_glob_init_list', Array());
		$glob_forms_list = get_option($app . '_glob_forms_list',Array());
		$glob_forms_list = apply_filters('emd_ext_form_var_init',$glob_forms_list,$app, '');
		$glob_forms_init_list = get_option($app . '_glob_forms_init_list',Array());
		$glob_forms_init_list = apply_filters('emd_ext_form_var_init', $glob_forms_init_list, $app, '');
		$non_field_confs = Array('captcha','noaccess_msg','error_msg','success_msg','login_reg','csrf','btn', 'noresult_msg');
		foreach($shc_list['forms'] as $kform => $vform){
			if(!in_array($kform,array_keys($saved_forms))){
				$ftitle = '';
				$myform = Array();
				$myform['name'] = $kform;
				$myform['type'] = $vform['type'];
				$myform['entity'] = $vform['ent'];	
				$myform['app'] = $app;	
				$myform['source'] = 'plugin';	
				$myform['settings']['captcha'] = $vform['show_captcha'];
				$myform['settings']['noaccess_msg'] = $vform['noaccess_msg'];
				$myform['settings']['success_msg'] = $vform['confirm_success_txt'];
				$myform['settings']['error_msg'] = $vform['confirm_error_txt'];
				$myform['settings']['login_reg'] = $vform['login_reg'];
				$rel_labels = Array();
				foreach($rel_list as $krel => $vrel){
					if($myform['entity'] == $vrel['from']){
						$rel_labels[$krel]['label'] = $vrel['from_title'];
					}
					elseif($myform['entity'] == $vrel['to']){
						$rel_labels[$krel]['label'] = $vrel['to_title'];
					}
					if(!empty($vrel['desc'])){
						$rel_labels[$krel]['desc'] = $vrel['desc'];
					}
					elseif(!empty($rel_labels[$krel]['label'])){
						$rel_labels[$krel]['desc'] = $rel_labels[$krel]['label'];
					}
				}
				if(!empty($vform['page_title'])){
					$myform['page_title'] = $vform['page_title'];
				}
				if(empty($glob_forms_list[$kform])){
					$fsettings = $glob_forms_init_list[$kform];
				}
				else {
					$fsettings = $glob_forms_list[$kform];
				}	
				foreach($fsettings as $skey => $sval){
					if($skey != 'btn'){
						$myfield = Array();
						if(in_array($skey,$non_field_confs) && !is_array($sval)){
							$myform['settings'][$skey] = ltrim($sval);
						}
						else {
							if(in_array($skey,Array('blt_title','blt_content','blt_excerpt'))){
								$myfield[$skey] = Array("show"=>$sval['show'],"req"=>$sval['req'],"size"=>$sval['size']);
								if(!empty($ent_list[$myform['entity']]['req_blt'][$skey])){
									$myfield[$skey]['label'] = $ent_list[$myform['entity']]['req_blt'][$skey]['msg'];
								}
								elseif(!empty($ent_list[$myform['entity']]['blt_list'][$skey])){
									$myfield[$skey]['label'] = $ent_list[$myform['entity']]['blt_list'][$skey];
								}	
								$myfield[$skey]['desc'] = $myfield[$skey]['label'];
							}
							elseif(!empty($rel_labels[$skey])){
								$myfield[$skey] = Array("show"=>$sval['show'],"req"=>$sval['req'],"size"=>$sval['size']);
								$myfield[$skey]['label'] = $rel_labels[$skey]['label'];
								$myfield[$skey]['desc'] = $rel_labels[$skey]['desc'];
							}
							elseif(!empty($attr_list[$myform['entity']][$skey])){
								if($myform['type'] == 'search' || (empty($attr_list[$myform['entity']][$skey]['uniqueAttr']) && $myform['type'] == 'submit')){
									$myfield[$skey] = Array("show"=>$sval['show'],"req"=>$sval['req'],"size"=>$sval['size']);
									if(!empty($attr_list[$myform['entity']][$skey]['desc'])){
										$myfield[$skey]['desc'] = $attr_list[$myform['entity']][$skey]['desc'];
									}	
									if(!empty($attr_list[$myform['entity']][$skey]['label'])){
										$myfield[$skey]['label'] = $attr_list[$myform['entity']][$skey]['label'];
									}
								}
							}
							elseif(!empty($txn_list[$myform['entity']][$skey])){
								$myfield[$skey] = Array("show"=>$sval['show'],"req"=>$sval['req'],"size"=>$sval['size']);
								$myfield[$skey]['label'] = $txn_list[$myform['entity']][$skey]['single_label'];
								if(!empty($txn_list[$myform['entity']][$skey]['desc'])){
									$myfield[$skey]['desc'] = $txn_list[$myform['entity']][$skey]['desc'];
								}
							}
							elseif(!empty($glob_list[$skey])){
								$myfield[$skey] = Array("show"=>$sval['show'],"req"=>$sval['req'],"size"=>$sval['size']);
								$myfield[$skey]['label'] = $glob_list[$skey]['label'];
								$myfield[$skey]['display_type'] = 'global';
								if(!empty($glob_list[$skey]['desc'])){
									$myfield[$skey]['desc'] = $glob_list[$skey]['desc'];
								}
							}
							if(!empty($myfield[$skey]['label'])){
								$myfield[$skey]['placeholder'] = $myfield[$skey]['label'];
							}
							if(!empty($myfield)){
								$myform['layout'][1]['rows'][$sval['row']][] = $myfield;
							}
						}
					}
				}
				ksort($myform['layout'][1]['rows']);
				if(empty($myform['page_title'])){
					$ftitle = ucwords(str_replace("_"," ",$myform['name']));
				}
				else {
					$ftitle = $myform['page_title'];
				}
				$myform['settings']['title'] = $ftitle;
				$myform['settings']['targeted_device'] = $vform['targeted_device'];
				$myform['settings']['label_position'] = $vform['label_position'];
				$myform['settings']['element_size'] = $vform['element_size'];
				$myform['settings']['display_inline'] = $vform['display_inline'];
				$myform['settings']['disable_submit'] = $vform['disable_submit'];
				$myform['settings']['submit_status'] = $vform['submit_status'];
				$myform['settings']['visitor_submit_status'] = $vform['visitor_submit_status'];
				$myform['settings']['submit_button_type'] = $vform['submit_button_type'];
				$myform['settings']['submit_button_label'] = $vform['submit_button_label'];
				$myform['settings']['submit_button_size'] = $vform['submit_button_size'];
				$myform['settings']['submit_button_block'] = $vform['submit_button_block'];
				$myform['settings']['submit_button_fa'] = $vform['submit_button_fa'];
				$myform['settings']['submit_button_fa_size'] = $vform['submit_button_fa_size'];
				$myform['settings']['submit_button_fa_pos'] = $vform['submit_button_fa_pos'];
				$myform['settings']['disable_after'] = $vform['disable_after'];
				$myform['settings']['confirm_method'] = $vform['confirm_method'];
				$myform['settings']['confirm_url'] = $vform['confirm_url'];
				$myform['settings']['enable_ajax'] = $vform['enable_ajax'];
				$myform['settings']['after_submit'] = $vform['after_submit'];
				$myform['settings']['schedule_start'] = $vform['schedule_start'];
				$myform['settings']['schedule_end'] = $vform['schedule_end'];
				$myform['settings']['enable_operators'] = $vform['enable_operators'];
				$myform['settings']['ajax_search'] = $vform['ajax_search'];
				$myform['settings']['result_templ'] = $vform['result_templ'];
				$myform['settings']['result_fields'] = $vform['result_fields'];
				if(!empty($vform['incl_select2'])){
					$myform['settings']['incl_select2'] = 1;
				}	
				$myform['settings']['noresult_msg'] = $vform['noresult_msg'];
				$myform['settings']['honeypot'] = $vform['honeypot'];
				$myform['settings']['view_name'] = $vform['view_name'];

				$form_data = array(
						'post_status' => 'publish',
						'post_type' => 'emd_form',
						'post_title' => $ftitle,
						'post_content' => wp_slash(json_encode($myform,true)),
						'comment_status' => 'closed'
						);
				$id = wp_insert_post($form_data);
				if(!empty($id)){
					update_option('emd_form_id_' . $myform['name'],$id);
				}
			}
			else {
				$saved_forms[$kform]['settings']['result_templ'] = $vform['result_templ'];
				$saved_forms[$kform]['settings']['result_fields'] = $vform['result_fields'];
				if(!empty($vform['incl_select2'])){
					$saved_forms[$kform]['settings']['incl_select2'] = 1;
				}	
				foreach($post_forms as $myp_form){
					$fcontent = json_decode($myp_form->post_content,true);
					if($app == $fcontent['app'] && $kform == $fcontent['name']){
						$form_id = $myp_form->ID;
						break;
					}
				}
				wp_update_post(Array('ID' => $form_id,'post_content'=> wp_slash(json_encode($saved_forms[$kform],true))));
			}
		}
	}
}


add_action('emd_show_forms_lite_page','emd_show_forms_lite_page',1);
/**
 * Show forms list page
 *
 * @param string $app
 * @since WPAS 4.4
 *
 * @return html page content
 */
function emd_show_forms_lite_page($app){
	if(!empty($_POST['submit']) && !empty($_POST['submit_settings'])){
		$nonce_verified = wp_verify_nonce(sanitize_text_field($_POST['emd_form_settings_nonce']), 'emd_form_settings_nonce');
		if(false === $nonce_verified){
			//error
			return;
		}
	}
	global $title;
	echo '<div class="wrap">';
	echo '<h2><span style="padding-right:10px;">' .  esc_html($title) . "</span>"; 
	$create_url = '#'; 
	echo '<a href="' . esc_url($create_url) . '" class="button btn-primary button-primary upgrade-pro">' . esc_html('Create New', 'youtube-showcase') . '</a>';
	echo '<a href="#" class="add-new-h2 upgrade-pro" style="padding:6px 10px;">' . esc_html('Import', 'youtube-showcase') . '</a>';
	echo '<a href="#" class="add-new-h2 upgrade-pro" style="padding:6px 10px;">' . esc_html('Export', 'youtube-showcase') . '</a>';
	echo '</h2>';
	echo '<p>' . esc_html__('Emd Form Builder makes it easy to create simple or advanced forms with a few clicks.','youtube-showcase') . ' <a href="https://emdplugins.com/best-form-builder-for-wordpress/?pk_campaign=' . esc_attr($app) . '&pk_kwd=emdformbuilderpagelink" target="_blank">' . esc_html__('Click here to learn more.','youtube-showcase') . '</a></p>';
	echo '<style>.tablenav.top{display:none;}</style>';
	if(!empty($_POST['submit']) && !empty($_POST['submit_settings'])){
		emd_form_builder_lite_save_settings($app);
	}
	elseif(!empty($_GET['edit']) && $_GET['edit'] == 'layout' && !empty($_GET['form_id'])){
		emd_form_builder_lite_layout($app,(int) $_GET['form_id']);
	}
	elseif(!empty($_GET['edit']) && $_GET['edit'] == 'settings' && !empty($_GET['form_id'])){
		emd_form_builder_lite_settings('edit',$app, (int) $_GET['form_id']);
	}
	else {
		$list_table = new Emd_List_Table($app,'form',0);
		$list_table->prepare_items();
		?>
			<div class="emd-form-list-admin-content">
			<form id="emd-form-list-table" method="get" action="<?php echo admin_url( 'admin.php?page=' . esc_attr($app) . '_forms'); ?>">
			<input type="hidden" name="page" value="<?php echo esc_attr($app) . '_forms';?>"/>
			<?php $list_table->views(); ?>
			<?php $list_table->display(); ?>
			</form>
			</div>
			<?php
	}
}
function emd_form_builder_lite_layout($app,$form_id,$from='admin'){
	$myform = get_post($form_id);
	$fcontent = json_decode($myform->post_content,true);
	$fentity = $fcontent['entity'];
	$pcount = count($fcontent['layout']);
	$htmlcount = 1;
	//var_dump($fcontent['layout']);
	foreach($fcontent['layout'] as $kpage => $cpage){
		if(!empty($cpage['rows'])){
			foreach($cpage['rows'] as $krow => $crow){
				foreach($crow as $fcount => $field){
					foreach($field as $kfield => $cfield){
						if(preg_match('/^html/',$kfield)){
							$htmlcount ++;
						} 
					}
				}
			}
		}
	}
	echo '<div class="updated is-dismissible notice emd-form-save-success" style="display:none;"><p><strong>' . esc_html__('Saved successfully.','youtube-showcase') . '</strong></p></div>';
	echo '<div class="emd-form-builder';
	if($from == 'front'){
		echo ' emd-frontend';
	}
	echo '">';
	echo '<form name="emd-form-builder" id="emd-form-builder-form" method="post" data-id="' . esc_attr($form_id) . '">';
	echo '<input type="hidden" name="id" value="' . esc_attr($form_id) . '">';
	echo '<div class="emd-form-builder-top"><div class="emd-form-builder-center">' . esc_html__('Editing Form:','youtube-showcase') . ' ' . esc_html($myform->post_title) . '</div>
		<div class="emd-form-builder-right"><a href="#" id="emd-form-save">' . esc_html__('Save','youtube-showcase') . '</a>
		<a href="#" id="emd-form-exit"><span class="field-icons times"></span></a>
		</div></div>';
	echo '<div class="emd-form-builder-sidebar-content">';
	echo '<div class="emd-form-builder-sidebar">
		<div class="emd-form-builder-add-row-wrap">
		<button class="emd-form-builder-add-row" data-app="' . esc_attr($app) . '"' . ' data-entity="' . esc_attr($fentity) . '" title="' . esc_html__('Click here to add row','youtube-showcase') . '">' . esc_html__('Add Row','youtube-showcase') . '</button>
		</div>
		<div class="emd-form-builder-html-fields">
		<button class="emdform-hr-button" data-app="' . esc_attr($app) . '"' . ' data-entity="' . esc_attr($fentity) . '" title="' . esc_html__('Click here to add','youtube-showcase') . '">' . esc_html__('Divider','youtube-showcase') . '</button>
		<button class="emdform-html-button upgrade-pro" data-app="' . esc_attr($app) . '"' . ' data-entity="' . esc_attr($fentity) . '" data-field="html' . esc_attr($htmlcount) . '" title="' . esc_html__('Click here to add','youtube-showcase') . '">' . esc_html__('HTML','youtube-showcase') . '</button>
		</div>
		<div class="emd-form-builder-add-page-wrap">';
	if($fcontent['type'] == 'submit'){	
		echo '<button class="emd-form-builder-add-page upgrade-pro" data-app="' . esc_attr($app) . '"' . ' data-entity="' . esc_attr($fentity) . '" title="' . esc_html__('Click here to add a form wizard page','youtube-showcase') . '">' . esc_html__('Add Page','youtube-showcase') . '</button>';
	}
	echo '<div class="emd-form-builder-pages">
		<div class="emd-form-builder-page active" id="emd-form-builder-page-1" title="' . esc_html__('Click to go to this page','youtube-showcase') . '">' . 
		esc_html__('Page 1','youtube-showcase') . '</div>';
	if($pcount > 1){
		for($i=2;$i<=$pcount;$i++){
			echo '<div class="emd-form-builder-page" id="emd-form-builder-page-' . esc_attr($i) . '" title="' . esc_html__('Click to go to this page','youtube-showcase') . '">' .
				'<a href="#" class="emd-form-builder-page-delete" title="' . esc_html__('Delete','youtube-showcase') . '"><span class="field-icons times-circle" aria-hidden="true"></span></a>' .
				sprintf(esc_html__('Page %s','youtube-showcase'),esc_html($i)) . '</div>';
		}
	}	
	echo '</div>
		</div>';
	echo '<div class="emd-form-builder-tabs">
		<a href="#" class="emd-form-builder-tab active" id="fields">' . esc_html__('Fields','youtube-showcase') . '</a>
		<a href="#" class="emd-form-builder-tab" id="settings">' . esc_html__('Settings','youtube-showcase') . '</a>
		</div>
		<div class="emd-form-builder-fields">';
	emd_form_builder_lite_fields($app,$fentity,$fcontent);
	echo '</div>
		<div class="emd-form-builder-fields-settings" style="display:none;">';
	emd_form_builder_lite_get_form_field_settings($app,$fentity,$fcontent);
	echo '</div>';
	echo '</div>';
	echo '<div class="emd-form-builder-content-wrap">
		<div class="emd-form-builder-content">';
	emd_form_builder_lite_get_form_layout($app,$fentity,$myform->post_title,$fcontent);
	echo '</div>';
	echo '</div>';
	echo '</div>';
	echo '</form>';
	echo '</div>';


}
function emd_form_builder_lite_fields($app,$fentity,$fcontent){
	$attr_list = get_option($app . '_attr_list');
	$txn_list = get_option($app . '_tax_list', Array());
	$rel_list = get_option($app . '_rel_list', Array());
	$ent_list = get_option($app . '_ent_list',Array());
	$glob_init_list = get_option($app . '_glob_init_list',Array());
	$glob_forms_init_list = get_option($app . '_glob_forms_init_list',Array());
	$glob_list = Array();
	if(!empty($glob_forms_init_list[$fcontent['name']])){
		foreach($glob_forms_init_list[$fcontent['name']] as $kglob => $vglob){
			$non_field_confs = Array('captcha','noaccess_msg','error_msg','success_msg','login_reg','csrf','btn', 'noresult_msg');
			if(!in_array($kglob,$non_field_confs)){
				$glob_list[] = $kglob;
			}
		}
	}
	$ext_list = apply_filters('emd_ext_form_rels',Array(),$app);
	$cust_fields = Array();
	$flist = Array();
	foreach($fcontent['layout'] as $pid => $pcont){
		if(!empty($pcont['rows'])){
			foreach($pcont['rows'] as $rid => $rcont){
				foreach($rcont as $r => $row){
					foreach($row as $f => $fval){
						if(!empty($fval['show'])){
							$flist[] = $f;
						}
					}  
				}
			}
		}
	}
	$blt_fields = Array('blt_title','blt_content','blt_excerpt');
	$blts = Array();
	foreach($blt_fields as $myblt){
		if(!empty($ent_list[$fentity]['req_blt'][$myblt])){
			$blts[$myblt] = $ent_list[$fentity]['req_blt'][$myblt]['msg'];
		}
		elseif(!empty($ent_list[$fentity]['blt_list'][$myblt])){
			$blts[$myblt] = $ent_list[$fentity]['blt_list'][$myblt];
		}	
	}
	if(!empty($glob_init_list)){
		foreach($glob_init_list as $kglob => $vglob){
			if(!empty($vglob['in_form'])){
				$glbs[$kglob] = $vglob;
			}
		}
	}
	if(post_type_supports($fentity, 'custom-fields') == 1){
		$cust_fields = apply_filters('emd_get_cust_fields', $cust_fields, $fentity);
	}
	$has_user = 0;
	$titl = __('Drag this field to a row.','youtube-showcase');
	if(!empty($attr_list[$fentity])){
		echo '<div class="emd-form-builder-fields-group"><a class="emd-form-builder-fields-heading attr"><span>Attributes</span><span class="emd-formbuilder-icons angle-down"></span></a>';
		echo '<div class="emd-form-builder-fields">';
		if(!empty($blts)){
			foreach($blts as $kblt => $vblt){
				echo '<button class="emdform-field-button emd-attr';
				if(in_array($kblt,$flist)){
					echo ' disabled';
				}
				elseif(!in_array($kblt,$glob_list)){
					echo ' upgrade-pro';
					$titl = __('Drag this field to a row - available in premium edition.','youtube-showcase');
				}
				echo '" id="' . esc_attr($kblt) . '-btn" data-field="' . esc_attr($kblt) . '" title="' . esc_attr($titl) . '">' . esc_html($vblt) . '</button>';
			}
		}
		foreach($attr_list[$fentity] as $kattr => $vattr){
			if($vattr['display_type'] == 'user'){
				$has_user = 1;
			}
			if(!preg_match('/^wpas_/',$kattr) && !(!empty($vattr['uniqueAttr']) && $vattr['display_type'] == 'hidden' && $fcontent['type'] == 'submit') && !($fcontent['type'] == 'search' && in_array($vattr['display_type'], Array('file','image','plupload_image','thickbox_image')))){
				echo '<button class="emdform-field-button ';
				if(in_array($kattr,$flist)){
					echo ' disabled';
				}
				elseif(!in_array($kattr,$glob_list)){
					echo ' upgrade-pro';
					$titl = __('Drag this field to a row - available in premium edition.','youtube-showcase');
				}
				echo ' emd-attr" id="' . esc_attr($kattr) . '-btn" data-field="' . esc_attr($kattr) . '" title="' . esc_attr($titl) . '">' . esc_html($vattr['label']) . '</button>';
			}
		}
		if(!empty($glbs)){
			foreach($glbs as $kglb => $vglb){
				echo '<button class="emdform-field-button ';
				if(in_array($kglb,$flist)){
					echo ' disabled';
				}
				echo ' emd-attr" id="' . esc_attr($kglb) . '-btn" data-field="' . esc_attr($kglb) . '" title="' . esc_html__('Drag this field to a row','youtube-showcase') . '">' . esc_html($vglb['label']) . '</button>';
			}
		}
		echo '</div>';
		echo '</div>';
	}	
	if(!empty($txn_list[$fentity])){
		echo '<div class="emd-form-builder-fields-group"><a class="emd-form-builder-fields-heading tax"><span>Taxonomies</span><span class="emd-formbuilder-icons angle-down"></span></a>';
		echo '<div class="emd-form-builder-fields">';
		foreach($txn_list[$fentity] as $ktxn => $vtxn){
			echo '<button class="emdform-field-button ';
			if(in_array($ktxn,$flist)){
				echo ' disabled';
			}
			elseif(!in_array($ktxn,$glob_list)){
				echo ' upgrade-pro';
				$titl = __('Drag this field to a row - available in premium edition.','youtube-showcase');
			}
			echo ' emd-attr" id="' . esc_attr($ktxn) . '-btn" data-field="' . esc_attr($ktxn) . '" title="' . esc_attr($titl) . '">' . esc_html($vtxn['single_label']) . '</button>';
		}
		echo '</div>';
		echo '</div>';
	}	
	if(!empty($rel_list)){
		$rels = Array();
		foreach($rel_list as $krel => $vrel){
			if($fentity == $vrel['from']){
				$rels[] = Array('key' => $krel, 'label' => $vrel['from_title']);
			}
			elseif($fentity == $vrel['to']){
				$rels[] = Array('key' => $krel, 'label' => $vrel['to_title']);
			}
		}
		if(!empty($rels)){
			echo '<div class="emd-form-builder-fields-group"><a class="emd-form-builder-fields-heading relate"><span>Relationships</span><span class="emd-formbuilder-icons angle-down"></span></a>';
			echo '<div class="emd-form-builder-fields">';
			foreach($rels as $myrel){
				echo '<button class="emdform-field-button ';
				if(in_array($myrel['key'],$flist)){
					echo ' disabled';
				}
				elseif(!in_array($myrel['key'],$glob_list) && empty($ext_list[$myrel['key']])){
					echo ' upgrade-pro';
					$titl = __('Drag this field to a row - available in premium edition.','youtube-showcase');
				}
				echo ' emd-attr" id="' . esc_attr($myrel['key']) . '-btn" data-field="' . esc_attr($myrel['key']) . '" title="' . esc_attr($titl) . '">' . esc_html($myrel['label']) . '</button>';
			}
			echo '</div>';
			echo '</div>';
		}
	}
	if(!empty($has_user)){
		echo '<div class="emd-form-builder-fields-group"><a class="emd-form-builder-fields-heading comp"><span>Components</span><span class="emd-formbuilder-icons angle-down"></span></a>';
		echo '<div class="emd-form-builder-fields">';
		echo '<button class="emdform-field-button ';
		if(in_array('login_box_username',$flist)){
			echo ' disabled';
		}
		else {
			echo ' upgrade-pro';
			$titl = __('Drag this field to a row - available in premium edition.','youtube-showcase');
		}
		echo ' emd-attr" id="login_box-btn" data-field="login_box_username" title="' . esc_attr($titl) . '">' . esc_html__('Login Box','youtube-showcase') . '</button>';
		echo '</div>';
		echo '</div>';
	}	
}	
function emd_form_builder_lite_get_form_layout($app,$fentity,$ftitle,$fcontent){
	$attr_list = get_option($app . '_attr_list',Array());
	$ent_list = get_option($app . '_ent_list',Array());
	$txn_list = get_option($app . '_tax_list', Array());
	$rel_list = get_option($app . '_rel_list', Array());
	$glob_list = get_option($app . '_glob_init_list', Array());
	//layout/page#/row#
	$count_pages = count($fcontent['layout']);
	echo '<div class="emd-form-page-list">';
	if(!empty($fcontent['layout'])){
		foreach($fcontent['layout'] as $kpage => $cpage){
			if(!empty($cpage['rows'])){
				echo '<div class="emd-form-page-wrap" id="emd-form-page-' . esc_attr($kpage) . '" data-page="' . esc_attr($kpage) . '" data-app="' . esc_attr($app) . '" data-entity="' . esc_attr($fentity) . '"';
				if($count_pages > 1){
					echo ' style="display:none;"';
				}
				echo '>';
				echo '<input type="hidden" class="emd-form-page-hidden" name="layout[]" value="page">';
				foreach($cpage['rows'] as $krow => $crow){
					echo '<div class="emd-form-row emd-row">';
					echo '<a href="#" class="emd-form-row-delete" title="' . esc_html__('Delete','youtube-showcase') . '"><span class="field-icons times-circle" aria-hidden="true"></span></a>';
					echo '<span class="emd-form-row-info">' . esc_html__('Drag to reorder','youtube-showcase') . '</span>';
					echo '<div class="emd-form-row-holder" data-app="' . esc_attr($app) . '" data-entity="' . esc_attr($fentity) . '" data-row="' . esc_attr($krow) . '">';
					echo '<input type="hidden" name="layout[]" value="row">';
					foreach($crow as $fcount => $field){
						foreach($field as $kfield => $cfield){
							if(!empty($cfield['show']) && !in_array($kfield, Array('login_box_password','login_box_reg_password','login_box_reg_confirm_password','login_box_reg_username'))){ 
								//if this field is an html field
								if(!empty($cfield['value'])){
									$cfield['size'] = 12;
								}
								if(empty($cfield['size'])){
									$cfield['size'] = 12;
								}
								echo '<div class="emd-form-field emd-col emd-md-' . esc_attr($cfield['size']) . '" data-size="'.  esc_attr($cfield['size']) . '" data-field="' . esc_attr($kfield) . '">';
								echo '<a href="#" class="emd-form-field-delete" title="' . esc_html__('Delete','youtube-showcase') . '"><span class="field-icons times-circle" aria-hidden="true"></span></a>';
								echo '<span class="emd-form-field-info" style="cursor:pointer;">' . esc_html__('Drag to reorder / Click for settings','youtube-showcase') . '</span>';
								if(!empty($attr_list[$fentity]) && in_array($kfield,array_keys($attr_list[$fentity]))){
									$cfield['display_type'] = $attr_list[$fentity][$kfield]['display_type'];
								}
								if(!empty($attr_list[$fentity][$kfield]['uniqueAttr'])){
									$cfield['uniqueAttr'] = $attr_list[$fentity][$kfield]['uniqueAttr'];
								}	
								if($kfield == 'hr'){
									emd_form_builder_lite_layout_hr($kfield);
								}
								elseif(preg_match('/^html/',$kfield)){
									emd_form_builder_lite_layout_html($kfield,$cfield);
								}
								else {
									if(!empty($fcontent['type'])){
										$cfield['form_type'] = $fcontent['type'];
									}
									if(in_array($kfield,array_keys($attr_list[$fentity]))){
										$cfield['display_type'] =  $attr_list[$fentity][$kfield]['display_type'];
									}
									emd_form_builder_lite_layout_field_top_bottom($kfield,$cfield,'top');
									if(in_array($kfield,Array('blt_title','blt_content','blt_excerpt'))){
										echo emd_form_builder_lite_blt_fields($kfield,$cfield);
									}
									elseif(preg_match('/^login_box_/',$kfield)){
										echo emd_form_builder_lite_login_box($kfield,$cfield);
									}
									elseif(!empty($attr_list[$fentity]) && in_array($kfield,array_keys($attr_list[$fentity]))){
										$cfield['display_type'] = $attr_list[$fentity][$kfield]['display_type'];
										if(!empty($attr_list[$fentity][$kfield]['options'])){
											$cfield['options'] = $attr_list[$fentity][$kfield]['options'];
										}
										echo emd_form_builder_lite_attr_fields($kfield,$cfield);
									}
									elseif(!empty($txn_list[$fentity]) && in_array($kfield,array_keys($txn_list[$fentity]))){
										echo emd_form_builder_lite_txn_fields($kfield,$cfield);
									}
									elseif(!empty($rel_list) && array_key_exists($kfield,$rel_list)){
										echo emd_form_builder_lite_rel_fields($kfield,$cfield);
									}
									elseif(!empty($glob_list) && array_key_exists($kfield,$glob_list)){
										$cfield['display_type'] = 'global';
										echo emd_form_builder_lite_attr_fields($kfield,$cfield);
									}
									if(!empty($attr_list[$fentity][$kfield]['display_type']) && in_array($attr_list[$fentity][$kfield]['display_type'], Array('checkbox'))){
										emd_form_builder_lite_layout_field_top_bottom($kfield,$cfield,'bottom');
										echo '</div>';
									}
								}
								echo '</div>';
							}
						}
					}
					echo '</div>';
					echo '</div>';
				}
				echo '</div>';
			}
		}
	}
	else {
		echo '<div class="emd-form-page-wrap" id="emd-form-page-1" data-page="1" data-app="' . esc_attr($app) . '" data-entity="' . esc_attr($fentity) . '">';
		echo '<input type="hidden" class="emd-form-page-hidden" name="layout[]" value="page">';
		echo '<div class="emd-form-row init">
			<a href="#" class="emd-form-row-delete" title="' . esc_html__('Delete','youtube-showcase') . '"><span class="field-icons times-circle" aria-hidden="true"></span></a>
			<span class="emd-form-row-info">' . esc_html__('Drag to reorder','youtube-showcase') . '</span>
			<div class="emd-form-row-holder" data-app="' . esc_attr($app) . '" data-entity="' . esc_attr($fentity) . '" data-row="0">
			<input type="hidden" name="layout[]" value="row">
			<div class="emd-form-insert-row">' . esc_html__('Drag fields here','youtube-showcase') . 
			'</div>
			</div>
			</div>';

	}
	echo '</div>';
}
function emd_form_builder_lite_layout_html($kfield,$cfield){
	echo '<div class="emd-form-group">
		<input type="hidden" name="layout[]" value="' . esc_attr($kfield) . '">
		<input type="text" name="' . esc_attr($kfield) . '" id="' . esc_attr($kfield) . '" class="text emd-input-md emd-form-control html-code" placeholder="' . esc_html__('HTML','youtube-showcase') . '" disabled/>
		</div>';
}
function emd_form_builder_lite_layout_hr($kfield){
	echo '<hr class="emd-form-row-hr">
		<input type="hidden" name="layout[]" value="hr">';
}
function emd_form_builder_lite_layout_field_top_bottom($kfield,$cfield,$loc){
	if(!empty($cfield['display_type']) && in_array($cfield['display_type'], Array('checkbox')) && $loc == 'top'){
		echo '<div class="emd-form-group">';
		echo '<input type="hidden" name="layout[]" value="' . esc_attr($kfield) . '">';
	}
	else {
		if($loc == 'top'){
			echo '<div class="emd-form-group">';
			if($kfield == 'login_box_username'){
				echo '<input type="hidden" name="layout[]" value="login_box_username">';
				echo '<input type="hidden" name="layout[]" value="login_box_password">';
				echo '<input type="hidden" name="layout[]" value="login_box_reg_username">';
				echo '<input type="hidden" name="layout[]" value="login_box_reg_password">';
				echo '<input type="hidden" name="layout[]" value="login_box_reg_confirm_password">';
			}
			else {
				echo '<input type="hidden" name="layout[]" value="' . esc_attr($kfield) . '">';
			}
		}
		echo '<label class="';
		if(!empty($cfield['display_type']) && in_array($cfield['display_type'],Array('checkbox'))){
			echo 'emd-form-check-label" for="' . esc_attr($kfield) . '">';
		}
		else {
			echo 'emd-control-label" for="' . esc_attr($kfield) . '">';
		}
		echo '<span id="label_' . esc_attr($kfield) . '">';
		if(!empty($cfield['label']) && $kfield != 'login_box_username'){
			echo esc_html($cfield['label']);
		}
		else {
			$cfield['label'] = '';
		}
		echo '</span>';	
		echo '<span style="display: inline-flex;right: 0px; position: relative; top:-6px;">';
		echo '<a data-html="true" href="#" data-toggle="tooltip"';
		if(empty($cfield['desc'])){
			echo ' style="display:none;"';
		}
		else {
			echo ' title="' . esc_attr($cfield['desc']) . '"';
		}
		echo ' id="info_' . esc_attr($kfield) . '" class="helptip"';
		echo '><span class="field-icons info"></span></a>';
		echo '<a href="#" data-html="true" data-toggle="tooltip" title="' . esc_attr($cfield['label']) . ' field is required" id="req_' . esc_attr($kfield) . '" class="helptip"';
		if (empty($cfield['req'])) { 
			echo ' style="display:none;"';
		}
		echo '>';
		echo '<span class="field-icons required"></span>
			</a>';
		echo '</span>';
		echo '</label>';
	}
}
function emd_form_builder_lite_blt_fields($kfield,$cfield){
	if($kfield == 'blt_title'){
		$blt_lay = '<input type="text" name="' . esc_attr($kfield) . '" id="' . esc_attr($kfield) . '" class="text emd-input-md emd-form-control" placeholder="' . esc_attr($cfield['placeholder']) . '" disabled/>';
	}	
	else {
		$blt_lay = '<textarea name="' . esc_attr($kfield) . '" id="' . esc_attr($kfield) . '" class="control wyrb" placeholder="' . esc_attr($cfield['placeholder']) . '" disabled></textarea>';
	}
	$blt_lay .= '</div>';
	return $blt_lay;
}
function emd_form_builder_lite_attr_fields($kfield,$cfield){
	$attr_lay = '';
	switch($cfield['display_type']){
		case 'select':
		case 'select_advanced':
			$attr_lay .= '<div class="dropdown">';
			$attr_lay .= '<input type="text" name="' . esc_attr($kfield) . '" id="' . esc_attr($kfield) . '" class="text emd-input-md emd-form-control" placeholder="' . esc_attr($cfield['placeholder']) . '" disabled>';
			$attr_lay .= '<div class="emd-arrow"></div>';
			$attr_lay .= '</div>';
			break;
		case 'wysiwyg':
			$attr_lay .= '<textarea name="' . esc_attr($kfield) . '" id="' . esc_attr($kfield) . '" class="control wyrb" placeholder="' . esc_attr($cfield['placeholder']) . '" disabled></textarea>';
			break;
		case 'checkbox':
			$attr_lay .= '<input type="checkbox" name="' . esc_attr($kfield) . '" id="' . esc_attr($kfield) . '" class="emd-checkbox emd-input-md emd-form-control" disabled>';
			break;
		case 'radio':
			if(!empty($cfield['options'])){
				foreach($cfield['options'] as $kopt => $vopt){
					$attr_lay .= '<div class="emd-form-check">';
					$attr_lay .= '<input type="radio" name="' . esc_attr($kfield) . '" id="' . esc_attr($kfield) . '_' . esc_attr($kopt) . '" class="emd-radio emd-input-md emd-form-control" disabled>';
					$attr_lay .= '<label class="emd-form-check-label" for="' . esc_attr($kfield) . '_' . esc_attr($kopt) . '">' . esc_html($vopt)  . '</label>';
					$attr_lay .= '</div>';
				}
			}
			break;
		case 'checkbox_list':
			if(!empty($cfield['options'])){
				foreach($cfield['options'] as $kopt => $vopt){
					$attr_lay .= '<div class="emd-form-check">';
					$attr_lay .= '<input type="checkbox" name="' . esc_attr($kfield) . '" id="' . esc_attr($kfield) . '_' . esc_attr($kopt) . '" class="emd-checkbox emd-input-md emd-form-control" disabled>';
					$attr_lay .= '<label class="emd-form-check-label" for="' . esc_attr($kfield) . '_' . esc_attr($kopt) . '">' . esc_html($vopt)  . '</label>';
					$attr_lay .= '</div>';
				}
			}
			break;
		case 'hidden':
			if($cfield['form_type'] == 'search'){
				$attr_lay .= '<input type="text" name="' . esc_attr($kfield) . '" id="' . esc_attr($kfield) . '" class="text emd-input-md emd-form-control" placeholder="' . esc_attr($cfield['placeholder']) . '" disabled/>';
			}
			elseif(empty($cfield['uniqueAttr'])){
				$attr_lay .= '<div>' . __('Hidden field','youtube-showcase') . '</div>';
			}
			break;
		case 'global':
			$attr_lay .= '<div>' . __('Global field','youtube-showcase') . '</div>';
			break;
		case 'text':
		default:
			$attr_lay .= '<input type="text" name="' . esc_attr($kfield) . '" id="' . esc_attr($kfield) . '" class="text emd-input-md emd-form-control" placeholder="' . esc_attr($cfield['placeholder']) . '" disabled/>';
			break;
	}
	if(!in_array($cfield['display_type'], Array('checkbox'))){
		$attr_lay .= '</div>';
	}
	return $attr_lay;
}
function emd_form_builder_lite_txn_fields($kfield,$cfield){
	$txn_lay = '<div class="dropdown">';
	$txn_lay .= '<input type="text" name="' . esc_attr($kfield) . '" id="' . esc_attr($kfield) . '" class="text emd-input-md emd-form-control" placeholder="' . esc_attr($cfield['placeholder']) . '" disabled>';
	$txn_lay .= '<div class="emd-arrow"></div>';
	$txn_lay .= '</div>';
	$txn_lay .= '</div>';
	return $txn_lay;
}
function emd_form_builder_lite_rel_fields($kfield,$cfield){
	$rel_lay = '<div class="dropdown">';
	$rel_lay .= '<input type="text" name="' . esc_attr($kfield) . '" id="' . esc_attr($kfield) . '" class="text emd-input-md emd-form-control" placeholder="' . esc_attr($cfield['placeholder']) . '" disabled>';
	$rel_lay .= '<div class="emd-arrow"></div>';
	$rel_lay .= '</div>';
	$rel_lay .= '</div>';
	return $rel_lay;
}
add_action('wp_ajax_emd_form_builder_lite_get_field', 'emd_form_builder_lite_get_field');
function emd_form_builder_lite_get_field(){
	check_ajax_referer('emd_form', 'nonce');
	$fcap = 'manage_options';
	$fcap = apply_filters('emd_settings_pages_cap', $fcap, sanitize_text_field($_POST['app']));
	if(!current_user_can($fcap)){
		echo false;
		die();
	}
	if(!empty($_POST['app']) && !empty($_POST['entity']) && !empty($_POST['field'])){	
		$app = sanitize_text_field($_POST['app']);
		$fentity = sanitize_text_field($_POST['entity']);
		$kfield = sanitize_text_field($_POST['field']);
		$form_id = (int) $_POST['form_id'];
		$attr_list = get_option($app . '_attr_list',Array());
		$ent_list = get_option($app . '_ent_list',Array());
		$txn_list = get_option($app . '_tax_list', Array());
		$rel_list = get_option($app . '_rel_list', Array());
		$glob_list = get_option($app . '_glob_init_list', Array());
		if(!empty($glob_list[$kfield])){
			$cfield['label'] = $glob_list[$kfield]['label'];
			$cfield['display_type'] = 'global';
			$cfield['size'] = 12;
		}
		$cfield = emd_form_builder_lite_get_cfield($fentity,$kfield,$attr_list,$ent_list,$txn_list,$rel_list);
		ob_start();
		echo '<div class="emd-form-field emd-col emd-md-' . esc_attr($cfield['size']) . '" data-size="'.  esc_attr($cfield['size']) . '" ui-sortable-handle ui-draggable ui-draggable-handle" data-field="' . esc_attr($kfield) . '">';
		echo '<a href="#" class="emd-form-field-delete" title="' . esc_html__('Delete','youtube-showcase') . '"><span class="field-icons times-circle" aria-hidden="true"></span></a>';
		echo '<span class="emd-form-field-info" style="cursor:pointer;">' . esc_html__('Drag to reorder / Click for settings','youtube-showcase') . '</span>';
		if(in_array($kfield,array_keys($attr_list[$fentity]))){
			$cfield['display_type'] =  $attr_list[$fentity][$kfield]['display_type'];
		}
		emd_form_builder_lite_layout_field_top_bottom($kfield,$cfield,'top');
		if(in_array($kfield,Array('blt_title','blt_content','blt_excerpt'))){
			echo emd_form_builder_lite_blt_fields($kfield,$cfield);
		}
		elseif(preg_match('/^login_box_/',$kfield)){
			echo emd_form_builder_lite_login_box($kfield,$cfield);
		}
		elseif(!empty($attr_list[$fentity]) && in_array($kfield,array_keys($attr_list[$fentity]))){
			$cfield['display_type'] = $attr_list[$fentity][$kfield]['display_type'];
			if(!empty($attr_list[$fentity][$kfield]['options'])){
				$cfield['options'] = $attr_list[$fentity][$kfield]['options'];
			}
			if(!empty($attr_list[$fentity][$kfield]['uniqueAttr'])){
				$cfield['uniqueAttr'] = $attr_list[$fentity][$kfield]['uniqueAttr'];
			}
			$myform = get_post($form_id);
			$fcontent = json_decode($myform->post_content,true);
			if(!empty($fcontent['type'])){
				$cfield['form_type'] = $fcontent['type'];
			}
			echo emd_form_builder_lite_attr_fields($kfield,$cfield);
		}
		elseif(!empty($txn_list[$fentity]) && in_array($kfield,array_keys($txn_list[$fentity]))){
			echo emd_form_builder_lite_txn_fields($kfield,$cfield);
		}
		elseif(!empty($rel_list) && array_key_exists($kfield,$rel_list)){
			echo emd_form_builder_lite_rel_fields($kfield,$cfield);
		}
		elseif(!empty($glob_list) && array_key_exists($kfield,$glob_list)){
			$cfield['display_type'] = 'global';
			echo emd_form_builder_lite_attr_fields($kfield,$cfield);
		}
		if(!empty($attr_list[$fentity][$kfield]['display_type']) && in_array($attr_list[$fentity][$kfield]['display_type'],Array('checkbox'))){
			emd_form_builder_lite_layout_field_top_bottom($kfield,$cfield,'bottom');
		}
		echo '</div>';
		$field = ob_get_clean();
		wp_send_json_success(array('field' => $field));
	}
	else {
		die();
	}
}
function emd_form_builder_lite_get_form_field_settings($app,$fentity,$fcontent){
	$attr_list = get_option($app . '_attr_list');
	$txn_list = get_option($app . '_tax_list', Array());
	$rel_list = get_option($app . '_rel_list', Array());
	$ent_list = get_option($app . '_ent_list',Array());
	$glob_list = get_option($app . '_glob_init_list',Array());
	$all_fields = Array();
	$html_fields = Array();
	$page_fields = Array();
	$login_fields = Array();
	$fields = Array('req','label','size','desc','display_type','placeholder','css_class','login_label','reg_label','username_label','password_label','redirect_link','enable_registration','reg_username_label','reg_password_label','reg_confirm_password_label');
	foreach($fcontent['layout'] as $pid => $pcont){
		if(!empty($pcont['rows'])){
			foreach($pcont['rows'] as $rid => $rcont){
				foreach($rcont as $r => $row){
					foreach($row as $f => $fval){
						if(preg_match('/^html/',$f)){
							$html_fields[$f] = $fval;
						}
						foreach($fields as $myfield){
							if(!empty($fval[$myfield]) && preg_match('/^login_box/',$f)){
								$login_fields[$f][$myfield] = $fval[$myfield];
							}
							elseif(!empty($fval[$myfield])){
								$all_fields[$f][$myfield] = $fval[$myfield];
							}
						}
					}  
				}
			}
		}
		$page_fields[$pid]['step_title'] = '';
		$page_fields[$pid]['step_desc'] = '';
		if(!empty($pcont['step_title'])){
			$page_fields[$pid]['step_title'] = $pcont['step_title'];
		}
		if(!empty($pcont['step_desc'])){
			$page_fields[$pid]['step_desc'] = $pcont['step_desc'];
		}
	}
	$blt_fields = Array('blt_title','blt_content','blt_excerpt');
	foreach($blt_fields as $myblt){
		if(!empty($ent_list[$fentity]['req_blt'][$myblt]) && empty($all_fields[$myblt]['label'])){
			$all_fields[$myblt]['label'] = $ent_list[$fentity]['req_blt'][$myblt]['msg'];
			$all_fields[$myblt]['desc'] = $all_fields[$myblt]['label'];
		}
		elseif(!empty($ent_list[$fentity]['blt_list'][$myblt]) && empty($all_fields[$myblt]['label'])){
			$all_fields[$myblt]['label'] = $ent_list[$fentity]['blt_list'][$myblt];
			$all_fields[$myblt]['desc'] = $all_fields[$myblt]['label'];
		}	
	}
	$has_user = 0;
	if(!empty($attr_list[$fentity])){
		foreach($attr_list[$fentity] as $kattr => $vattr){
			if($vattr['display_type'] == 'user'){
				$has_user = 1;
			}
			if(!preg_match('/^wpas_/',$kattr)){
				if(empty($all_fields[$kattr]['label'])){
					$all_fields[$kattr]['label'] = $vattr['label'];
				}
				if(empty($all_fields[$kattr]['desc']) && !empty($vattr['desc'])){
					$all_fields[$kattr]['desc'] = $vattr['desc'];
				}
				if($vattr['display_type'] == 'hidden'){
					$all_fields[$kattr]['display_type'] = 'hidden';
				}
			}
		}
	}
	if(!empty($txn_list[$fentity])){
		foreach($txn_list[$fentity] as $ktxn => $vtxn){
			if(empty($all_fields[$ktxn]['label'])){
				$all_fields[$ktxn]['label'] = $vtxn['single_label'];
			}
			if(empty($all_fields[$ktxn]['desc']) && !empty($vtxn['desc'])){
				$all_fields[$ktxn]['desc'] = $vtxn['desc'];
			}
		}
	}
	if(!empty($glob_list)){
		foreach($glob_list as $kglob => $vglob){
			if(!empty($vglob['in_form'])){
				$all_fields[$kglob]['label'] = $vglob['label'];
				$all_fields[$kglob]['display_type'] = 'global';
			}
		}
	}
	if(!empty($rel_list)){
		$rels = Array();
		foreach($rel_list as $krel => $vrel){
			if(empty($all_fields[$krel]['label'])){
				if($fentity == $vrel['from']){
					$all_fields[$krel]['label'] = $vrel['from_title'];
				}
				elseif($fentity == $vrel['to']){
					$all_fields[$krel]['label'] = $vrel['to_title'];
				}
			}
			if(empty($all_fields[$krel]['desc']) && !empty($vrel['desc'])){
				$all_fields[$krel]['desc'] = $vrel['desc'];
			}
		}
	}
	foreach($all_fields as $kfield => $vfield){
		if(empty($vfield['placeholder'])){
			$vfield['placeholder'] = $vfield['label'];
		}
		if(empty($vfield['size'])){
			$vfield['size'] = 12;
		}
		echo '<div class="emd-form-builder-field-settings-wrap emd-field-' . esc_attr($kfield) . '" style="display:none;">';
		if(!empty($vfield['display_type']) && $vfield['display_type'] == 'hidden' && $fcontent['type'] == 'submit'){
			echo '<input type="hidden" name="fields[' . esc_attr($kfield) . '][label]" class="emd-form-builder-field-label" id="emd-fbl-' . esc_attr($kfield) . '" value="' . esc_html($vfield['label']) . '"/>';
			echo '<div>' . esc_html__('There are no settings options for hidden fields.','youtube-showcase') . '</div>';
		}
		elseif(!empty($vfield['display_type']) && $vfield['display_type'] == 'global' && $fcontent['type'] == 'submit'){
			echo '<input type="hidden" name="fields[' . esc_attr($kfield) . '][label]" class="emd-form-builder-field-label" id="emd-fbl-' . esc_attr($kfield) . '" value="' . esc_html($vfield['label']) . '"/>';
			echo '<div>' . esc_html__('There are no settings options for global fields.','youtube-showcase') . '</div>';
		}
		else {
			echo '<div class="emd-form-builder-field-setting label">';
			echo '<label for="' . esc_attr($kfield) . '-label">' . esc_html__('Label','youtube-showcase') . '</label>';
			echo '<input type="text" name="fields[' . esc_attr($kfield) . '][label]" class="emd-form-builder-field-label" id="emd-fbl-' . esc_attr($kfield) . '" value="' . esc_html($vfield['label']) . '"/>'; 
			echo '</div>';
			echo '<div class="emd-form-builder-field-setting req">';
			echo '<input type="checkbox" name="fields[' . esc_attr($kfield) . '][req]" class="inline emd-form-builder-field-req" id="emd-fbr-' . esc_attr($kfield) . '" value=1';
			if(!empty($vfield['req'])){
				echo ' checked';
			}
			echo '>'; 
			echo '<label class="inline" for="' . esc_attr($kfield) . '-req">' . esc_html__('Required','youtube-showcase') . '</label>';
			echo '</div>';
			echo '<div class="emd-form-builder-field-setting size">';
			echo '<label for="' . esc_attr($kfield) . '-size">' . esc_html__('Size','youtube-showcase') . '</label>';
			echo '<select name="fields[' . esc_attr($kfield) . '][size]" class="emd-form-builder-field-size" id="emd-fbs-' . esc_attr($kfield) . '">';
			for($i=1;$i<=12;$i++){
				echo '<option value=' . esc_attr($i);
				if(!empty($vfield['size']) && $vfield['size'] == $i){
					echo ' selected';
				}
				echo '>' . esc_html($i) . '</option>';
			}
			echo '</select>';
			echo '<p class="desc">' . esc_html__('Size column refers to the form elements length relative to the other elements in the same row. Total element size in each row can not exceed 12 units.','youtube-showcase') . '</p>';
			echo '</div>';
			echo '<div class="emd-form-builder-field-setting desc">';
			echo '<label for="' . esc_attr($kfield) . '-desc">' . esc_html__('Description','youtube-showcase') . '</label>';
			echo '<textarea name="fields[' . esc_attr($kfield) . '][desc]" id="emd-fbd-' . esc_attr($kfield) . '" class="emd-form-builder-field-desc" rows=3>'; 
			if(!empty($vfield['desc'])){
				echo esc_textarea($vfield['desc']);
			}
			echo '</textarea>';
			echo '</div>';
			echo '<div class="emd-form-builder-field-setting placeholder">';
			echo '<label for="' . esc_attr($kfield) . '-placeholder">' . esc_html__('Placeholder','youtube-showcase') . '</label>';
			echo '<input type="text" name="fields[' . esc_attr($kfield) . '][placeholder]" class="emd-form-builder-field-placeholder" id="emd-fbp-' . esc_attr($kfield) . '" value="' . esc_html($vfield['placeholder']) . '"/>'; 
			echo '</div>';
			echo '<div class="emd-form-builder-field-setting css">';
			echo '<label for="' . esc_attr($kfield) . '-css-class">' . esc_html__('Css Class','youtube-showcase') . '</label>';
			echo '<input type="text" name="fields[' . esc_attr($kfield) . '][css_class]" class="emd-form-builder-field-css" id="emd-fbc-' . esc_attr($kfield) . '"';
			if(!empty($vfield['css_class'])){
				echo ' value="' . esc_html($vfield['css_class']) . '"';
			}
			echo '/>'; 
			echo '</div>';
		}
		echo '</div>';
	}	
	foreach($html_fields as $hfield => $hvfield){
		echo '<div class="emd-form-builder-field-settings-wrap emd-field-' . esc_attr($hfield) . '" style="display:none;">
			<div class="emd-form-builder-field-setting html">
			<label for="emd-field-html">' . esc_html__('HTML','youtube-showcase') . '</label>
			<textarea name="fields[' . esc_attr($hfield) .'][value]" id="emd-fbhtml-' . esc_attr(str_replace('html','',$hfield)) . '" class="emd-form-builder-field-html" rows=3>' . esc_html($hvfield['value']) . '</textarea>
			</div></div>';
	}
	foreach($page_fields as $pfield => $pvfield){
		echo '<div class="emd-form-builder-field-settings-wrap emd-field-page' . esc_attr($pfield) . '" style="display:none;">
			<div class="emd-form-builder-field-setting step-title">
			<label for="emd-field-step-title">' . esc_html__('Step Title','youtube-showcase') . '</label>
			<input type="text" name="fields[step_title_' . esc_attr($pfield) . '][value]" id="emd-fbform-' . esc_attr($pfield) . '" class="emd-form-builder-field-step-title"';
		if(!empty($pvfield['step_title'])){
			echo ' value="' . esc_html($pvfield['step_title']) . '"';
		}
		echo '>
			</div>
			<div class="emd-form-builder-field-setting step-desc">
			<label for="emd-field-step-desc">' . esc_html__('Step Description','youtube-showcase') . '</label>
			<textarea name="fields[step_desc_' . esc_attr($pfield) . '][value]" id="emd-fbform-' . esc_attr($pfield) . '" class="emd-form-builder-field-step-desc" rows=3>';
		if(!empty($pvfield['step_desc'])){
			echo esc_textarea($pvfield['step_desc']);
		}
		echo '</textarea>
			</div></div>';
	}
	if(!empty($has_user)){
		echo '<div class="emd-form-builder-field-settings-wrap emd-field-login_box_username" style="display:none;">
			<div class="emd-form-builder-field-setting label">
			<label for="login_box-login-label">' . esc_html__('Login Label','youtube-showcase') . '</label>
			<input type="text" name="fields[login_box_username][login_label]" class="emd-form-builder-field-label" id="emd-fbl-login_box_login_label" value="';
		if(!empty($login_fields['login_box_username']['login_label'])){
			echo  esc_html($login_fields['login_box_username']['login_label']);
		}
		else {
			echo esc_html__('Already have an account? Login.','youtube-showcase');
		}
		echo  '"></div>
			<div class="emd-form-builder-field-setting label">
			<label for="login_box-register-label">' . esc_html__('Register Label','youtube-showcase') . '</label>
			<input type="text" name="fields[login_box_username][reg_label]" class="emd-form-builder-field-label" id="emd-fbl-login_box_reg_label" value="';
		if(!empty($login_fields['login_box_username']['reg_label'])){
			echo  esc_html($login_fields['login_box_username']['reg_label']);
		}
		else {
			echo esc_html__('Need to create an account? Register.','youtube-showcase');
		}
		echo  '"></div>
		<div class="emd-form-builder-field-setting label">
		<label for="login_box-username-label">' . esc_html__('Login Username Label','youtube-showcase') . '</label>
		<input type="text" name="fields[login_box_username][label]" class="emd-form-builder-field-label" id="emd-fbl-login_box_username_label" value="';
		if(!empty($login_fields['login_box_username']['label'])){
			echo  esc_html($login_fields['login_box_username']['label']);
		}
		else {
			echo esc_html__('Username','youtube-showcase');
		}
		echo  '"></div>
		<div class="emd-form-builder-field-setting label">
		<label for="login_box-password-label">' . esc_html__('Login Password Label','youtube-showcase') . '</label>
		<input type="text" name="fields[login_box_password][label]" class="emd-form-builder-field-label" id="emd-fbl-login_box_password_label" value="';
		if(!empty($login_fields['login_box_password']['label'])){
			echo  esc_html($login_fields['login_box_password']['label']);
		}
		else {
			echo esc_html__('Password','youtube-showcase');
		}
		echo  '"></div>
		<div class="emd-form-builder-field-setting label">
		<label for="login_box-redirect-link">' . esc_html__('Redirect Link','youtube-showcase') . '</label>
		<input type="text" name="fields[login_box_username][redirect_link]" class="emd-form-builder-field-label" id="emd-fbl-login_box_redirect_link" value="';
		if(!empty($login_fields['login_box_username']['redirect_link'])){
			echo  esc_url($login_fields['login_box_username']['redirect_link']);
		}
		echo  '">';
		echo '<p class="desc">' . esc_html__('If left empty after login user will be redirected to single entity page.','youtube-showcase') . '</p>
		</div>
		<div class="emd-form-builder-field-setting enable-register">';
		echo '<input type="checkbox" name="fields[login_box_reg_username][enable_registration]" class="inline emd-form-builder-field-label" id="emd-fbr-login_box_enable_registation" value=1';
		if(!empty($login_fields['login_box_reg_username']['enable_registration'])){
			echo ' checked';
		}
		echo '>'; 
		echo '<label class="inline" for="login_box-enable_registration">' . esc_html__('Enable Registration','youtube-showcase') . '</label>
		</div>
		<div class="emd-form-builder-field-setting label">
		<label for="login_box-reg-username-label">' . esc_html__('Registration Username Label','youtube-showcase') . '</label>
		<input type="text" name="fields[login_box_reg_username][label]" class="emd-form-builder-field-label" id="emd-fbl-login_box_reg_username_label" value="';
		if(!empty($login_fields['login_box_reg_username']['label'])){
			echo  esc_html($login_fields['login_box_reg_username']['label']);
		}
		else {
			echo esc_html__('Username','youtube-showcase');
		}
		echo  '"></div>
		<div class="emd-form-builder-field-setting label">
		<label for="login_box-reg-password-label">' . esc_html__('Registration Password Label','youtube-showcase') . '</label>
		<input type="text" name="fields[login_box_reg_password][label]" class="emd-form-builder-field-label" id="emd-fbl-login_box_reg_password_label" value="';
		if(!empty($login_fields['login_box_reg_password']['label'])){
			echo  esc_html($login_fields['login_box_reg_password']['label']);
		}
		else {
			echo esc_html__('Password','youtube-showcase');
		}
		echo  '"></div>
		<div class="emd-form-builder-field-setting label">
		<label for="login_box-reg-confirm-password-label">' . esc_html__('Registration Confirm Password Label','youtube-showcase') . '</label>
		<input type="text" name="fields[login_box_reg_confirm_password][label]" class="emd-form-builder-field-label" id="emd-fbl-login_box_reg_confirm_password_label" value="';
		if(!empty($login_fields['login_box_reg_confirm_password']['label'])){
			echo  esc_html($login_fields['login_box_reg_confirm_password']['label']);
		}
		else {
			echo esc_html__('Confirm Password','youtube-showcase');
		}
		echo  '"></div>
		</div>';
	}
}
add_action('wp_ajax_emd_form_builder_lite_get_page', 'emd_form_builder_lite_get_page');
function emd_form_builder_lite_get_page(){
	check_ajax_referer('emd_form', 'nonce');
	$fcap = 'manage_options';
	$fcap = apply_filters('emd_settings_pages_cap', $fcap, sanitize_text_field($_POST['app']));
	if(!current_user_can($fcap)){
		echo false;
		die();
	}
	if(!empty($_GET['page_id']) && !empty($_GET['app']) && !empty($_GET['entity'])){
		$npid = (int) $_GET['page_id'] + 1;
		$app = sanitize_text_field($_GET['app']);
		$entity = sanitize_text_field($_GET['entity']);
		$page = '<div class="emd-form-builder-page" id="emd-form-builder-page-' . $npid . '" title="' . esc_html__('Click to go to this page','youtube-showcase') . '">
			<a href="#" class="emd-form-builder-page-delete" title="' . esc_html__('Delete','youtube-showcase') . '"><span class="field-icons times-circle" aria-hidden="true"></span></a>' .
			esc_html__('Page','youtube-showcase') . ' ' . esc_attr($npid) . '</div>';
		$playout = '<div class="emd-form-page-wrap" id="emd-form-page-' . esc_attr($npid) . '" data-page="' . esc_attr($npid) . '" style="display:none;">
			<input type="hidden" class="emd-form-page-hidden" name="layout[]" value="page">
			<div class="emd-form-row init">
			<a href="#" class="emd-form-row-delete" title="' . esc_html__('Delete','youtube-showcase') . '"><span class="field-icons times-circle" aria-hidden="true"></span></a>
			<span class="emd-form-row-info">' . esc_html__('Drag to reorder','youtube-showcase') . '</span>
			<div class="emd-form-row-holder" data-app="' . esc_attr($app) . '" data-entity="' . esc_attr($entity) . '" data-row="0">
			<input type="hidden" name="layout[]" value="row">
			<div class="emd-form-insert-row">' . esc_html__('Drag fields here','youtube-showcase') . 
			'</div>
			</div>
			</div>
			</div>';
		$psetting = '';
		if($_GET['page_id'] == 1){
			$page_id = sanitize_text_field($_GET['page_id']);
			$psetting .= '<div class="emd-form-builder-field-settings-wrap emd-field-page' . esc_attr($page_id) . '" style="display:none;">
				<div class="emd-form-builder-field-setting step-title">
				<label for="emd-field-step-title">' . esc_html__('Step Title','youtube-showcase') . '</label>
				<input type="text" name="fields[step_title_' . esc_attr($page_id) . '][value]" id="emd-fbform-' . esc_attr($page_id) . '" class="emd-form-builder-field-step-title">
				</div>
				<div class="emd-form-builder-field-setting step-desc">
				<label for="emd-field-step-desc">' . esc_html__('Step Description','youtube-showcase') . '</label>
				<textarea name="fields[step_desc_' . esc_attr($page_id) . '][value]" id="emd-fbform-' . esc_attr($page_id) . '" class="emd-form-builder-field-step-desc" rows=3></textarea>
				</div></div>';
		}
		$psetting .= '<div class="emd-form-builder-field-settings-wrap emd-field-page' . esc_attr($npid) . '" style="display:none;">
			<div class="emd-form-builder-field-setting step-title">
			<label for="emd-field-step-title">' . esc_html__('Step Title','youtube-showcase') . '</label>
			<input type="text" name="fields[step_title_' . esc_attr($npid) . '][value]" id="emd-fbform-' . esc_attr($npid) . '" class="emd-form-builder-field-step-title">
			</div>
			<div class="emd-form-builder-field-setting step-desc">
			<label for="emd-field-step-desc">' . esc_html__('Step Description','youtube-showcase') . '</label>
			<textarea name="fields[step_desc_' . esc_attr($npid) . '][value]" id="emd-fbform-' . esc_attr($npid) . '" class="emd-form-builder-field-step-desc" rows=3></textarea>
			</div></div>';
		wp_send_json_success(array('page' => $page, 'playout' => $playout, 'setting' => $psetting));
	}
}
add_action('wp_ajax_emd_form_builder_lite_get_row','emd_form_builder_lite_get_row');
function emd_form_builder_lite_get_row(){
	check_ajax_referer('emd_form', 'nonce');
	$fcap = 'manage_options';
	$fcap = apply_filters('emd_settings_pages_cap', $fcap, sanitize_text_field($_POST['app']));
	if(!current_user_can($fcap)){
		echo false;
		die();
	}
	if(!empty($_GET['app']) && !empty($_GET['entity'])){
		$entity = sanitize_text_field($_GET['entity']);
		$app = sanitize_text_field($_GET['app']);
		$playout = '<div class="emd-form-row init">
			<a href="#" class="emd-form-row-delete" title="' . esc_html__('Delete','youtube-showcase') . '"><span class="field-icons times-circle" aria-hidden="true"></span></a>
			<span class="emd-form-row-info">' . esc_html__('Drag to reorder','youtube-showcase') . '</span>
			<div class="emd-form-row-holder" data-app="' . esc_attr($app) . '" data-entity="' . esc_attr($entity) . '" data-row="0">
			<input type="hidden" name="layout[]" value="row">
			<div class="emd-form-insert-row">' . esc_html__('Drag fields here','youtube-showcase') . 
			'</div>
			</div>
			</div>';
		wp_send_json_success(array('row' => $playout));
	}
}
function emd_form_sanitize_layout_data(&$mydata,$key){
	$mydata = sanitize_text_field($mydata);
}	
add_action('wp_ajax_emd_form_builder_lite_save_form','emd_form_builder_lite_save_form');

function emd_form_builder_lite_save_form(){
	check_ajax_referer('emd_form', 'nonce');
	$fcap = 'manage_options';
	$fcap = apply_filters('emd_settings_pages_cap', $fcap, sanitize_text_field($_POST['app']));
	if(!current_user_can($fcap)){
		echo false;
		die();
	}
	$layout = Array();
	$fields = Array();
	if(!empty($_POST['data'])){
		$data = json_decode(stripslashes($_POST['data']),true);
		array_walk_recursive($data,'emd_form_sanitize_layout_data');
		$pcount = 0;
		$rcount = 0;
		foreach($data as $mydata){
			if($mydata['name'] == 'id'){
				$form_id = $mydata['value'];
			}
			elseif(preg_match('/^fields(\[([^\[\]]+)\]\[([^\[\]]+)\])/',$mydata['name'],$matches)){
				$fields[$matches[2]][$matches[3]] = $mydata['value'];
				if($matches[2] == 'login_box_username'){
					$fields['login_box_password'][$matches[3]] = $mydata['value'];
					$fields['login_box_password']['req'] = 1;
					$fields[$matches[2]]['req'] = 1;
				}
			}
		}
		foreach($data as $mydata){
			if($mydata['name'] == 'layout[]' && $mydata['value'] == 'page'){
				$pcount ++;
				$rcount = 0;
				$layout[$pcount]['step_title'] = $fields['step_title_' . $pcount]['value'];
				$layout[$pcount]['step_desc'] = $fields['step_desc_' . $pcount]['value'];
			}
			elseif($mydata['name'] == 'layout[]' && $mydata['value'] == 'row'){
				$rcount ++;
			}
			elseif($mydata['name'] == 'layout[]' && $mydata['value'] == 'hr'){
				$layout[$pcount]['rows'][$rcount][][$mydata['value']] = Array('show' => 1);
			}
			elseif($mydata['name'] == 'layout[]' && !empty($fields[$mydata['value']])){
				$fields[$mydata['value']]['show'] = 1;
				$layout[$pcount]['rows'][$rcount][][$mydata['value']] = $fields[$mydata['value']];
			}
		}
	}
	if(!empty($form_id)){		
		foreach($layout as $kpage => $cpage){
			if(empty($cpage['rows'])){
				unset($layout[$kpage]);
			}
		}
		$form = get_post($form_id);
		$myform = json_decode($form->post_content,true);
		$myform['layout'] = $layout;
		$form_data = array(
				'ID' => $form_id,
				'post_content' => wp_slash(json_encode($myform,true)),
				);
		$res = wp_update_post($form_data);
		if(!is_wp_error($res)){
			wp_send_json_success();
		}
	}
	die();
}
function emd_form_builder_lite_get_cfield($fentity,$kfield,$attr_list,$ent_list,$txn_list,$rel_list){
	$rel_labels = Array();
	$cfield = Array('req' => 0);
	foreach($rel_list as $krel => $vrel){
		if($fentity == $vrel['from']){
			$rel_labels[$krel]['label'] = $vrel['from_title'];
		}
		elseif($fentity == $vrel['to']){
			$rel_labels[$krel]['label'] = $vrel['to_title'];
		}
		if(!empty($vrel['desc'])){
			$rel_labels[$krel]['desc'] = $vrel['desc'];
		}
		elseif(!empty($rel_labels[$krel]['label'])){
			$rel_labels[$krel]['desc'] = $rel_labels[$krel]['label'];
		}
	}
	if(in_array($kfield,Array('blt_title','blt_content','blt_excerpt'))){
		if(!empty($ent_list[$fentity]['req_blt'][$kfield])){
			$cfield['label'] = $ent_list[$fentity]['req_blt'][$kfield]['msg'];
		}
		elseif(!empty($ent_list[$fentity]['blt_list'][$kfield])){
			$cfield['label'] = $ent_list[$fentity]['blt_list'][$kfield];
		}	
		$cfield['desc'] = $cfield['label'];
	}
	elseif(!empty($rel_labels[$kfield])){
		$cfield['label'] = $rel_labels[$kfield]['label'];
		$cfield['desc'] = $rel_labels[$kfield]['desc'];
	}
	elseif(!empty($attr_list[$fentity][$kfield])){
		if(!empty($attr_list[$fentity][$kfield]['desc'])){
			$cfield['desc'] = $attr_list[$fentity][$kfield]['desc'];
		}	
		if(!empty($attr_list[$fentity][$kfield]['label'])){
			$cfield['label'] = $attr_list[$fentity][$kfield]['label'];
		}
	}
	elseif(!empty($txn_list[$fentity][$kfield])){
		$cfield['label'] = $txn_list[$fentity][$kfield]['single_label'];
		if(!empty($txn_list[$fentity][$kfield]['desc'])){
			$cfield['desc'] = $txn_list[$fentity][$kfield]['desc'];
		}
	}
	if(!empty($cfield['label'])){
		$cfield['placeholder'] = $cfield['label'];
	}
	$cfield['size'] = 12;
	return $cfield;
}
add_action('wp_ajax_emd_form_builder_lite_get_hr', 'emd_form_builder_lite_get_hr');
function emd_form_builder_lite_get_hr(){
	check_ajax_referer('emd_form', 'nonce');
	$fcap = 'manage_options';
	$fcap = apply_filters('emd_settings_pages_cap', $fcap, sanitize_text_field($_POST['app']));
	if(!current_user_can($fcap)){
		echo false;
		die();
	}
	if(!empty($_GET['app']) && !empty($_GET['entity'])){
		$playout = '<div class="emd-form-row">
			<a href="#" class="emd-form-row-delete" title="' . esc_html__('Delete','youtube-showcase') . '"><span class="field-icons times-circle" aria-hidden="true"></span></a>
			<span class="emd-form-row-info">' . esc_html__('Drag to reorder','youtube-showcase') . '</span>
			<input type="hidden" name="layout[]" value="row">
			<hr class="emd-form-row-hr">
			<input type="hidden" name="layout[]" value="hr">
			</div>';
		wp_send_json_success(array('row' => $playout));
	}
}
add_action('wp_ajax_emd_form_builder_lite_get_html', 'emd_form_builder_lite_get_html');
function emd_form_builder_lite_get_html(){
	check_ajax_referer('emd_form', 'nonce');
	$fcap = 'manage_options';
	$fcap = apply_filters('emd_settings_pages_cap', $fcap, sanitize_text_field($_POST['app']));
	if(!current_user_can($fcap)){
		echo false;
		die();
	}
	if(!empty($_GET['htmlcount']) && !empty($_GET['app']) && !empty($_GET['entity'])){
		$htmlcount = sanitize_text_field($_GET['htmlcount']);
		$app = sanitize_text_field($_GET['app']);
		$entity = sanitize_text_field($_GET['entity']);
		$playout = '<div class="emd-form-row init html">
			<a href="#" class="emd-form-row-delete" title="' . esc_html__('Delete','youtube-showcase') . '"><span class="field-icons times-circle" aria-hidden="true"></span></a>
			<span class="emd-form-row-info">' . esc_html__('Drag to reorder','youtube-showcase') . '</span>
			<div class="emd-form-row-holder" data-app="' . esc_attr($app) . '" data-entity="' . esc_attr($entity) . '" data-row="0">
			<input type="hidden" name="layout[]" value="row">
			<div class="emd-form-field emd-form-html emd-col" data-field="html' . esc_attr($htmlcount) . '">
			<a href="#" class="emd-form-field-delete" title="' . esc_html__('Delete','youtube-showcase') . '"><span class="field-icons times-circle" aria-hidden="true"></span></a>
			<span class="emd-form-field-info" style="cursor:pointer;">' . esc_html__('Drag to reorder / Click for settings','youtube-showcase') . '</span>
			<div class="emd-form-group">
			<input type="hidden" name="layout[]" value="html' . esc_attr($htmlcount) . '">
			<div id="html' . esc_attr($htmlcount) . '" class="emd-form-html-div"><span class="emd-html-click">' . esc_html__('Click to go to settings','youtube-showcase') . '</span>
			<input type="text" class="text emd-input-md emd-form-control html-code" style="display:none;" placeholder="' . esc_html__('HTML','youtube-showcase') . '" disabled/>
			</div>
			</div>
			</div>
			</div>
			</div>';
		$psetting = '<div class="emd-form-builder-field-settings-wrap emd-field-html' . esc_attr($htmlcount) . '" style="display:none;">
			<div class="emd-form-builder-field-setting html">
			<label for="emd-field-html">' . esc_html__('HTML','youtube-showcase') . '</label>
			<textarea name="fields[html' . esc_attr($htmlcount) . '][value]" id="emd-fbhtml-' . esc_attr($htmlcount) . '" class="emd-form-builder-field-html" rows=3></textarea>
			</div>';
		wp_send_json_success(array('row' => $playout,'setting' => $psetting));
	}
}
function emd_form_builder_lite_login_box($kfield,$cfield){
	if($kfield == 'login_box_username'){
		$lay = '<div class"login_register">
			<input type="text" name="' . esc_attr($kfield) . '" id="' . esc_attr($kfield) . '" class="text emd-input-md emd-form-control" placeholder="' . esc_html__('Login / Register Box','youtube-showcase') . '" disabled/>';
		$lay .= '</div></div>';
	}
	else {
		$lay = '';
	}
	return $lay;
}

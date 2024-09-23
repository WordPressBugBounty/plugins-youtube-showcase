<?php
/**
 * Form Settings Functions
 *
 */
if (!defined('ABSPATH')) exit;

function emd_form_builder_lite_settings($type,$app,$form_id=0,$result = ''){
	if($type == 'edit'){
		$myform = get_post($form_id);
		$fcontent = json_decode($myform->post_content,true);
		$fcontent['title'] = $myform->post_title;
		$fcontent['settings']['type'] = $fcontent['type'];
	}
	elseif($type == 'new'){
		$fcontent = Array();
	}
	$tabs['general'] = __('General', 'youtube-showcase');
	$tabs['display'] = __('Display', 'youtube-showcase');
	$tabs['submit'] = __('Submissions', 'youtube-showcase');
	if(empty($fcontent['type']) || (!empty($fcontent['type']) && $fcontent['type'] != 'search')){
		$tabs['confirm'] = __('Confirmations', 'youtube-showcase');
	}
	$tabs['schedule'] = __('Scheduling', 'youtube-showcase');
	$tabs['antispam'] = __('Anti Spam', 'youtube-showcase');
	if(empty($fcontent['type']) || (!empty($fcontent['type']) && $fcontent['type'] != 'search')){
		$tabs['page'] = __('Pages', 'youtube-showcase');
	}
	else {
		//this will be added when we add ability to create new search forms
		$tabs['search'] = __('Search','youtube-showcase');
	}
	$tabs['code'] = __('Code', 'youtube-showcase');
	$active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';
	echo '<div class="wrap">';
	echo '<form name="emd-form-settings" method="post">';
	echo '<input type="hidden" name="submit_settings" value="1">';
	wp_nonce_field('emd_form_settings', 'emd_form_settings_nonce', false, true);
	if($type == 'edit'){
		echo '<input type="hidden" name="id" value="' . esc_attr($form_id) . '">';
		echo '<input type="hidden" name="stype" value="edit">';
		echo '<input type="hidden" name="app" value="' . esc_attr($app) . '">';
		echo '<h2>' . esc_html__('Form Settings:','youtube-showcase') . ' ' . esc_html($myform->post_title) . '</h2>';
	}
	else {
		echo '<input type="hidden" name="stype" value="new">';
		echo '<input type="hidden" name="app" value="' . esc_attr($app) . '">';
		echo '<h2>' . esc_html__('Form Settings:','youtube-showcase')  . '</h2>';
	}	
	if($result == 'error'){
		echo '<div class="is-dismissible notice error"><p><strong>' . esc_html__('Error when saving.','youtube-showcase') . '</strong></p></div>';
	}
	elseif($result == 'success'){
		echo '<div class="updated is-dismissible notice"><p><strong>' . esc_html__('Saved successfully.','youtube-showcase') . '</strong></p></div>';
	}
	echo '<h2 class="nav-tab-wrapper">';
	foreach ($tabs as $ktab => $mytab) {
		$turl = remove_query_arg(array('action','_wpnonce'));
		$tab_url[$ktab] = esc_url(add_query_arg(array(
						'tab' => $ktab
						),$turl));
		$active = "";
		$pro = "";
		if ($active_tab == $ktab) {
			$active = "nav-tab-active";
		}
		if(in_array($ktab,Array('display','submit','schedule','page','code'))){
			$pro = " upgrade-pro";
		}
		echo '<a href="' . esc_url($tab_url[$ktab]) . '" class="nav-tab ' . esc_attr($active) . esc_attr($pro) . '" id="nav-' . esc_attr($ktab) . '">' . esc_html($mytab) . '</a>';
	}
	echo '</h2>';
	foreach(array_keys($tabs) as $tab){
		emd_form_builder_lite_tab($type,$tab,$app,$active_tab,$fcontent);
	}
	echo submit_button(); 
	echo '</form>';
	echo '</div>';
}
function emd_form_builder_lite_tab($type,$mytab,$app,$active_tab,$fcontent){
	switch($mytab){
		case 'general':
			$login_reg_options = Array('none' => __('None','youtube-showcase'),
				'both' => __('Registration and Login Forms','youtube-showcase'),
				'login' => __('Login Form only','youtube-showcase'));
			$type_options = Array('submit' => __('Submit','youtube-showcase'),
				'search' => __('Search','youtube-showcase')
			);
			$disabled = false;
			if((!empty($fcontent['source']) && $fcontent['source'] == 'plugin') || $type != 'new'){
				$disabled = true;
			}
			$ent_list = get_option($app . '_ent_list', Array());
			foreach($ent_list as $kent => $myent){
				$ent_options[$kent] = $myent['label'];
			}	
			$tab_fields = Array('title' => Array('label' => __('Name','youtube-showcase'), 'type' => 'text', 'required' => true),
			'type' => Array('label' => __('Type','youtube-showcase'), 'type' => 'select', 'options' => $type_options, 'disabled' => $disabled, 'required' => true),
			'entity' => Array('label' => __('Entity','youtube-showcase'), 'type' => 'select', 'options' => $ent_options, 'disabled' => $disabled, 'required' => true),
			'login_reg' => Array('label' => __('Show Register / Login Form','youtube-showcase'), 'type' => 'select', 'options' => $login_reg_options,
					'desc'=>__('Displays or hides registration and login forms on this form for non-logged-in users.','youtube-showcase')),
			'noaccess_msg' => Array('label' => __('No Access Message','youtube-showcase'), 'type' => 'textarea', 'default' => __('You are not allowed to access to this area. Please contact the site administrator.','youtube-showcase')),
			);	
			break;
		case 'display':
			$device_options = Array('desktops' => __("Desktops","youtube-showcase"),
					'phones' => __("Phones","youtube-showcase"),
					'tablets' => __("Tablets","youtube-showcase"),
					'large_desktops' => __("Large Desktops","youtube-showcase")
			);
			$placement_options = Array('top' => __("Top","youtube-showcase"),
						'left' => __("Left","youtube-showcase"),
						'inside' => __('Inside','youtube-showcase'),
			);
			$size_options = Array('medium' => __("Medium","youtube-showcase"),
						'small' => __("Small","youtube-showcase"),
						'large' => __('Large','youtube-showcase'),
			);
			$tab_fields = Array('targeted_device' => Array('label' => __('Targeted Device','youtube-showcase'), 'type' => 'select', 'options' => $device_options),
					'label_position' => Array('label' => __('Label Placement','youtube-showcase'), 'type' => 'select', 'options' => $placement_options,
						'desc' => __("Sets the field label position relative to the field input location. Options are Top,Left or Inside. Pick your label placement based on the space you have available for the form. Min 680px required for inside/top label placement with 3 column layout. If you enabled operators in your search form, you will need more space for multi-layout designs. You can always adjust the width css element of your form container when needed. Enabling operators will give access to all of your data so limiting access by role may always be a good idea.","youtube-showcase")
						),
					'element_size' => Array('label' => __('Element Size','youtube-showcase'), 'type' => 'select', 'options' => $size_options),		
					'display_inline' => Array('label' => __('Display Radios and Checkboxes Inline','youtube-showcase'), 'type'=>'checkbox'),
					'fill_usermap' => Array('label' => __('Do not autofill Logged-in User Information','youtube-showcase'), 'type'=>'checkbox'),
					'form_class' => Array('label' => __('Form Class','youtube-showcase'), 'type'=>'text'),
				);		
			break;
		case 'submit':
			$status_options = Array('publish' => __('Publish','youtube-showcase'),
						'draft' => __('Draft','youtube-showcase'),
						'future' => __('Future','youtube-showcase'),
						'private' => __('Private','youtube-showcase'),
						'trash' => __('Trash','youtube-showcase')
			);
			$button_options = Array('btn-standard' =>  __("Standard (White - #FFFFF)","youtube-showcase"),
						'btn-primary' => __("Primary (Blue - #006DCC)","youtube-showcase"),
						'btn-info' => __("Info (Light Blue - #49AFCD)","youtube-showcase"),
						'btn-success' => __("Success (Green - #5BB75B)","youtube-showcase"),
						'btn-warning' => __("Warning (Orange - #FAA732)","youtube-showcase"),
						'btn-danger' => __("Danger (Red - #DA4F49)","youtube-showcase"),
						'btn-inverse' => __("Inverse (Black - #363636)","youtube-showcase"),
						'btn-link' => __("Link (Blue -  #0088CC)","youtube-showcase"),
						'btn-custom' => __("Custom","youtube-showcase"),
			);
			$button_size_options = Array('btn-std' =>  __("Standard","youtube-showcase"),
							'btn-xlarge' => __("XLarge","youtube-showcase"),
							'btn-large' => __("Large","youtube-showcase"),
							'btn-small' => __("Small","youtube-showcase"),
							'btn-mini' => __("Mini","youtube-showcase"),
			);
			$fa_size_options = Array('' => __('Standard','youtube-showcase'),
					'fa-lg' => __('Large','youtube-showcase'),
					'fa-2x' => __('2x','youtube-showcase'),
					'fa-3x' => __('3x','youtube-showcase'),
					'fa-4x' => __('4x','youtube-showcase'),
					'fa-5x' => __('5x','youtube-showcase'),
					);
			$fa_pos_options = Array('left' => __('Left','youtube-showcase'),
						'right' => __('Right','youtube-showcase'),
					);
			if(empty($fcontent['type']) || (!empty($fcontent['type']) && $fcontent['type'] != 'search')){
				$tab_fields = Array('disable_submit' => Array('label' => 'Disable Submit Action', 'type' => 'checkbox'),
					'submit_status' => Array('label' => 'Submit Status', 'type' => 'select', 'depend' => 'settings_disable_submit_1','options' => $status_options),		
					'visitor_submit_status' => Array('label' => 'Visitor Submit Status', 'type' => 'select', 'depend' => 'settings_disable_submit_1','options' => $status_options),		
					'submit_button_type' => Array('label' => 'Submit Button Type', 'type' => 'select','options' => $button_options),		
					'submit_button_label' => Array('label' => 'Submit Button Label', 'type' => 'text'),		
					'submit_button_class' => Array('label' => 'Submit Button Class', 'type' => 'text'),		
					'submit_button_block' => Array('label' => 'Create Block Level Button', 'type'=>'checkbox'),
					'submit_button_size' => Array('label' => 'Submit Button Size', 'type' => 'select','options' => $button_size_options),
					'submit_button_fa' => Array('label' => 'Submit Button Icon Class', 'type' => 'text', 'size'=> 'medium'),
					'submit_button_fa_size' => Array('label' => 'Submit Button Icon Size', 'type' => 'select', 'options' => $fa_size_options),
					'submit_button_fa_pos' => Array('label' => 'Submit Button Icon Position', 'type' => 'select', 'options' => $fa_pos_options),
					'disable_after' => Array('label' => 'Disable After', 'type' => 'text', 'size'=> 'mini', 'depend' => 'settings_disable_submit_1'),		
				);
			}
			else {
				$tab_fields = Array('submit_button_type' => Array('label' => 'Submit Button Type', 'type' => 'select','options' => $button_options),		
					'submit_button_label' => Array('label' => 'Submit Button Label', 'type' => 'text'),		
					'submit_button_block' => Array('label' => 'Create Block Level Button', 'type'=>'checkbox'),
					'submit_button_size' => Array('label' => 'Submit Button Size', 'type' => 'select','options' => $button_size_options),
					'submit_button_fa' => Array('label' => 'Submit Button Icon Class', 'type' => 'text', 'size'=> 'small'),
					'submit_button_fa_size' => Array('label' => 'Submit Button Icon Size', 'type' => 'select', 'options' => $fa_size_options),
					'submit_button_fa_pos' => Array('label' => 'Submit Button Icon Position', 'type' => 'select', 'options' => $fa_pos_options),
				);
			}
			break;
		case 'confirm':
			$confirm_options = Array('text' => __('Show text','youtube-showcase'),
						'redirect' => __('Redirect','youtube-showcase'),
						'form' => __('Go to another form','youtube-showcase')
					);
			$after_options = Array('show' => __('Show Form','youtube-showcase'),
						'hide' => __('Hide Form','youtube-showcase'),
			);
			$forms = get_posts(Array('post_type'=>'emd_form','posts_per_page'=>-1,'post_status'=>'publish'));
			$form_options = Array();
			if(!empty($forms)){
				foreach($forms as $myform){
					$mycont = json_decode($myform->post_content,true);
					if((empty($fcontent['name']) || (!empty($fcontent['name']) && $mycont['name'] != $fcontent['name'])) && $mycont['type'] == 'submit'){
						$form_options[$myform->ID] = $myform->post_title;
					}
				}
			}
			$tab_fields = Array('confirm_method' => Array('label' => 'Confirmation Method', 'type' => 'select', 'options' => $confirm_options, 'upgrade' => true, 'disabled' => true),
					'confirm_form' => Array('label' => 'Next Form', 'type' => 'select', 'depend' => 'settings_confirm_method_form', 'options' => $form_options, 'upgrade' => true, 'disabled' => true),
					'confirm_url' => Array('label' => 'Redirect URL', 'type' => 'text', 'depend' => 'settings_confirm_method_redirect', 'upgrade' => true, 'disabled' => true),
					'enable_ajax' => Array('label' => 'Enable Ajax', 'type' => 'checkbox', 'depend' => 'settings_confirm_method_text', 'upgrade' => true, 'disabled' => true),
					'after_submit' => Array('label' => 'After Submit', 'type' => 'select', 'depend' => 'settings_confirm_method_text', 'options' => $after_options, 'upgrade' => true, 'disabled' => true),
					'success_msg' => Array('label' => 'Success Message', 'type' => 'textarea', 'depend' => 'settings_confirm_method_text','default'=> __('Thanks for your submission.','youtube-showcase')),
					'error_msg' => Array('label' => 'Error Message', 'type' => 'textarea', 'depend' => 'settings_confirm_method_text','default'=> __('There has been an error when submitting your entry. Please contact the site administrator.')),
					
			);
			break;
		case 'schedule':
			$tab_fields = Array('schedule_start' => Array('label' => 'Start Datetime', 'type' => 'text', 'size' => 'small'),
				'schedule_end' => Array('label' => 'End Datetime', 'type' => 'text', 'size' => 'small'),
			);
			break;
		case 'antispam':
			$captcha_options = Array('never_show' => __('Never Show','youtube-showcase'),
						'show_always' => __('Always Show','youtube-showcase'),
						'show_to_visitors' => __('Visitors Only','youtube-showcase'),
					);
			$tab_fields = Array('honeypot' => Array('label' => 'HoneyPot', 'type' => 'checkbox'),
					'captcha' => Array('label' => 'Show Captcha', 'type' => 'radio','options' => $captcha_options, 'upgrade' => true, 'disabled' => true),
					'captcha_site_key' => Array('label' => 'reCAPTCHA v3 Site Key', 'type' => 'text', 'depend' => 'settings_captcha_show_always' , 'depend2' => 'settings_captcha_show_to_visitors', 'upgrade' => true, 'disabled' => true),
					'captcha_secret_key' => Array('label' => 'reCAPTCHA v3 Secret Key', 'type' => 'text', 'depend' => 'settings_captcha_show_always', 'depend2' => 'settings_captcha_show_to_visitors', 'upgrade' => true, 'disabled' => true),
			);
			break;
		case 'page':
			$wizard_options = Array('default' => __('Tabs','youtube-showcase'),
						'arrows' => __('Arrows','youtube-showcase'),
						'circles' => __('Circles','youtube-showcase'),
						'dots' => __('Dots','youtube-showcase'),
					);
			$wizard_toolbar_options = Array('bottom' => __('Bottom','youtube-showcase'),
							'top' => __('Top','youtube-showcase'),
							'both' => __('Both','youtube-showcase')
						);
			$transition_options = Array('none' => __('None','youtube-showcase'),
						'slide' => __('Slide','youtube-showcase'),
						'fade' => __('Fade','youtube-showcase')
					);
			$status_options = Array('publish' => __('Publish','youtube-showcase'),
						'draft' => __('Draft','youtube-showcase'),
						'future' => __('Future','youtube-showcase'),
						'private' => __('Private','youtube-showcase'),
						'trash' => __('Trash','youtube-showcase')
			);
			$tab_fields = Array('wizard_style' => Array('label' => __('Wizard Style','youtube-showcase'), 'type' => 'select','options' => $wizard_options),
					'wizard_vertical' => Array('label' => __('Display Vertical Wizard Steps','youtube-showcase'), 'type' => 'checkbox'),
					'wizard_toolbar' => Array('label' => __('Wizard Button Toolbar','youtube-showcase'), 'type' => 'select','options' => $wizard_toolbar_options),
					'wizard_cancel_url' => Array('label' => 'Cancel Button Link', 'type' => 'text'),
					'wizard_trans_effect' => Array('label' => __('Wizard Transition Effect','youtube-showcase'), 'type' => 'select','options' => $transition_options),
					'wizard_trans_speed' => Array('label' => 'Wizard Transition Speed', 'type' => 'text', 'size' => 'small', 'default' => 400),
					'wizard_save_step' => Array('label' => __('Save Wizard Step Data','youtube-showcase'),'type'=>'checkbox'),
					'step_submit_status' => Array('label' => 'Wizard Step Submit Status', 'type' => 'select', 'depend' => 'settings_wizard_save_step_1','options' => $status_options),		
					'step_visitor_submit_status' => Array('label' => 'Wizard Step Visitor Submit Status', 'type' => 'select', 'depend' => 'settings_wizard_save_step_1','options' => $status_options),		
			);
			break;
		case 'search':
			$attr_list = get_option($fcontent['app'] . '_attr_list',Array());
			$txn_list = get_option($fcontent['app'] . '_tax_list', Array());
			$rel_list = get_option($fcontent['app'] . '_rel_list', Array());
			$ent_list = get_option($fcontent['app'] . '_ent_list',Array());
			$blt_fields = Array('blt_title','blt_content','blt_excerpt');
			$result_options = Array();
			foreach($blt_fields as $myblt){
				if(!empty($ent_list[$fcontent['entity']]['req_blt'][$myblt])){
					$result_options[$myblt] = $ent_list[$fcontent['entity']]['req_blt'][$myblt]['msg'];
				}
				elseif(!empty($ent_list[$fcontent['entity']]['blt_list'][$myblt])){
					$result_options[$myblt] = $ent_list[$fcontent['entity']]['blt_list'][$myblt];
				}	
			}
			if(!empty($attr_list[$fcontent['entity']])){
				foreach($attr_list[$fcontent['entity']] as $kattr => $vattr){
					if(!preg_match('/^wpas_/',$kattr)){
						$result_options[$kattr] = $vattr['label'];
					}
				}
			}
			if(!empty($txn_list[$fcontent['entity']])){
				foreach($txn_list[$fcontent['entity']] as $ktxn => $vtxn){
					$result_options[$ktxn] = $vtxn['label'];
				}
			}
			if(!empty($rel_list)){
				foreach($rel_list as $krel => $vrel){
					if($fcontent['entity'] == $vrel['from']){
						$result_options[$krel] = $vrel['from_title'];
					}
					elseif($fcontent['entity'] == $vrel['to']){
						$result_options[$krel] = $vrel['to_title'];
					}
				}
			}
			if(!empty($fcontent['source']) && $fcontent['source'] == 'plugin'){
				$temp_options = Array('simple_table' => __('Basic HTML Table','youtube-showcase'),'adv_table'=>__('Advanced Datagrid Table','youtube-showcase'),'cust_table' => __('Default','youtube-showcase'));
			}
			else {
				$temp_options = Array('simple_table' => __('Basic HTML Table','youtube-showcase'),'adv_table'=>__('Advanced Datagrid Table','youtube-showcase'));
			}
			$tab_fields = Array(
					'ajax_search' => Array('label' => __('Enable Ajax','youtube-showcase'), 'type' => 'checkbox', 'upgrade' => true, 'disabled' => true),
					'enable_operators' => Array('label' => __('Enable Search Operators','youtube-showcase'), 'type' => 'checkbox', 'upgrade' => true, 'disabled' => true),
					'display_records' => Array('label' => __('Display Records On Page Load','youtube-showcase'), 'type' => 'checkbox', 'upgrade' => true, 'disabled' => true),
					'result_templ' => Array('label' => __('Result Template Type','youtube-showcase'), 'type' => 'select', 'options' => $temp_options, 'upgrade' => true, 'disabled' => true),
					'adv_search' => Array('label' => __('Search','youtube-showcase'), 'type' => 'checkbox', 'depend' => 'settings_result_templ_adv_table', 'upgrade' => true, 'disabled' => true),
					'adv_click' => Array('label' => __('Click to Select','youtube-showcase'), 'type' => 'checkbox', 'depend' => 'settings_result_templ_adv_table', 'upgrade' => true, 'disabled' => true),
					'adv_show_col' => Array('label' => __('Show Columns','youtube-showcase'), 'type' => 'checkbox', 'depend' => 'settings_result_templ_adv_table', 'upgrade' => true, 'disabled' => true),
					'adv_show_export' => Array('label' => __('Show Export','youtube-showcase'), 'type' => 'checkbox', 'depend' => 'settings_result_templ_adv_table', 'upgrade' => true, 'disabled' => true),
					'adv_show_toggle' => Array('label' => __('Show Toggle','youtube-showcase'), 'type' => 'checkbox', 'depend' => 'settings_result_templ_adv_table', 'upgrade' => true, 'disabled' => true),
					'adv_show_all' => Array('label' => __('Show All Records','youtube-showcase'), 'type' => 'checkbox', 'depend' => 'settings_result_templ_adv_table', 'upgrade' => true, 'disabled' => true),
					'adv_page_size' => Array('label' => __('Page Size','youtube-showcase'), 'type' => 'text', 'depend' => 'settings_adv_show_all_0','size'=> 'small', 'upgrade' => true, 'disabled' => true),
					'adv_page_list' => Array('label' => __('Page List','youtube-showcase'), 'type' => 'text', 'depend' => 'settings_adv_show_all_0','desc'=> __('Enter semicolon separated values such as 10, 20, 50, all','youtube-showcase'), 'upgrade' => true, 'disabled' => true),
					'adv_maintain' => Array('label' => __('Maintain Selected on Page Change','youtube-showcase'), 'type' => 'checkbox', 'depend' => 'settings_adv_show_all_0', 'upgrade' => true, 'disabled' => true),
					'result_fields' => Array('label' => __('Result Fields','youtube-showcase'), 'type' => 'select','options' => $result_options, 'multiple' => true, 'order' => true, 'upgrade' => true, 'disabled' => true),
					'noresult_msg' => Array('label' => __('No Result Message','youtube-showcase'), 'type' => 'textarea','default'=>__('Your search returned no results.','youtube-showcase'))
					);
			break;
		case 'code':
			$tab_fields = Array('css_enq' => Array('label' => __('CDN CSS File','youtube-showcase'), 'type' => 'textarea', 'desc' => __('Enter semicolon separated CSS file urls starting with http(s).','youtube-showcase')),
					'js_enq' => Array('label' => __('CDN JS File','youtube-showcase'), 'type' => 'textarea', 'desc' => __('Enter semicolon separated JavaScript file urls starting with http(s).','youtube-showcase'))
			);
			break;
	}
	echo '<div class="tab-content" id="tab-' . esc_attr($mytab) .'"';
	if ($mytab != $active_tab ){ 
		echo 'style="display:none;"'; 
	} 
	echo '>';
	echo '<div class="emd-form-builder-fields-settings">';
	echo emd_form_builder_lite_show_setting($tab_fields,$fcontent);
	echo '</div>';
	echo '</div>';
}
function emd_form_builder_lite_show_setting($tab_fields,$fcontent){
	if(!empty($tab_fields)){
		foreach($tab_fields as $skey => $sval){
			echo '<div class="emd-form-builder-field-setting"';
			if(!empty($sval['depend'])){
				echo ' data-depend="' . esc_attr($sval['depend']) . '"';
			}
			if(!empty($sval['depend2'])){
				echo ' data-depend2="' . esc_attr($sval['depend2']) . '"';
			}
			echo '>';
			if($sval['type'] != 'checkbox'){
				echo '<label for="emd-form-' . esc_attr($skey) . '" class="emd-label';
				if(!empty($sval['required'])){
					echo ' required';
				}
				echo '">' . esc_html($sval['label']);
				if(isset($sval['upgrade']) && $sval['upgrade'] === true){
					echo '<span class="dashicons dashicons-cart upgrade-pro" data-upgrade="' . esc_attr($sval['label']) . '"></span>';
				}
				echo '</label>';
			}
			if(empty($sval['size'])){
				$sval['size'] = 'medium';
			}
			switch($sval['type']){
				case 'text':
					echo '<input type="text" id="settings_' . esc_attr($skey) . '" class="input-' . esc_attr($sval['size']) . '" name="settings[' . esc_attr($skey) . ']"';
					if(!empty($fcontent['settings'][$skey])){
						echo ' value="' . esc_attr($fcontent['settings'][$skey]) . '"';
					}
					elseif(!empty($sval['default'])){
						echo ' value="' . esc_attr($sval['default']) . '"';
					}
					if(isset($sval['disabled']) && $sval['disabled'] === true){
						echo ' disabled';
					}
					echo '/>'; 
					break;
				case 'textarea':
					echo '<textarea id="settings_' . esc_attr($skey) . '" name="settings[' . esc_attr($skey) . ']" class="input-medium">';
					if(!empty($fcontent['settings'][$skey])){
 						echo esc_textarea($fcontent['settings'][$skey]);
					}
					elseif(!empty($sval['default'])){
						echo esc_textarea($sval['default']);
					}
					echo '</textarea>'; 
					break;
				case 'select':
					if(isset($sval['multiple']) && $sval['multiple'] === true){
						echo '<select id="settings_' . esc_attr($skey) . '" name="settings[' . esc_attr($skey) . '][]" multiple';
					}
					else {
						echo '<select id="settings_' . esc_attr($skey) . '" name="settings[' . esc_attr($skey) . ']"';
					}
						if(isset($sval['disabled']) && $sval['disabled'] === true){
							echo ' disabled';
						}
						echo '>';
						if(!empty($sval['order']) && !empty($fcontent['settings'][$skey])){
							foreach($fcontent['settings'][$skey] as $saved){
								echo '<option value="' . esc_attr($saved) . '" selected';
								echo '>' . esc_html($sval['options'][$saved]) . '</option>';
							}
							foreach($sval['options'] as $kopt => $vopt){
								if(!in_array($kopt,$fcontent['settings'][$skey])){
									echo '<option value="' . esc_attr($kopt) . '"';
									echo '>' . esc_html($vopt) . '</option>';
								}
							}
						}
						else {
							foreach($sval['options'] as $kopt => $vopt){
								echo '<option value="' . esc_attr($kopt) . '"';
								if(!empty($fcontent['settings'][$skey]) && is_array($fcontent['settings'][$skey]) && in_array($kopt,$fcontent['settings'][$skey])){
									echo ' selected';
								}
								elseif(!empty($fcontent['settings'][$skey]) && $fcontent['settings'][$skey] == $kopt){
									echo ' selected';
								}
								echo '>' . esc_html($vopt) . '</option>';
							}
						}
						echo  '</select>';
					break;
				case 'checkbox':
					echo '<input type="checkbox" id="settings_' . esc_attr($skey) . '" name="settings[' . esc_attr($skey) . ']"';
					if(!empty($fcontent['settings'][$skey])){
						echo ' checked';
					}
					if(isset($sval['disabled']) && $sval['disabled'] === true){
						echo ' disabled';
					}
					echo ' value=1>';
					break;
				case 'radio':
					foreach($sval['options'] as $kopt => $vopt){
						echo '<div class="emd-form-check radio">';
						echo '<input type="radio" id="settings_' . esc_attr($skey) . '" name="settings[' . esc_attr($skey) . ']"';
						if(!empty($fcontent['settings'][$skey]) && $fcontent['settings'][$skey] == $kopt){
							echo ' checked';
						}
						if(isset($sval['disabled']) && $sval['disabled'] === true){
							echo ' disabled';
						}
						echo ' value=' . esc_attr($kopt) . '>';
						echo '<label for="emd-form-' . esc_attr($skey) . '" class="emd-label inline';
						echo '">' . esc_html($vopt) . '</label>';
						echo '</div>';
					}
					break;
			}
			if($sval['type'] == 'checkbox'){
				echo '<label for="emd-form-' . esc_attr($skey) . '" class="emd-label inline';
				if(!empty($sval['required'])){
					echo ' required';
				}
				echo '">' . esc_html($sval['label']);
				if(isset($sval['upgrade']) && $sval['upgrade'] === true){
					echo '<span class="dashicons dashicons-cart upgrade-pro" data-upgrade="' . esc_attr($sval['label']) . '"></span>';
				}
 				echo '</label>';
			}
			if(!empty($sval['desc'])){
				echo '<p class="description">' . esc_html($sval['desc']) . '</p>';
			}
			echo '</div>';
		}
	}
}
function emd_form_builder_lite_save_settings($app){
	if(!empty($_POST['stype']) && $_POST['stype'] == 'edit' && !empty($_POST['id']) && !empty($_POST['settings'])){
		$myform = get_post((int) $_POST['id']);
		$fcontent = json_decode($myform->post_content,true);
		$new_settings = $fcontent['settings'];
		if(is_array($_POST['settings'])){
			foreach($_POST['settings'] as $skey => $sval){
				if(!in_array($skey,Array('result_fields','result_templ','ajax_search','enable_operators','display_records','form_class','css_enq','js_enq'))){
					$new_settings[$skey] = sanitize_text_field($sval);
				}
			}
		}
		$fcontent['settings'] = $new_settings;
		$form_data = array(
			'ID' => (int) $_POST['id'],
			'post_content' => wp_slash(json_encode($fcontent,true)),
		);
		$res = wp_update_post($form_data);
		if(!is_wp_error($res)){
			emd_form_builder_lite_settings('edit',$app,(int) $_GET['form_id'],'success');
		}
		else {
			emd_form_builder_lite_settings('edit',$app,(int) $_GET['form_id'],'error');
		}
	}
	else if(!empty($_POST['stype']) && $_POST['stype'] == 'new' && empty($_POST['id']) && !empty($_POST['settings'])){
		$fcontent = Array();
		$fcontent['type'] = sanitize_text_field($_POST['settings']['type']);
		$fcontent['app'] = sanitize_text_field($_POST['app']);
		$fcontent['name'] = strtolower(str_replace(" ","_",sanitize_text_field($_POST['settings']['title'])));

		$fcontent['entity'] = sanitize_text_field($_POST['settings']['entity']);
		$fcontent['settings'] = isset( $_POST['settings'] ) ? array_map('sanitize_text_field',$_POST['settings']) : array();
		$fcontent['source']  = 'user';
		$fcontent['layout']  = Array();
		$form_data = array(
			'post_title' => sanitize_text_field($_POST['settings']['title']),
			'post_type' => 'emd_form',
			'post_status' => 'publish',
			'post_content' => wp_slash(json_encode($fcontent,true)),
		);
		if ($id = wp_insert_post($form_data)) {
			emd_form_builder_lite_settings('edit',$app,$id,'success');
		}
		else {
			emd_form_builder_lite_settings('new',$app,0,'error');
		}
	}
	else {
		emd_form_builder_lite_settings('edit',$app,(int) $_GET['form_id'],'error');
	}
}

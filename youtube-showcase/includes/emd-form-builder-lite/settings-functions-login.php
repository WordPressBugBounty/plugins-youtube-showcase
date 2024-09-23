<?php
/**
 * Login Settings Tab
 *
 * @package     EMD
 * @copyright   Copyright (c) 2014,  Emarket Design
 * @since       WPAS 4.0
 */
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

add_action('emd_ext_register','emd_login_register_settings');
add_filter('emd_add_settings_tab','emd_login_settings_tab',10,2);
add_action('emd_show_settings_tab','emd_show_login_settings_tab',10,2);

function emd_login_settings_tab($tabs,$app){
	$has_login_shc = 0;	
	$ent_list = get_option($app . '_ent_list');
	foreach($ent_list as $kent => $myent){
		if(!empty($myent['user_key'])){
			$has_login_shc = 1;	
		}
	}
	if($has_login_shc == 1){
		$tabs['login'] = __('Login', 'youtube-showcase');
		echo '<p>' . settings_errors($app . '_login_settings') . '</p>';
	}
	return $tabs;
}
function emd_show_login_settings_tab($app,$active_tab){
	$login_settings = get_option($app . '_login_settings');
	emd_login_tab($app,$active_tab,$login_settings);
}
function emd_login_register_settings($app){
	register_setting($app . '_login_settings', $app . '_login_settings', 'emd_login_sanitize');
}
function emd_login_sanitize($input){
	if(empty($input['app'])){
		return $input;
	}
	$login_settings = get_option($input['app'] . '_login_settings');
	$keys = Array('login_page','pass_reset_subj','pass_reset_msg','redirect_login','redirect_logout');
	foreach($keys as $mkey){	
		if(isset($input[$mkey])){
			$login_settings[$mkey] = $input[$mkey];
		}
	}	
	return $login_settings;
}
function emd_login_tab($app,$active_tab,$login_settings){
?>
	<div class='tab-content' id='tab-login' <?php if ( 'login' != $active_tab ) { echo 'style="display:none;"'; } ?>>
	<?php	echo '<form method="post" action="options.php">';
		settings_fields($app .'_login_settings');
		echo '<input type="hidden" name="' . esc_attr($app) . '_login_settings[app]" id="' . esc_attr($app) . '_login_settings_app" value="' . esc_attr($app) . '">';
		echo "<h4>" . esc_html__('Login/Register:','youtube-showcase') . ' ' . esc_html__('Use the options below to customize Login page.','youtube-showcase');
		echo '</h4>';
		echo '<div id="login-settings" class="accordion-container"><ul class="outer-border">';
		echo '<table class="form-table"><tbody>';
		echo "<tr><th scope='row'><label for='login_settings_login_page'>";
		echo esc_html__('Login Page','youtube-showcase');
		echo '</label></th><td>';
		echo '<select id="' . esc_attr($app) . '_login_settings_login_page" name="' . esc_attr($app) . '_login_settings[login_page]">';
		$login_page = '';
		if(!empty($login_settings['login_page'])){
			$login_page = $login_settings['login_page'];
		}
		$lpages = get_pages();
		$lpages_options = Array();
		if(!empty($lpages)){
			foreach($lpages as $page) {
				$lpages_options[$page->ID] = $page->post_title;
			}
		}
		foreach($lpages_options as $klp => $vlp){
			echo '<option value="' . esc_attr($klp) . '"';
			if($klp == $login_page){
				echo ' selected';
			}
			echo '>' . esc_html($vlp) . '</option>';
		}
		echo '</select>';
		echo "<p class='description'>" . sprintf(esc_html__('Use [emd_login app="%s"] shortcode in your page. Attributes: ent="emd_customer,emd_vendor" show_reg=1 , reg_label="",reg_link="",show_lost=1  ','youtube-showcase'),esc_html($app)) . "</p></td></tr>";
		echo "<tr><th scope='row'><label for='login_settings_pass_reset_subj'>";
		echo esc_html__('Password Reset Message Subject','youtube-showcase');
		echo '</label></th><td>';
		echo '<input class="regular-text" type="text" id="' . esc_attr($app) . '_login_settings_pass_reset_subj" name="' . esc_attr($app) . '_login_settings[pass_reset_subj]"';
		if(!empty($login_settings['pass_reset_subj'])){
			echo 'value="' . esc_attr($login_settings['pass_reset_subj']) . '"';
		}
		echo '>';
		echo '</td></tr>';
		echo "<tr><th scope='row'><label for='login_settings_pass_reset_msg'>";
		echo esc_html__('Password Reset Message','youtube-showcase');
		echo '</label></th><td>';
		ob_start();
		wp_editor($login_settings['pass_reset_msg'], esc_attr($app) . '_login_settings_pass_reset_msg', array(
			'tinymce' => false,
			'textarea_rows' => 10,
			'media_buttons' => true,
			'textarea_name' => esc_attr($app) . '_login_settings[pass_reset_msg]',
			'quicktags' => Array(
				'buttons' => 'strong,em,link,block,del,ins,img,ul,ol,li,spell'
			)
		));
		$html = ob_get_clean();
		echo wp_kses_post($html);
		echo '</td></tr>';
		echo "<tr><th scope='row'><label for='login_settings_pass_reset_msg'>";
		echo '</label></th><td>';
		echo esc_html__('Use these template tags to customize your email: {username}, {password_reset_link}, {sitename}','youtube-showcase'); 
		echo '</td></tr>';
		$cust_roles = get_option($app . '_cust_roles',Array());
		if(!empty($cust_roles)){
			echo "<tr style='border-top:2px solid #e0e0e0;border-bottom:2px solid #e0e0e0;'><th scope='row' style='padding:5px 5px;' colspan=2><h3 style='display:inline;color:#5f9ea0;'>";
			echo esc_html__('User Role Redirects','youtube-showcase');
			echo '</h3>';
			echo "<span class='description' style='padding-left:10px;color:#777;'>- " . esc_html__('Set a redirect url which users belonging to a role will be redirected to after a successful login or logout.','youtube-showcase') . "</span></th></tr>";
			$ent_list = get_option($app . '_ent_list');
			foreach($cust_roles as $krole => $vrole){
				foreach($ent_list as $kent => $myent){
					if(in_array($krole,$myent['limit_user_roles'])){
						$ent_roles[$krole] = $kent;
					}
				}
				echo "<tr><th scope='row'><label for='login_settings_redirect_login'>";
				echo sprintf(esc_html__('%s Login','youtube-showcase'),esc_html($vrole));
				echo '</label></th><td>';
				echo '<input class="regular-text" type="text" id="' . esc_attr($app) . '_login_settings_redirect_login_' . esc_attr($krole) . '" name="' . esc_attr($app) . '_login_settings[redirect_login][' . esc_attr($krole) . ']"';
				if(!empty($login_settings['redirect_login'][$krole])){
					echo 'value="' . esc_attr($login_settings['redirect_login'][$krole]) . '"';
				}
				echo '>';
				if(!empty($ent_roles[$krole])){
					echo '<p class="description">' . esc_html__('If left empty, user will be directed to the corresponding user entity page','youtube-showcase') . '</p>';
				}
				else {
					echo '<p class="description">' . esc_html__('If left empty, user will be directed to the site\'s homepage','youtube-showcase') . '</p>';
				}
				echo '</td></tr>';
				echo "<tr><th scope='row'><label for='login_settings_redirect_logout'>";
				echo sprintf(esc_html__('%s Logout','youtube-showcase'),esc_html($vrole));
				echo '</label></th><td>';
				echo '<input class="regular-text" type="text" id="' . esc_attr($app) . '_login_settings_redirect_logout_' . esc_attr($krole) . '" name="' . esc_attr($app) . '_login_settings[redirect_logout][' . esc_attr($krole) . ']"';
				if(!empty($login_settings['redirect_logout'][$krole])){
					echo 'value="' . esc_attr($login_settings['redirect_logout'][$krole]) . '"';
				}
				echo '>';
				echo '<p class="description">' . esc_html__('If left empty user, will be directed to the login page','youtube-showcase') . '</p>';
				echo '</td></tr>';
			}
		}
		echo '</tbody></table>';
		echo '</ul></div>';
		submit_button(); 
		echo '</form></div>';
}

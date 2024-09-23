<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;
add_action('emd_show_shortcodes_page','emd_show_shortcodes_page',1);
/**
 * Show shortcodes builder page
 *
 * @param string $app
 * @since WPAS 4.4
 *
 * @return html page content
 */
if (!function_exists('emd_show_shortcodes_page')) {
	function emd_show_shortcodes_page($app){
		global $title;
		add_thickbox();
		?>
		<div class="wrap">
		<h2><?php 
		$has_bulk = 0;
		if(function_exists('emd_std_media_js') || function_exists('emd_analytics_media_js')){	
			echo '<span style="padding-right:10px;">' . esc_html__('Visual ShortCode Builder','youtube-showcase') . '</span>'; 
			$has_bulk = 1;
			$create_url = admin_url('admin.php?page=' . $app . '_shortcodes#TB_inline?width=640&height=750&inlineId=wpas-component');
			echo '<a href="' . esc_url($create_url) . '" class="thickbox button button-primary">' . esc_html('Create New', 'youtube-showcase') . '</a>';
			echo '</h2>';
			echo '<p>' . esc_html__('The following shortcodes are provided by default. To use the shortcode, click copy button and paste it in a page.','youtube-showcase');
			echo ' ' . esc_html__('To create advanced shortcodes click Create New button.','youtube-showcase') . '</p>';
		}
		else {
			echo '<span style="padding-right:10px;">' . esc_html__('ShortCodes','youtube-showcase') . '</span>'; 
			echo '<a href="#" class="button button-primary btn-primary upgrade-pro">' . esc_html('Create New', 'youtube-showcase') . '</a>';
			echo '<a href="#" class="add-new-h2 upgrade-pro" style="padding:6px 10px;">' . esc_html('Import', 'youtube-showcase') . '</a>';
			echo '<a href="#" class="add-new-h2 upgrade-pro" style="padding:6px 10px;">' . esc_html('Export', 'youtube-showcase') . '</a>';
			echo '</h2>';
			echo '<p>' . esc_html__('The following shortcodes are provided by default. To use the shortcode, click copy button and paste it in a page.','youtube-showcase');
			echo ' ' . sprintf(esc_html__('To learn more on how to create new shortcodes with filters go to the %s documentation.%s','youtube-showcase'),'<a href="https://docs.emdplugins.com/docs/' . esc_attr(str_replace('_','-',$app)) . '" target="_blank">','</a>') . '</p>';
			echo '<style>.tablenav.top{display:none;}</style>';
		}
		$list_table = new Emd_List_Table($app,'shortcode',$has_bulk);
                $list_table->prepare_items();
?>
		<div class="emd-shortcode-list-admin-content">
		<form id="emd-shortcode-list-table" method="get" action="<?php echo admin_url( 'admin.php?page=' . esc_attr($app) . '_shortcodes'); ?>">
		<input type="hidden" name="page" value="<?php echo esc_attr($app . '_shortcodes');?>"/>
		<?php $list_table->views(); ?>
		<?php $list_table->display(); ?>
		</form>
		</div>
<?php
	}
}
add_action('emd_create_shc_with_filters', 'emd_create_shc_with_filters');

function emd_create_shc_with_filters(){
?>
app = $('#add-wpas-component').data('app');
$.ajax({
type:'GET',
url : ajaxurl,
data: {action:'emd_insert_new_shc',nonce:'<?php echo wp_create_nonce('emd-new-shc'); ?>',app:app,shc:shc},
success : function(response){
	if(!response){
		alert('<?php echo esc_html__('Error: Please try again.','youtube-showcase'); ?>');
	}
	redirect_link = '<?php echo admin_url('admin.php');?>?page='+app+'_shortcodes';
	window.location.href = redirect_link;
}
});
<?php
}
add_action('wp_ajax_emd_insert_new_shc','emd_insert_new_shc');
function emd_insert_new_shc(){
	check_ajax_referer('emd-new-shc', 'nonce');
	$shc_pages_cap = 'manage_options';
	$shc_pages_cap = apply_filters('emd_settings_pages_cap', $shc_pages_cap, sanitize_text_field($_GET['app']));
	if(!current_user_can($shc_pages_cap)){
		echo false;
		die();
	}
	if(!empty($_GET['app']) && !empty($_GET['shc'])){
		$user_shortcodes = get_option(sanitize_text_field($_GET['app']) . '_user_shortcodes',Array());
		$shc_list = get_option(sanitize_text_field($_GET['app']) . '_shc_list');
		preg_match('/\[(\w*)( filter="(.+)")?\]/',stripslashes(sanitize_textarea_field($_GET['shc'])),$matches);
		if(!empty($matches[1])){
			$myshc['name'] = sanitize_text_field($matches[1]);
			$myshc['type'] = '';
			if(!empty($myshc['name']) && !empty($shc_list['forms'])){
				if(array_key_exists($myshc['name'],$shc_list['forms'])){
					$myshc['type'] = __('Form','youtube-showcase') . ' - ' . ucfirst($shc_list['forms'][$myshc['name']]['type']);
				}
			}
			if(empty($myshc['type']) && !empty($myshc['name']) && !empty($shc_list['shcs'])){
				if(array_key_exists($myshc['name'],$shc_list['shcs'])){
					$myshc['type'] = __('View','youtube-showcase');
				}
			}
			if(empty($myshc['type']) && !empty($myshc['name']) && !empty($shc_list['integrations'])){
				if(array_key_exists($myshc['name'],$shc_list['integrations'])){
					$myshc['type'] = __('View','youtube-showcase');
				}
			}
			$myshc['shortcode'] = sanitize_text_field($_GET['shc']);	
			$myshc['created'] = current_time('timestamp',0);
			$user_shortcodes[] = $myshc;
			update_option(sanitize_text_field($_GET['app']) . '_user_shortcodes',$user_shortcodes);
			echo true;
			die();
		}
		else {
			echo false;
			die();
		}
	}
	else {
		echo false;
		die();
	}
}

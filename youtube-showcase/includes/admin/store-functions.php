<?php
/**
 * Add-On Page Functions
 *
 * @package     EMD
 * @copyright   Copyright (c) 2014,  Emarket Design
 * @since       WPAS 4.2
 */
if (!defined('ABSPATH')) exit;
/**
 * Show emdplugins plugins and extensions
 *
 * @param string $appd
 * @since WPAS 4.2
 *
 * @return html page content
 */
if (!function_exists('emd_display_store')) {
	function emd_display_store($appd) {
		global $title;
?>
	<div class="wrap">
	<h2><?php echo esc_html($title);?> &nbsp;&mdash;&nbsp;<a href="https://emdplugins.com/wordpress-plugins?pk_source=plugin-addons-page&pk_medium=plugin&pk_campaign=<?php echo esc_attr($appd);?>-addonspage&pk_content=browseall" class="button-primary" title="<?php esc_html_e( 'Browse All', 'youtube-showcase' ); ?>" target="_blank"><?php esc_html_e( 'Browse All', 'youtube-showcase' ); ?></a>
	</h2>
	<p><?php esc_html_e('The following plugins extend and expand the functionality of your app.','youtube-showcase'); ?></p>
			<?php emd_add_ons('tabs',$appd); ?>
		</div>
		<?php
	}
}
/**
 * Get plugin and extension list from emdplugins site and save it in a transient
 *
 * @since WPAS 4.2
 *
 * @return $cache html content
 */
if (!function_exists('emd_add_ons')) {
	function emd_add_ons($type,$appd) {
		if($type == 'tabs'){
			require_once(constant(strtoupper($appd) . "_PLUGIN_DIR") . '/includes/admin/tabs.php');
		}
		elseif($type == 'plugin-support'){
			require_once(constant(strtoupper($appd) . "_PLUGIN_DIR") . '/includes/admin/plugin-support.php');
		}	
	}
}
/**
 * Show support info
 *
 * @param string $appd
 * @since WPAS 4.3
 *
 * @return html page content
 */
if (!function_exists('emd_display_support')) {
	function emd_display_support($appd,$show_review,$rev=''){
		global $title;
		?>
		<div class="wrap">
		<h2><?php echo esc_html($title);?></h2>
		<div id="support-header"><?php printf(esc_html__('Thanks for installing %s.','youtube-showcase'),esc_attr(constant(strtoupper($appd) . '_NAME')));?> &nbsp; <?php  printf(esc_html__('All support requests are accepted through <a href="%s" target="_blank">our support site.</a>','youtube-showcase'),'https://support.emdplugins.com/?pk_source=support-page&pk_medium=plugin&pk_campaign=plugin-support&pk_content=supportlink'); ?>
	<?php 
		switch($show_review){
			case '1':
			//if prodev or freedev generation
			emd_display_review('wp-app-studio');
			break;
			case '2':
			//eMarketDesign free plugin
			emd_display_review($rev);
			break;
			default:
			echo "<br></br>";
			break;
		}
		echo '</div>';
		emd_add_ons('plugin-support',$appd); 
		echo '</div>';
	}
}
if (!function_exists('emd_display_review')) {
	function emd_display_review($plugin){
	?>
	<div id="plugin-review">
	<div class="plugin-review-text"><a href="https://wordpress.org/support/view/plugin-reviews/<?php echo esc_attr($plugin); ?>" target="_blank"><?php esc_html_e('Like our plugin? Leave us a review','youtube-showcase'); ?></a>
	</div><div class="plugin-review-star"><span class="dashicons dashicons-star-filled"></span>
	<span class="dashicons dashicons-star-filled"></span>
	<span class="dashicons dashicons-star-filled"></span>
	<span class="dashicons dashicons-star-filled"></span>
	<span class="dashicons dashicons-star-filled"></span>
	</div>
	</div>
	<?php
	}
}

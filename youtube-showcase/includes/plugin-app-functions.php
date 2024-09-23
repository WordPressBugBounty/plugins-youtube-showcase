<?php
/**
 * Plugin Functions
 * @package     EMD
 * @since       5.3
 */
if (!defined('ABSPATH')) exit;
add_filter('emd_lite_upgrade_url', 'emd_yt_lite_upgrade_url', 10, 2);
function emd_yt_lite_upgrade_url($url, $hook) {
	if (!preg_match('/_shortcodes$/', $hook)) {
		$url = esc_url("https://emdplugins.com/support/?pk_campaign=upgradelink");
	}
	return $url;
}
add_filter('emd_lite_upgrade_message', 'emd_yt_lite_upgrade_message', 10, 2);
function emd_yt_lite_upgrade_message($msg, $hook) {
	if (!preg_match('/_shortcodes$/', $hook)) {
		$msg = esc_html__('Unfortunately, this feature is not available. Please contact our development team for customization services by opening a support ticket.', 'youtube-showcase');
	}
	return $msg;
}
add_filter('emd_lite_upgrade_modal', 'emd_yt_lite_upgrade_modal', 10, 2);
function emd_yt_lite_upgrade_modal($msg, $hook) {
	if (!preg_match('/_shortcodes$/', $hook)) {
		$msg = '<p>' . wp_kses(__("Don't worry, all your records will be preserved after purchasing customization services.", "youtube-showcase") , array(
			'strong' => array() ,
		)) . '</p>';
	}
	return $msg;
} ?>
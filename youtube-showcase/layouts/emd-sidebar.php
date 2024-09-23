<?php
/**
 * The template for the sidebar containing the main widget area
 *
 */
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;
?>
<?php if ( is_active_sidebar( 'sidebar-emd' ) ) : ?>
	<div class="emd-sidebar" id="emd-primary-sidebar">
		<?php dynamic_sidebar( 'sidebar-emd' ); ?>
	</div><!-- #secondary -->
<?php endif; ?>

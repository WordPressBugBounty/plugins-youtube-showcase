<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

// Make sure "text" field is loaded
require_once EMD_MB_FIELDS_DIR . 'url.php';

if ( ! class_exists( 'EMD_MB_OEmbed_Field' ) )
{
	class EMD_MB_OEmbed_Field extends EMD_MB_URL_Field
	{
		/**
		 * Enqueue scripts and styles
		 *
		 * @return void
		 */
		static function admin_enqueue_scripts()
		{
			wp_enqueue_style( 'emd-mb-oembed', EMD_MB_CSS_URL . 'oembed.css' );
			wp_enqueue_script( 'emd-mb-oembed', EMD_MB_JS_URL . 'oembed.js', array(), EMD_MB_VER, true );
		}

		/**
		 * Add actions
		 *
		 * @return void
		 */
		static function add_actions()
		{
			add_action( 'wp_ajax_emd_mb_get_embed', array( __CLASS__, 'wp_ajax_get_embed' ) );
		}

		/**
		 * Ajax callback for returning oEmbed HTML
		 *
		 * @return void
		 */
		static function wp_ajax_get_embed()
		{
			$url = isset( $_POST['url'] ) ? sanitize_url($_POST['url']) : '';
			wp_send_json_success( self::get_embed( $url ) );
		}

		/**
		 * Get embed html from url
		 *
		 * @param string $url
		 *
		 * @return string
		 */
		static function get_embed( $url )
		{
			$embed = @wp_oembed_get( $url );
			return $embed ? $embed : __( 'Embed HTML not available.', 'youtube-showcase' );
		}

		/**
		 * Get field HTML
		 *
		 * @param mixed  $meta
		 * @param array  $field
		 *
		 * @return string
		 */
		static function html( $meta, $field )
		{
			return sprintf(
				'<input type="url" class="emd-mb-oembed" name="%s" id="%s" value="%s" size="%s">
				<a href="#" class="show-embed button">%s</a>
				<span class="spinner"></span>
				<div class="embed-code">%s</div>',
				$field['field_name'],
				$field['id'],
				$meta,
				$field['size'],
				__( 'Preview', 'youtube-showcase' ),
				$meta ? self::get_embed( $meta ) : ''
			);
		}
		/**
		 * Output the field value
		 * Display embed media
		 *
		 * @param  array    $field   Field parameters
		 * @param  array    $args    Additional arguments. Not used for these fields.
		 * @param  int|null $post_id Post ID. null for current post. Optional.
		 *
		 * @return mixed Field value
		 */
		static function the_value( $field, $args = array(), $post_id = null )
		{
			$value = self::get_value( $field, $args, $post_id );
			if ( $field['clone'] )
			{
				$output = '<ul>';
				foreach ( $value as $subvalue )
				{
					$output .= '<li>' . self::get_embed( $subvalue ) . '</li>';
				}
				$output .= '</ul>';
			}
			else
			{
				$output = self::get_embed( $value );
			}
			return $output;
		}
	}
}

<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( !class_exists( 'EMD_MB_Date_Field' ) )
{
	class EMD_MB_Date_Field extends EMD_MB_Field
	{
		/**
		 * Enqueue scripts and styles
		 *
		 * @return void
		 */
		static function admin_enqueue_scripts()
		{
			$deps = array( 'jquery-ui-datepicker' );
			$locale = get_locale();
			$date_vars['closeText'] = __('Done','youtube-showcase');
			$date_vars['prevText'] = __('Prev','youtube-showcase');
			$date_vars['nextText'] = __('Next','youtube-showcase');
			$date_vars['currentText'] = __('Today','youtube-showcase');
			$date_vars['monthNames'] = Array(__('January','youtube-showcase'),__('February','youtube-showcase'),__('March','youtube-showcase'),__('April','youtube-showcase'),__('May','youtube-showcase'),__('June','youtube-showcase'),__('July','youtube-showcase'),__('August','youtube-showcase'),__('September','youtube-showcase'),__('October','youtube-showcase'),__('November','youtube-showcase'),__('December','youtube-showcase'));
			$date_vars['monthNamesShort'] = Array(__('Jan','youtube-showcase'),__('Feb','youtube-showcase'),__('Mar','youtube-showcase'),__('Apr','youtube-showcase'),__('May','youtube-showcase'),__('Jun','youtube-showcase'),__('Jul','youtube-showcase'),__('Aug','youtube-showcase'),__('Sep','youtube-showcase'),__('Oct','youtube-showcase'),__('Nov','youtube-showcase'),__('Dec','youtube-showcase'));
			$date_vars['dayNames'] = Array(__('Sunday','youtube-showcase'),__('Monday','youtube-showcase'),__('Tuesday','youtube-showcase'),__('Wednesday','youtube-showcase'),__('Thursday','youtube-showcase'),__('Friday','youtube-showcase'),__('Saturday','youtube-showcase'));
			$date_vars['dayNamesShort'] = Array(__('Sun','youtube-showcase'),__('Mon','youtube-showcase'),__('Tue','youtube-showcase'),__('Wed','youtube-showcase'),__('Thu','youtube-showcase'),__('Fri','youtube-showcase'),__('Sat','youtube-showcase'));	
			$date_vars['dayNamesMin'] = Array(__('Su','youtube-showcase'),__('Mo','youtube-showcase'),__('Tu','youtube-showcase'),__('We','youtube-showcase'),__('Th','youtube-showcase'),__('Fr','youtube-showcase'),__('Sa','youtube-showcase'));	
			$date_vars['weekHeader'] = __('Wk','youtube-showcase');
		
			$vars['date'] = $date_vars;
			$vars['locale'] = $locale;	
			wp_enqueue_script( 'emd-mb-date', EMD_MB_JS_URL . 'date.js', $deps, EMD_MB_VER, true );
			wp_localize_script( 'emd-mb-date', 'vars', $vars);
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
			if($meta != '')
                        {
				if(DateTime::createFromFormat('Y-m-d',$meta)){
                                	$meta = DateTime::createFromFormat('Y-m-d',$meta)->format(self::translate_format($field));
				}
                        }
			return sprintf(
				'<input type="text" class="emd-mb-date" name="%s" value="%s" id="%s" size="%s" data-options="%s" %s readonly/>',
				$field['field_name'],
				$meta,
				isset( $field['clone'] ) && $field['clone'] ? '' : $field['id'],
				$field['size'],
				esc_attr( json_encode( $field['js_options'] ) ),
				isset($field['data-cell']) ? "data-cell='{$field['data-cell']}'" : ''
			);
		}

		/**
		 * Normalize parameters for field
		 *
		 * @param array $field
		 *
		 * @return array
		 */
		static function normalize_field( $field )
		{
			$field = wp_parse_args( $field, array(
				'size'       => 30,
				'js_options' => array(),
			) );

			// Deprecate 'format', but keep it for backward compatible
			// Use 'js_options' instead
			$field['js_options'] = wp_parse_args( $field['js_options'], array(
				'dateFormat'      => empty( $field['format'] ) ? 'yy-mm-dd' : $field['format'],
				'showButtonPanel' => true,
				'changeMonth' => true,
				'changeYear' => true,
				'yearRange' => '-100:+10'
			) );

			return $field;
		}
	
                /**
                 * Returns a date() compatible format string from the JavaScript format
                 *
                 * @see http://www.php.net/manual/en/function.date.php
                 *
                 * @param array $field
                 *
                 * @return string
                 */
                static function translate_format( $field )
                {
                        return strtr( $field['js_options']['dateFormat'], self::$date_format_translation );
                }

                static function save( $new, $old, $post_id, $field )
                {
                        $name = $field['id'];
                        if ( '' === $new)
                        {
                                delete_post_meta( $post_id, $name );
                                return;
                        }
			if(DateTime::createFromFormat(self::translate_format($field), $new)){
                        	$new = DateTime::createFromFormat(self::translate_format($field), $new)->format('Y-m-d');
                        	update_post_meta( $post_id, $name, $new );
			}
                }
	}
}

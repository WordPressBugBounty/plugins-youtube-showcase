<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'EMD_MB_File_Field' ) )
{
	class EMD_MB_File_Field extends EMD_MB_Field
	{
		/**
		 * Enqueue scripts and styles
		 *
		 * @return void
		 */
		static function admin_enqueue_scripts()
		{
			wp_enqueue_style( 'emd-mb-file', EMD_MB_CSS_URL . 'file.css', array(), EMD_MB_VER );
			wp_enqueue_script( 'emd-mb-file', EMD_MB_JS_URL . 'file.js', array( 'jquery' ), EMD_MB_VER, true );
			wp_localize_script( 'emd-mb-file', 'emdmbFile', array(
				'maxFileUploadsSingle' => __( 'You may only upload maximum %d file', 'youtube-showcase' ),
				'maxFileUploadsPlural' => __( 'You may only upload maximum %d files', 'youtube-showcase' ),
				'maxFileSizeError' => __('File Size is greater than allowed limit.','youtube-showcase'),
				'FileExtError'	=> __('Invalid file type.','youtube-showcase'),
			) );
		}

		/**
		 * Add actions
		 *
		 * @return void
		 */
		static function add_actions()
		{
			// Add data encoding type for file uploading
			add_action( 'post_edit_form_tag', array( __CLASS__, 'post_edit_form_tag' ) );

			// Delete file via Ajax
			add_action( 'wp_ajax_emd_mb_delete_file', array( __CLASS__, 'wp_ajax_delete_file' ) );
		}

		/**
		 * Add data encoding type for file uploading
		 *
		 * @return void
		 */
		static function post_edit_form_tag()
		{
			echo ' enctype="multipart/form-data"';
		}

		/**
		 * Ajax callback for deleting files.
		 * Modified from a function used by "Verve Meta Boxes" plugin
		 *
		 * @link http://goo.gl/LzYSq
		 * @return void
		 */
		static function wp_ajax_delete_file()
		{
			$post_id       = isset( $_POST['post_id'] ) ? (int) $_POST['post_id'] : 0;
			$field_id      = isset( $_POST['field_id'] ) ? sanitize_text_field($_POST['field_id']) : 0;
			$attachment_id = isset( $_POST['attachment_id'] ) ? (int) $_POST['attachment_id'] : 0;
			$force_delete  = isset( $_POST['force_delete'] ) ? (int) $_POST['force_delete'] : 0;

			check_ajax_referer( "emd-mb-delete-file_{$field_id}" );

			delete_post_meta( $post_id, $field_id, $attachment_id );
			$ok = $force_delete ? wp_delete_attachment( $attachment_id ) : true;

			if ( $ok )
				wp_send_json_success();
			else
				wp_send_json_error( __( 'Error: Cannot delete file', 'youtube-showcase' ) );
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
			$i18n_title = apply_filters( 'emd_mb_file_upload_string', _x( 'Upload Files', 'file upload', 'youtube-showcase' ), $field );
			$i18n_more  = apply_filters( 'emd_mb_file_add_string', _x( '+ Add new file', 'file upload', 'youtube-showcase' ), $field );

			// Uploaded files
			$html = self::get_uploaded_files( $meta, $field );
			$new_file_classes = array( 'new-files' );
			if ( !empty( $field['max_file_uploads'] ) && count( $meta ) >= (int) $field['max_file_uploads'] )
				$new_file_classes[] = 'hidden';

			$file_settings = "";
			if(!empty($field['max_file_uploads'])){
				$file_settings .= sprintf(__('Max number of files: %s','youtube-showcase'),$field['max_file_uploads']);
			}
			if(!empty($field['max_file_size'])){
				$file_settings .= '<br> ' . sprintf(__('Max file size: %s','youtube-showcase'),$field['max_file_size']) . ' KB';
			}
			else {
				$server_size = ini_get('upload_max_filesize');
				if(preg_match('/M$/',$server_size)){
					$server_size = preg_replace('/M$/','',$server_size);
					$server_size = $server_size * 1000;
				}
				$file_settings .= '<br> ' . sprintf(__('Max file size: %s','youtube-showcase'),$server_size) . ' KB';
			}
			if(!empty($field['mime_type'])){
				$file_settings .= '<br> ' . sprintf(__('File extensions allowed: %s','youtube-showcase'),$field['mime_type']);
			}
			// Show form upload
			$html .= sprintf(
				'<div class="%s">
					<h4>%s</h4>
					<div class="small text-muted" style="margin:0.75rem 0 0.50rem;">%s</div>
					<div class="file-input"><input type="file" name="%s[]" /></div>
					<div id="emd-file-err-msg" class="hidden" style="display:none;padding:15px;background:#f2dede;border-color:#ebccd1;color:#a94442;font-size:0.9rem;"></div>
					<a class="emd-mb-add-file" href="#"><strong>%s</strong></a>
				</div>',
				implode( ' ', $new_file_classes ),
				$i18n_title,
				$file_settings,
				$field['id'],
				$i18n_more
			);

			return $html;
		}

		static function get_uploaded_files( $files, $field )
		{
			$delete_nonce = wp_create_nonce( "emd-mb-delete-file_{$field['id']}" );
			$classes = array('emd-mb-file', 'emd-mb-uploaded');
			if ( count( $files ) <= 0  )
				$classes[] = 'hidden';
			$ol = '<ul class="%s" data-field_id="%s" data-delete_nonce="%s" data-force_delete="%s" data-max_file_uploads="%s" data-mime_type="%s" data-max_size="%s">';
			$html = sprintf(
				$ol,
				implode( ' ', $classes ),
				$field['id'],
				$delete_nonce,
				$field['force_delete'] ? 1 : 0,
				$field['max_file_uploads'],
				$field['mime_type'],
				$field['max_file_size']
			);

			foreach ( $files as $attachment_id )
			{
				$html .= self::file_html( $attachment_id );
			}

			$html .= '</ul>';

			return $html;
		}

		static function file_html( $attachment_id )
		{
			$i18n_delete = apply_filters( 'emd_mb_file_delete_string', _x( 'Delete', 'file upload', 'youtube-showcase' ) );
			$i18n_edit   = apply_filters( 'emd_mb_file_edit_string', _x( 'Edit', 'file upload', 'youtube-showcase' ) );
			$li = '
			<li>
				<div class="emd-mb-icon">%s</div>
				<div class="emd-mb-info">
					<a href="%s" target="_blank">%s</a>
					<p>%s</p>
					<a title="%s" href="%s" target="_blank">%s</a> |
					<a title="%s" class="emd-mb-delete-file" href="#" data-attachment_id="%s">%s</a>
				</div>
			</li>';

			$mime_type = get_post_mime_type( $attachment_id );
			return sprintf(
				$li,
				str_replace('http:','',wp_get_attachment_image( $attachment_id, array(60,60), true )),
				str_replace('http:','',wp_get_attachment_url($attachment_id)),
				get_the_title( $attachment_id ),
				$mime_type,
				$i18n_edit,
				str_replace('http:','',get_edit_post_link( $attachment_id )),
				$i18n_edit,
				$i18n_delete,
				$attachment_id,
				$i18n_delete
			);
		}

		/**
		 * Get meta values to save
		 *
		 * @param mixed $new
		 * @param mixed $old
		 * @param int   $post_id
		 * @param array $field
		 *
		 * @return array|mixed
		 */
		static function value( $new, $old, $post_id, $field )
		{
			$name = $field['id'];
			if ( empty( $_FILES[ $name ] ) )
				return $new;

			$new = array();
			$files	= self::fix_file_array( $_FILES[ $name ] );

			foreach ( $files as $file_item )
			{
				$file = wp_handle_upload( $file_item, array( 'test_form' => false ) );

				if ( ! isset( $file['file'] ) )
					continue;

				$file_name = $file['file'];

				$attachment = array(
					'post_mime_type' => $file['type'],
					'guid'           => $file['url'],
					'post_parent'    => $post_id,
					'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file_name ) ),
					'post_content'   => '',
				);
				$id = wp_insert_attachment( $attachment, $file_name, $post_id );

				if ( ! is_wp_error( $id ) )
				{
					wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $file_name ) );

					// Save file ID in meta field
					$new[] = $id;
				}
			}

			return array_unique( array_merge( $old, $new ) );
		}

		/**
		 * Fixes the odd indexing of multiple file uploads from the format:
		 *	 $_FILES['field']['key']['index']
		 * To the more standard and appropriate:
		 *	 $_FILES['field']['index']['key']
		 *
		 * @param array $files
		 *
		 * @return array
		 */
		static function fix_file_array( $files )
		{
			$output = array();
			foreach ( $files as $key => $list )
			{
				foreach ( $list as $index => $value )
				{
					$output[$index][$key] = $value;
				}
			}
			return $output;
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
				'std'              => array(),
				'force_delete'     => false,
				'max_file_uploads' => 0,
				'mime_type'        => '',
				'max_file_size' => '',
			) );
			$field['multiple'] = true;
			return $field;
		}

		/**
		 * Standard meta retrieval
		 *
		 * @param int   $post_id
		 * @param array $field
		 * @param bool  $saved
		 *
		 * @return mixed
		 */
		static function meta( $post_id, $saved, $field )
		{
			$meta = parent::meta( $post_id, $saved, $field );
			return empty( $meta ) ? array() : (array) $meta;
		}
	}
}

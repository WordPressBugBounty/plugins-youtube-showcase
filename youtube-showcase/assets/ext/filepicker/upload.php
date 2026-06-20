<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;
class UploadHandler
{
	protected $options;
	// PHP File Upload error message codes:
	// http://php.net/manual/en/features.file-upload.errors.php
	protected $error_messages;

	function __construct($initialize = true,$fieldid,$fileTypes="",$myapp) {
		$this->error_messages = array(
			1 => __('The uploaded file exceeds the upload_max_filesize directive in php.ini','youtube-showcase'),
			2 => __('The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form','youtube-showcase'),
			3 => __('The uploaded file was only partially uploaded','youtube-showcase'),
			4 => __('No file was uploaded','youtube-showcase'),
			6 => __('Missing a temporary folder','youtube-showcase'),
			7 => __('Failed to write file to disk','youtube-showcase'),
			8 => __('A PHP extension stopped the file upload','youtube-showcase'),
			'post_max_size' => __('The uploaded file exceeds the post_max_size directive in php.ini','youtube-showcase'),
			'max_file_size' => __('File is too big','youtube-showcase'),
			'min_file_size' => __('File is too small','youtube-showcase'),
			'accept_file_types' => __('Filetype not allowed','youtube-showcase'),
			'max_number_of_files' => __('Maximum number of files exceeded','youtube-showcase'),
			'max_width' => __('Image exceeds maximum width','youtube-showcase'),
			'min_width' => __('Image requires a minimum width','youtube-showcase'),
			'max_height' => __('Image exceeds maximum height','youtube-showcase'),
			'min_height' => __('Image requires a minimum height','youtube-showcase')
			);

		if (!empty($_FILES) && isset($_FILES['file'])) {
			$upload_file = 0;
			// Validate the file type
			if(empty($fileTypes))
			{
				$fileTypes_arr = array('jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'zip');
			}
			else {
				$fileTypes_arr = is_array($fileTypes) ? $fileTypes : explode(",", $fileTypes);
			}
			$fileTypes_arr = array_map('trim', $fileTypes_arr);
                        $fileTypes_arr = array_map('strtolower', $fileTypes_arr);

                        $original_name = $_FILES['file']['name'];
                        $fileParts = pathinfo($original_name);
                        $file_ext = isset($fileParts['extension']) ? strtolower($fileParts['extension']) : '';

			if (empty($file_ext) || !in_array($file_ext, $fileTypes_arr, true)) {
                                echo esc_html__('Invalid file type.', 'youtube-showcase');
                                return; // Halt execution immediately
                        }
                        if (strpos(strtolower($original_name), '.php') !== false) {
                                echo esc_html__('Invalid file type signature.', 'youtube-showcase');
                                return;
			}
			// If it survives all checks above, flag it as safe to upload
                        $upload_file = 1;

                        if ($upload_file === 1) {
                                // Sanitize the file name inside the $_FILES array before WordPress touches it
                                $_FILES['file']['name'] = sanitize_file_name($original_name);

                                $file = wp_handle_upload($_FILES['file'], array('test_form' => false));

                                if (isset($file['error'])) {
                                        echo esc_html($file['error']);
                                } else {
                                        $_FILES['file']['path'] = $file['file'];
					
					if (!empty($myapp)) {
                                                $new_sess_files = array();

                                                // Sanitize $myapp dynamically to prevent arbitrary class instantiation attacks
                                                $clean_myapp = preg_replace('/[^a-zA-Z0-9_-]/', '', $myapp);
                                                $sess_name = strtoupper($clean_myapp);

                                                if (function_exists($sess_name)) {
                                                        $session_class = $sess_name();
                                                        $sess_files = $session_class->session->get('uploads');

                                                        if (!empty($sess_files) && is_array($sess_files)) {
                                                            $new_sess_files = $sess_files;
                                                        }

                                                        if (empty($sess_files[$fieldid])) {
                                                            $new_sess_files[$fieldid][] = $_FILES['file'];
                                                        } elseif (is_array($sess_files[$fieldid])) {
                                                            $new_sess_files[$fieldid] = $sess_files[$fieldid];
                                                            $new_sess_files[$fieldid][] = $_FILES['file'];
                                                        }

                                                        $session_class->session->set('uploads', $new_sess_files);
                                                }
                                        }
                                        echo '1';
                                }
			}
		}
	}
}
?>

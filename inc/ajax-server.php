<?php if (!defined('ABSPATH')) exit;

function WPSM_ajax_get_server_files()
{
	if( ! current_user_can('publish_posts')) {
		wp_send_json_error();
	}

	$nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';

	if( ! wp_verify_nonce($nonce, 'wpsafeguard_nonce')) {
		wp_send_json_error();
	}

	wp_send_json(WPSM_get_server_files());
}
add_action('wp_ajax_wpsm_get_server_files', 'WPSM_ajax_get_server_files');

function WPSM_ajax_save_uploaded_file()
{
	if( ! current_user_can('publish_posts')) {
		wp_send_json_error();
	}

	$nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';

	if( ! wp_verify_nonce($nonce, 'wpsafeguard_nonce')) {
		wp_send_json_error();
	}

	try {
		$success             = true;
		$message             = '';
		$option_form         = '';
		$safeguard_media_url = isset($_POST['url']) ? sanitize_url(wp_unslash($_POST['url'])) : '';
		$post_id             = isset($_POST['post_id']) ? (int)$_POST['post_id'] : '';
		$filename            = basename($safeguard_media_url);

		if(empty($filename)) {
			throw new Exception(__('Invalid file.', 'safeguard-media'));
		}

		$tmp = explode('.', $filename);
		
		$file_type = end($tmp);
		$file_type = strtolower($file_type);

		if( ! in_array($file_type, WPSM_allowed_file_types())) {
			throw new Exception(__( 'Sorry, only jpg, jpeg, png, gif, mp4, doc, docx, pdf files are allowed.', 'safeguard-media' ));
		}

		$client_id = WPSM_get_client_id();

		if(empty($client_id)) {
			throw new Exception(__('Account does not exist on the SafeGuard Media server.', 'safeguard-media'));
		}

		$parts = WPSM_register_media($safeguard_media_url, $client_id);
		
		if($parts[0] != 'success') {
			throw new Exception(__('An error occured while publishing your file to the SafeGuard Media server.', 'safeguard-media'));
		}

		$fileparts = explode('@', $parts[1]);
		$filelink  = '';

		if(isset($fileparts[0])) {
			$filelink=$fileparts[0];
		}
		
		$message     = WPSM_file_upload_message(isset($_POST['error']) ? sanitize_text_field(wp_unslash($_POST["error"])) : 0);
		$option_form = WPSM_file_option_form($filename, $post_id, $filelink);

	} catch(Exception $e) {
		$success = false;
		$message = '<div class="error"><p><strong>' . esc_html(__('Error', 'safeguard-media')) . '!</strong></p><p>' . esc_html($e->getMessage()) . '</p></div>';
	}

	wp_send_json([
		'success'     => $success,
		'message'     => $message,
		'option_form' => $option_form,
	]);
}
add_action('wp_ajax_wpsm_save_uploaded_file', 'WPSM_ajax_save_uploaded_file');

function WPSM_ajax_save_file_settings() {
	if( ! current_user_can('publish_posts')) {
		wp_send_json_error();
	}

	$nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';

	if( ! wp_verify_nonce($nonce, 'wpsafeguard_nonce')) {
		wp_send_json_error();
	}

	$message = WPSM_setting_save($_POST);

	wp_send_json([
		'message' => $message,
	]);
}
add_action('wp_ajax_wpsm_save_file_settings', 'WPSM_ajax_save_file_settings');

function WPSM_ajax_get_file_settings()
{
	if( ! current_user_can('publish_posts')) {
		wp_send_json_error();
	}

	$nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
	$type  = isset($_POST['type']) ? sanitize_text_field(wp_unslash($_POST['type'])) : '';

	if( ! wp_verify_nonce($nonce, 'wpsafeguard_nonce')) {
		wp_send_json_error();
	}

	$parameter_data = WPSM_get_parameters($_POST);

	if($type != 'json')
	{
		$parameters =
			" width='" . $parameter_data['width'] . "'" .
			" height='" . $parameter_data['height'] . "'" .
			" remote='" . $parameter_data['remote'] . "'";
	}
	else
	{
		$parameters = $parameter_data;
	}

	wp_send_json([
		'parameters' => $parameters,
	]);
}
add_action('wp_ajax_wpsm_get_file_settings', 'WPSM_ajax_get_file_settings');
<?php if (!defined('ABSPATH')) exit;

function WPSM_ajax_action()
{
	if( ! current_user_can('upload_files')) {
		wp_send_json_error();
	}

	$nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';

	if( ! wp_verify_nonce($nonce, 'wpsafeguard_nonce')) {
		wp_send_json_error();
	}

	add_filter('upload_dir', 'WPSM_upload_dir');
	
	$response = [];
	// handle file upload
	$id = media_handle_upload(
		'async-upload',
		0,
		[
			'test_form' => TRUE,
			'action' => 'wpsm_upload',
		]
	);

	// send the file' url as response
	if (is_wp_error($id)) {
		$response['status'] = 'error22';
		$response['error'] = $id->get_error_messages();
	}
	else {
		$response['status'] = 'success';

		//$src = wp_get_attachment_image_src($id, 'thumbnail');
		$src = wp_get_attachment_url($id);

		$response['attachment'] = [];
		$response['attachment']['id'] = $id;
		$response['attachment']['src'] = $src;
	}

	remove_filter('upload_dir', 'WPSM_upload_dir');

	wp_send_json($response);
}
add_action('wp_ajax_wpsm_upload', 'WPSM_ajax_action');

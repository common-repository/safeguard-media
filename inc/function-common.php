<?php if (!defined('ABSPATH')) exit;

function WPSM_help_icon($message)
{
	$help_icon =
		'<img src="' . esc_attr(WPSAFEGUARD_PLUGIN_URL) . 'images/help-24-30.png" '.
			'alt="' . esc_attr($message) . '" border="0">';
	
	return $help_icon;
}

function WPSM_upload_dir($upload) {
	$upload['subdir'] = '/safeguard-media';
	$upload['path'] = $upload['basedir'] . $upload['subdir'];
	$upload['url'] = $upload['baseurl'] . $upload['subdir'];
	return $upload;
}

function WPSM_get_upload_url()
{
	$upload_dir = wp_upload_dir();
	
	return $upload_dir['baseurl'] . '/safeguard-media/';
}

function WPSM_get_upload_dir()
{
	$upload_dir = wp_upload_dir();
	
	return $upload_dir['basedir'] . '/safeguard-media/';
}

function WPSM_sanitize_option($key, $option, $source)
{
	$default = isset($option['default']) ? $option['default'] : '';

	$option_value = isset($source[$key]) ? $source[$key] : $default;

	if( ! empty($option['type']))
	{
		if($option['type'] == '1_0') {
			$option_value = $option_value == '1' ? '1' : '0';
		} else if($option['type'] == 'checked') {
			$option_value = $option_value ? 'checked' : '';
		} else if($option['type'] == 'int') {
			$option_value = (int)$option_value;
		} else if($option['type'] == 'float') {
			$option_value = (float)$option_value;
		} else if($option['type'] == 'hex_color') {
			$option_value = str_replace('#','', sanitize_text_field($option_value));
		} else {
			$option_value = sanitize_text_field($option_value);
		}
	}
	else
	{
		$option_value = sanitize_text_field($option_value);
	}

	return $option_value;
}

function WPSM_encrypt_decrypt($action, $string) {
	$output = false;

	$encrypt_method = "AES-256-CBC";
	$av_apikey      = get_option('wpsm_av_apikey');
	$secret_key     = $av_apikey;
	$secret_iv      = 'This is my secret iv';

	// hash
	$key = hash('sha256', $secret_key);

	// iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
	$iv = substr(hash('sha256', $secret_iv), 0, 16);

	if ( $action == 'encrypt' ) {
		$output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
		$output = base64_encode($output);
	} else if( $action == 'decrypt' ) {
		$output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
	}

	return $output;
}

function WPSM_get_parameters($params) {

	$postid      = sanitize_text_field($params["post_id"]);
	$filename    = sanitize_text_field($params["filename"]);

	$settings = WPSM_get_first_class_settings();

	$options = get_option("wpsafeguard_settings");

	if (isset($options["classsetting"][$postid][$filename])
		&& is_array($options["classsetting"][$postid][$filename]))
	{
		$settings = wp_parse_args($options["classsetting"][$postid][$filename], $settings);
	}

	extract($settings);

	//$prints_allowed = ($prints_allowed) ? $prints_allowed : 0;
	//$print_anywhere = ($print_anywhere) ? 1 : 0;
	//$allow_capture = ($allow_capture) ? 1 : 0;
	$allowremote = ($remote) ? 1 : 0;

	$params = [
		'width' => $width,
		'height' => $height,
		'remote' => $allowremote,
	];

	return $params;
}

function WPSM_get_first_class_settings() {
	$settings = [
		'width' => '600',
		'height' => '600',
		//'prints_allowed' => 0,
		//'print_anywhere' => 0,
		//'allow_capture' => 0,
		'remote' => 0,
		//'background' => 'CCCCCC',
	];

	return $settings;
}

function WPSM_file_upload_message($file_error) {

	$file_errors = [
		0 => __("There is no error, the file uploaded with success", 'safeguard-media'),
		1 => __("The uploaded file exceeds the upload_max_filesize directive in php.ini", 'safeguard-media'),
		2 => __("The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form", 'safeguard-media'),
		3 => __("The uploaded file was only partially uploaded", 'safeguard-media'),
		4 => __("No file was uploaded", 'safeguard-media'),
		6 => __("Missing a temporary folder", 'safeguard-media'),
		7 => __("Upload directory is not writable", 'safeguard-media'),
		8 => __("User not logged in", 'safeguard-media'),
	];

	if ($file_error == 0) {
		$msg = '<div class="updated"><p><strong>' . esc_html(__('File Uploaded. You must save "File Details" to insert post', 'safeguard-media')) . '</strong></p></div>';
	}
	else {
		$error_message = isset($file_errors[$file_error]) ? $file_errors[$file_error] : __('Unknown error', 'safeguard-media');

		$msg = '<div class="error"><p><strong>' . esc_html(__('Error', 'safeguard-media')) . '!</strong></p><p>' . esc_html($error_message) . '</p></div>';
	}

	return $msg;
}

function WPSM_file_option_form($file_name, $post_id, $safeguard_media_id)
{
	if ( ! empty($file_name) && ! empty($post_id))
	{
		$file_options        = WPSM_get_first_class_settings();
		$wpsafeguard_options = get_option('wpsafeguard_settings');

		if (isset($wpsafeguard_options["classsetting"][$post_id][$file_name])) {
			$file_options = $wpsafeguard_options["classsetting"][$post_id][$file_name];
		}

		$width       = isset($file_options['width']) ? esc_attr($file_options['width']) : '';
		$height      = isset($file_options['height']) ? esc_attr($file_options['height']) : '';
		$allowremote = isset($file_options['allowremote']) ? $file_options['allowremote'] : '';

		$str = "<hr />
					<div class='icon32' id='icon-file'><br /></div>
					<h2>" . esc_html(__('Media Settings for Shortcode', 'safeguard-media')) . "</h2>
					<div>
						<table cellpadding='0' cellspacing='0' border='0' >
							<tbody id='wpsafeguard_setting_body'> 
								<tr> 
									<td align='left' width='50'>&nbsp;</td>
									<td align='left' width='40'><img src='" . esc_attr(WPSAFEGUARD_PLUGIN_URL) . "images/help-24-30.png' border='0' alt='Number of prints allowed per session. For no printing set 0.'></td>
									<td align='left' width='120'>Viewer Width:&nbsp;&nbsp;</td>
									<td> 
										<input name='width' type='text' value='$width' size='3'>
									</td>
								</tr>
								<tr> 
									<td align='left' width='50'>&nbsp;</td>
									<td align='left' width='40'><img src='" . esc_attr(WPSAFEGUARD_PLUGIN_URL) . "images/help-24-30.png' border='0' alt='Number of prints allowed per session. For no printing set 0.'></td>
									<td align='left'>Viewer Height:&nbsp;&nbsp;</td>
									<td> 
									<input name='height' type='text' value='$height' size='3'>
									</td>
								</tr>
								<tr> 
									<td align='left'>&nbsp;</td>
									<td align='left'><img src='" . esc_attr(WPSAFEGUARD_PLUGIN_URL) . "images/help-24-30.png' border='0' alt='Check this box to prevent viewing by remote or virtual computers when the class image loads.'></td>
									<td align='left'>Allow Remote:</td>
									<td> 
									<input name='allowremote' type='checkbox' value='1' " . esc_attr($allowremote) . ">
									</td>
								</tr>
							</tbody> 
						</table>
						<p class='submit'>
							<button type='button' class='button-primary' id='wpsm_setting_save' data-filename='" . esc_attr($safeguard_media_id) . "'>Save</button>
							<button type='button' class='button-primary' id='wpsm_setting_cancel'>Cancel</button>
						</p>
				</div>";

		return $str;
	}
}

function WPSM_setting_save($params)
{
	$postid = sanitize_text_field($params["post_id"]);
	$name   = sanitize_text_field($params["nname"]);

	$wpsafeguard_settings = get_option('wpsafeguard_settings');

	if (!is_array($wpsafeguard_settings)) {
		$wpsafeguard_settings = [];
	}

	$width       = isset($params["set_data"]['width']) ? esc_attr(sanitize_text_field($params["set_data"]['width'])) : '';
	$height      = isset($params["set_data"]['height']) ? esc_attr(sanitize_text_field($params["set_data"]['height'])) : '';
	$allowremote = isset($params["set_data"]['allowremote']) ? esc_attr(sanitize_text_field($params["set_data"]['allowremote'])) : '';

	$data = [
		'width' => "$width",
		'height' => "$height",
		'remote' => $allowremote ? "1" : "0",
	];

	$wpsafeguard_settings["classsetting"][$postid][$name] = $data;

	update_option('wpsafeguard_settings', $wpsafeguard_settings);

	$msg = '<div class="updated fade">
				<p><strong>' . esc_html(__('File Options Are Saved', 'safeguard-media')) . '</strong></p>
			</div>';

	return $msg;
}

function WPSM_get_browser_info()
{
	$u_agent = isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) : '';

	$bname = 'Unknown';
	$platform = 'Unknown';
	$version= "";

	//First get the platform?
	if (preg_match('/linux/i', $u_agent)) {
		$platform = 'linux';
	}
	else if (preg_match('/macintosh|mac os x/i', $u_agent)) {
		$platform = 'mac';
	}
	else if (preg_match('/windows|win32/i', $u_agent)) {
		$platform = 'windows';
	}

	// Next get the name of the useragent yes seperately and for good reason
	if(preg_match('/Firefox/i',$u_agent)){
		$bname = 'Mozilla Firefox';
		$ub = "Firefox";
	}
	else if(preg_match('/Chrome/i',$u_agent) && !preg_match('/Edge/i',$u_agent)){
		$bname = 'Google Chrome';
		$ub = "Chrome";
	}

	// finally get the correct version number
	$known = array('Version', @$ub, 'other');
	$pattern = '#(?<browser>' . join('|', $known) .')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';

	if (!preg_match_all($pattern, $u_agent, $matches)) {
		// we have no matching number just continue
	}

	// see how many we have

	$i = count($matches['browser']);

	if ($i != 1) {
		//we will have two since we are not using 'other' argument yet
		//see if version is before or after the name

		if (strripos($u_agent,"Version") < strripos($u_agent,@$ub)){
			$version= $matches['version'][0];
		}
		else {
			$version = $matches['version'][1];
		}
	}
	else {
		$version = $matches['version'][0];
	}

	// check if we have a number
	if( $version == null || $version == "" ){ 
		$version = "?";
	}

	return array(
		'userAgent' => $u_agent,
		'name'      => $bname,
		'version'   => $version,
		'platform'  => $platform,
		'pattern'   => $pattern
	);
}

function WPSM_check_artis_browser_version()
{
	$redirect = false;

	$wpsafeguard_current_browser = WPSM_get_browser_info();
	$wpsafeguard_current_browser_data = $wpsafeguard_current_browser['userAgent'];

	if( $wpsafeguard_current_browser_data != "")
	{
		$wpsafeguard_browser_data = explode("/", $wpsafeguard_current_browser_data);

		if (strpos($wpsafeguard_current_browser_data, 'ArtisBrowser') !== false || strpos($wpsafeguard_current_browser_data, 'ArtisReader') !== false)
		{
			$current_version = end($wpsafeguard_browser_data);

			$wpsafeguard_options = get_option('wpsafeguard_settings');

			//$latest_version = $wpsafeguard_options["settings"]["latest_version"];

			$minimum_version = $wpsafeguard_options["settings"]["minimum_version"];

			if( $current_version < $minimum_version )
			{
				$ref_url  = get_permalink(get_the_ID());
				$redirect = WPSAFEGUARD_ARTISBROWSER_URL . "/download/?artisbrowser=update&ref=" . urlencode($ref_url);
			}
		}
		else
		{
			$ref_url  = get_permalink(get_the_ID());
			$redirect = WPSAFEGUARD_ARTISBROWSER_URL . "/download/?artisbrowser=required&ref=" . urlencode($ref_url);
		}
	}

	return $redirect;
}
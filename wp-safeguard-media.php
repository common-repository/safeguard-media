<?php
/*
  Plugin Name: SafeGuard Media Protection
  Plugin URI: https://safeguard.media/wordpress-protection.asp
  Description: Copy protect images, PDF and video on WordPress pages and posts. Click here for the <a href="https://youtu.be/nFKZ42oBUDY" target="_blank">Usage Video</a> and the <a href="https://safeguard.media/download/SafeGuard_Media_for_WordPress.pdf" target="_blank">Setup Guide</a>.
  Author: ArtistScope
  Version: 3.3.0
  Author URI: https://safeguard.media/
  License: GPLv2
  Text Domain: safeguard-media
  Domain Path: /languages/
  
  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

// ================================================================================ //
//                                                                                  //
//  WARNING : DO NOT CHANGE ANYTHING BELOW IF YOU DONT KNOW WHAT YOU ARE DOING      //
//                                                                                  //
// ================================================================================ //

if (!defined('ABSPATH')) {
	exit;
} // Exit if accessed directly

define('WPSAFEGUARD_VERSION', '3.0');
define('WPSAFEGUARD_ASSET_VERSION', 1.8);
define('WPSAFEGUARD_ARTISBROWSER_URL', 'https://artisbrowser.com');

require_once __DIR__ . '/inc/class-wpsm.php';

function WPSM_enable_extended_upload($mime_types = [])
{
	// This function is added to allow the upload of .MP4 file in wordpress. In case this function does not

	// You can add as many MIME types as you want.
	//$mime_types['class'] = 'application/octet-stream';
	$mime_types['mp4'] = 'application/octet-stream';
	// If you want to forbid specific file types which are otherwise allowed,
	// specify them here.  You can add as many as possible.
	return $mime_types;
}

add_filter('upload_mimes', 'WPSM_enable_extended_upload');

// ============================================================================================================================
# register WordPress menus
function WPSM_admin_menus() {
	$listfile = esc_html(__( 'List Files', 'safeguard-media' ));
	$settings = esc_html(__( 'Settings', 'safeguard-media' ));

	add_menu_page('SafeGuard Media', 'SafeGuard Media', 'publish_posts', 'wpsafeguard_list');
	add_submenu_page('wpsafeguard_list', 'SafeGuard Media List Files', $listfile, 'publish_posts', 'wpsafeguard_list', 'WPSM_admin_page_list');
	add_submenu_page('wpsafeguard_list', 'SafeGuard Media Settings', $settings, 'publish_posts', 'wpsafeguard_settings', 'WPSM_admin_page_settings');
}

// ============================================================================================================================

# convert shortcode to html output
function WPSM_shortcode($atts)
{
	if(is_admin()) {
		return '<p>Shortcode is disabled on admin preview.</p>';
	}

	$wpsafeguard_options = get_option('wpsafeguard_settings');
	
	$redirect = WPSM_check_artis_browser_version();

	if($redirect && (empty($wpsafeguard_options['settings']['mode']) || $wpsafeguard_options['settings']['mode'] != 'debug'))
	{
		if(headers_sent())
		{
			$html = "<script>document.location = '" . esc_js($redirect) . "'</script>";
			return $html;
		}
		else
		{
			wp_redirect($redirect);
			exit;
		}
	}

	$browserinfo = WPSM_get_aspscp_browser_info();
	
	global $post;

	$currentuser = wp_get_current_user();
	$postid      = $post->ID;
	$filename    = $atts["name"];
	$external    = true;

	if(strpos(strtolower($filename), 'https://safeguard.media/') === 0)
	{
		$parts = explode('https://safeguard.media/', $filename);

		if(count($parts) == 2)
		{
			if( ! is_numeric($parts[1]) || strlen($parts[1]) < 10)
			{
				return "<div style='padding:5px 10px;background-color:#fffbcc'><strong>Media not found!</strong></div>";
			}
			else
			{
				$filename = $parts[1];
			}
		}
		else
		{
			return "<div style='padding:5px 10px;background-color:#fffbcc'><strong>Media not found!</strong></div>";
		}
	}
	else
	{
		if( ! is_numeric($filename) || strlen($filename) < 10)
		{
			return "<div style='padding:5px 10px;background-color:#fffbcc'><strong>Media not found!</strong></div>";
		}
	}

	$settings = WPSM_get_first_class_settings();

	// get plugin options
	if ($wpsafeguard_options["settings"]) {
		$settings = wp_parse_args($wpsafeguard_options["settings"], $settings);
	}

	if (isset($wpsafeguard_options["classsetting"][$postid][$filename])) {
		$settings = wp_parse_args($wpsafeguard_options["classsetting"][$postid][$filename], $settings);
	}

	$settings = wp_parse_args($atts, $settings);

	extract($settings, EXTR_SKIP);

	$asps    = ($asps) ? '1' : '0';
	$firefox = ($ff) ? '1' : '0';
	$chrome  = ($ch) ? '1' : '0';

	$allowremote = ($allowremote) ? '1' : '0';

	if(isset($atts['remote']))
	{
		$allowremote = $atts['remote'] ? 1 : 0;
	}

	$plugin_url  = WPSAFEGUARD_PLUGIN_URL;
	$upload_url  = WPSM_get_upload_url();

	$devicekey = isset($_SERVER['HTTP_ARTISDRM']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_ARTISDRM'])) : 'devicekey';
	$devicekey_encrypted = WPSM_encrypt_decrypt('encrypt', $devicekey);
	
	if($currentuser->ID>0)
		$user_details_encrypted = WPSM_encrypt_decrypt('encrypt', $devicekey.'#'.$currentuser->ID.' - '.$currentuser->user_login.' - '.$currentuser->user_email.' - '. gmdate('Y-m-d')); 
	else
	{
		$remote_address = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '';
		$user_details_encrypted = WPSM_encrypt_decrypt('encrypt',  $devicekey.'#'.$remote_address.' - ' . gmdate('Y-m-d'));
	}

	$user_login = WPSM_encrypt_decrypt('encrypt', $currentuser->user_login);
	$user_email = WPSM_encrypt_decrypt('encrypt', $currentuser->user_email); 

	$currentuser            = wp_get_current_user();
	$av_watermark_onoff     = get_option('wpsm_av_watermark_onoff');
	$av_watermark_userid    = $currentuser->ID;
	$av_watermark_user_name = $user_login;
	$av_watermark_useremail = $user_email;
	$av_watermark_date      = gmdate('Y-m-d');
	
	$av_watermarktextcolor    = get_option('wpsm_av_watermarktextcolor');
	$av_watermarkshadecolor_1 = get_option('wpsm_av_watermarkshadecolor_1');
	$av_watermarkshadecolor_2 = get_option('wpsm_av_watermarkshadecolor_2');
	$av_watermarkshadecolor_3 = get_option('wpsm_av_watermarkshadecolor_3');
	$av_watermarkshadecolor_4 = get_option('wpsm_av_watermarkshadecolor_4');
	$av_watermarkposition     = get_option('wpsm_av_watermarkposition');
	$av_watermarkfontsize     = get_option('wpsm_av_watermarkfontsize');
	$av_watermarkfontstyle    = get_option('wpsm_av_watermarkfontstyle');
	$av_watermarkopacity      = get_option('wpsm_av_watermarkopacity');
	$av_allowwindows          = get_option('wpsm_av_allowwindows');
	$av_allowmac              = get_option('wpsm_av_allowmac');
	$av_allowandroid          = get_option('wpsm_av_allowandroid');
	$av_allowios              = get_option('wpsm_av_allowios');
	$av_allowremote           = get_option('wpsm_av_allowremote');
	$av_Version_windows       = get_option('wpsm_av_Version_windows');
	$av_Version_mac           = get_option('wpsm_av_Version_mac');
	$av_Version_ios           = get_option('wpsm_av_Version_ios');
	$av_Version_android       = get_option('wpsm_av_Version_android');
	
	$browsername    = $browserinfo['name'];
	$browserversion = $browserinfo['version'];

	if(empty($av_wa8termarkfontsize))
	{
		$av_watermarkfontsize = '20';
	}

	// for watermark end
	if (!$external) {
		$name = $upload_url.$name;
	}

	$errormessage = __('Device type not authorised by this website!', 'safeguard-media');
	$id = 'safeguard-media-outer-' . uniqid();

	$server_version = str_replace('.', '-', WPSAFEGUARD_VERSION);

	$data = [
		'wpsafeguard_plugin_url' => $plugin_url,
		'wpsafeguard_upload_url' => $upload_url,
		'browser' => $browsername,
		'version' => $browserversion,
		'server_version' => $server_version,

		// for watermark start
		'av_watermark_onoff' => $av_watermark_onoff,
		'av_watermark_userid' => $av_watermark_userid,
		'av_watermark_user_name' => $av_watermark_user_name,
		'av_watermark_useremail' => $av_watermark_useremail,
		'av_watermark_date' => $av_watermark_date,
		'av_watermarktextcolor' => $av_watermarktextcolor,
		'av_watermarkshadecolor_1' => $av_watermarkshadecolor_1,
		'av_watermarkshadecolor_2' => $av_watermarkshadecolor_2,
		'av_watermarkshadecolor_3' => $av_watermarkshadecolor_3,
		'av_watermarkshadecolor_4' => $av_watermarkshadecolor_4,
		'av_watermarkposition' => $av_watermarkposition,
		'av_watermarkfontsize' => $av_watermarkfontsize,
		'av_watermarkfontstyle' => $av_watermarkfontstyle,
		'av_watermakdetailsenc' => $user_details_encrypted,
		'av_watermarkopacity' => $av_watermarkopacity,
		'av_allowwindows' => $av_allowwindows,
		'av_allowmac' => $av_allowmac,
		'av_allowandroid' => $av_allowandroid,
		'av_allowios' => $av_allowios,
		'av_allowremote' => $av_allowremote,
		'av_Version_windows' => $av_Version_windows,
		'av_Version_mac' => $av_Version_mac,
		'av_Version_ios' => $av_Version_ios,
		'av_Version_android' => $av_Version_android,
	
		// for watermark end
		'm_bpDebugging' => ($mode == 'debug' ? true : false),
		'm_szMode' => $mode,
		'm_szClassName' => $name,
		'm_szImageFolder' => $upload_url, // path from root with / on both ends
		'm_bpAllowRemote' => $allowremote,
		'm_bpWidth' => $width, // width of media display in pixels
		'm_bpHeight' => $height, // height of media display in pixels
		'm_computerId' => $devicekey_encrypted,

		'm_bpASPS' => $asps,
		'm_bpChrome' => $chrome,
		'm_bpFx' => $firefox, // all firefox browsers from version 5 and later
		'm_allowmac' => $av_allowmac,
		'm_allowwindows' => $av_allowwindows,
		'm_allowandroid' => $av_allowandroid,
		'm_allowios' => $av_allowios,
		'm_allowremote' => $av_allowremote,
		'errormessage' => $errormessage,
	];

	$output = '';

	if(current_user_can('edit_posts'))
	{
		$output = '<p>Please use ArtisBrowser with a non-admin account for testing protected pages.</p>';
	}
	else
	{
		if( ! defined('WPSAFEGUARD_SCRIPT_LOADED'))
		{
			$script_tag = 'script';
			$script_url = plugins_url('/js/wp-safeguard.js',__FILE__) . "?v=" . WPSAFEGUARD_ASSET_VERSION;
			$output .= '<' . $script_tag . ' type="text/javascript" src="' . esc_attr($script_url) . '"></' . $script_tag . '>';

			define('WPSAFEGUARD_SCRIPT_LOADED', true);
		}

// display output
$output .= '
<div id="' . esc_attr($id) . '"></div>
<style>
#safeguard media{
	width: 100%;
	position: absolute;
	height: 100%;
	left: 0;
	top: 0;
}
</style>
<script type="text/javascript">
	insertSafeGuardmedia("' . esc_js($id) . '", ' . wp_json_encode($data) . ');
</script>
';
	}

	return $output;
}

// ============================================================================================================================
# delete short code
function WPSM_delete_shortcode() {
	// get all posts
	$posts_array = get_posts();
	foreach ($posts_array as $post) {
		// delete short code
		$post->post_content = WPSM_deactivate_shortcode($post->post_content);
		// update post
		wp_update_post($post);
	}
}

// ============================================================================================================================
# deactivate short code
function WPSM_deactivate_shortcode($content) {
	// delete short code
	$content = preg_replace('/\[safeguard name="[^"]+"\]\[\/safeguard\]/s', '', $content);
	return $content;
}

// ============================================================================================================================
# search short code in post content and get post ids
function WPSM_search_shortcode($file_name) {
	// get all posts
	$posts = get_posts();
	$IDs = [];
	foreach ($posts as $post) {
		$file_name = preg_quote($file_name, '\\');
		preg_match('/\[safeguard name="' . esc_attr($file_name) . '"\]\[\/safeguard\]/s', $post->post_content, $matches);
		if (is_array($matches) && isset($matches[1])) {
		$IDs[] = $post->ID;
		}
	}
	return empty($IDs) ? false : $IDs;
}

function WPSM_media_buttons($context) {
	global $post_ID;

	if (current_user_can('edit_posts'))
	{
		echo wp_kses("<a href='#' id='wpsafeguard_link' data-body='no-overflow' title='".esc_attr( __( 'SafeGuard Media', 'safeguard-media') )."'><img src='" . plugin_dir_url(__FILE__) . "images/safeguardbutton.png'></a>", WPSM_kses_allowed_options());
	}
}

// ============================================================================================================================
# setup plugin
function WPSM_setup() {
	//----add codding----
	define('WPSAFEGUARD_PLUGIN_PATH', str_replace("\\", "/", plugin_dir_path(__FILE__))); //use for include files to other files
	define('WPSAFEGUARD_PLUGIN_URL', plugins_url('/', __FILE__));

	require_once WPSAFEGUARD_PLUGIN_PATH . "inc/function-config.php";
	require_once WPSAFEGUARD_PLUGIN_PATH . "inc/function-common.php";
	require_once WPSAFEGUARD_PLUGIN_PATH . "inc/function-server.php";
	require_once WPSAFEGUARD_PLUGIN_PATH . "inc/function-browser.php";
	require_once WPSAFEGUARD_PLUGIN_PATH . "inc/function-enqueue.php";
	require_once WPSAFEGUARD_PLUGIN_PATH . "inc/function-body-class.php";

	require_once WPSAFEGUARD_PLUGIN_PATH . "inc/admin-page-settings.php";
	require_once WPSAFEGUARD_PLUGIN_PATH . "inc/admin-page-list.php";
	require_once WPSAFEGUARD_PLUGIN_PATH . "inc/admin-editor.php";
	require_once WPSAFEGUARD_PLUGIN_PATH . "inc/frontend-general.php";

	require_once WPSAFEGUARD_PLUGIN_PATH . "inc/ajax-common.php";
	require_once WPSAFEGUARD_PLUGIN_PATH . "inc/ajax-server.php";

	// add short code
	add_shortcode('safeguard', 'WPSM_shortcode');

	//Admin menu
	add_action('admin_menu', 'WPSM_admin_menus');

	//Admin CSS
	add_action('admin_enqueue_scripts', 'WPSM_admin_enqueue_scripts');

	//Admin body class
	add_filter('admin_body_class', 'WPSM_amin_body_classes' );

	// load media button
	add_action('media_buttons', 'WPSM_media_buttons');
}

// ============================================================================================================================
# runs when plugin activated
function WPSM_activate()
{
	$upload_dir = wp_upload_dir();

	$default_upload_dir = $upload_dir['basedir'] . '/safeguard-media/';

	// if this is first activation, setup plugin options
	if (!get_option('wpsafeguard_settings')) {

		// set default options
		$wpsafeguard_options['settings'] = [
			'admin_only'  => "checked",
			'mode'        => "demo",
			'language'    => "",
			'width'       => '620',
			'height'      => '400',
			'asps'        => "checked",
			'ff'          => "",
			'ch'          => "",
		];

		update_option('wpsafeguard_settings', $wpsafeguard_options);

		update_option('wpsm_av_allowwindows', 1);
		update_option('wpsm_av_allowmac', 1);
		update_option('wpsm_av_allowandroid', 1);
		update_option('wpsm_av_allowios', 1);
	}

	if( ! is_dir($default_upload_dir))
	{
		wp_mkdir_p($default_upload_dir);
	}
}

// ============================================================================================================================
# runs when plugin deactivated
function WPSM_deactivate() {
	// remove text editor short code
	remove_shortcode('safeguard');
}

// ============================================================================================================================
# runs when plugin deleted.
function WPSM_uninstall()
{
	$upload_dir = wp_upload_dir();

	// delete all safemedia uploaded files
	$default_upload_dir = $upload_dir['basedir'] . '/safeguard-media/';

	if (is_dir($default_upload_dir))
	{
		global $wp_filesystem;

		require_once ABSPATH . 'wp-admin/includes/file.php';
		WP_Filesystem();

		$dir = scandir($default_upload_dir);
		foreach ($dir as $file)
		{
			if ($file != '.' || $file != '..')
			{
				wp_delete_file($default_upload_dir . $file);
			}
		}

		$wp_filesystem->rmdir($default_upload_dir);
	}

	// delete plugin options
	delete_option('wpsafeguard_settings');

	// unregister short code
	remove_shortcode('safeguard');
}

// ============================================================================================================================
# register plugin hooks
register_activation_hook(__FILE__, 'WPSM_activate'); // run when activated
register_deactivation_hook(__FILE__, 'WPSM_deactivate'); // run when deactivated
register_uninstall_hook(__FILE__, 'WPSM_uninstall'); // run when uninstalled

add_action('init', 'WPSM_setup');
//Imaster Coding

function WPSM_admin_head() {
	$get_setting_option = get_option('wpsafeguard_settings');
	$uploader_options = [
		'runtimes' => 'html5,silverlight,flash,html4',
		'browse_button' => 'mfu-plugin-uploader-button',
		'container' => 'mfu-plugin-uploader',
		'drop_element' => 'mfu-plugin-uploader',
		'file_data_name' => 'async-upload',
		'multiple_queues' => TRUE,
		'url' => admin_url('admin-ajax.php'),
		'flash_swf_url' => includes_url('js/plupload/plupload.flash.swf'),
		'silverlight_xap_url' => includes_url('js/plupload/plupload.silverlight.xap'),
		'filters' => [
			[
				'title' => esc_html(__('Allowed Files', 'safeguard-media')),
				'extensions' => '*',
			],
		],
		'multipart' => TRUE,
		'urlstream_upload' => TRUE,
		'multi_selection' => TRUE,
		'multipart_params' => [
		'_ajax_nonce' => '',
		'action' => 'wpsm_upload',
		],
	];
	?>
    <script type="text/javascript">
      var global_uploader_options = <?php echo wp_json_encode($uploader_options); ?>;
    </script>
	<?php
}

add_action('admin_head', 'WPSM_admin_head');
add_action('elementor/editor/wp_head', 'WPSM_admin_head');
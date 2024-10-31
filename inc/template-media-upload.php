<?php
if (!defined('ABSPATH')) exit;

global $post;

$timestamp   = time();
$nonce_token = wp_create_nonce('wpsafeguard_token');
$token       = md5('unique_salt' . $timestamp);

$post_id             = isset($post->ID) ? $post->ID : 0;
$wpsafeguard_options = get_option("wpsafeguard_settings");
$upload_path         = WPSM_get_upload_dir();
$_SESSION['token']   = $token;

$session_id    = session_id();
$token_session = "{$token}-{$session_id}";
$admin_only    = $wpsafeguard_options["settings"]["admin_only"];

$allow_uploads = TRUE;

if ($admin_only && ! current_user_can('administrator')) {
	$allow_uploads = FALSE;
}
?>
<style type="text/css">
.ui-dialog { z-index: 10000 !important ;}
.processing {position:relative;}
.processing-blocker {
    background-color: #fff;
    opacity: .6;
    width: 100%;
    height: 100%;
    position: absolute;
    top: 0;
    left: 0;
    z-index: 10;
}
</style>
<div class="wrap" id="wpsafeguard_div" title="SafeGuard Media Upload" style="display:none;">

	<div id="wpsafeguard_message"></div>

	<div id="wpsm-upload-tabs">
		<ul>
			<?php if ($allow_uploads) { ?>
			<li>
				<a href="#wpsm-tabs-1" id="tabs-1-bt"><?php echo esc_html( __( 'Add New', 'safeguard-media') );  ?></a>
			</li>
			<?php } ?>
			<li>
				<a href="#wpsm-tabs-3" id="tabs-3-bt"><?php echo esc_html( __( 'Existing Files', 'safeguard-media') );  ?></a>
			</li>
		</ul>

		<?php if ($allow_uploads) { ?>
		<div id="wpsm-tabs-1">
			<div class="icon32" id="icon-addnew"><br/></div>
			<h2><?php echo esc_html( __( 'Add New Media', 'safeguard-media') );  ?></h2>

			<div class="wpsafeguard_upload_content">
				<div id="upload-queue"></div>

				<table>
					<tr>
						<td>
							<div class="mfu-plugin-uploader multiple">
								<input id="mfu-plugin-uploader-button"
										type="button"
										value="<?php esc_attr_e('Select Files', 'safeguard-media'); ?>"
										class="mfu-plugin-uploader-button button">

								<span class="ajaxnonce"
										id="<?php echo esc_attr(wp_create_nonce('wpsafeguard_upload_nonce')); ?>"></span>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div id="upload-filename"></div>
						</td>
					</tr>
					<tr>
						<td>
							<div id="upload-status"></div>
						</td>
					</tr>
					<tr>
						<td>
							<div id="upload-insert-form"></div>
						</td>
					</tr>
				</table>
			</div>

			<p><?php echo esc_html( __( 'You can choose file options after file is uploaded.', 'safeguard-media') );  ?></p>
			<p><?php echo esc_html( __( 'If you use same name with uploaded media, it will be  overwritten.', 'safeguard-media') );  ?></p>

			<input type="hidden" value="<?php echo (int)$post_id; ?>" name="postid" id="postid"/>
			<input type="hidden" value="<?php echo esc_attr(WPSAFEGUARD_PLUGIN_URL); ?>" id="plugin-url"/>
			<input type="hidden" value="<?php echo esc_attr(WPSAFEGUARD_PLUGIN_PATH); ?>" id="plugin-dir"/>
			<input type="hidden" value="<?php echo esc_attr(WPSM_get_upload_dir()); ?>" id="upload-path"/>
			<input type="hidden" value="<?php echo esc_attr($timestamp); ?>" id="token_timestamp"/>
			<input type="hidden" value="<?php echo esc_attr($token_session); ?>" id="token"/>
		</div>
		<?php } ?>

		<div id="wpsm-tabs-3">
			<div class="icon32" id="icon-file"><br/></div>
			<h2><?php echo esc_html( __( 'Uploaded Files', 'safeguard-media') );  ?></h2>
			<table class="wp-list-table widefat">
				<thead>
					<tr>
						<th><?php echo esc_html( __( 'File', 'safeguard-media') );  ?></th>
						<th width="230px"><?php echo esc_html( __( 'Date', 'safeguard-media') );  ?></th>
					</tr>
				</thead>
				<tbody id="wpsafeguard_upload_list">
					<tr>
						<td colspan="2" style="text-align: center;">Loading files...</td>
					</tr>
				</tbody>
				<tfoot>
					<tr>
						<th><?php echo esc_html( __( 'File', 'safeguard-media') );  ?></th>
						<th><?php echo esc_html( __( 'Date', 'safeguard-media') );  ?></th>
					</tr>
				</tfoot>
			</table>
		</div>
	</div>

	<div id="wpsafeguard_ajax_process">
		<div class="wpsafeguard_ajax_process"></div>
	</div>
</div>
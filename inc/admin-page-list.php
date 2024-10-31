<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly

function WPSM_admin_page_list()
{
	$msg        = '';
	$table      = '';
	$base_url   = get_site_url();

	if( ! empty($_POST))
	{
		// Check if image file is a actual image or fake image
		if (isset($_POST["safeguard-media-url-submit"]) && isset($_POST["safeguard-media-url"])
			&& isset($_POST['_wpnonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'wpsafeguard_nonce_upload_url'))
		{
			$target_file         = isset($_FILES["safeguard-media-file"]["name"]) ? basename(sanitize_file_name($_FILES["safeguard-media-file"]["name"])) : '';
			$imageFileType       = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
			$allowedfiletypes    = WPSM_allowed_file_types();
			$safeguard_media_url = sanitize_url(wp_unslash($_POST["safeguard-media-url"]));

			$tmp = explode('.', $safeguard_media_url);

			$imageFileType = end($tmp);
			$imageFileType = strtolower($imageFileType);

			try {
				if( ! in_array($imageFileType,$allowedfiletypes)) {
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

				$filename = basename($safeguard_media_url);

				$success_message = sprintf(
					/* translators: %1s: File name, %2s: Base Url */
					__('The file %1$s has been uploaded. Click <a href="%2$s/wp-admin/admin.php?page=wpsafeguard_list">here</a> to update below list.', 'safeguard-media'),
					$filename,
					$base_url
				);

				$msg .= '<div class="updated"><p><strong>' . wp_kses_post($success_message) . '</strong></p></div>';

			} catch(Exception $e) {
				$msg .= '<div class="error"><p><strong>' . esc_html($e->getMessage()) . '</strong></p></div>';
			}
		}

		if (isset($_POST["safeguard-media-file-submit"])
			&& isset($_POST['_wpnonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'wpsafeguard_nonce_upload_file'))
		{
			$uploadOk            = 1;
			$target_file         = isset($_FILES["safeguard-media-file"]["name"]) ? basename(sanitize_file_name($_FILES["safeguard-media-file"]["name"])) : '';
			$imageFileType       = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
			$allowedfiletypes    = WPSM_allowed_file_types();
			
			// Allow only .mp4 file formats
			$uploadfile =__( 'Please upload file to continue.', 'safeguard-media' );
			$fileformat =__( 'Sorry, only jpg, jpeg, png, gif, mp4, doc, docx, pdf files are allowed.', 'safeguard-media' );
			$uploaderr  =__( 'Sorry, your file was not uploaded.', 'safeguard-media' );
		
			if ($_FILES["safeguard-media-file"]["name"] == "") {
				$msg .= '<div class="error"><p><strong>' . esc_html($uploadfile) . '</strong></p></div>';
				$uploadOk = 0;
			}
			else if (!in_array($imageFileType, $allowedfiletypes)) {
				$msg .= '<div class="error"><p><strong>' . esc_html($fileformat) . '</strong></p></div>';
				$uploadOk = 0;
			}

			// Check if $uploadOk is set to 0 by an error
			else if ($uploadOk == 0) {
				$msg .= '<div class="error"><p><strong>' . esc_html($uploaderr) . '</strong></p></div>';
				// if everything is ok, try to upload file
			}
			else
			{
				//Register path override
				add_filter('upload_dir', 'WPSM_upload_dir');

				//Move file
				$movefile = wp_handle_upload($_FILES["safeguard-media-file"], [
					'test_form' => false,
				]);

				//Remove path override
				remove_filter('upload_dir', 'WPSM_upload_dir');

				if ($movefile && ! isset($movefile['error']))
				{
					$safeguard_media_url = $movefile['url'];

					try {
						$client_id = WPSM_get_client_id();

						if(empty($client_id)) {
							throw new Exception(__('Account does not exist on the SafeGuard Media server.', 'safeguard-media'));
						}
						
						$parts = WPSM_register_media($safeguard_media_url, $client_id);

						if($parts[0] != 'success') {
							throw new Exception(__('An error occured while publishing your file to the SafeGuard Media server.', 'safeguard-media'));
						}

						$filename = basename($movefile['file']);

						$success_message = sprintf(
							/* translators: %1s: File name, %2s: Base URL */
							__('The file %1$s has been uploaded. Click <a href="%2$s/wp-admin/admin.php?page=wpsafeguard_list">here</a> to update below list.', 'safeguard-media'),
							$filename,
							$base_url
						);

						$msg .= '<div class="updated"><p><strong>' . wp_kses_post($success_message) . '</strong></p></div>';

					} catch(Exception $e) {
						$msg .= '<div class="error"><p><strong>' . esc_html($e->getMessage()) . '</strong></p></div>';
					}
				}
				else {
					$msg .= '<div class="error"><p><strong>' . esc_html($movefile['error']) . '</strong></p></div>';
				}
			}
		}
	}

	$files = WPSM_get_server_files();

	foreach($files as $file)
	{
		$filelink = $file['file'];

		if( ! isset($table)) {
			$table = "";
		}

		$table .= "<tr><td><2/td><td>".esc_html($filelink)."</td><td>".esc_html($file['title'])."</td><td>[safeguard name='".esc_html($filelink)."' width='600' height='600' remote='0']</td><td>".esc_html($file['date'])."</td></tr>";
	}

	if (!$table) {
		$table .= '<tr><td colspan="4">' . esc_html(__('No file uploaded yet.', 'safeguard-media')) . '</td></tr>';
	}
	?>
	<div class="wrap">
		<div class="icon32" id="icon-file"><br/></div>
		<?php
		if( ! isset($msg)) {
			$msg = "";
		}
		echo wp_kses_post($msg); 
		?>
		<h2><?php echo esc_html(__( 'List Media Files', 'safeguard-media' )); ?></h2>
		<br>
		<span style="font-size:12px"><?php echo esc_html(__( 'Upload Only jpg,jpeg,gif,png,mp4,doc,docx,pdf', 'safeguard-media' )); ?></span>
		<br>
		
		<form action="" method="post" enctype="multipart/form-data">
			<input type="file" name="safeguard-media-file" value="" />
			<?php echo wp_kses(wp_nonce_field('wpsafeguard_nonce_upload_file'), WPSM_kses_allowed_options()); ?>

			<input type="submit" name="safeguard-media-file-submit"
				value="<?php echo esc_attr(__( 'Upload', 'safeguard-media' )); ?>"/>
		</form>
		<hr>

		<?php echo esc_html(__( 'Provide link to external media', 'safeguard-media' ));  ?>
		<form action="" method="post" enctype="multipart/form-data" onsubmit="if(document.getElementById('safeguard-media-url').value=='') return false;">
			<?php echo wp_kses(wp_nonce_field('wpsafeguard_nonce_upload_url'), WPSM_kses_allowed_options()); ?>

			<input type="text" name="safeguard-media-url" id="safeguard-media-url" value="" style="width:600px;">
			<input type="submit" name="safeguard-media-url-submit"
				value="<?php echo esc_attr(__( 'Submit', 'safeguard-media' ));  ?> "/>
		</form>

		<div id="col-container" style="width:100%;">
			<div class="col-wrap">
				<h3><?php echo esc_html(__( 'Registered Media Files', 'safeguard-media' )); ?></h3>
				<table class="wp-list-table widefat">
					<thead>
					<tr>
						<th width="5px">&nbsp;</th>
						<th><?php echo esc_html(__( 'Media Link', 'safeguard-media' )); ?></th>
						<th><?php echo esc_html(__( 'Filename', 'safeguard-media' )); ?></th>
						<th><?php echo esc_html(__( 'Shortcode', 'safeguard-media' )); ?></th>
						<th><?php echo esc_html(__( 'Last updated', 'safeguard-media' )); ?></th>
						
					</tr>
					</thead>
					<tbody>
					<?php echo wp_kses_post($table); ?>
					</tbody>
					<tfoot>
					<tr>
						<th width="5px">&nbsp;</th>
						<th><?php echo esc_html(__( 'Media Link', 'safeguard-media' )); ?></th>
						<th><?php echo esc_html(__( 'Filename', 'safeguard-media' )); ?></th>
						<th><?php echo esc_html(__( 'Shortcode', 'safeguard-media' )); ?></th>
						<th><?php echo esc_html(__( 'Last updated', 'safeguard-media' )); ?></th>
						
					</tr>
					</tfoot>
				</table>
			</div>
		</div>
		<div class="clear"></div>
	</div>
	<?php
}
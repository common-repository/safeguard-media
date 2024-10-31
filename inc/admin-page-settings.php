<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly

function WPSM_admin_page_settings()
{
	$msg              = '';
	$current_user     = '';
	$upload_path      = WPSM_get_upload_dir();
	$option_structure = WPSM_option_structure();

	if( ! empty($_POST))
	{
		if( ! empty($_POST['_wpnonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'wpsafeguard_nonce_register') &&
			isset($_POST['action_two']) && sanitize_text_field(wp_unslash($_POST['action_two'])) == 'newdemo')
		{
			/**********************
			 * Register
			 *********************/

			$firstname = (isset($_POST['fm_firstname']))?sanitize_text_field(wp_unslash($_POST['fm_firstname'])):"";
			$lastname  = (isset($_POST['fm_lastname']))?sanitize_text_field(wp_unslash($_POST['fm_lastname'])):"";
			$email     = (isset($_POST['fm_email']))?sanitize_email(wp_unslash($_POST['fm_email'])):"";
			$company   = (isset($_POST['fm_company']))?sanitize_text_field(wp_unslash($_POST['fm_company'])):"";
			$domain    = (isset($_POST['fm_domain']))?sanitize_url(wp_unslash($_POST['fm_domain'])):"";
			
			$post = [
				'action'       => 'newdemo',
				'fm_firstname' => $firstname,
				'fm_lastname'  => $lastname,
				'fm_email'     => $email,
				'fm_company'   => $company,
				'fm_domain'    => $domain,
			];
			$args = array(
				'body'        => $post,
				'timeout'     => '5',
				'redirection' => '5',
				'httpversion' => '1.0',
				'blocking'    => true,
				'headers'     => array(),
				'cookies'     => array(),
			);
			$url='https://safeguard.media/asps/webservice.asp?action=newdemo&fm_firstname='.
				$firstname.'&fm_lastname='.$lastname.'&fm_email='.$email.
				'&fm_company='.$company.'&fm_domain='.$domain;
			$response = wp_remote_post( $url, $args );

			if( ! is_wp_error($response))
			{
				$parts = explode('~',$response['body']);

				if( ! empty($parts[0]) && $parts[0] == 'success' && ! empty($parts[1]))
				{
					if($parts[1] == 'exists')
					{
						$msg = '<div class="updated"><p><strong>'. esc_html(__('You already have an account. Please enter your api key below.', 'safeguard-media')) .'</strong></p></div>';
					}
					else
					{
						update_option('wpsm_av_apikey', $parts[1]);

						$msg = '<div class="updated"><p><strong>'. esc_html(__('You are now registered. Please update your settings below.', 'safeguard-media')) .'</strong></p></div>';
					}
				}
				else
				{
					$msg = '<div class="error"><p><strong>'. esc_html(__('Registration unsuccessful, please verify your information entered.', 'safeguard-media')) .'</strong></p></div>';
				}
			}
			else
			{
				$msg = '<div class="error"><p><strong>'. esc_html(__('Registration unsuccessful, there is a problem with the plugin SafeGuard Media server, please contact administrator is problem persists.', 'safeguard-media')) .'</strong></p></div>';
			}
		}
		else if( ! empty($_POST['wpsafeguard_save_settings'])
			&& isset($_POST['_wpnonce'])&& wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'wpsafeguard_nonce_save_settings'))
		{
			/******************************
			 * Save user settings
			 *****************************/
			$option_data = [];

			foreach($option_structure as $option_key => $option)
			{
				$option_data[$option_key] = WPSM_sanitize_option($option_key, $option, $_POST);
			}
			
			$update_isset = [
				'wpsm_av_watermark_userid',
				'wpsm_av_watermark_user_name',
				'wpsm_av_watermark_useremail',
				'wpsm_av_watermark_date',
				'wpsm_av_watermarktextcolor',
				'wpsm_av_watermarkshadecolor_1',
				'wpsm_av_watermarkshadecolor_2',
				'wpsm_av_watermarkshadecolor_3',
				'wpsm_av_watermarkshadecolor_4',
				'wpsm_av_watermarkposition',
				'wpsm_av_watermarkfontsize',
				'wpsm_av_apikey',
				'wpsm_av_watermarkopacity',
				'wpsm_av_allowremote',
				'wpsm_av_Version',
				'wpsm_av_Version_windows',
				'wpsm_av_Version_mac',
				'wpsm_av_Version_android',
				'wpsm_av_Version_ios',
			];
			$update_all = [
				'wpsm_av_allowwindows',
				'wpsm_av_allowmac',
				'wpsm_av_allowandroid',
				'wpsm_av_allowios',
				'wpsm_av_watermark_onoff',
			];

			foreach($option_data as $option_key => $value)
			{
				if(in_array($option_key, $update_isset))
				{
					if(isset($_POST[$option_key]))
					{
						update_option($option_key, $value);
					}
				}
				else if(in_array($option_key, $update_all))
				{
					update_option($option_key, $value);
				}
			}

			$av_apikey = get_option('wpsm_av_apikey');

			/******************************
			 * Save settings to server
			 *****************************/
			$varstring =
				'&av_watermark_onoff='.$option_data['wpsm_av_watermark_onoff'].
				'&av_watermarktextcolor='.$option_data['wpsm_av_watermarktextcolor'].
				'&av_watermarkshadecolor_4='.$option_data['wpsm_av_watermarkshadecolor_4'].
				'&av_watermarkposition='.$option_data['wpsm_av_watermarkposition'].
				'&av_watermarkfontsize='.$option_data['wpsm_av_watermarkfontsize'].
				'&av_watermarkopacity='.$option_data['wpsm_av_watermarkopacity'].
				'&av_allowwindows='.$option_data['wpsm_av_allowwindows'].
				'&av_allowmac='.$option_data['wpsm_av_allowmac'].
				'&av_allowandroid='.$option_data['wpsm_av_allowandroid'].
				'&av_allowios='.$option_data['wpsm_av_allowios'].
				'&av_allowremote='.$option_data['wpsm_av_allowremote'].
				'&av_Version_windows='.$option_data['wpsm_av_Version_windows'].
				'&av_Version_mac='.$option_data['wpsm_av_Version_mac'].
				'&av_Version_ios='.$option_data['wpsm_av_Version_ios'].
				'&av_Version_android='.$option_data['wpsm_av_Version_android'];
			
			$post = [
				'action'                   => 'savesettings',
				'av_apikey'                => $av_apikey,
				'av_watermark_onoff'       => $option_data['wpsm_av_watermark_onoff'],
				'current_user'             => $current_user,
				'av_watermark_userid'      => $option_data['wpsm_av_watermark_userid'],
				'av_watermark_user_name'   => $option_data['wpsm_av_watermark_user_name'],
				'av_watermark_useremail'   => $option_data['wpsm_av_watermark_useremail'],
				'av_watermark_date'        => $option_data['wpsm_av_watermark_date'],
				'av_watermarktextcolor'    => $option_data['wpsm_av_watermarktextcolor'],
				'av_watermarkshadecolor_4' => $option_data['wpsm_av_watermarkshadecolor_4'],
				'av_watermarkposition'     => $option_data['wpsm_av_watermarkposition'],
				'av_watermarkfontsize'     => $option_data['wpsm_av_watermarkfontsize'],
				'av_watermarkopacity'      => $option_data['wpsm_av_watermarkopacity'],
				'av_allowwindows'          => $option_data['wpsm_av_allowwindows'],
				'av_allowmac'              => $option_data['wpsm_av_allowmac'],
				'av_allowandroid'          => $option_data['wpsm_av_allowandroid'],
				'av_allowios'              => $option_data['wpsm_av_allowios'],
				'av_allowremote'           => $option_data['wpsm_av_allowremote'],
				'av_Version_windows'       => $option_data['wpsm_av_Version_windows'],
				'av_Version_mac'           => $option_data['wpsm_av_Version_mac'],
				'av_Version_ios'           => $option_data['wpsm_av_Version_ios'],
				'av_Version_android'       => $option_data['wpsm_av_Version_android'],
			];
			
			$args = array(
				'body'        => $post,
				'timeout'     => '5',
				'redirection' => '5',
				'httpversion' => '1.0',
				'blocking'    => true,
				'headers'     => array(),
				'cookies'     => array(),
			);
			$url='https://safeguard.media/asps/webservice.asp?action=savesettings&av_apikey='.$av_apikey.$varstring;
			$response = wp_remote_post( $url, $args );
			
			if( ! is_wp_error($response))
			{
				$parts=explode('~',$response['body']);

				/*********************
				* Safeguard Settings
				********************/
				$wpsafeguard_options = get_option('wpsafeguard_settings');
				
				$admin_only     = isset($_POST['admin_only']) ? sanitize_text_field(wp_unslash($_POST['admin_only'])) : '';
				$mode           = isset($_POST['mode']) ? sanitize_text_field(wp_unslash($_POST['mode'])) : '';
				$language       = isset($_POST['language']) ? sanitize_text_field(wp_unslash($_POST['language'])) : '';
				$background     = isset($_POST['background']) ? sanitize_text_field(wp_unslash($_POST['background'])) : '';
				$width          = isset($_POST['width']) ? sanitize_text_field(wp_unslash($_POST['width'])) : '';
				$height         = isset($_POST['height']) ? sanitize_text_field(wp_unslash($_POST['height'])) : '';
				$av_allowremote = empty($_POST['av_allowremote']) ? '' : 'checked';
				$asps           = empty($_POST['asps']) ? '' : 'checked';
				$ff             = empty($_POST['ff']) ? '' : 'checked';
				$ch             = empty($_POST['ch']) ? '' : 'checked';
				$av_Version     = isset($_POST['av_Version']) ? sanitize_text_field(wp_unslash($_POST['av_Version'])) : '';

				$wpsafeguard_options['settings'] = [
					'admin_only'      => $admin_only,
					'mode'            => $mode,
					'language'        => $language,
					'background'      => $background,
					'width'           => $width,
					'height'          => $height,
					'allowremote'     => $av_allowremote,
					'asps'            => $asps,
					'ff'              => $ff,
					'ch'              => $ch,
					'minimum_version' => $av_Version,
				];

				if ( ! is_dir($upload_path)) {
					wp_mkdir_p($upload_path);
				}

				update_option('wpsafeguard_settings', $wpsafeguard_options);

				$msg = '<div class="updated"><p><strong>'. esc_html(__('Settings Saved', 'safeguard-media')) .'</strong></p></div>';
			}
			else
			{
				$msg = '<div class="error"><p><strong>'. esc_html(__('There is a problem with the plugin SafeGuard Media server, please contact administrator is problem persists.', 'safeguard-media')) .'</strong></p></div>';
			}
		}
		else if(isset($_POST['action_three']) && $_POST['action_three'] =='getclient'
			&& isset($_POST['_wpnonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'wpsafeguard_nonce_getclient'))
		{
			/***************************
			 * Save api key, then
			 * Get settings from server
			 **************************/

			try {
				$av_apikey = isset($_POST['wpsm_av_apikey']) ? sanitize_text_field(wp_unslash($_POST['wpsm_av_apikey'])) : '';

				if(empty($av_apikey)) {
					throw new Exception(__('Please enter an api key', 'safeguard-media'));
				}

				$post = [
					'action'    => 'getclient',
					'av_apikey' => $av_apikey,
				];
				
				$args = array(
					'body'        => $post,
					'timeout'     => '5',
					'redirection' => '5',
					'httpversion' => '1.0',
					'blocking'    => true,
					'headers'     => array(),
					'cookies'     => array(),
				);
				$url='https://safeguard.media/asps/webservice.asp?action=getclient&av_apikey='.$av_apikey;
				$response = wp_remote_post( $url, $args );

				if(is_wp_error($response)) {
					throw new Exception(__('There is a problem with the plugin SafeGuard Media server, please contact administrator is problem persists.', 'safeguard-media'));
				}
				
				// execute!
				$parts=explode('~',$response['body']);
					
				if($parts[0] != 'success' || ! isset($parts[1])) {
					throw new Exception(__('Invalid api key', 'safeguard-media'));
				}

				update_option('wpsm_av_apikey', $av_apikey);

				/***************************
				 * Get settings from server
				 **************************/
				$post = [
					'action' => 'getsettings',
					'av_apikey' => $av_apikey,
				];
				
				$args = array(
					'body'        => $post,
					'timeout'     => '5',
					'redirection' => '5',
					'httpversion' => '1.0',
					'blocking'    => true,
					'headers'     => array(),
					'cookies'     => array(),
				);
				$url='https://safeguard.media/asps/webservice.asp?action=getsettings&av_apikey='.$av_apikey;
				$response = wp_remote_post( $url, $args );
				
				if(is_wp_error($response)) {
					throw new Exception(__('There is a problem with the plugin SafeGuard Media server, please contact administrator is problem persists.', 'safeguard-media'));
				}

				$parts2=explode('~',$response['body']);
				
				if($parts2[0]=='success' && isset($parts2[1]))
				{
					$resparts = explode(',',$parts2[1]);
					
					if(count($resparts) > 1)
					{
						update_option('wpsm_av_watermark_onoff', $resparts[0]);
						update_option('wpsm_av_watermarkfontsize', $resparts[1]);
						update_option('wpsm_av_watermarktextcolor', $resparts[2]);
						update_option('wpsm_av_watermarkshadecolor_4', $resparts[3]);
						update_option('wpsm_av_watermarkposition', $resparts[4]);
						update_option('wpsm_av_watermarkopacity', $resparts[5]);
						update_option('wpsm_av_allowandroid', $resparts[6]);
						update_option('wpsm_av_allowios', $resparts[7]);
						update_option('wpsm_av_allowmac', $resparts[8]);
						update_option('wpsm_av_allowwindows', $resparts[9]);
						update_option('wpsm_av_allowremote', $resparts[10]);
						update_option('wpsm_av_Version_windows', $resparts[11]);
						update_option('wpsm_av_Version_mac', $resparts[12]);
						update_option('wpsm_av_Version_ios', $resparts[13]);
						update_option('wpsm_av_Version_android', $resparts[14]);
					}
				}
				
				$msg = '<div class="updated"><p><strong>'. esc_html(__('Settings successfully saved.', 'safeguard-media')) .'</strong></p></div>';
			} catch(Exception $e) {
				$msg = '<div class="error"><p><strong>'. esc_html($e->getMessage()) .'</strong></p></div>';
			}
		}

	} //End if POST

	$admin_only     = '';
	$mode           = '';
	$language       = '';
	$background     = '';
	$width          = '';
	$height         = '';
	$av_allowremote = '';
	$asps           = '';
	$ff             = '';
	$ch             = '';
	$av_Version     = '';

	$urlparts = wp_parse_url(home_url());
	$fm_domain = $urlparts['host'];

	$wpsafeguard_options = get_option('wpsafeguard_settings');
	if ($wpsafeguard_options["settings"])
	{
		extract($wpsafeguard_options["settings"], EXTR_OVERWRITE);
	}

	$av_apikey = get_option('wpsm_av_apikey');
	
	$select = '<option value="licensed">Active</option><option value="debug">Debugging Mode</option>';
	$select = str_replace('value="' . esc_attr($mode) . '"', 'value="' . esc_attr($mode) . '" selected', $select);

	$validapikey = 0;

	if( ! empty($av_apikey))
	{
		$client_id = WPSM_get_client_id();

		if( ! empty($client_id)) {
			$validapikey = 1;
		}
	}
	?>
	<style type="text/css">
		.wpsafeguard_page_setting img {
			cursor: pointer;
		}
	</style>
	<div class="wrap">
		<div class="icon32" id="icon-settings"><br/></div>

		<?php echo wp_kses_post($msg); ?>
		<h2><?php echo esc_html( __( 'Default Settings', 'safeguard-media') ); ?></h2>

		<div class="card">
			<h3><?php echo esc_html__('Safeguard Media - Setup Guide', 'safeguard-media'); ?></h3>
			<a href="https://youtu.be/nFKZ42oBUDY" target="_blank" class="button"><?php echo esc_html__('Usage Video', 'safeguard-media'); ?></a>
			<a href="https://safeguard.media/download/SafeGuard_Media_for_WordPress.pdf" target="_blank" class="button"><?php echo esc_html__('Instruction PDF', 'safeguard-media'); ?></a>
		</div>
		
		<?php
		if( ! $validapikey)
		{
		?>
		<!--start sing up form-->
		<div>
			<form action="" method="post" id="Register" name="Register_account">
				<?php echo wp_kses(wp_nonce_field('wpsafeguard_nonce_register'), WPSM_kses_allowed_options()); ?>
				<input type="hidden" name="action_two"value="newdemo" >
				<h2><?php echo esc_html( __( 'Register Demo Account', 'safeguard-media') ); ?></h2>
				
				<table cellpadding='1' cellspacing='0' border='0' class='wpsafeguard_page_setting'>
					<tbody>
						<tr>
							<td width="50" align="left">&nbsp;</td>
							<td width="30" align="left"><?php echo wp_kses_post(WPSM_help_icon(__( 'SafeGuard API Key', 'safeguard-media' ))); ?></td>
							<td align="left"><?php echo esc_html( __( 'Action', 'safeguard-media') ); ?>:</td>
							<td align="left"> <?php echo esc_html( __( 'Register demo account', 'safeguard-media') ); ?></td>
						</tr>
						<tr>
							<td width="50" align="left">&nbsp;</td>
							<td width="30" align="left"><?php echo wp_kses_post(WPSM_help_icon(__( 'Your first name', 'safeguard-media' ))); ?></td>
							<td align="left"><?php echo esc_html( __( 'Firstname', 'safeguard-media') ); ?></td>
							<td align="left">
								<input type="text" name="fm_firstname" value="" />
							</td>
						</tr>
						
						<tr>
							<td width="50" align="left">&nbsp;</td>
							<td width="30" align="left"><?php echo wp_kses_post(WPSM_help_icon(__( 'Your last name', 'safeguard-media' ))); ?></td>
							<td align="left"><?php echo esc_html( __( 'Lastname', 'safeguard-media') ); ?></td>
							<td align="left">
								<input type="text" name="fm_lastname" value="" />
							</td>
						</tr>
						<tr>
							<td width="50" align="left">&nbsp;</td>
							<td width="30" align="left"><?php echo wp_kses_post(WPSM_help_icon(__( 'Your email address.', 'safeguard-media' ))); ?></td>
							<td align="left"><?php echo esc_html( __( 'Email', 'safeguard-media') ); ?></td>
							<td align="left">
							<input type="text" name="fm_email" value="" style="width: 300px;" />
							</td>
						</tr>
						<tr>
							<td width="50" align="left">&nbsp;</td>
							<td width="30" align="left"><?php echo wp_kses_post(WPSM_help_icon(__( 'Your company name.', 'safeguard-media' ))); ?></td>
							<td align="left"><?php echo esc_html( __( 'Company', 'safeguard-media') ); ?></td>
							<td align="left">
							<input type="text" name="fm_company" value="" style="width: 300px;" />
							</td>
						</tr>
						<tr>
							<td width="50" align="left">&nbsp;</td>
							<td width="30" align="left"><?php echo wp_kses_post(WPSM_help_icon(__( 'Your domain name, ie: the url of the website.', 'safeguard-media' ))); ?></td>
							<td align="left"><?php echo esc_html( __( 'Domain', 'safeguard-media') ); ?></td>
							<td align="left">
							<input type="text" name="fm_domain" value="<?php echo esc_attr($fm_domain); ?>" style="width: 300px;" />
							</td>
						</tr>
						<tr>
						<td width="50" align="left">&nbsp;</td>
						<td width="30" align="left"><input type="submit" class="button-primary" value="<?php echo esc_attr( __( 'Submit', 'safeguard-media') ); ?>"/></td>
						</tr>
					</tbody>
				</table>
			</form>
		</div>
		<!--end sing up form-->
		<hr>
		<?php } ?>
		
		<?php
		if(!($av_apikey && $validapikey))
		{
		?>
		<form action="" method="post">
			<?php echo wp_kses(wp_nonce_field('wpsafeguard_nonce_getclient'), WPSM_kses_allowed_options()); ?>
			<input type="hidden" name="action_three" value="getclient" >
			<table cellpadding='1' cellspacing='0' border='0' class='wpsafeguard_page_setting'>
				<p><strong><?php echo esc_html( __( 'Default settings applied to all media:', 'safeguard-media' )); ?></strong></p>
				<tbody>
					<tr>
						<td align='left' width='50'>&nbsp;</td>
						<td width="30" align="left"><?php echo wp_kses_post(WPSM_help_icon(__( 'SafeGuard API Key', 'safeguard-media' ))); ?></td>
						<td align="left"><?php echo esc_html( __( 'SafeGuard API Key:', 'safeguard-media' )); ?></td>
						<td align="left">
							<input type="text" name="wpsm_av_apikey" value="<?php echo esc_attr($av_apikey); ?>" size="45">
						</td>
					</tr>
				</tbody>
			</table>
			<p class="submit">
				<input type="submit" value="<?php echo esc_attr( __( 'Save Settings', 'safeguard-media' )); ?>"
						class="button-primary" id="submit" name="submit">
			</p>
			
		</form>
		<?php 
		}
		
		if($av_apikey && $validapikey)
		{
		?>
		<form action="" method="post">
			<?php echo wp_kses(wp_nonce_field('wpsafeguard_nonce_save_settings'), WPSM_kses_allowed_options()); ?>
			<input type="hidden" name="wpsafeguard_save_settings" value="1" />
			
			<table cellpadding='1' cellspacing='0' border='0' class='wpsafeguard_page_setting'>
				<p><strong><?php echo esc_html( __( 'Default settings applied to all media:', 'safeguard-media' )); ?></strong></p>
				<tbody>
				<tr>
					<td width="50" align="left">&nbsp;</td>
					<td width="30" align="left"><?php echo wp_kses_post(WPSM_help_icon(__( 'Domain', 'safeguard-media' ))); ?></td>
					<td align="left"><?php echo esc_html( __( 'Domain', 'safeguard-media') ); ?></td>
					<td align="left">
						<?php echo esc_html($fm_domain); ?>

						<a href="https://safeguard.media/asps/login.asp" target="_blank" style="display:block;float:right;">change</a>
					</td>
				</tr>
				<tr>
					<td align='left' width='50'>&nbsp;</td>
					<td width="30" align="left"><?php echo wp_kses_post(WPSM_help_icon(__( 'SafeGuard API Key', 'safeguard-media' ))); ?></td>
					<td align="left"><?php echo esc_html( __( 'SafeGuard API Key:', 'safeguard-media' )); ?></td>
					<td align="left">
						<input type="text" name="wpsm_av_apikey" value="<?php echo esc_attr($av_apikey); ?>" size="45">
					</td>
				</tr>
				<tr>
					<td align='left' width='50'>&nbsp;</td>
					<td width="30" align="left"><?php echo wp_kses_post(WPSM_help_icon(__( 'Set the mode to use. Use Active if you have a SafeGuard Media account. Otherise set for Debug mode.', 'safeguard-media' ))); ?></td>
					<td align="left"><?php echo esc_html( __( 'Mode', 'safeguard-media' )); ?></td>
					<td align="left">
						<select name="mode">
							<option value="licensed" <?php if($mode=='licensed') echo 'selected'; ?>><?php echo esc_html( __( 'Active', 'safeguard-media' )); ?></option>
							<option value="debug" <?php if($mode=='debug') echo 'selected'; ?>><?php echo esc_html( __( 'Debugging Mode', 'safeguard-media' )); ?></option>
						
						</select>
					</td>
				</tr>
				<tr class="safeguard-media-attributes">
					<td colspan="5"></td>
				</tr>
				<tr>
					<td align='left' width='50'>&nbsp;</td>
					<td width="30" align="left"><?php echo wp_kses_post(WPSM_help_icon(__( 'Set width of the media viewer.', 'safeguard-media' ))); ?></td>
					<td align="left"><?php echo esc_html( __( 'Width - in pixels:', 'safeguard-media' )); ?></td>
					<td align="left"><input value="<?php echo esc_attr($width); ?>"
											name="width" type="text"
											size="8"></td>
				</tr>
				<tr>
					<td align='left' width='50'>&nbsp;</td>
					<td width="30" align="left"><?php echo wp_kses_post(WPSM_help_icon(__( 'Set height of the media viewer.', 'safeguard-media' ))); ?></td>
					<td align="left"><?php echo esc_html( __( 'Height - in pixels:', 'safeguard-media' )); ?></td>
					<td align="left"><input value="<?php echo esc_attr($height); ?>"
											name="height" type="text"
											size="8"></td>
				</tr>
			</table>

				<?php
				global $current_user;

					$currentuser            = wp_get_current_user();
					$av_watermark_userid    = $currentuser->ID;
					$av_watermark_user_name = $currentuser->user_login;
					$av_watermark_useremail = $currentuser->user_email;
					$av_watermark_date      = gmdate('Y-m-d');

					$av_watermark_onoff       = get_option('wpsm_av_watermark_onoff');
					$av_watermarktextcolor    = get_option('wpsm_av_watermarktextcolor');
					$av_watermarkshadecolor_1 = get_option('wpsm_av_watermarkshadecolor_1');
					$av_watermarkshadecolor_2 = get_option('wpsm_av_watermarkshadecolor_2');
					$av_watermarkshadecolor_3 = get_option('wpsm_av_watermarkshadecolor_3');
					$av_watermarkshadecolor_4 = get_option('wpsm_av_watermarkshadecolor_4');
					$av_watermarkposition     = get_option('wpsm_av_watermarkposition');
					$av_watermarkfontsize     = get_option('wpsm_av_watermarkfontsize');
					$av_watermarkfontstyle    = get_option('wpsm_av_watermarkfontstyle');
					$av_apikey                = get_option('wpsm_av_apikey');
					$av_watermarkopacity      = get_option('wpsm_av_watermarkopacity');
					$av_allowwindows          = get_option('wpsm_av_allowwindows');
					$av_allowmac              = get_option('wpsm_av_allowmac');
					$av_allowandroid          = get_option('wpsm_av_allowandroid');
					$av_allowios              = get_option('wpsm_av_allowios');
					$av_allowremote           = get_option('wpsm_av_allowremote');
					$av_Version_windows       = get_option('wpsm_av_Version_windows');
					$av_Version_mac           = get_option('wpsm_av_Version_mac');
					$av_Version_android       = get_option('wpsm_av_Version_android');
					$av_Version_ios           = get_option('wpsm_av_Version_ios');
				?>
				<hr>
				<label ><b><?php echo esc_html( __( 'ArtisBrowser Versions to Allow', 'safeguard-media' )); ?>:</b></label>
				<table border="0" cellspacing="0" cellpadding="1" width="500" class="wpsafeguard_page_setting">
					<tr>
						<td align='left' width='50'>&nbsp;</td>
						<td><?php echo wp_kses_post(WPSM_help_icon(__( 'To allow access to Windows computers or not.', 'safeguard-media' ))); ?></td>
						<td><?php esc_html_e( 'Allow Windows:&nbsp;', 'safeguard-media' ); ?></td>
						<td><input type="checkbox" name="wpsm_av_allowwindows"  value="1" <?php if($av_allowwindows=="1"){echo "checked";}?> ></td>
						<td><?php 
							if(empty($av_Version_windows)){$av_Version_windows = "34.11";}?>
							<input type="text" name="wpsm_av_Version_windows" value="<?php echo $av_Version_windows ? esc_attr($av_Version_windows) : ''; ?>" style="width:80px;"/>
						</td>
						<td><?php echo esc_html( __( 'Min.Version', 'safeguard-media' )); ?></td>
					</tr>
					<tr>
						<td align='left' width='50'>&nbsp;</td>
						<td><?php echo wp_kses_post(WPSM_help_icon(__( 'To allow access to Mac OSX computers or not.', 'safeguard-media' ))); ?></td>
						<td><?php esc_html_e( 'Allow Mac OSX:&nbsp;', 'safeguard-media' ); ?></td>
						<td><input type="checkbox" name="wpsm_av_allowmac"  value="1" <?php if($av_allowmac=="1"){echo "checked";}?> ></td>
						<td><?php 
							if(empty($av_Version_mac)){$av_Version_mac = "32.1";}?>
							<input type="text" name="wpsm_av_Version_mac" value="<?php echo $av_Version_mac ? esc_attr($av_Version_mac) : ''; ?>" style="width:80px;"/>
						</td><td><?php echo esc_html( __( 'Min.Version', 'safeguard-media' )); ?></td>
					</tr>
					<tr>
						<td align='left' width='50'>&nbsp;</td>
						<td><?php echo wp_kses_post(WPSM_help_icon(__( 'To allow access to Android devices or not.', 'safeguard-media' ))); ?></td>
						<td><?php esc_attr_e( 'Allow Android:', 'safeguard-media' ); ?></td>
						<td><input type="checkbox" name="wpsm_av_allowandroid"  value="1" <?php if($av_allowandroid=="1"){echo "checked";}?> ></td>
						<td><?php 
							if(empty($av_Version_android)){$av_Version_android = "34.0";}?>
							<input type="text" name="wpsm_av_Version_android" value="<?php echo $av_Version_android ? esc_attr($av_Version_android) : ''; ?>" style="width:80px;"/>
						</td>
						<td><?php echo esc_html( __( 'Min.Version', 'safeguard-media' )); ?></td>
					</tr>
					<tr>
						<td align='left' width='50'>&nbsp;</td>
						<td><?php echo wp_kses_post(WPSM_help_icon(__( 'To allow access to iOS (iPad/iPhone) devices or not.', 'safeguard-media' ))); ?></td>
						<td><?php esc_html_e( 'Allow IOS:', 'safeguard-media' ); ?></td>
						<td><input type="checkbox" name="wpsm_av_allowios"  value="1" <?php if($av_allowios=="1"){echo "checked";}?> ></td>
						<td><?php 
							if(empty($av_Version_ios)){$av_Version_ios = "34.0";}?>
							<input type="text" name="wpsm_av_Version_ios" value="<?php echo $av_Version_ios ? esc_attr($av_Version_ios) : ''; ?>" style="width:80px;"/>
						</td>
						<td><?php echo esc_html( __( 'Min.Version', 'safeguard-media' )); ?></td>
					</tr>
				</table>
					
				<hr>
				<table cellpadding='1' cellspacing='0' border='0' class='wpsafeguard_page_setting'>
				<tr>
					<td align='left' width='50'>&nbsp;</td>
					<td align="left" width="30"><?php echo wp_kses_post(WPSM_help_icon(__( 'To allow acess to Remote/virtual devices or not.', 'safeguard-media' ))); ?></td>
					<td align="left"><?php echo esc_html( __( 'Allow Remote:', 'safeguard-media' )); ?></td>
					<td align="left">
						<select name="wpsm_av_allowremote" >
						<option value="1" <?php if($av_allowremote=="1"){echo "selected";}?>><?php echo esc_html( __( 'Yes', 'safeguard-media' )); ?></option>
						<option value="0" <?php if($av_allowremote=="0"){echo "selected";}?>><?php echo esc_html( __( 'No', 'safeguard-media' )); ?></option>
						</select>
					</td>	
				</tr>
				<tr style="display:none;">
					<td align='left' width='50'>&nbsp;</td>
					<td align="left" width="30"><?php echo wp_kses_post(WPSM_help_icon(__( 'ArtisBrowser min. version required.', 'safeguard-media' ))); ?></td>
					<td align="left"><?php echo esc_html( __( 'Min.Version Default:', 'safeguard-media' )); ?></td>
					<td align="left"> <?php $av_Version=(isset($av_Version))?$av_Version:'32.0'; ?>
					<input type="text" value="<?php echo esc_attr($av_Version);?>" name="wpsm_av_Version" placeholder="32.0" size="8">
					</td>
				</tr>
				
				<tr class="artistscope-media-browsers">
					<td colspan="5"><h2 class="title"><?php echo esc_html( __( 'Watermark Settings', 'safeguard-media' )); ?></h2></td>
				</tr>
				
				<tr>
					<td align='left' width='50'>&nbsp;</td>
					<td align="left" width="30"><?php echo wp_kses_post(WPSM_help_icon(__( 'To show a Watermark or not.', 'safeguard-media' ))); ?></td>
					<td align="left" nowrap><?php echo esc_html( __( 'Watermark On/Off:', 'safeguard-media' )); ?></td>
					<td align="left">
						<input name="wpsm_av_watermark_onoff" type="checkbox" <?php echo ($av_watermark_onoff== 'checked')?'checked':''; ?> value="1">
					</td>
				</tr>

				<tr>
					<td align='left' width='50'>&nbsp;</td>
					<td align="left" width="30"><?php echo wp_kses_post(WPSM_help_icon(__( 'Info for watermark message: Current user ID, Name, Email, Date.', 'safeguard-media' ))); ?></td>
					<td align="left" nowrap><?php echo esc_html( __( 'Watermark Text:', 'safeguard-media' )); ?></td>
					<td align="left">
						<input name="wpsm_av_watermark_userid" type="text" value="<?php echo esc_attr($av_watermark_userid); ?>" size="6" readonly>
						<input name="wpsm_av_watermark_user_name" type="text" value="<?php echo esc_attr($av_watermark_user_name); ?>"  size="20" readonly>
						<input name="wpsm_av_watermark_useremail" type="text" value="<?php echo esc_attr($av_watermark_useremail); ?>"  size="30" readonly>
						<input name="wpsm_av_watermark_date" type="text" value="<?php echo esc_attr($av_watermark_date); ?>"  size="12" readonly>
					</td>
				</tr>
				<tr>
					<td align='left' widt8h='50'>&nbsp;</td>
					<td align="left" width="30"><?php echo wp_kses_post(WPSM_help_icon(__( 'CSS code for Text color for watermark.', 'safeguard-media' ))); ?></td>
					<td align="left"><?php echo esc_html( __( 'Text Color:', 'safeguard-media' )); ?></td>
					<td align="left">
						<select name="wpsm_av_watermarktextcolor" >
						<option value="#999999" <?php if($av_watermarktextcolor=="999999"){echo "selected";}?>><?php echo esc_html( __( 'Grey', 'safeguard-media' )); ?></option>
						<option value="#FFFFFF" <?php if($av_watermarktextcolor=="FFFFFF"){echo "selected";}?>><?php echo esc_html( __( 'White', 'safeguard-media' )); ?></option>
						<option value="#000000" <?php if($av_watermarktextcolor=="000000"){echo "selected";}?>><?php echo esc_html( __( 'Black', 'safeguard-media' )); ?></option>
						<option value="#FF3333" <?php if($av_watermarktextcolor=="FF3333"){echo "selected";}?>><?php echo esc_html( __( 'Red', 'safeguard-media' )); ?></option>
						<option value="#FFFF00" <?php if($av_watermarktextcolor=="FFFF00"){echo "selected";}?>><?php echo esc_html( __( 'Yellow', 'safeguard-media' )); ?></option>
						<option value="#00FF00" <?php if($av_watermarktextcolor=="00FF00"){echo "selected";}?>><?php echo esc_html( __( 'Green', 'safeguard-media' )); ?></option>
						<option value="#00FFCC" <?php if($av_watermarktextcolor=="00FFCC"){echo "selected";}?>><?php echo esc_html( __( 'Blue', 'safeguard-media' )); ?></option>
						</select>
					</td>
				</tr>

				<tr>
					<td align='left' width='50'>&nbsp;</td>
					<td align="left" width="30"><?php echo wp_kses_post(WPSM_help_icon(__( 'CSS code for Shade color for watermark.', 'safeguard-media' ))); ?></td>
					<td align="left"><?php echo esc_html( __( 'Shade Color:', 'safeguard-media' )); ?></td>
					<td align="left">
					
						<select name="wpsm_av_watermarkshadecolor_4" >
						<option value="#ffffff00" <?php if($av_watermarkshadecolor_4=="ffffff00"){echo "selected";}?>><?php echo esc_html( __( 'Transparent', 'safeguard-media' )); ?></option>
						<option value="#999999" <?php if($av_watermarkshadecolor_4=="999999"){echo "selected";}?>><?php echo esc_html( __( 'Grey', 'safeguard-media' )); ?></option>
						<option value="#FFFFFF" <?php if($av_watermarkshadecolor_4=="FFFFFF"){echo "selected";}?>><?php echo esc_html( __( 'White', 'safeguard-media' )); ?></option>
						<option value="#000000" <?php if($av_watermarkshadecolor_4=="000000"){echo "selected";}?>><?php echo esc_html( __( 'Black', 'safeguard-media' )); ?></option>
						<option value="#FF3333" <?php if($av_watermarkshadecolor_4=="FF3333"){echo "selected";}?>><?php echo esc_html( __( 'Red', 'safeguard-media' )); ?></option>
						<option value="#FFFF00" <?php if($av_watermarkshadecolor_4=="FFFF00"){echo "selected";}?>><?php echo esc_html( __( 'Yellow', 'safeguard-media' )); ?></option>
						<option value="#00FFCC" <?php if($av_watermarkshadecolor_4=="00FFCC"){echo "selected";}?>><?php echo esc_html( __( 'Blue', 'safeguard-media' )); ?></option>
						<option value="#00FF00" <?php if($av_watermarkshadecolor_4=="00FF00"){echo "selected";}?>><?php echo esc_html( __( 'Green', 'safeguard-media' )); ?></option>
						</select>
					</td>
				</tr>

				<tr>
					<td align='left' width='50'>&nbsp;</td>
					<td align="left" width="30"><?php echo wp_kses_post(WPSM_help_icon(__( 'Position for watermark.', 'safeguard-media' ))); ?></td>
					<td align="left"><?php echo esc_html( __( 'Position:', 'safeguard-media' )); ?></td>
					<td align="left">
						<select name="wpsm_av_watermarkposition" >
							<option value='1' <?php if($av_watermarkposition=="1"){echo "selected";}?>><?php echo esc_html( __( 'Top left', 'safeguard-media' )); ?></option>
							<option value='2' <?php if($av_watermarkposition=="2"){echo "selected";}?>><?php echo esc_html( __( 'Bottom Left', 'safeguard-media' )); ?></option>
							<option value='3' <?php if($av_watermarkposition=="3"){echo "selected";}?>><?php echo esc_html( __( 'Top Right', 'safeguard-media' )); ?></option>
							<option value='4' <?php if($av_watermarkposition=="4"){echo "selected";}?>><?php echo esc_html( __( 'Bottom Right', 'safeguard-media' )); ?></option>
							<option value='5' <?php if($av_watermarkposition=="5"){echo "selected";}?>><?php echo esc_html( __( 'Center', 'safeguard-media' )); ?></option>
							<option value='6' <?php if($av_watermarkposition=="6"){echo "selected";}?>><?php echo esc_html( __( 'Random', 'safeguard-media' )); ?></option>
							<option value='7' <?php if($av_watermarkposition=="7"){echo "selected";}?>><?php echo esc_html( __( 'Rotating', 'safeguard-media' )); ?></option>
							<option value='8' <?php if($av_watermarkposition=="8"){echo "selected";}?>><?php echo esc_html( __( 'Diagonal', 'safeguard-media' )); ?></option>
						</select>
					</td>
				</tr>

				<tr>
					<td align='left' width='50'>&nbsp;</td>
					<td align="left" width="30"><?php echo wp_kses_post(WPSM_help_icon(__( 'Font Size for watermark.', 'safeguard-media' ))); ?></td>
					<td align="left"><?php echo esc_html( __( 'Font Size:', 'safeguard-media' )); ?></td>
					<td align="left">
						
						<select name="wpsm_av_watermarkfontsize" >  <?php echo esc_html( __( '(in pixel)', 'safeguard-media' )); ?>
							<option value="10" <?php if($av_watermarkfontsize=="10"){echo "selected";}?>><?php echo esc_html( __( '10', 'safeguard-media' )); ?></option>
							<option value="20" <?php if($av_watermarkfontsize=="20"){echo "selected";}?>><?php echo esc_html( __( '20', 'safeguard-media' )); ?></option>
							<option value="30" <?php if($av_watermarkfontsize=="30"){echo "selected";}?>><?php echo esc_html( __( '30', 'safeguard-media' )); ?></option>
							<option value="40" <?php if($av_watermarkfontsize=="40"){echo "selected";}?>><?php echo esc_html( __( '40', 'safeguard-media' )); ?></option>
							<option value="50" <?php if($av_watermarkfontsize=="50"){echo "selected";}?>><?php echo esc_html( __( '50', 'safeguard-media' )); ?></option>
							<option value="60" <?php if($av_watermarkfontsize=="60"){echo "selected";}?>><?php echo esc_html( __( '60', 'safeguard-media' )); ?></option>
							<option value="70" <?php if($av_watermarkfontsize=="70"){echo "selected";}?>><?php echo esc_html( __( '70', 'safeguard-media' )); ?></option>
							<option value="80" <?php if($av_watermarkfontsize=="80"){echo "selected";}?>><?php echo esc_html( __( '80', 'safeguard-media' )); ?></option>
							<option value="90" <?php if($av_watermarkfontsize=="90"){echo "selected";}?>><?php echo esc_html( __( '90', 'safeguard-media' )); ?></option>
							<option value="100" <?php if($av_watermarkfontsize=="100"){echo "selected";}?>><?php echo esc_html( __( '100', 'safeguard-media' )); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<td align='left' width='50'>&nbsp;</td>
					<td align="left" width="30"><?php echo wp_kses_post(WPSM_help_icon(__( 'CSS code for opacity of watermark.', 'safeguard-media' ))); ?></td>
					<td align="left"><?php echo esc_html( __( 'Opacity:', 'safeguard-media' )); ?></td>
					<td align="left">
						<select name="wpsm_av_watermarkopacity" >
						<option value="1" <?php if($av_watermarkopacity=="1"){echo "selected";}?>><?php echo esc_html( __( '100%', 'safeguard-media' )); ?></option>
						<option value="0.9" <?php if($av_watermarkopacity=="0.9"){echo "selected";}?>><?php echo esc_html( __( '90%', 'safeguard-media' )); ?></option>
						<option value="0.8" <?php if($av_watermarkopacity=="0.8"){echo "selected";}?>><?php echo esc_html( __( '80%', 'safeguard-media' )); ?></option>
						<option value="0.7" <?php if($av_watermarkopacity=="0.7"){echo "selected";}?>><?php echo esc_html( __( '70%', 'safeguard-media' )); ?></option>
						<option value="0.6" <?php if($av_watermarkopacity=="0.6"){echo "selected";}?>><?php echo esc_html( __( '60%', 'safeguard-media' )); ?></option>
						<option value="0.5" <?php if($av_watermarkopacity=="0.5"){echo "selected";}?>><?php echo esc_html( __( '50%', 'safeguard-media' )); ?></option>
						<option value="0.4" <?php if($av_watermarkopacity=="0.4"){echo "selected";}?>><?php echo esc_html( __( '40%', 'safeguard-media' )); ?></option>
						<option value="0.3" <?php if($av_watermarkopacity=="0.3"){echo "selected";}?>><?php echo esc_html( __( '30%', 'safeguard-media' )); ?></option>
						<option value="0.2" <?php if($av_watermarkopacity=="0.2"){echo "selected";}?>><?php echo esc_html( __( '20%', 'safeguard-media' )); ?></option>
						<option value="0.1" <?php if($av_watermarkopacity=="0.1"){echo "selected";}?>><?php echo esc_html( __( '10%', 'safeguard-media' )); ?></option>
						</select>
					</td>	
				</tr>
				<tr>
					<td align='left' colspan="4">
						<p class="submit">
							<input type="submit" value="<?php echo esc_attr( __( 'Save Settings', 'safeguard-media' )); ?>" class="button-primary" id="submit" name="submit">
						</p>
					</td>
				</tr>
				<?php } ?>
				</tbody>
			</table>
		</form>
		
		<div class="clear"></div>
	</div>
	<div class="clear"></div>
	<script type='text/javascript'>
		jQuery(document).ready(function ($) {
			$(".wpsafeguard_page_setting img").click(function () {
				alert($(this).attr("alt"));
			});
		});
	</script>
	<?php
}
<?php if (!defined('ABSPATH')) exit;

function WPSM_admin_enqueue_scripts()
{
	$screen = get_current_screen();

	if( ! empty($screen->id) && $screen->id == 'safeguard-media_page_wpsafeguard_settings')
	{
		wp_enqueue_style('wpsm-admin', WPSAFEGUARD_PLUGIN_URL . 'css/wpsm-admin.css', [], WPSAFEGUARD_ASSET_VERSION);
	}
}
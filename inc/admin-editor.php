<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

add_action('admin_enqueue_scripts', 'WPSM_admin_popup_editor_scripts');
add_action('elementor/editor/before_enqueue_scripts', 'WPSM_admin_popup_editor_scripts');

function WPSM_admin_popup_editor_scripts()
{
	$screen = get_current_screen();
	
	if( ! empty($screen->post_type))
	{
		global $post;

		wp_register_script('wpsm-safeguard-uploader', WPSAFEGUARD_PLUGIN_URL . 'js/safeguard_media_uploader.js', [
			'jquery',
			'plupload-all',
		],
		WPSAFEGUARD_ASSET_VERSION,
		['in_footer' => true]
		);

		wp_enqueue_script("jquery-ui-tabs");
		wp_enqueue_script("jquery-ui-dialog");
		wp_enqueue_script('wpsm-safeguard-uploader', WPSAFEGUARD_PLUGIN_URL . 'js/safeguard_media_uploader.js', [], WPSAFEGUARD_ASSET_VERSION, ['in_footer' => true]);

		wp_localize_script('wpsm-safeguard-uploader', 'wpsm_safeguard_uploader_data', [
			'nonce' => wp_create_nonce('wpsafeguard_nonce'),
			'ID' => empty($post->ID) ? 0 : $post->ID,
		]);

		wp_enqueue_style('wp-safeguard-jquery-ui', WPSAFEGUARD_PLUGIN_URL . 'css/jquery-ui.min.css', [], WPSAFEGUARD_ASSET_VERSION);
		wp_enqueue_style('wp-safeguard-jquery-ui-theme', WPSAFEGUARD_PLUGIN_URL . 'css/theme.min.css', [], WPSAFEGUARD_ASSET_VERSION);
	}
}

add_action('admin_footer', 'WPSM_admin_popup_editor_html');
add_action('elementor/editor/footer', 'WPSM_admin_popup_editor_html');

function WPSM_admin_popup_editor_html()
{
	$screen = get_current_screen();

	if( ! empty($screen->post_type))
	{
		require_once WPSAFEGUARD_PLUGIN_PATH . 'inc/template-media-upload.php';
	}
}
<?php defined('ABSPATH') or exit;

class WPSM_Elementor {

	public function __construct()
	{
		add_action('elementor/widgets/register', [$this, 'register_widget']);
		add_action('elementor/editor/before_enqueue_styles', [$this, 'register_style']);
	}

	public function register_widget($widgets_manager)
	{
		require_once __DIR__ . '/widget-safeguardmedia.php';

		$widgets_manager->register(new \WPSM_Elementor_Widget());
	}

	public function register_style()
	{
		wp_register_script('wpsm-elementor-editor', WPSAFEGUARD_PLUGIN_URL . 'inc/elementor/assets/js/editor.js', ['wpsm-safeguard-uploader'], WPSAFEGUARD_VERSION, true);
		wp_enqueue_script('wpsm-elementor-editor');

		wp_register_style('wpsm-elementor-editor', WPSAFEGUARD_PLUGIN_URL . 'inc/elementor/assets/css/editor.css', [], WPSAFEGUARD_ASSET_VERSION);
		wp_enqueue_style('wpsm-elementor-editor');
	}
}
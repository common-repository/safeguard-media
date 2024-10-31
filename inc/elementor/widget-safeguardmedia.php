<?php defined('ABSPATH') or exit;

class WPSM_Elementor_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'wpsm_widget';
	}

	public function get_title() {
		return esc_html__('Safeguard Media', 'safeguard-media');
	}

	public function get_icon() {
		return 'eicon-code';
	}

	public function get_categories() {
		return ['basic'];
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$name   = empty($settings['wpsm_name']) ? '' : $settings['wpsm_name'];
		$width  = empty($settings['wpsm_width']) ? '' : $settings['wpsm_width'];
		$height = empty($settings['wpsm_height']) ? '' : $settings['wpsm_height'];
		$remote = empty($settings['wpsm_remote']) ? '' : $settings['wpsm_remote'];

		if(\Elementor\Plugin::$instance->editor->is_edit_mode())
		{
			?>
		<p><strong><?php echo esc_html__('Safeguard Media', 'safeguard-media'); ?></strong></p>
		<p>
		<?php echo esc_html('Name:', 'safeguard-media'); ?> <span><?php echo esc_html($name); ?></span><br />
		<?php echo esc_html('Width:', 'safeguard-media'); ?> <span><?php echo esc_html($width); ?></span><br />
		<?php echo esc_html('Height:', 'safeguard-media'); ?> <span><?php echo esc_html($height); ?></span><br />
		<?php echo esc_html('Remote:', 'safeguard-media'); ?> <span><?php echo esc_html($remote); ?></span></p>
			<?php
		}
		else
		{
			if( ! empty($name))
			{
			?>
			[safeguard name="<?php echo esc_attr($name); ?>" width="<?php echo esc_attr($width); ?>" height="<?php echo esc_attr($height); ?>" remote="<?php echo esc_attr($remote); ?>"]
			<?php
			}
		}
	}

	protected function content_template()
	{
		?>
		<p><strong><?php echo esc_html__('Safeguard Media', 'safeguard-media'); ?></strong></p>
		<p>
		<?php echo esc_html__('Name:', 'safeguard-media'); ?> <span>{{ settings.wpsm_name }}</span><br />
		<?php echo esc_html__('Width:', 'safeguard-media'); ?> <span>{{ settings.wpsm_width }}</span><br />
		<?php echo esc_html__('Height:', 'safeguard-media'); ?> <span>{{ settings.wpsm_height }}</span><br />
		<?php echo esc_html__('Remote:', 'safeguard-media'); ?> <span>{{ settings.wpsm_remote }}</span></p>
		<?php
	}

	protected function register_controls()
	{
		$this->start_controls_section(
			'selection_title',
			[
				'label' => esc_html__( 'Safeguard Media', 'safeguard-media' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'wpsm_action',
			[
				'label' => esc_html__('File selection', 'safeguard-media'),
				'text'  => esc_html__('S-Media', 'safeguard-media'),
				'type'  => \Elementor\Controls_Manager::BUTTON,
				'event' => 'safeguardmedia:editor:modal',
			]
		);

		$this->add_control(
			'wpsm_name',
			[
				'label'       => esc_html__('File ID', 'safeguard-media'),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => esc_html__('File ID', 'safeguard-media'),
				'ai'          => false,
			]
		);

		$this->add_control(
			'wpsm_width',
			[
				'label'       => esc_html__('Width', 'safeguard-media'),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => 'Auto',
				'ai'          => false,
			]
		);

		$this->add_control(
			'wpsm_height',
			[
				'label'       => esc_html__('Height', 'safeguard-media'),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => 'Auto',
				'ai'          => false,
			]
		);

		$this->add_control(
			'wpsm_remote',
			[
				'label'   => esc_html__('Allow remote', 'safeguard-media'),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 0,
				'options' => [
					'0' => 'No',
					'1' => 'Yes',
				],
			]
		);

		$this->end_controls_section();
	}
}
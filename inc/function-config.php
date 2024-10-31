<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

function WPSM_option_structure()
{
	$option_structure = [
		'wpsm_av_apikey'                => [
			'default' => '',
		],
		'wpsm_av_watermark_onoff'       => [
			'default' => '',
			'type'    => 'checked',
		],
		'wpsm_av_watermarktextcolor'    => [
			'default' => '',
			'type'    => 'hex_color',
		],
		'wpsm_av_watermarkshadecolor_1' => [
			'default' => '',
			'type'    => 'hex_color',
		],
		'wpsm_av_watermarkshadecolor_2' => [
			'default' => '',
			'type'    => 'hex_color',
		],
		'wpsm_av_watermarkshadecolor_3' => [
			'default' => '',
			'type'    => 'hex_color',
		],
		'wpsm_av_watermarkshadecolor_4' => [
			'default' => '',
			'type'    => 'hex_color',
		],
		'wpsm_av_watermarkposition'     => [
			'default' => ''
		],
		'wpsm_av_watermarkfontsize'     => [
			'default' => ''
		],
		'wpsm_av_watermarkopacity'      => [
			'default' => ''
		],
		'wpsm_av_allowwindows'          => [
			'default' => '0',
			'type'    => '1_0',
		],
		'wpsm_av_allowmac'              => [
			'default' => '0',
			'type'    => '1_0',
		],
		'wpsm_av_allowandroid'          => [
			'default' => '0',
			'type'    => '1_0',
		],
		'wpsm_av_allowios'              => [
			'default' => '0',
			'type'    => '1_0',
		],
		'wpsm_av_allowremote'           => [
			'default' => '0',
			'type'    => '1_0',
		],
		'wpsm_av_Version'               => [
			'default' => '1.0',
			'type'    => 'float',
		],
		'wpsm_av_Version_windows'       => [
			'default' => '34.11',
			'type'    => 'float',
		],
		'wpsm_av_Version_mac'           => [
			'default' => '32.1',
			'type'    => 'float',
		],
		'wpsm_av_Version_ios'           => [
			'default' => '33.0',
			'type'    => 'float',
		],
		'wpsm_av_Version_android'       => [
			'default' => '34.0',
			'type'    => 'float',
		],
		'wpsm_av_watermark_userid'      => [
			'default' => '',
		],
		'wpsm_av_watermark_user_name'   => [
			'default' => '',
		],
		'wpsm_av_watermark_useremail'   => [
			'default' => '',
		],
		'wpsm_av_watermark_date'        => [
			'default' => '',
		],
	];

	return $option_structure;
}

function WPSM_kses_allowed_options()
{
	$default = wp_kses_allowed_html('post');

	$default['input'] = [
		'type' => 1,
		'name' => 1,
		'value' => 1,
		'class' => 1,
		'id' => 1,
	];

	return $default;
}

function WPSM_allowed_file_types() {
	return ['jpg','jpeg','png','gif','mp4','pdf', 'doc', 'docx'];
}
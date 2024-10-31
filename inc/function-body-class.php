<?php if (!defined('ABSPATH')) exit;

function WPSM_amin_body_classes($classes)
{
	$screen = get_current_screen();

	if( ! empty($screen->id) && in_array($screen->id, ['safeguard-media_page_wpsafeguard_settings']))
	{
		$classes .= ' wpsm-admin';
	}

	return $classes;
}
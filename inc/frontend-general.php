<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

add_action('wp_footer', 'WPSM_cstmjsonfrontend');

function WPSM_cstmjsonfrontend()
{
	?>
	<style>
		#media-controls_outer{ display:none; }
		#media-container #media-controls_outer {
			display: none;
		}
		#media-container:hover #media-controls_outer {
			display: block;
		}
	</style>
	<?php
}
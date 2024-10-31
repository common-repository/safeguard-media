<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly

require_once __DIR__ . '/elementor/class-elementor.php';
require_once __DIR__ . '/gutenberg/class-gutenberg.php';

class WPSM {

	protected static $instance;

	public
		$frontend,
		$elementor,
		$gutenberg;

	protected function __construct()
	{
		self::$instance =& $this;

		$this->elementor = new WPSM_Elementor();
		$this->gutenberg = new WPSM_Gutenberg();
	}

	public static function &get_instance()
	{
		if (self::$instance == null)
		{
			self::$instance = new WPSM();
		}

		return self::$instance;
	}

	private function __clone() { }

	public function __wakeup() {
		throw new Exception("Cannot unserialize a singleton.");
	}
}

function &WPSM() {
	return WPSM::get_instance();
}

WPSM();
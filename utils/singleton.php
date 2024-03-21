<?php
/**
 * Singleton class.
 *
 * @package Utils/Singleton
 */

namespace Poptics\Utils;

defined( 'ABSPATH' ) || exit;

/**
 * Singleton trait
 * get instance
 *
 * @since 1.0.0
 */
trait Singleton {

	/**
	 * Current class instance.
	 *
	 * @var object
	 */
	private static $instance;

	/**
	 * Returns the singleton instance of the called class.
	 *
	 * @return object
	 */
	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new static();
		}
		return self::$instance;
	}
}

<?php

/**
 * Plugin Name:       Poptics
 * Plugin URI:        https://aethonic.com/poptics/
 * Description:       Most advanced pop-up builder plugin.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.3
 * Author:            Aethonic
 * Author URI:        https://aethonic.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       poptics
 * Domain Path:       /languages

 * Poptics is a free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.

 * Poptics essential is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with Poptics. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package Poptics
 * @category Core
 * @author Aethonic
 * @version 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * The Main Plugin Requirements Checker
 *
 * @since 1.0.0
 */
final class Poptics {

	/**
	 * Static Property To Hold Singleton Instance
	 *
	 * @var Poptics The Poptics Requirement Checker Instance
	 */
	private static $instance;

	/**
	 * Plugin Current Production Version
	 *
	 * @return string
	 */
	public static function get_version() {
		return '1.0.0';
	}

	/**
	 * Requirements Array
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private $requirements = array(
		'php' => array(
			'name'    => 'PHP',
			'minimum' => '7.3',
			'exists'  => true,
			'met'     => false,
			'checked' => false,
			'current' => false,
		),
		'wp'  => array(
			'name'    => 'WordPress',
			'minimum' => '5.2',
			'exists'  => true,
			'checked' => false,
			'met'     => false,
			'current' => false,
		),
	);

	/**
	 * Singleton Instance
	 *
	 * @since 1.0.0
	 *
	 * @return Poptics
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Setup Plugin Requirements
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function __construct() {
		// Always load translation.
		add_action( 'plugins_loaded', array( $this, 'load_text_domain' ) );

		// Initialize plugin functionalities or quit.
		$this->requirements_met() ? $this->initialize_modules() : $this->quit();
	}

	/**
	 * Load Localization Files
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function load_text_domain() {
		$locale = apply_filters( 'plugin_locale', get_user_locale(), 'poptics' );

		unload_textdomain( 'poptics' );
		load_textdomain( 'poptics', WP_LANG_DIR . '/poptics/poptics-' . $locale . '.mo' );
		load_plugin_textdomain( 'poptics', false, self::get_plugin_dir() . 'languages/' );
	}

	/**
	 * Initialize Plugin Modules.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function initialize_modules() {
		require_once __DIR__ . '/autoloader.php';

		// Include the bootstrap file if not loaded.
		if ( ! class_exists( 'Poptics\Bootstrap' ) ) {
			require_once self::get_plugin_dir() . 'bootstrap.php';
		}

		// Initialize the bootstrapper if exists.
		if ( class_exists( 'Poptics\Bootstrap' ) ) {

			// Initialize all modules through plugins_loaded.
			add_action( 'plugins_loaded', array( $this, 'init' ) );

			register_activation_hook( self::get_plugin_file(), array( $this, 'activate' ) );
			register_deactivation_hook( self::get_plugin_file(), array( $this, 'deactivate' ) );
		}
	}

	/**
	 * Check If All Requirements Are Fulfilled.
	 *
	 * @since 1.0.0
	 *
	 * @return boolean
	 */
	private function requirements_met() {
		$this->prepare_requirement_versions();

		$passed  = true;
		$to_meet = wp_list_pluck( $this->requirements, 'met' );

		foreach ( $to_meet as $met ) {
			if ( empty( $met ) ) {
				$passed = false;
				break;
			}
		}

		return $passed;
	}

	/**
	 * Requirement Version Prepare
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function prepare_requirement_versions() {
		foreach ( $this->requirements as $dependency => $config ) {
			switch ( $dependency ) {
				case 'php':
					$version = phpversion();
					break;
				case 'wp':
					$version = get_bloginfo( 'version' );
					break;
				default:
					$version = false;
			}

			if ( ! empty( $version ) ) {
				$this->requirements[ $dependency ]['current'] = $version;
				$this->requirements[ $dependency ]['checked'] = true;
				$this->requirements[ $dependency ]['met']     = version_compare( $version, $config['minimum'], '>=' );
			}
		}
	}

	/**
	 * Initialize everything.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function init() {
		Poptics\Bootstrap::instantiate( self::get_plugin_file() );
	}

	/**
	 * Called Only Once While Activation.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function activate() {
	}

	/**
	 * Called Only Once While Deactivation
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function deactivate() {
	}

	/**
	 * Quit Plugin Execution.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function quit() {
		add_action( 'admin_head', array( $this, 'show_plugin_requirements_not_met_notice' ) );
	}

	/**
	 * Show Error Notice For Missing Requirements.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function show_plugin_requirements_not_met_notice() {
		printf( '<div>Minimum requirements for Poptics are not met. Please update requirements to continue.</div>' );
	}

	/**
	 * Plugin Main File
	 *
	 * @return string
	 */
	public static function get_plugin_file() {
		return __FILE__;
	}

	/**
	 * Plugin Base Directory Path
	 *
	 * @return string
	 */
	public static function get_plugin_dir() {
		return trailingslashit( plugin_dir_path( self::get_plugin_file() ) );
	}
}

Poptics::get_instance();

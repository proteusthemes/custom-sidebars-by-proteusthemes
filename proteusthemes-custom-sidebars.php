<?php

/*
Plugin Name: ProteusThemes Custom Sidebars
Plugin URI:  https://www.proteusthemes.com/
Description: Allows you to create custom sidebars. Replace sidebars for specific posts, pages, archives,...
Version:     0.1
Author:      ProteusThemes
Author URI:  https://www.proteusthemes.com/
Textdomain:  pt-cs
License:     GPL3
License URI: http://www.gnu.org/licenses/gpl.html
*/

// Block direct access to the main plugin file.
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// Path/URL to root of this plugin, with trailing slash.
define( 'PT_CS_PATH', plugin_dir_path( __FILE__ ) );
define( 'PT_CS_URL', plugin_dir_url( __FILE__ ) );
define( 'PT_CS_VERSION', '0.1' );

/**
 * PT Custom Sidebars class, so we don't have to worry about namespaces.
 */
class PT_Custom_Sidebars {

	/**
	 * Reference to Singleton instance of this class.
	 *
	 * @var $instance the reference to *Singleton* instance of this class.
	 */
	private static $instance;


	/**
	 * Returns the *Singleton* instance of this class.
	 *
	 * @return PT_Custom_Sidebars the *Singleton* instance.
	 */
	public static function get_instance() {
		if ( null === static::$instance ) {
			static::$instance = new static();
		}

		return static::$instance;
	}


	/**
	 * Class construct function, to initiate the plugin.
	 * Protected constructor to prevent creating a new instance of the
	 * *Singleton* via the `new` operator from outside of this class.
	 */
	protected function __construct() {

		// Actions.
		add_action( 'plugins_loaded', array( $this, 'setup_custom_sidebars_plugin' ) );
	}

	/**
	 * Plugin setup function.
	 */
	public function setup_custom_sidebars_plugin() {

		// Used for more readable i18n functions: __( 'text', PT_CS_TD ).
		define( 'PT_CS_TD', 'pt-cs' );

		// Define some constants for easier use.
		define( 'PT_CS_VIEWS_DIR', PT_CS_PATH . 'views/' );

		// Load empty widget needed in this plugin.
		require_once PT_CS_PATH . 'inc/class-pt-cs-empty-widget.php';

		// Load the actual core of this plugin.
		require_once PT_CS_PATH . 'inc/class-pt-cs-main.php';

		// Load the text domain for the plugin.
		load_plugin_textdomain( PT_CS_TD, false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );

		// Initialize the plugin.
		PT_CS_Main::instance();
	}


	/**
	 * Private clone method to prevent cloning of the instance of the *Singleton* instance.
	 *
	 * @return void
	 */
	private function __clone() {}


	/**
	 * Private unserialize method to prevent unserializing of the *Singleton* instance.
	 *
	 * @return void
	 */
	private function __wakeup() {}
}

$pt_custom_sidebars = PT_Custom_Sidebars::get_instance();

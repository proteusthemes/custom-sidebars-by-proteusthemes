<?php

/*
Plugin Name: Custom Sidebars by ProteusThemes
Plugin URI:  https://www.proteusthemes.com/
Description: Allows you to create custom sidebars. Replace sidebars for specific posts and pages.
Version:     1.0.1
Author:      ProteusThemes
Author URI:  https://www.proteusthemes.com/
Textdomain:  pt-cs
License:     GPL3
License URI: http://www.gnu.org/licenses/gpl.html
*/

// Block direct access to the main plugin file.
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

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

		// Path/URL to root of this plugin, with trailing slash.
		define( 'PT_CS_PATH', plugin_dir_path( __FILE__ ) );
		define( 'PT_CS_URL', plugin_dir_url( __FILE__ ) );
		define( 'PT_CS_VERSION', '1.0.1' );

		// Define some constants for easier use.
		define( 'PT_CS_VIEWS_DIR', PT_CS_PATH . 'inc/views/' );

		// Load the actual core of this plugin.
		require_once PT_CS_PATH . 'inc/class-pt-cs-main.php';

		// Initialize the plugin.
		PT_CS_Main::get_instance();

		// Actions.
		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );
	}


	/**
	 * Load the text domain for the plugin.
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'pt-cs', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
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


/**
 * Check if the original Custom Sidebars plugin is active.
 * If it is, then display a notice, else instantiate our plugin.
 *
 * Have to make this check in plugins_loaded hook (to check if the plugin is active).
 */
function ptcs_plugin_loaded_hook() {
	if ( class_exists( 'CustomSidebars' ) ) {
		add_action( 'admin_notices', 'ptcs_old_original_cs_plugin_activated_notice' );
	}
	else {
		$pt_custom_sidebars = PT_Custom_Sidebars::get_instance();
	}
}
add_action( 'plugins_loaded', 'ptcs_plugin_loaded_hook' );


/**
 * Admin error notice, when the original Custom Sidebars plugin is active.
 * Hook it to the 'admin_notices' action.
 */
function ptcs_old_original_cs_plugin_activated_notice() {
	$message = sprintf( esc_html__( 'The %1$sCustom Sidebars by ProteusThemes%2$s plugin is not working, because the  %1$sCustom Sidebars%2$s plugin is active. These two plugins can not work together, because they have almost the same functionality.%3$sPlease deactivate the %1$sCustom Sidebars%2$s plugin in order to use the %1$sCustom Sidebars by ProteusThemes%2$s.', 'pt-cs' ), '<strong>', '</strong>', '<br>' );

	printf( '<div class="notice notice-error"><p>%1$s</p></div>', wp_kses_post( $message ) );
}

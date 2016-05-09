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

add_action(
	'plugins_loaded',
	'inc_sidebars_free_init'
);

function inc_sidebars_free_init() {
	// Check if the PRO plugin is present and activated.
	if ( class_exists( 'CustomSidebars' ) ) {
		return false;
	}

	// used for more readable i18n functions: __( 'text', CSB_LANG );
	define( 'CSB_LANG', 'custom-sidebars' );

	$plugin_dir = dirname( __FILE__ );
	$plugin_dir_rel = dirname( plugin_basename( __FILE__ ) );
	$plugin_url = plugin_dir_url( __FILE__ );

	define( 'CSB_PLUGIN', __FILE__ );
	define( 'CSB_LANG_DIR', $plugin_dir_rel . '/lang/' );
	define( 'CSB_VIEWS_DIR', $plugin_dir . '/views/' );
	define( 'CSB_INC_DIR', $plugin_dir . '/inc/' );
	define( 'CSB_JS_URL', $plugin_url . 'js/' );
	define( 'CSB_CSS_URL', $plugin_url . 'css/' );
	define( 'CSB_IMG_URL', $plugin_url . 'img/' );

	// Load the actual core.
	require_once CSB_INC_DIR . 'class-custom-sidebars.php';

	// Include function library
	if ( file_exists( CSB_INC_DIR . 'external/wpmu-lib/core.php' ) ) {
		require_once CSB_INC_DIR . 'external/wpmu-lib/core.php';
	}

	// Load the text domain for the plugin
	WDev()->translate_plugin( CSB_LANG, CSB_LANG_DIR );

	// Initialize the plugin
	CustomSidebars::instance();
}

if ( ! class_exists( 'CustomSidebarsEmptyPlugin' ) ) {
	class CustomSidebarsEmptyPlugin extends WP_Widget {
		public function CustomSidebarsEmptyPlugin() {
			parent::WP_Widget( false, $name = 'CustomSidebarsEmptyPlugin' );
		}
		public function form( $instance ) {
			//Nothing, just a dummy plugin to display nothing
		}
		public function update( $new_instance, $old_instance ) {
			//Nothing, just a dummy plugin to display nothing
		}
		public function widget( $args, $instance ) {
			echo '';
		}
	} //end class
} //end if class exists

<?php

// Require helpers file containing the helper functions.
// require_once dirname( __FILE__ ) . '/../inc/class-ocdi-helpers.php';

class PT_Custom_Sidebars_Test extends WP_UnitTestCase {

	/**
	 * Test the plguin constants. If they are defined.
	 */
	function test_plugin_constants() {
		$this->assertTrue( defined( 'PT_CS_PATH' ), 'Constant PT_CS_PATH is not defined!' );
		$this->assertTrue( defined( 'PT_CS_URL' ), 'Constant PT_CS_URL is not defined!' );
		$this->assertTrue( defined( 'PT_CS_VERSION' ), 'Constant PT_CS_VERSION is not defined!' );
		$this->assertTrue( defined( 'PT_CS_VIEWS_DIR' ), 'Constant PT_CS_VIEWS_DIR is not defined!' );
	}

	/**
	 * Test if the main plugin file exists.
	 */
	function test_main_plugin_file_exists() {
		$this->assertFileExists( PT_CS_PATH . 'inc/class-pt-cs-main.php', 'Main plugin file: inc/class-pt-cs-main.php is missing!' );
	}

	/**
	 * Test if the load_plugin_textdomain action is registered.
	 */
	function test_load_plugin_textdomain() {
		$instance = PT_Custom_Sidebars::get_instance();
		$this->assertEquals( 10, has_action( 'plugins_loaded', array( $instance, 'load_plugin_textdomain' ) ) );
	}
}

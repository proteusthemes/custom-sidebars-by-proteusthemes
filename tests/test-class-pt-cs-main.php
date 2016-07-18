<?php

class PT_CS_Main_Test extends WP_UnitTestCase {
	/**
	 * Test if plugin files required in this class exist.
	 */
	function test_plugin_files_exists() {
		$this->assertFileExists( PT_CS_PATH . 'inc/class-pt-cs-widgets.php', 'Widgets plugin file: inc/class-pt-cs-widgets.php is missing!' );
		$this->assertFileExists( PT_CS_PATH . 'inc/class-pt-cs-editor.php', 'Editor plugin file: inc/class-pt-cs-editor.php is missing!' );
		$this->assertFileExists( PT_CS_PATH . 'inc/class-pt-cs-replacer.php', 'Replacer plugin file: inc/class-pt-cs-replacer.php is missing!' );
	}

	/**
	 * Test if class has attributes.
	 */
	function test_class_attributes() {
		$instance = PT_CS_Main::get_instance();
		$this->assertObjectHasAttribute( 'instance', $instance );
		$this->assertObjectHasAttribute( 'sidebar_prefix', $instance );
		$this->assertObjectHasAttribute( 'cap_required', $instance );
	}

	/**
	 * Test if actions in class construct are registered. Accessibility not tested.
	 */
	function test_class_wp_actions() {
		$instance = PT_CS_Main::get_instance();
		$this->assertEquals( 10, has_action( 'admin_enqueue_scripts', array( $instance, 'load_plugin_admin_scripts' ) ) );
		$this->assertEquals( 10, has_action( 'wp_ajax_cs-ajax', array( $instance, 'ajax_handler' ) ) );
	}

	/**
	 * Test PT_CS_Main::get_array methods.
	 */
	function test_get_array() {
		$this->assertEquals( array(), PT_CS_Main::get_array( array() ) );
		$this->assertEquals( array(), PT_CS_Main::get_array( array(), array() ) );
		$this->assertEquals( array( '1' ), PT_CS_Main::get_array( array( '1' ), array() ) );
		$this->assertEquals( array( '1' ), PT_CS_Main::get_array( '1', array( '1' ) ) );
		$this->assertEquals( array(), PT_CS_Main::get_array( '1', '1' ) );
	}

	/**
	 * Test PT_CS_Main::set_options method.
	 */
	function test_set_options() {

		// By default all theme sidebars are "modifiable".
		$options = $this->get_all_theme_sidebars();

		$this->assertFalse( PT_CS_Main::set_options( $options ), 'set_options method should not work, without proper user role!' );

		// Create an admin user, to pass the current_user_can check in set_options.
		$this->create_and_set_admin_user();

		$this->assertTrue( PT_CS_Main::set_options( $options ), 'set_options method should work, with proper user role!' );
	}

	/**
	 * Test PT_CS_Main::get_options method.
	 */
	function test_get_options() {

		// By default all theme sidebars are "modifiable".
		$options = $this->get_all_theme_sidebars();

		$this->assertEquals( $options, PT_CS_Main::get_options(), 'get_options method does not return default sidebars!' );

		// Create an admin user, to pass the current_user_can check in set_options.
		$this->create_and_set_admin_user();

		$this->assertEquals( $options, PT_CS_Main::get_options(), 'get_options method does not return default sidebars (set_options was used)!' );

		$this->assertEquals( $options['modifiable'], PT_CS_Main::get_options( 'modifiable' ), 'get_options method does not return proper data, with specified key!' );

		// Remove the first sidebar from $options.
		unset( $options['modifiable'][0] );

		PT_CS_Main::set_options( $options );

		$this->assertEquals( $options, PT_CS_Main::get_options(), 'get_options method did not return the correct data!' );
	}

	/**
	 * Test PT_CS_Main::validate_options method.
	 */
	function test_validate_options() {
		$this->assertEmpty( PT_CS_Main::validate_options( '' ), 'Default should be an empty array!' );

		// By default all theme sidebars are "modifiable".
		$options = $this->get_all_theme_sidebars();

		$this->assertEquals( $options, PT_CS_Main::validate_options( $options ), 'All theme sidebars should be replaceable and valid by default!' );

		// Remove the first sidebar from $options.
		unset( $options['modifiable'][0] );

		$this->assertEquals( $options, PT_CS_Main::validate_options( $options ), 'Parameter has first default sidebar removed, others should still be valid!' );

		// Add an invalid sidebar and test if not equal.
		$options['modifiable'][] = 'invalid-sidebar';
		$this->assertNotEquals( $options, PT_CS_Main::validate_options( $options ), 'Parameter should have one invalid sidebar!' );
	}

	/**
	 * Test PT_CS_Main::get_custom_sidebars method.
	 *
	 * @dataProvider custom_siderbars_data_set
	 */
	function test_get_custom_sidebars( $custom_sidebars, $expected ) {
		$this->assertEquals( array(), PT_CS_Main::get_custom_sidebars() );

		update_option( 'pt_cs_sidebars', $custom_sidebars );

		$this->assertEquals( array( $custom_sidebars[0] ), PT_CS_Main::get_custom_sidebars() );
	}


	/**
	 * Test PT_CS_Main::set_custom_sidebars method.
	 *
	 * @dataProvider custom_siderbars_data_set
	 */
	function test_set_custom_sidebars( $custom_sidebars ) {

		$this->assertFalse( PT_CS_Main::set_custom_sidebars( $custom_sidebars ), 'set_custom_sidebars method should not work, without proper user role!' );

		// Create an admin user, to pass the current_user_can check in set_custom_sidebars.
		$this->create_and_set_admin_user();

		$this->assertTrue( PT_CS_Main::set_custom_sidebars( $custom_sidebars ), 'set_custom_sidebars method should work, with proper user role!' );
	}

	/**
	 * Test PT_CS_Main::get_sidebar_widgets method.
	 */
	function test_get_sidebar_widgets() {
		$this->assertArrayHasKey( 'wp_inactive_widgets', PT_CS_Main::get_sidebar_widgets(), 'wp_inactive_widgets array key should always be in the WP sidebar widgets array!' );

		$this->assertArrayHasKey( 'sidebar-1', PT_CS_Main::get_sidebar_widgets(), 'sidebar-1 array key should be in the WP sidebar widgets array by default!' );
	}


/************************************************************/
/************* Helper functions and data sets ***************/
/************************************************************/

	/**
	 * This is a dataProvider for testing custom sidebars "created" by the plugin.
	 */
	function custom_siderbars_data_set() {
		return array(
			array(
				array(
					array(
						'id' => 'pt-cs-1',
						'name' => 'Custom sidebar 1',
						'description' => 'Testing custom sidebar',
						'before_title' => '',
						'after_title' => '',
						'before_widget' => '',
						'after_widget' => '',
					),
					'invalid array item, that will be ignored',
				),
				array(
					array(
						'id' => 'pt-cs-1',
						'name' => 'Custom sidebar 1',
						'description' => 'Testing custom sidebar',
						'before_title' => '',
						'after_title' => '',
						'before_widget' => '',
						'after_widget' => '',
					),
				),
			),
		);
	}


	/**
	 * Helper function!
	 * Create a WP admin user and set it as current user.
	 */
	private function create_and_set_admin_user() {
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
	}

	/**
	 * Helper function!
	 * Return all theme sidebars.
	 * By default all theme sidebars are "modifiable".
	 */
	private function get_all_theme_sidebars() {
		$theme_sidebar_ids = array_keys( PT_CS_Main::get_sidebars( 'theme' ) );
		return array( 'modifiable' => $theme_sidebar_ids );
	}
}

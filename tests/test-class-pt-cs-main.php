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
		$theme_sidebar_ids = array_keys( PT_CS_Main::get_sidebars( 'theme' ) );
		$options = array( 'modifiable' => $theme_sidebar_ids );

		$this->assertFalse( PT_CS_Main::set_options( $options ), 'set_options method should not work, without proper user role!' );

		// Create an admin user, to pass the current_user_can check in set_options.
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		$this->assertTrue( PT_CS_Main::set_options( $options ), 'set_options method should work, with proper user role!' );
	}

	/**
	 * Test PT_CS_Main::get_options method.
	 */
	function test_get_options() {

		// By default all theme sidebars are "modifiable".
		$theme_sidebar_ids = array_keys( PT_CS_Main::get_sidebars( 'theme' ) );
		$options = array( 'modifiable' => $theme_sidebar_ids );

		$this->assertEquals( $options, PT_CS_Main::get_options(), 'get_options method does not return default sidebars!' );

		// Create an admin user, to pass the current_user_can check in set_options.
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

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
		$this->assertEmpty( PT_CS_Main::validate_options( '' ) );

		// By default all theme sidebars are "modifiable".
		$theme_sidebar_ids = array_keys( PT_CS_Main::get_sidebars( 'theme' ) );
		$options = array( 'modifiable' => $theme_sidebar_ids );

		$this->assertEquals( $options, PT_CS_Main::validate_options( $options ) );

		// Remove the first sidebar from $options.
		unset( $options['modifiable'][0] );

		$this->assertEquals( $options, PT_CS_Main::validate_options( $options ) );

		// Add an invalid sidebar and test if not equal.
		$options['modifiable'][] = 'invalid-sidebar';
		$this->assertNotEquals( $options, PT_CS_Main::validate_options( $options ) );
	}
}

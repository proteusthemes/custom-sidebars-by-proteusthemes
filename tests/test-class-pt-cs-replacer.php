<?php

class PT_CS_Replacer_Test extends WP_UnitTestCase {

	/**
	 * Test if class has attributes.
	 */
	function test_class_attributes() {
		$instance = PT_CS_Replacer::get_instance();
		$this->assertObjectHasAttribute( 'original_post_id', $instance );
	}

	/**
	 * Test if actions in class construct are registered.
	 */
	function test_class_wp_actions() {
		$instance = PT_CS_Replacer::get_instance();
		$this->assertEquals( 10, has_action( 'widgets_init', array( $instance, 'register_custom_sidebars' ) ) );
		$this->assertEquals( 10, has_action( 'wp_head', array( $instance, 'replace_sidebars' ) ) );
		$this->assertEquals( 10, has_action( 'wp', array( $instance, 'store_original_post_id' ) ) );
	}

	/**
	 * Test PT_CS_Replacer::register_custom_sidebars methods.
	 *
	 * @dataProvider PT_CS_Main_Test::custom_siderbars_data_set
	 */
	function test_register_custom_sidebars( $custom_sidebars ) {
		global $wp_registered_sidebars;
		$default_sidebars = $wp_registered_sidebars;

		$instance = PT_CS_Replacer::get_instance();

		$this->assertArrayNotHasKey( $custom_sidebars[0]['id'] , $wp_registered_sidebars, 'By default there are no custom sidebars registered!' );

		// Create an admin user, to pass the current_user_can check in set_custom_sidebars.
		$this->create_and_set_admin_user();

		// Set custom sidebars.
		PT_CS_Replacer::set_custom_sidebars( $custom_sidebars );

		// Register custom sidebars.
		$instance->register_custom_sidebars();

		$this->assertArrayHasKey( $custom_sidebars[0]['id'] , $wp_registered_sidebars, 'Custom sidebar should be registered!' );

		// Revert the global variable change.
		$wp_registered_sidebars = $default_sidebars;
	}

	/**
	 * Test PT_CS_Replacer::has_wrapper_code methods.
	 *
	 * @dataProvider PT_CS_Main_Test::custom_siderbars_data_set
	 */
	function test_has_wrapper_code( $custom_sidebars ) {
		$instance = PT_CS_Replacer::get_instance();

		$this->assertTrue( $instance->has_wrapper_code( $custom_sidebars[0] ), 'First sidebar test should have wrapper code!' );

		$this->assertFalse( $instance->has_wrapper_code( $custom_sidebars[1] ), 'Second sidebar test should not have wrapper code!' );
	}

	/**
	 * Test PT_CS_Replacer::is_valid_replacement methods.
	 *
	 * @dataProvider PT_CS_Main_Test::custom_siderbars_data_set
	 */
	function test_is_valid_replacement( $custom_sidebars, $expected ) {
		global $wp_registered_sidebars;
		$default_sidebars = $wp_registered_sidebars;

		$instance = PT_CS_Replacer::get_instance();

		// Get the sidebar replacement data.
		$replacements = $this->get_replacement_data();

		// Test without any custom sidebars being registered.
		$this->assertFalse( $instance->is_valid_replacement( $replacements[0][0], $replacements[0][1] ), 'By default no custom sidebars are registered, so the replacement with a custom sidebar is not valid!' );
		$this->assertFalse( $instance->is_valid_replacement( $replacements[1][0], $replacements[1][1] ), 'Replacement with a invalid sidebar id will always be invalid!' );

		// Create an admin user, to pass the current_user_can check in set_custom_sidebars.
		$this->create_and_set_admin_user();

		// Set custom sidebars.
		PT_CS_Replacer::set_custom_sidebars( $custom_sidebars );

		// Register custom sidebars.
		$instance->register_custom_sidebars();

		// Test again with custom sidebars registered!
		$this->assertTrue( $instance->is_valid_replacement( $replacements[0][0], $replacements[0][1] ), 'After custom sidebars have been registered, the replacement should be valid!' );

		// Revert the global variable change.
		$wp_registered_sidebars = $default_sidebars;
	}

	/**
	 * Test PT_CS_Replacer::determine_replacements methods.
	 *
	 * @dataProvider PT_CS_Main_Test::postmeta_data_set
	 */
	function test_determine_replacements( $post_meta ) {
		$instance = PT_CS_Replacer::get_instance();

		// Create a post.
		$post_id = $this->factory->post->create();

		// Set post meta to the created post.
		PT_CS_Replacer::set_post_meta( $post_id, $post_meta );

		// Go to the created post.
		$this->go_to( 'http://mysite.me/?p=' . $post_id );

		$defaults = PT_CS_Replacer::get_options();

		$this->assertArraySubset( $post_meta, $instance->determine_replacements( $defaults ), 'Post meta replacement should be a subset of the returned array!' );
	}


/************************************************************/
/************* Helper functions and dataProviders ***********/
/************************************************************/

	/**
	 * This is a dataProvider for testing postmeta for single posts "created" by the plugin.
	 */
	function sidebar_replacement_data_set() {
		return array(
			array(
				array(
					array( 'sidebar-1', 'pt-cs-1' ),
					array( 'sidebar-2', 'invalid-sidebar-id' ),
				)
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
	 * Return the sidebar_replacement_data_set data.
	 */
	private function get_replacement_data() {
		$replacement_data = $this->sidebar_replacement_data_set();
		return $replacement_data[0][0];
	}
}

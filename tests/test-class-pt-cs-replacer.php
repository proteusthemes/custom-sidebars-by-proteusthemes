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

		$instance = PT_CS_Replacer::get_instance();

		$this->assertArrayNotHasKey( $custom_sidebars[0]['id'] , $wp_registered_sidebars, 'By default there are no custom sidebars registered!' );

		// Create an admin user, to pass the current_user_can check in set_custom_sidebars.
		$this->create_and_set_admin_user();

		// Set custom sidebars.
		PT_CS_Replacer::set_custom_sidebars( $custom_sidebars );

		// Register custom sidebars.
		$instance->register_custom_sidebars();

		$this->assertArrayHasKey( $custom_sidebars[0]['id'] , $wp_registered_sidebars, 'Custom sidebar should be registered!' );
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
		$instance = PT_CS_Replacer::get_instance();

	}




/************************************************************/
/************* Helper functions *****************************/
/************************************************************/

	/**
	 * Helper function!
	 * Create a WP admin user and set it as current user.
	 */
	private function create_and_set_admin_user() {
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
	}
}

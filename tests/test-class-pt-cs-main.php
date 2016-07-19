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

		$this->assertEquals( $expected, PT_CS_Main::get_custom_sidebars() );
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

	/**
	 * Test PT_CS_Main::refresh_sidebar_widgets method.
	 *
	 * @dataProvider custom_siderbars_data_set
	 */
	function test_refresh_sidebar_widgets( $custom_sidebars ) {
		$this->assertFalse( PT_CS_Main::refresh_sidebar_widgets(), 'refresh_sidebar_widgets method should return false by default, because there was nothing to update!' );

		// Create an admin user, to pass the current_user_can check in set_custom_sidebars.
		$this->create_and_set_admin_user();

		PT_CS_Main::set_custom_sidebars( $custom_sidebars );

		$this->assertTrue( PT_CS_Main::refresh_sidebar_widgets(), 'refresh_sidebar_widgets method should return true, because there was an update for the sidebars_widgets wp option!' );

		$this->assertArrayHasKey( $custom_sidebars[0]['id'], PT_CS_Main::get_sidebar_widgets(), 'The id of the first custom sidebar, should be in the sidebars_widgets wp option!' );
	}

	/**
	 * Test PT_CS_Main::set_post_meta and PT_CS_Main::get_post_meta method.
	 *
	 * @dataProvider postmeta_data_set
	 */
	function test_set_and_get_post_meta( $post_meta ) {

		// Create a post.
		$post_id = $this->factory->post->create();

		// Set post meta
		PT_CS_Main::set_post_meta( $post_id, $post_meta );

		$this->assertEquals( $post_meta, PT_CS_Main::get_post_meta( $post_id ) );

		// Delete the post meta.
		PT_CS_Main::set_post_meta( $post_id, '' );

		$this->assertEmpty( PT_CS_Main::get_post_meta( $post_id ) );
	}

	/**
	 * Test PT_CS_Main::get_sidebars method.
	 *
	 * @dataProvider custom_siderbars_data_set
	 */
	function test_get_sidebars( $custom_sidebars, $expected ) {
		global $wp_registered_sidebars;
		$default_sidebars = $wp_registered_sidebars;

		// By default 'all' and 'theme' should be the same and there are no custom sidebars.
		$this->assertEquals( $wp_registered_sidebars, PT_CS_Main::get_sidebars( 'all' ), 'By default "all" parameter should return the same array as global $wp_registered_sidebars!' );
		$this->assertEquals( $wp_registered_sidebars, PT_CS_Main::get_sidebars( 'theme' ), 'By default "theme" parameter should return the same array as global $wp_registered_sidebars!' );
		$this->assertEmpty( PT_CS_Main::get_sidebars( 'cust' ), 'There should be no custom sidebars by default.' );

		// Modify global $wp_registered_sidebars. Add custom sidebar
		$wp_registered_sidebars = $this->modify_global_wp_registered_sidebars( $wp_registered_sidebars, $expected );

		$this->assertEquals( $wp_registered_sidebars, PT_CS_Main::get_sidebars( 'all' ) );
		$this->assertEquals( $default_sidebars, PT_CS_Main::get_sidebars( 'theme' ) );
		$this->assertEquals( array( $expected[0]['id'] => $expected[0] ), PT_CS_Main::get_sidebars( 'cust' ) );

		// Revert the global variable change.
		$wp_registered_sidebars = $default_sidebars;
	}

	/**
	 * Test PT_CS_Main::get_sidebar method.
	 *
	 * @dataProvider custom_siderbars_data_set
	 */
	function test_get_sidebar( $custom_sidebars, $expected ) {
		global $wp_registered_sidebars;
		$default_sidebars = $wp_registered_sidebars;

		// Modify global $wp_registered_sidebars. Add custom sidebar
		$wp_registered_sidebars = $this->modify_global_wp_registered_sidebars( $wp_registered_sidebars, $expected );

		$all_sidebars = PT_CS_Main::get_sidebars( 'all' );

		$this->assertFalse( PT_CS_Main::get_sidebar(''), 'Empty sidebar ID should return false!' );

		$this->assertEquals( $all_sidebars[ 'sidebar-1' ] , PT_CS_Main::get_sidebar( 'sidebar-1' ), 'Default sidebar, sidebar-1 should be returned!' );

		$this->assertFalse( PT_CS_Main::get_sidebar( 'this-sidebar-does-not-exist-id' ), 'An undefined sidebar ID, should return false!' );

		$this->assertEquals( $all_sidebars[ 'pt-cs-1' ] , PT_CS_Main::get_sidebar( 'pt-cs-1' ), 'Custom sidebar pt-cs-1, should be returned!' );
		$this->assertEquals( $all_sidebars[ 'pt-cs-1' ] , PT_CS_Main::get_sidebar( 'pt-cs-1', 'cust' ), 'Custom sidebar pt-cs-1, should be returned (used "cust" sidebar type)!' );

		// Revert the global variable change.
		$wp_registered_sidebars = $default_sidebars;
	}

	/**
	 * Test PT_CS_Main::get_replacements method.
	 *
	 * @dataProvider postmeta_data_set
	 */
	function test_get_replacements( $post_meta ) {

		// Create a post.
		$post_id = $this->factory->post->create();

		$this->assertEmpty( PT_CS_Main::get_replacements( $post_id ), 'By default (no post meta was set for this post), an empty array should be returned!' );

		// Set post meta
		PT_CS_Main::set_post_meta( $post_id, $post_meta );

		$this->assertEquals( $post_meta, PT_CS_Main::get_replacements( $post_id ), 'Post meta (replacement data), should be returned!' );
	}

	/**
	 * Test PT_CS_Main::supported_post_type method.
	 */
	function test_supported_post_type() {
		$this->assertTrue( PT_CS_Main::supported_post_type( 'post' ), 'Post post type is allowed by default!' );
		$this->assertFalse( PT_CS_Main::supported_post_type( 'attachment' ), 'Attachment post type is ignored by default!' );
	}





/************************************************************/
/************* Helper functions and data sets ***************/
/************************************************************/

	/**
	 * This is a dataProvider for testing custom sidebars "created" by the plugin.
	 */
	public static function custom_siderbars_data_set() {
		return array(
			array(
				array(
					array(
						'id'            => 'pt-cs-1',
						'name'          => 'Custom sidebar 1',
						'description'   => 'Testing custom sidebar',
						'class'         => '',
						'before_title'  => '<h3 class="widget-title">',
						'after_title'   => '</h3>',
						'before_widget' => '<section id="%1$s" class="widget %2$s">',
						'after_widget'  => '</section>',
					),
					array(
						'id'            => 'pt-cs-2',
						'name'          => 'Custom sidebar 2',
						'description'   => 'Testing custom sidebar 2',
						'class'         => '',
						'before_title'  => '',
						'after_title'   => '',
						'before_widget' => '',
						'after_widget'  => '',
					),
					'invalid array item, that will be ignored',
				),
				array(
					array(
						'id'            => 'pt-cs-1',
						'name'          => 'Custom sidebar 1',
						'description'   => 'Testing custom sidebar',
						'class'         => '',
						'before_title'  => '<h3 class="widget-title">',
						'after_title'   => '</h3>',
						'before_widget' => '<section id="%1$s" class="widget %2$s">',
						'after_widget'  => '</section>',
					),
					array(
						'id'            => 'pt-cs-2',
						'name'          => 'Custom sidebar 2',
						'description'   => 'Testing custom sidebar 2',
						'class'         => '',
						'before_title'  => '',
						'after_title'   => '',
						'before_widget' => '',
						'after_widget'  => '',
					),
				),
			),
		);
	}


	/**
	 * This is a dataProvider for testing postmeta for single posts "created" by the plugin.
	 */
	function postmeta_data_set() {
		return array(
			array(
				array(
					'sidebar-1' => 'pt-cs-1',
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
	 * Return all theme sidebars.
	 * By default all theme sidebars are "modifiable".
	 */
	private function get_all_theme_sidebars() {
		$theme_sidebar_ids = array_keys( PT_CS_Main::get_sidebars( 'theme' ) );
		return array( 'modifiable' => $theme_sidebar_ids );
	}

	/**
	 * Helper function!
	 * Modify the global $wp_registered_sidebars var.
	 */
	private function modify_global_wp_registered_sidebars( $org_wp_registered_sidebars, $custom_sidebars ) {
		return array_merge( $org_wp_registered_sidebars, array( $custom_sidebars[0]['id'] => $custom_sidebars[0] ) );
	}
}

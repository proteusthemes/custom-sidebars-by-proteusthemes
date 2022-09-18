<?php

use DMS\PHPUnitExtensions\ArraySubset\Assert;

class PT_CS_Editor_Test extends WP_UnitTestCase {

	/**
	 * Test PT_CS_Editor::prepare_ajax_response method.
	 *
	 * @dataProvider PT_CS_Main_Test::custom_siderbars_data_set
	 */
	function test_prepare_ajax_response( $custom_sidebars, $expected ) {
		$instance               = PT_CS_Editor::get_instance();
		$replacer_instance      = PT_CS_Replacer::get_instance();
		$ajax_req_and_resp_data = $this->get_ajax_requests_and_responses_data();

		$this->assertFalse( $instance->prepare_ajax_response( 'made_up_action' ), 'An invalid AJAX action request should return false!' );

		Assert::assertArraySubset( $ajax_req_and_resp_data[0]['expected'], (array) $instance->prepare_ajax_response( $ajax_req_and_resp_data[0]['action'] ), 'AJAX action request without proper user role should return object with a status=ERR!' );

		// Create and set an admin user, to pass the current_user_can check in test_prepare_ajax_response.
		$this->create_and_set_admin_user();

		// Set custom sidebars.
		PT_CS_Replacer::set_custom_sidebars( $custom_sidebars );

		// Register custom sidebars.
		$replacer_instance->register_custom_sidebars();

		// Set $_POST (AJAX request simulation).
		$_POST = $ajax_req_and_resp_data[1]['post'];

		Assert::assertArraySubset( $ajax_req_and_resp_data[1]['expected'], (array) $instance->prepare_ajax_response( $ajax_req_and_resp_data[1]['action'] ), $ajax_req_and_resp_data[1]['test_message'] );

		// Set $_POST (AJAX request simulation).
		$_POST = $ajax_req_and_resp_data[2]['post'];

		Assert::assertArraySubset( $ajax_req_and_resp_data[2]['expected'], (array) $instance->prepare_ajax_response( $ajax_req_and_resp_data[2]['action'] ), $ajax_req_and_resp_data[2]['test_message'] );

		for ( $i=3; $i < count( $ajax_req_and_resp_data ); $i++ ) {

			// Set $_POST (AJAX request simulation).
			$_POST = $ajax_req_and_resp_data[ $i ]['post'];

			$this->assertEquals( $ajax_req_and_resp_data[ $i ]['expected'], (array) $instance->prepare_ajax_response( $ajax_req_and_resp_data[ $i ]['action'] ), $ajax_req_and_resp_data[ $i ]['test_message'] );
		}

		// Revert the $_POST variable.
		$_POST = null;
	}

/************************************************************/
/************* Helper functions and dataProviders ***********/
/************************************************************/

	/**
	 * This is a dataProvider for testing AJAX responses.
	 */
	function ajax_requests_and_responses() {
		return array(
			array(
				array(
					array(
						'action'   => 'get',
						'expected' => array(
							'status' => 'ERR',
						),
					),
					array(
						'action'   => 'replaceable',
						'expected' => array(
							'status' => false,
							'action' => 'replaceable',
							'id'     => 'sidebar-1',
							'state'  => '',
						),
						'post' => array(
							'sb'    => 'sidebar-1',
							'state' => '',
						),
						'test_message' => 'AJAX replaceable action request with empty state should return object with false status!',
					),
					array(
						'action'   => 'replaceable',
						'expected' => array(
							'status' => true,
							'action' => 'replaceable',
							'id'     => 'sidebar-2',
							'state'  => 'true',
						),
						'post' => array(
							'sb'    => 'sidebar-2',
							'state' => 'true',
						),
						'test_message' => 'AJAX replaceable action request with state=true should return object with true status!',
					),
					array(
						'action'   => 'get',
						'expected' => array(
							'status'  => 'OK',
							'action'  => 'get',
							'id'      => 'pt-cs-1',
							'sidebar' => array(
								'id'             => 'pt-cs-1',
								'name'           => 'Custom sidebar 1',
								'description'    => 'Testing custom sidebar',
								'class'          => '',
								'before_title'   => '<h3 class="widget-title">',
								'after_title'    => '</h3>',
								'before_widget'  => '<section id="%1$s" class="widget %2$s">',
								'after_widget'   => '</section>',
								'before_sidebar' => '',
								'after_sidebar'  => '',
							),
						),
						'post' => array(
							'sb' => 'pt-cs-1',
						),
						'test_message' => 'AJAX get action request with proper user role should return object with specific data!',
					),
					array(
						'action'   => 'save',
						'expected' => array(
							'status'   => 'ERR',
							'message'  => 'Sidebar-name cannot be empty',
							'action'   => 'save',
							'id'       => 'pt-cs-1',
						),
						'post' => array(
							'sb' => 'pt-cs-1',
						),
						'test_message' => 'AJAX save action request without name should return and error object!',
					),
					array(
						'action'   => 'save',
						'expected' => array(
							'status'  => 'OK',
							'action'  => 'insert',
							'message' => 'Created new sidebar <strong>New Sidebar</strong>',
							'id'      => null,
							'data' => array(
								'id'             => 'pt-cs-3',
								'name'           => 'New Sidebar',
								'description'    => 'Testing new sidebar',
								'class'          => '',
								'before_title'   => '',
								'after_title'    => '',
								'before_widget'  => '',
								'after_widget'   => '',
							),
						),
						'post' => array(
							'name'        => 'New Sidebar',
							'description' => 'Testing new sidebar',
						),
						'test_message' => 'AJAX save action request without id should insert a new sidebar and return an object with data!',
					),
					array(
						'action'   => 'save',
						'expected' => array(
							'status'  => 'ERR',
							'action'  => 'save',
							'message' => 'The sidebar does not exist',
							'id'      => 'made_up_cs_id',
						),
						'post' => array(
							'sb' => 'made_up_cs_id',
							'name' => 'Made up cs name',
						),
						'test_message' => 'AJAX save action request with invalid id should return an error object!',
					),
					array(
						'action'   => 'save',
						'expected' => array(
							'status'  => 'OK',
							'action'  => 'update',
							'message' => 'Updated sidebar <strong>New Sidebar update</strong>',
							'id'      => 'pt-cs-2',
							'data' => array(
								'id'             => 'pt-cs-2',
								'name'           => 'New Sidebar update',
								'description'    => '',
								'class'          => '',
								'before_title'   => '',
								'after_title'    => '',
								'before_widget'  => '<div>',
								'after_widget'   => '</div>',
								'before_sidebar' => '',
								'after_sidebar'  => '',
							),
						),
						'post' => array(
							'sb'             => 'pt-cs-2',
							'name'           => 'New Sidebar update',
							'description'    => '',
							'before_widget'  => '<div>',
							'after_widget'   => '</div>',
							'before_sidebar' => '',
							'after_sidebar'  => '',
						),
						'test_message' => 'AJAX save action request with valid id should update the sidebar and return and object with data!',
					),
					array(
						'action'   => 'delete',
						'expected' => array(
							'status'  => 'ERR',
							'action'  => 'delete',
							'message' => 'The sidebar does not exist',
							'id'      => 'made_up_cs_id',
						),
						'post' => array(
							'sb' => 'made_up_cs_id',
							'name' => 'Made up cs name',
						),
						'test_message' => 'AJAX delete action request with invalid id should return an error object!',
					),
					array(
						'action'   => 'delete',
						'expected' => array(
							'status'  => 'OK',
							'action'  => 'delete',
							'message' => 'Deleted sidebar <strong>Custom sidebar 2</strong>',
							'id'      => 'pt-cs-2',
						),
						'post' => array(
							'sb' => 'pt-cs-2',
						),
						'test_message' => 'AJAX delete action request with valid id should return object with data!',
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
	 * Return the sidebar_replacement_data_set data.
	 */
	private function get_ajax_requests_and_responses_data() {
		$data = $this->ajax_requests_and_responses();
		return $data[0][0];
	}
}

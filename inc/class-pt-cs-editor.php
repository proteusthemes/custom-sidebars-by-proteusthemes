<?php
/**
 * Provides all the functionality for editing sidebars on the widgets page. *Singleton*
 *
 * @package pt-cs
 */

// Initialize this class in the main plugin class.
add_action( 'pt-cs/init', array( 'PT_CS_Editor', 'get_instance' ) );

/**
 * Provides all the functionality for editing sidebars on the widgets page.
 */
class PT_CS_Editor extends PT_CS_Main {
	/**
	 * Reference to Singleton instance of this class.
	 *
	 * @var Singleton The reference to *Singleton* instance of this class
	 */
	private static $instance;

	/**
	 * Returns the *Singleton* instance of this class.
	 *
	 * @return PT_CS_Editor the *Singleton* instance.
	 */
	public static function get_instance() {
		if ( null === static::$instance ) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * Protected constructor to prevent creating a new instance of the
	 * *Singleton* via the `new` operator from outside of this class.
	 */
	protected function __construct() {
		if ( is_admin() ) {

			// Add the sidebar metabox to posts.
			add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );

			// Save the options from the sidebars-metabox.
			add_action( 'save_post', array( $this, 'store_replacements' ) );

			// Handle ajax requests.
			add_action( 'pt-cs/ajax_request', array( $this, 'handle_ajax' ) );
		}
	}

	/**
	 * Handles the ajax requests.
	 *
	 * @param string $action String for the appropriate action.
	 */
	public function handle_ajax( $action ) {

		// Prepare the response.
		$response = $this->prepare_ajax_response( $action );

		// Make the ajax response as JSON.
		self::json_response( $response );
	}

	/**
	 * Handles the ajax request. Prepare the ajax response.
	 *
	 * @param string $action String for the appropriate action.
	 */
	public function prepare_ajax_response( $action ) {
		$response = (object) array(
			'status' => 'ERR',
		);
		$handle_it = false;
		$sb_id     = isset( $_POST['sb'] ) ? $_POST['sb'] : null;

		switch ( $action ) {
			case 'get':
			case 'save':
			case 'delete':
			case 'replaceable':
				$handle_it = true;
				$response->status = 'OK';
				$response->action = $action;
				$response->id = $sb_id;
				break;
		}

		// The ajax request was not meant for us...
		if ( ! $handle_it ) {
			return false;
		}

		$sb_data = self::get_sidebar( $sb_id );

		if ( ! current_user_can( self::$cap_required ) ) {
			$response = self::req_err(
				$response,
				esc_html__( 'You do not have permission for this', 'pt-cs' )
			);
		}
		else {
			switch ( $action ) {

				// Return details for the specified sidebar.
				case 'get':
					$response->sidebar = $sb_data;
					break;

				// Save or insert the specified sidebar.
				case 'save':
					$response = $this->save_item( $response, $_POST );
					break;

				// Delete the specified sidebar.
				case 'delete':
					$response = $this->delete_item( $response );
					break;

				// Toggle theme sidebar replaceable-flag.
				case 'replaceable':
					$response->state = isset( $_POST['state'] ) ? $_POST['state'] : '';
					$response = $this->set_replaceable( $response );
					break;
			}
		}

		return $response;
	}

	/**
	 * Saves the item specified by $data array and populates the response
	 * object. When $req->id is empty a new sidebar will be created. Otherwise
	 * the existing sidebar is updated.
	 *
	 * @param  object $req Initial response object.
	 * @param  array  $data Sidebar data to save (typically this is $_POST).
	 * @return object Updated response object.
	 */
	private function save_item( $req, $data ) {
		$sidebars = self::get_custom_sidebars();
		$sb_id    = $req->id;
		$sb_desc  = isset( $data['description'] ) ? stripslashes( trim( $data['description'] ) ) : '';

		if ( function_exists( 'mb_substr' ) ) {
			$sb_name = isset( $data['name'] ) ? mb_substr( stripslashes( trim( $data['name'] ) ), 0, 40 ) : '';
		} else {
			$sb_name = isset( $data['name'] ) ? substr( stripslashes( trim( $data['name'] ) ), 0, 40 ) : '';
		}

		if ( empty( $sb_name ) ) {
			return self::req_err(
				$req,
				esc_html__( 'Sidebar-name cannot be empty', 'pt-cs' )
			);
		}

		if ( empty( $sb_id ) ) {

			// Create a new sidebar.
			$action = 'insert';
			$num    = count( $sidebars );
			do {
				$num += 1;
				$sb_id = self::$sidebar_prefix . $num;
			} while ( self::get_sidebar( $sb_id, 'cust' ) );

			$sidebar = array(
				'id' => $sb_id,
			);
		} else {

			// Update existing sidebar.
			$action = 'update';
			$sidebar = self::get_sidebar( $sb_id, 'cust' );

			if ( ! $sidebar ) {
				return self::req_err(
					$req,
					esc_html__( 'The sidebar does not exist', 'pt-cs' )
				);
			}
		}

		if ( function_exists( 'mb_strlen' ) ) {
			if ( mb_strlen( $sb_desc ) > 200 ) {
				$sb_desc = mb_substr( $sb_desc, 0, 200 );
			}
		} else {
			if ( strlen( $sb_desc ) > 200 ) {
				$sb_desc = substr( $sb_desc, 0, 200 );
			}
		}

		// Populate the sidebar object.
		$sidebar['name']          = $sb_name;
		$sidebar['description']   = $sb_desc;
		$sidebar['class']         = '';
		$sidebar['before_widget'] = isset( $data['before_widget'] ) ? stripslashes( trim( $data['before_widget'] ) ) : '';
		$sidebar['after_widget']  = isset( $data['after_widget'] ) ? stripslashes( trim( $data['after_widget'] ) ) : '';
		$sidebar['before_title']  = isset( $data['before_title'] ) ? stripslashes( trim( $data['before_title'] ) ) : '';
		$sidebar['after_title']   = isset( $data['after_title'] ) ? stripslashes( trim( $data['after_title'] ) ) : '';

		if ( 'insert' === $action ) {
			$sidebars[]   = $sidebar;
			$req->message = sprintf(
				esc_html__( 'Created new sidebar %1$s', 'pt-cs' ),
				'<strong>' . esc_html( $sidebar['name'] ) . '</strong>'
			);
		}
		else {
			$found = false;
			foreach ( $sidebars as $ind => $item ) {
				if ( $item['id'] === $sb_id ) {
					$req->message = sprintf(
						esc_html__( 'Updated sidebar %1$s', 'pt-cs' ),
						'<strong>' . esc_html( $sidebar['name'] ) . '</strong>'
					);
					$sidebars[ $ind ] = $sidebar;
					$found = true;
					break;
				}
			}
			if ( ! $found ) {
				return self::req_err(
					$req,
					esc_html__( 'The sidebar was not found', 'pt-cs' )
				);
			}
		}

		// Save the changes.
		self::set_custom_sidebars( $sidebars );
		self::refresh_sidebar_widgets();

		$req->action = $action;
		$req->data   = $sidebar;

		return $req;
	}

	/**
	 * Delete the specified sidebar and update the response object.
	 *
	 * @param  object $req Initial response object.
	 * @return object Updated response object.
	 */
	private function delete_item( $req ) {
		$sidebars = self::get_custom_sidebars();
		$sidebar  = self::get_sidebar( $req->id, 'cust' );

		if ( ! $sidebar ) {
			return self::req_err(
				$req,
				esc_html__( 'The sidebar does not exist', 'pt-cs' )
			);
		}

		$found = false;
		foreach ( $sidebars as $ind => $item ) {
			if ( $item['id'] == $req->id ) {
				$found = true;
				$req->message = sprintf(
					esc_html__( 'Deleted sidebar %1$s', 'pt-cs' ),
					'<strong>' . esc_html( $sidebar['name'] ) . '</strong>'
				);
				unset( $sidebars[ $ind ] );
				break;
			}
		}

		if ( ! $found ) {
			return self::req_err(
				$req,
				esc_html__( 'The sidebar was not found', 'pt-cs' )
			);
		}

		// Save the changes.
		self::set_custom_sidebars( $sidebars );
		self::refresh_sidebar_widgets();

		return $req;
	}

	/**
	 * Save the replaceable flag of a theme sidebar.
	 *
	 * @param  object $req Initial response object.
	 * @return object Updated response object.
	 */
	private function set_replaceable( $req ) {
		$options = self::get_options();

		if ( 'true' === $req->state ) {
			$req->status = true;
			if ( ! in_array( $req->id, $options['modifiable'] ) ) {
				$options['modifiable'][] = $req->id;
			}
		}
		else {
			$req->status = false;
			foreach ( $options['modifiable'] as $i => $sb_id ) {
				if ( $sb_id == $req->id ) {
					unset( $options['modifiable'][ $i ] );
					break;
				}
			}
		}
		$options['modifiable'] = array_values( $options['modifiable'] );
		self::set_options( $options );
		$req->replaceable = (object) $options['modifiable'];

		return $req;
	}

	/**
	 * Registers the "Sidebars" meta box in the post-editor.
	 */
	public function add_meta_box() {

		$post_type = get_post_type();
		if ( ! $post_type ) { return false; }
		if ( ! self::supported_post_type( $post_type ) ) { return false; }

		$pt_obj = get_post_type_object( $post_type );
		if ( $pt_obj->publicly_queryable || $pt_obj->public ) {
			add_meta_box(
				'pt-customsidebars-mb',
				esc_html__( 'Sidebars', 'pt-cs' ),
				array( $this, 'print_metabox_editor' ),
				$post_type,
				'side'
			);
		}
	}

	/**
	 * Renders the Custom Sidebars meta box in the post-editor.
	 */
	public function print_metabox_editor() {
		global $post;
		$this->print_sidebars_form( $post->ID, 'metabox' );
	}

	/**
	 * Renders the Custom Sidebars form.
	 *
	 * @param int    $post_id The post-ID to display.
	 * @param string $type Which form to display. 'metabox/quick-edit/col-sidebars'.
	 */
	protected function print_sidebars_form( $post_id, $type = 'metabox' ) {
		global $wp_registered_sidebars;

		$replacements = self::get_replacements( $post_id );

		$available = $wp_registered_sidebars;
		ksort( $available );
		$sidebars = self::get_options( 'modifiable' );
		$selected = array();
		if ( ! empty( $sidebars ) ) {
			foreach ( $sidebars as $s ) {
				if ( isset( $replacements[ $s ] ) ) {
					$selected[ $s ] = $replacements[ $s ];
				} else {
					$selected[ $s ] = '';
				}
			}
		}

		include PT_CS_VIEWS_DIR . 'metabox.php';
	}

	/**
	 * Saves the options from the metabox.
	 *
	 * @param int $post_id The post-ID to display.
	 */
	public function store_replacements( $post_id ) {
		global $action;

		if ( ! current_user_can( self::$cap_required ) ) {
			return;
		}

		/*
		 * Verify if this is an auto save routine. If it is our form has not
		 * been submitted, so we don't want to do anything
		 * (Copied and pasted from wordpress add_metabox_tutorial)
		 */
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		/*
		 * 'editpost' .. Saved from full Post-Editor screen.
		 * 'inline-save' .. Saved via the quick-edit form.
		 * We do not (yet) offer a bulk-editing option for custom sidebars.
		 */
		if ( 'editpost' !== $action ) {
			return $post_id;
		}

		// Make sure meta is added to the post, not a revision.
		if ( $the_post = wp_is_post_revision( $post_id ) ) {
			$post_id = $the_post;
		}

		$sidebars = self::get_options( 'modifiable' );
		$data = array();
		if ( ! empty( $sidebars ) ) {
			foreach ( $sidebars as $sb_id ) {
				if ( isset( $_POST[ 'pt_cs_replacement_' . $sb_id ] ) ) {
					$replacement = $_POST[ 'pt_cs_replacement_' . $sb_id ];
					if ( ! empty( $replacement ) && '' != $replacement ) {
						$data[ $sb_id ] = $replacement;
					}
				}
			}
		}

		self::set_post_meta( $post_id, $data );
	}
}

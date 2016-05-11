<?php
/**
 * Core plugin class. *Singleton*
 *
 * @package pt-cs
 */

// Load additional files.
require_once PT_CS_PATH . 'inc/class-custom-sidebars-widgets.php';
require_once PT_CS_PATH . 'inc/class-custom-sidebars-editor.php';
require_once PT_CS_PATH . 'inc/class-custom-sidebars-replacer.php';
require_once PT_CS_PATH . 'inc/class-custom-sidebars-explain.php';

/**
 * Main plugin file.
 * The PT_CS_Main class encapsulates all our plugin logic.
 */
class PT_CS_Main {
	/**
	 * Prefix used for the sidebar-ID of custom sidebars. This is also used to
	 * distinguish theme sidebars from custom sidebars.
	 *
	 * @var  string
	 */
	static protected $sidebar_prefix = 'cs-';

	/**
	 * Capability required to use *any* of the plugin features. If user does not
	 * have this capability then he will not see any change on admin dashboard.
	 *
	 * @var  string
	 */
	static protected $cap_required = 'edit_theme_options';

	/**
	 * Flag that specifies if the page is loaded in accessibility mode.
	 * This plugin does not support accessibility mode!
	 *
	 * @var   bool
	 */
	static protected $accessibility_mode = false;


	/**
	 * Returns the singleton instance of the custom sidebars class.
	 *
	 * @since  2.0
	 */
	public static function instance() {
		static $inst = null;

		// We can initialize the plugin once we know the current user:
		// The WDev()->pointer() notification is based on current user...
		if ( ! did_action( 'set_current_user' ) ) {
			add_action( 'set_current_user', array( __CLASS__, 'instance' ) );
			return null;
		}

		if ( null === $inst ) {
			$inst = new PT_CS_Main();
		}

		return $inst;
	}

	/**
	 * Private, since it is a singleton.
	 * We directly initialize sidebar options when class is created.
	 */
	private function __construct() {

		// Find out if the page is loaded in accessibility mode.
		$flag = isset( $_GET['widgets-access'] ) ? $_GET['widgets-access'] : get_user_setting( 'widgets_access' );
		self::$accessibility_mode = ( 'on' == $flag );

		// We don't support accessibility mode. Display a note to the user.
		if ( true === self::$accessibility_mode ) {
			WDev()->message(
				sprintf(
					__(
						'<strong>Accessibility mode is not supported by the
						%1$s plugin.</strong><br /><a href="%2$s">Click here</a>
						to disable accessibility mode and use the %1$s plugin!',
						PT_CS_TD
					),
					'Custom Sidebars',
					admin_url( 'widgets.php?widgets-access=off' )
				),
				'err',
				'widgets'
			);
		} else {
			// Load javascripts/css files.
			WDev()->add_ui( 'core', 'widgets.php' );
			WDev()->add_ui( 'scrollbar', 'widgets.php' );
			WDev()->add_ui( 'select', 'widgets.php' );
			WDev()->add_ui( PT_CS_URL . 'js/cs.min.js', 'widgets.php' );
			WDev()->add_ui( PT_CS_URL . 'css/cs.css', 'widgets.php' );

			// AJAX actions.
			add_action( 'wp_ajax_cs-ajax', array( $this, 'ajax_handler' ) );

			// Extensions use this hook to initialize themselves.
			do_action( 'cs_init' );

			// Display a message after import.
			if ( ! empty( $_GET['cs-msg'] ) ) {
				$msg = base64_decode( $_GET['cs-msg'] );

				// Prevent XSS attacks.
				$kses_args = array(
					'br' => array(),
					'b' => array(),
					'strong' => array(),
					'i' => array(),
					'em' => array(),
				);
				$msg = wp_kses( $msg, $kses_args );

				if ( ! empty( $msg ) ) {
					WDev()->message( $msg );
				}
			}
		}
	}

	/**
	 *
	 * =========================================================================
	 * == DATA ACCESS
	 * =========================================================================
	 *
	 * ==1== PLUGIN OPTIONS
	 *   Option-Key: cs_modifiable
	 *
	 *   {
	 *       // Sidebars that can be replaced:
	 *       'modifiable': [
	 *           'sidebar_1',
	 *           'sidebar_2'
	 *       ],
	 *
	 *       // Default replacements:
	 *       'post_type_single': [ // Former "defaults"
	 *           'post_type1': <replacement-def>,
	 *           'post_type2': <replacement-def>
	 *       ],
	 *       'post_type_archive': [  // Former "post_type_pages"
	 *           'post_type1': <replacement-def>,
	 *           'post_type2': <replacement-def>
	 *       ],
	 *       'category_single': [ // Former "category_posts"
	 *           'category_id1': <replacement-def>,
	 *           'category_id2': <replacement-def>
	 *       ],
	 *       'category_archive': [ // Former "category_pages"
	 *           'category_id1': <replacement-def>,
	 *           'category_id2': <replacement-def>
	 *       ],
	 *       'blog': <replacement-def>,
	 *       'tags': <replacement-def>,
	 *       'authors': <replacement-def>,
	 *       'search': <replacement-def>,
	 *       'date': <replacement-def>
	 *   }
	 *
	 * ==2== REPLACEMENT-DEF
	 *   Meta-Key: _cs_replacements
	 *   Option-Key: cs_modifiable <replacement-def>
	 *
	 *   {
	 *       'sidebar_1': 'custom_sb_id1',
	 *       'sidebar_2': 'custom_sb_id2'
	 *   }
	 *
	 * ==3== SIDEBAR DEFINITION
	 *   Option-Key: cs_sidebars
	 *
	 *   Array of these objects
	 *   {
	 *       id: '', // sidebar-id
	 *       name: '',
	 *       description: '',
	 *       before_title: '',
	 *       after_title: '',
	 *       before_widget: '',
	 *       after_widget: ''
	 *   }
	 *
	 * ==4== WIDGET LIST
	 *   Option-Key: sidebars_widgets
	 *
	 *   {
	 *       'sidebar_id': [
	 *           'widget_id1',
	 *           'widget_id2'
	 *       ],
	 *       'sidebar_2': [
	 *       ],
	 *       'sidebar_3': [
	 *           'widget_id1',
	 *           'widget_id3'
	 *       ],
	 *   }
	 */


	/**
	 * If the specified variable is an array it will be returned. Otherwise
	 * an empty array is returned.
	 *
	 * @param  mixed $val1 Value that maybe is an array.
	 * @param  mixed $val2 Optional, Second value that maybe is an array.
	 * @return array
	 */
	public static function get_array( $val1, $val2 = array() ) {
		if ( is_array( $val1 ) ) {
			return $val1;
		} else if ( is_array( $val2 ) ) {
			return $val2;
		} else {
			return array();
		}
	}

	/**
	 * Returns a list with sidebars that were marked as "modifiable".
	 * Also contains information on the default replacements of these sidebars.
	 *
	 * Option-Key: 'cs_modifiable' (1)
	 *
	 * @param string $key a key of the options array.
	 */
	public static function get_options( $key = null ) {
		static $options = null;
		$need_update = false;

		if ( null === $options ) {
			$options = get_option( 'cs_modifiable', array() );
			if ( ! is_array( $options ) ) {
				$options = array();
			}

			// List of modifiable sidebars.
			if ( ! is_array( $options['modifiable'] ) ) {

				// By default we make ALL theme sidebars replaceable.
				$all = self::get_sidebars( 'theme' );
				$options['modifiable'] = array_keys( $all );
				$need_update = true;
			}

			// Single/Archive pages.
			$options['post_type_single']  = isset( $options['post_type_single'] ) ? self::get_array( $options['post_type_single'] ) : array();
			$options['post_type_archive'] = isset( $options['post_type_archive'] ) ? self::get_array( $options['post_type_archive'] ) : array();
			$options['category_single']   = isset( $options['category_single'] ) ? self::get_array( $options['category_single'] ) : array();
			$options['category_archive']  = isset( $options['category_archive'] ) ? self::get_array( $options['category_archive'] ) : array();

			// Special archive pages.
			$options['blog']    = isset( $options['blog'] ) ? self::get_array( $options['blog'] ) : array();
			$options['tags']    = isset( $options['tags'] ) ? self::get_array( $options['tags'] ) : array();
			$options['authors'] = isset( $options['authors'] ) ? self::get_array( $options['authors'] ) : array();
			$options['search']  = isset( $options['search'] ) ? self::get_array( $options['search'] ) : array();
			$options['date']    = isset( $options['date'] ) ? self::get_array( $options['date'] ) : array();

			$options = self::validate_options( $options );

			if ( $need_update ) {
				self::set_options( $options );
			}
		}

		if ( ! empty( $key ) ) {
			return $options[ $key ];
		} else {
			return $options;
		}
	}

	/**
	 * Saves the sidebar options to DB.
	 *
	 * Option-Key: 'cs_modifiable' (1)
	 *
	 * @param  array $value The options array.
	 */
	public static function set_options( $value ) {

		// Permission check.
		if ( ! current_user_can( self::$cap_required ) ) {
			return;
		}

		update_option( 'cs_modifiable', $value );
	}

	/**
	 * Removes invalid settings from the options array.
	 *
	 * @param  array $data This array will be validated and returned.
	 * @return array
	 */
	public static function validate_options( $data = null ) {
		$data = (is_object( $data ) ? (array) $data : $data );
		if ( ! is_array( $data ) ) {
			return array();
		}
		$valid = array_keys( self::get_sidebars( 'theme' ) );
		$current = isset( $data['modifiable'] ) ? self::get_array( $data['modifiable'] ) : array();

		// Get all the sidebars that are modifiable AND exist.
		$modifiable = array_intersect( $valid, $current );
		$data['modifiable'] = $modifiable;

		return $data;
	}

	/**
	 * Returns a list with all custom sidebars that were created by the user.
	 * Array of custom sidebars
	 *
	 * Option-Key: 'cs_sidebars' (3)
	 */
	public static function get_custom_sidebars() {
		$sidebars = get_option( 'cs_sidebars', array() );
		if ( ! is_array( $sidebars ) ) {
			$sidebars = array();
		}

		// Remove invalid items.
		foreach ( $sidebars as $key => $data ) {
			if ( ! is_array( $data ) ) {
				unset( $sidebars[ $key ] );
			}
		}

		return $sidebars;
	}

	/**
	 * Saves the custom sidebars to DB.
	 *
	 * Option-Key: 'cs_sidebars' (3)
	 *
	 * @param array $value Array with custom sidebars data.
	 */
	public static function set_custom_sidebars( $value ) {
		// Permission check.
		if ( ! current_user_can( self::$cap_required ) ) {
			return;
		}

		update_option( 'cs_sidebars', $value );
	}

	/**
	 * Returns a list of all registered sidebars including a list of their
	 * widgets (this is stored inside a WordPress core option).
	 *
	 * Option-Key: 'sidebars_widgets' (4)
	 */
	public static function get_sidebar_widgets() {
		return get_option( 'sidebars_widgets', array() );
	}

	/**
	 * Update the WordPress core settings for sidebar widgets:
	 * 1. Add empty widget information for new sidebars.
	 * 2. Remove widget information for sidebars that no longer exist.
	 *
	 * Option-Key: 'sidebars_widgets' (4)
	 */
	public static function refresh_sidebar_widgets() {

		// Contains an array of all sidebars and widgets inside each sidebar.
		$widgetized_sidebars = self::get_sidebar_widgets();

		$cs_sidebars = self::get_custom_sidebars();
		$delete_widgetized_sidebars = array();

		foreach ( $widgetized_sidebars as $id => $bar ) {
			if ( substr( $id, 0, 3 ) == self::$sidebar_prefix ) {
				$found = false;
				foreach ( $cs_sidebars as $csbar ) {
					if ( $csbar['id'] == $id ) {
						$found = true;
					}
				}
				if ( ! $found ) {
					$delete_widgetized_sidebars[] = $id;
				}
			}
		}

		$all_ids = array_keys( $widgetized_sidebars );
		foreach ( $cs_sidebars as $cs ) {
			$sb_id = $cs['id'];
			if ( ! in_array( $sb_id, $all_ids ) ) {
				$widgetized_sidebars[ $sb_id ] = array();
			}
		}

		foreach ( $delete_widgetized_sidebars as $id ) {
			unset( $widgetized_sidebars[ $id ] );
		}

		update_option( 'sidebars_widgets', $widgetized_sidebars );
	}

	/**
	 * Returns the custom sidebar metadata of a single post.
	 *
	 * Meta-Key: '_cs_replacements' (2)
	 *
	 * @param int $post_id ID of the post.
	 */
	public static function get_post_meta( $post_id ) {
		$data = get_post_meta( $post_id, '_cs_replacements', true );
		if ( ! is_array( $data ) ) {
			$data = array();
		}
		return $data;
	}

	/**
	 * Saves custom sidebar metadata to a single post.
	 *
	 * Meta-Key: '_cs_replacements' (2)
	 *
	 * @param int   $post_id ID of the post.
	 * @param array $data When array is empty the meta data will be deleted.
	 */
	public static function set_post_meta( $post_id, $data ) {
		if ( ! empty( $data ) ) {
			update_post_meta( $post_id, '_cs_replacements', $data );
		} else {
			delete_post_meta( $post_id, '_cs_replacements' );
		}
	}

	/**
	 * Returns a list of all sidebars available.
	 * Depending on the parameter this will be either all sidebars or only
	 * sidebars defined by the current theme.
	 *
	 * @param string $type [all|cust|theme] What kind of sidebars to return.
	 */
	public static function get_sidebars( $type = 'theme' ) {
		global $wp_registered_sidebars;
		$allsidebars = $wp_registered_sidebars;
		$result = array();

		// Remove inactive sidebars.
		foreach ( $allsidebars as $sb_id => $sidebar ) {
			if ( false !== strpos( $sidebar['class'], 'inactive-sidebar' ) ) {
				unset( $allsidebars[ $sb_id ] );
			}
		}

		ksort( $allsidebars );
		if ( 'all' === $type ) {
			$result = $allsidebars;
		} else if ( 'cust' === $type ) {
			foreach ( $allsidebars as $key => $sb ) {

				// Only keep custom sidebars in the results.
				if ( substr( $key, 0, 3 ) === self::$sidebar_prefix ) {
					$result[ $key ] = $sb;
				}
			}
		} else if ( 'theme' === $type ) {
			foreach ( $allsidebars as $key => $sb ) {

				// Remove custom sidebars from results.
				if ( substr( $key, 0, 3 ) !== self::$sidebar_prefix ) {
					$result[ $key ] = $sb;
				}
			}
		}

		return $result;
	}

	/**
	 * Returns the sidebar with the specified ID.
	 * Sidebar can be both a custom sidebar or theme sidebar.
	 *
	 * @param string $id Sidebar-ID.
	 * @param string $type [all|cust|theme] What kind of sidebars to check.
	 */
	public static function get_sidebar( $id, $type = 'all' ) {
		if ( empty( $id ) ) { return false; }

		// Get all sidebars.
		$sidebars = self::get_sidebars( $type );

		if ( isset( $sidebars[ $id ] ) ) {
			return $sidebars[ $id ];
		} else {
			return false;
		}
	}

	/**
	 * Get sidebar replacement information for a single post.
	 *
	 * @param int $postid ID of the post.
	 */
	public static function get_replacements( $postid ) {
		$replacements = self::get_post_meta( $postid );
		if ( is_array( $replacements ) ) {
			return $replacements;
		}
		return array();
	}

	/**
	 * Returns true, when the specified post type supports custom sidebars.
	 *
	 * @param  object|string $posttype The posttype to validate. Either the posttype name or the full posttype object.
	 * @return bool
	 */
	public static function supported_post_type( $posttype ) {
		$ignored_types = null;
		$response = array();

		if ( null === $ignored_types ) {
			$ignored_types = get_post_types(
				array( 'public' => false ),
				'names'
			);
			$ignored_types[] = 'attachment';
		}

		if ( is_object( $posttype ) ) {
			$posttype = $posttype->name;
		}

		if ( ! isset( $response[ $posttype ] ) ) {
			$supported = ! in_array( $posttype, $ignored_types );

			/**
			 * Filters the support-flag. The flag defines if the posttype supports
			 * custom sidebars or not.
			 *
			 * @since  2.0
			 *
			 * @param  bool $supported Flag if the posttype is supported.
			 * @param  string $posttype Name of the posttype that is checked.
			 */
			$supported = apply_filters( 'cs_support_posttype', $supported, $posttype );
			$response[ $posttype ] = $supported;
		}

		return $response[ $posttype ];
	}

	/**
	 * Returns a list of all post types that support custom sidebars.
	 *
	 * @uses   self::supported_post_type()
	 * @param  string $type [names|objects] Defines details of return data.
	 * @return array List of posttype names or objects, depending on the param.
	 */
	public static function get_post_types( $type = 'names' ) {
		$valid = array();

		if ( 'objects' !== $type ) {
			$type = 'names';
		}

		if ( ! isset( $valid[ $type ] ) ) {
			$all = get_post_types( array(), $type );
			$valid[ $type ] = array();

			foreach ( $all as $post_type ) {
				if ( self::supported_post_type( $post_type ) ) {
					$valid[ $type ][] = $post_type;
				}
			}
		}

		return $valid[ $type ];
	}

	/**
	 * Returns an array of all categories.
	 *
	 * @return array List of categories, including empty ones.
	 */
	public static function get_all_categories() {
		$args = array(
			'hide_empty' => 0,
			'taxonomy'   => 'category',
		);

		return get_categories( $args );
	}

	/**
	 * Returns a sorted list of all category terms of the current post.
	 * This information is used to find sidebar replacements.
	 *
	 * @uses  self::cmp_cat_level()
	 * @param int $post_id ID of the post.
	 */
	public static function get_sorted_categories( $post_id = null ) {
		static $sorted = array();

		// Return categories of current post when no post_id is specified.
		$post_id = empty( $post_id ) ? get_the_ID() : $post_id;

		if ( ! isset( $sorted[ $post_id ] ) ) {
			$sorted[ $post_id ] = get_the_category( $post_id );
			@usort( $sorted[ $post_id ], array( self, 'cmp_cat_level' ) );
		}
		return $sorted[ $post_id ];
	}

	/**
	 * Helper function used to sort categories.
	 *
	 * @uses  self::get_category_level()
	 * @param obj $cat1 Category 1.
	 * @param obj $cat2 Category 2.
	 */
	public static function cmp_cat_level( $cat1, $cat2 ) {
		$l1 = self::get_category_level( $cat1->cat_ID );
		$l2 = self::get_category_level( $cat2->cat_ID );
		if ( $l1 === $l2 ) {
			return strcasecmp( $cat1->name, $cat1->name );
		} else {
			return $l1 < $l2 ? 1 : -1;
		}
	}

	/**
	 * Helper function used to sort categories.
	 *
	 * @param int $catid ID of the category.
	 */
	public static function get_category_level( $catid ) {
		if ( 0 === $catid ) {
			return 0;
		}

		$cat = get_category( $catid );
		return 1 + self::get_category_level( $cat->category_parent );
	}


	// =========================================================================
	// == AJAX FUNCTIONS
	// =========================================================================


	/**
	 * Output JSON data and die()
	 *
	 * @param obj $obj Response object.
	 */
	protected static function json_response( $obj ) {

		// Flush any output that was made prior to this function call.
		while ( 0 < ob_get_level() ) { ob_end_clean(); }

		wp_send_json( (object) $obj );
	}

	/**
	 * Output HTML data and die()
	 *
	 * @param string $data HTML output string.
	 */
	protected static function plain_response( $data ) {

		// Flush any output that was made prior to this function call.
		while ( 0 < ob_get_level() ) { ob_end_clean(); }

		header( 'Content-Type: text/plain' );
		echo '' . $data;
		die();
	}

	/**
	 * Sets the response object to ERR state with the specified message/reason.
	 *
	 * @param  object $req Initial response object.
	 * @param  string $message Error message or reason; already translated.
	 * @return object Updated response object.
	 */
	protected static function req_err( $req, $message ) {
		$req->status = 'ERR';
		$req->message = $message;
		return $req;
	}

	/**
	 * All Ajax request are handled by this function.
	 * It analyzes the post-data and calls the required functions to execute
	 * the requested action.
	 *
	 * --------------------------------
	 *
	 * IMPORTANT! ANY SERVER RESPONSE MUST BE MADE VIA ONE OF THESE FUNCTIONS!
	 * Using direct `echo` or include an html file will not work.
	 *
	 *    self::json_response( $obj )
	 *    self::plain_response( $text )
	 */
	public function ajax_handler() {

		// Permission check.
		if ( ! current_user_can( self::$cap_required ) ) {
			return;
		}

		// Try to disable debug output for ajax handlers of this plugin.
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			defined( 'WP_DEBUG_DISPLAY' ) || define( 'WP_DEBUG_DISPLAY', false );
			defined( 'WP_DEBUG_LOG' ) || define( 'WP_DEBUG_LOG', true );
		}
		// Catch any unexpected output via output buffering.
		ob_start();

		$action = isset( $_POST['do'] ) ? $_POST['do'] : '';

		/**
		 * Notify all extensions about the ajax call.
		 *
		 * @param  string $action The specified ajax action.
		 */
		do_action( 'cs_ajax_request', $action );
	}
}

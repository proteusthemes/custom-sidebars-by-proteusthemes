<?php
/**
 * Core plugin class. *Singleton*
 *
 * @package pt-cs
 */

// Load additional files.
require_once PT_CS_PATH . 'inc/class-pt-cs-widgets.php';
require_once PT_CS_PATH . 'inc/class-pt-cs-editor.php';
require_once PT_CS_PATH . 'inc/class-pt-cs-replacer.php';

/**
 * Main plugin file.
 * The PT_CS_Main class encapsulates all our plugin logic.
 */
class PT_CS_Main {
	/**
	 * Reference to Singleton instance of this class.
	 *
	 * @var Singleton The reference to *Singleton* instance of this class
	 */
	private static $instance;
	/**
	 * Prefix used for the sidebar-ID of custom sidebars. This is also used to
	 * distinguish theme sidebars from custom sidebars.
	 *
	 * @var  string
	 */
	protected static $sidebar_prefix = 'pt-cs-';

	/**
	 * Capability required to use *any* of the plugin features. If user does not
	 * have this capability then he will not see any change on admin dashboard.
	 *
	 * @var  string
	 */
	protected static $cap_required = 'edit_theme_options';

	/**
	 * Returns the *Singleton* instance of this class.
	 *
	 * @return PT_CS_Main the *Singleton* instance.
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
	 *
	 * We directly initialize sidebar options when class is created.
	 */
	protected function __construct() {

		// We don't support accessibility mode. Display a note to the user (on|off|null).
		if ( 'on' === ( isset( $_GET['widgets-access'] ) ? $_GET['widgets-access'] : get_user_setting( 'widgets_access' ) ) ) {
			add_action( 'admin_notices', array( $this, 'accessibility_mode_notice' ) );
		} else {
			// Load javascripts/css files.
			add_action( 'admin_enqueue_scripts', array( $this, 'load_plugin_admin_scripts' ) );

			// AJAX actions.
			add_action( 'wp_ajax_cs-ajax', array( $this, 'ajax_handler' ) );

			// Extensions use this hook to initialize themselves.
			do_action( 'pt-cs/init' );
		}
	}

	/**
	 * Enqueue admin scripts and styles.
	 *
	 * @param string $hook Current admin page.
	 */
	public function load_plugin_admin_scripts( $hook ) {
		if ( 'widgets.php' === $hook ) {

			// JS.
			wp_enqueue_script( 'pt-cs-tiny-scrollbar-js', PT_CS_URL . 'bower_components/tinyscrollbar/lib/jquery.tinyscrollbar.min.js', array( 'jquery' ), PT_CS_VERSION, true );
			wp_enqueue_script( 'pt-cs-main-js', PT_CS_URL . 'assets/js/main.min.js', array( 'jquery' ), PT_CS_VERSION, true );

			// CSS.
			wp_enqueue_style( 'pt-cs-main-css', PT_CS_URL . 'assets/css/main.min.css', array(), PT_CS_VERSION );
		}
	}

	/**
	 * Admin notice if the accessibility mode is on.
	 */
	public function accessibility_mode_notice() {
	?>
		<div class="notice notice-error is-dismissible"><p>
		<strong><?php esc_html_e( 'Accessibility mode is not supported in the Custom Sidebars by ProteusThemes plugin.' , 'pt-cs' ); ?></strong>
			<?php
				printf(
					esc_html__( '%1$sClick here%2$s to disable accessibility mode and use the Custom Sidebars by ProteusThemes plugin!', 'pt-cs' ),
					'<a href="' . esc_url( admin_url( 'widgets.php?widgets-access=off' ) ) . '">',
					'</a>'
				);
			?>
		</p></div>
	<?php
	}

	/**
	 *
	 * =========================================================================
	 * == DATA ACCESS
	 * =========================================================================
	 *
	 * ==1== PLUGIN OPTIONS
	 *   Option-Key: pt_cs_modifiable
	 *
	 *   {
	 *       // Sidebars that can be replaced:
	 *       'modifiable': [
	 *           'sidebar_1',
	 *           'sidebar_2'
	 *       ]
	 *   }
	 *
	 *
	 * ==2== SIDEBAR DEFINITION
	 *   Option-Key: pt_cs_sidebars
	 *
	 *   Array of these arrays
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
	 * ==3== WIDGET LIST
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
	 * Option-Key: 'pt_cs_modifiable' (1)
	 *
	 * @param string $key a key of the options array.
	 */
	public static function get_options( $key = null ) {
		$need_update = false;

		$options = get_option( 'pt_cs_modifiable', array() );
		if ( ! is_array( $options ) ) {
			$options = array();
		}

		// List of modifiable sidebars.
		if ( ! isset( $options['modifiable'] ) || ! is_array( $options['modifiable'] ) ) {

			// By default we make ALL theme sidebars replaceable.
			$all                   = self::get_sidebars( 'theme' );
			$options['modifiable'] = array_keys( $all );
			$need_update           = true;
		}

		$options = self::validate_options( $options );

		if ( $need_update ) {
			self::set_options( $options );
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
	 * Option-Key: 'pt_cs_modifiable' (1)
	 *
	 * @param  array $value The options array.
	 */
	public static function set_options( $value ) {

		// Permission check.
		if ( ! current_user_can( self::$cap_required ) ) {
			return false;
		}

		return update_option( 'pt_cs_modifiable', $value );
	}

	/**
	 * Removes invalid settings from the options array.
	 * Checks 'modifiable' array values.
	 *
	 * @param  array $data This array will be validated and returned.
	 * @return array
	 */
	public static function validate_options( $data ) {
		$data = ( is_object( $data ) ? (array) $data : $data );

		if ( ! is_array( $data ) ) {
			return array();
		}

		$valid   = array_keys( self::get_sidebars( 'theme' ) );
		$current = isset( $data['modifiable'] ) ? self::get_array( $data['modifiable'] ) : array();

		// Get all the sidebars that are modifiable AND exist.
		$modifiable         = array_intersect( $valid, $current );
		$data['modifiable'] = $modifiable;

		return $data;
	}

	/**
	 * Returns a list with all custom sidebars that were created by the user.
	 * Array of custom sidebars
	 *
	 * Option-Key: 'pt_cs_sidebars' (3)
	 */
	public static function get_custom_sidebars() {
		$sidebars = get_option( 'pt_cs_sidebars', array() );
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
	 * Option-Key: 'pt_cs_sidebars' (3)
	 *
	 * @param array $value Array with custom sidebars data.
	 */
	public static function set_custom_sidebars( $value ) {

		// Permission check.
		if ( ! current_user_can( self::$cap_required ) ) {
			return false;
		}

		return update_option( 'pt_cs_sidebars', $value );
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
	 * 1. Remove widget information for custom sidebars that no longer exist.
	 * 2. Add empty widget information for new custom sidebars.
	 * 3. Update sidebars_widgets option.
	 *
	 * Option-Key: 'sidebars_widgets' (4)
	 */
	public static function refresh_sidebar_widgets() {
		$widgetized_sidebars = self::get_sidebar_widgets();
		$cs_sidebars         = self::get_custom_sidebars();

		// 1. Remove widget information for custom sidebars that no longer exist.
		foreach ( $widgetized_sidebars as $id => $bar ) {
			if ( substr( $id, 0, strlen( self::$sidebar_prefix ) ) == self::$sidebar_prefix ) {
				$found = false;
				foreach ( $cs_sidebars as $csbar ) {
					if ( $csbar['id'] == $id ) {
						$found = true;
						break;
					}
				}
				if ( ! $found ) {
					unset( $widgetized_sidebars[ $id ] );
				}
			}
		}

		// 2. Add empty widget information for new custom sidebars.
		$all_ids = array_keys( $widgetized_sidebars );
		foreach ( $cs_sidebars as $cs ) {
			if ( ! in_array( $cs['id'], $all_ids ) ) {
				$widgetized_sidebars[ $cs['id'] ] = array();
			}
		}

		// 3. Update sidebars_widgets option.
		return update_option( 'sidebars_widgets', $widgetized_sidebars );
	}

	/**
	 * Returns the custom sidebar metadata of a single post.
	 *
	 * Meta-Key: '_pt_cs_replacements' (2)
	 *
	 * @param int $post_id ID of the post.
	 */
	public static function get_post_meta( $post_id ) {
		$data = get_post_meta( $post_id, '_pt_cs_replacements', true );
		if ( ! is_array( $data ) ) {
			$data = array();
		}
		return $data;
	}

	/**
	 * Saves custom sidebar metadata to a single post.
	 *
	 * Meta-Key: '_pt_cs_replacements' (2)
	 *
	 * @param int   $post_id ID of the post.
	 * @param array $data When array is empty the meta data will be deleted.
	 */
	public static function set_post_meta( $post_id, $data ) {
		if ( ! empty( $data ) ) {
			update_post_meta( $post_id, '_pt_cs_replacements', $data );
		} else {
			delete_post_meta( $post_id, '_pt_cs_replacements' );
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
		$allsidebars               = $wp_registered_sidebars;
		$result = $theme = $custom = array();


		// Remove inactive sidebars.
		foreach ( $allsidebars as $sb_id => $sidebar ) {
			if ( false !== strpos( $sidebar['class'], 'inactive-sidebar' ) ) {
				unset( $allsidebars[ $sb_id ] );
			}
		}

		// Sort custom and theme sidebars in the appropriate arrays.
		foreach ( $allsidebars as $key => $sb ) {
			if ( substr( $key, 0, strlen( self::$sidebar_prefix ) ) === self::$sidebar_prefix ) {
				$custom[ $key ] = $sb;
			}
			else {
				$theme[ $key ] = $sb;
			}
		}

		ksort( $allsidebars );
		if ( 'all' === $type ) {
			$result = $allsidebars;
		}
		else if ( 'cust' === $type ) {
			$result = $custom;
		}
		else if ( 'theme' === $type ) {
			$result = $theme;
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
		if ( empty( $id ) ) {
			return false;
		}

		// Get sidebars from specified type.
		$sidebars = self::get_sidebars( $type );

		if ( isset( $sidebars[ $id ] ) ) {
			return $sidebars[ $id ];
		}

		return false;
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

		// Use posttype name.
		if ( is_object( $posttype ) ) {
			$posttype = $posttype->name;
		}

		// Get post type names, that will be ignored.
		$ignored_types = get_post_types(
			array( 'public' => false ),
			'names'
		);
		$ignored_types[] = 'attachment';

		$supported = ! in_array( $posttype, $ignored_types );
		$supported = apply_filters( 'pt-cs/support_posttype', $supported, $posttype );

		return $supported;
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
		while ( 0 < ob_get_level() ) {
			ob_end_clean();
		}

		wp_send_json( (object) $obj );
	}

	/**
	 * Output HTML data and die()
	 *
	 * @param string $data HTML output string.
	 */
	protected static function plain_response( $data ) {

		// Flush any output that was made prior to this function call.
		while ( 0 < ob_get_level() ) {
			ob_end_clean();
		}

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
		do_action( 'pt-cs/ajax_request', $action );
	}

	/**
	 * Private clone method to prevent cloning of the instance of the *Singleton* instance.
	 *
	 * @return void
	 */
	private function __clone() {}

	/**
	 * Private unserialize method to prevent unserializing of the *Singleton* instance.
	 *
	 * @return void
	 */
	private function __wakeup() {}
}

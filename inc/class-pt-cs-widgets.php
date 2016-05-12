<?php
/**
 * Extends the Appearance -> Widgets section. *Singleton*
 *
 * @package pt-cs
 */

// Initialize this class in the main plugin class.
add_action( 'pt_cs_init', array( 'PT_CS_Widgets', 'instance' ) );

/**
 * Extends the widgets section to add the custom sidebars UI elements.
 */
class PT_CS_Widgets extends PT_CS_Main {

	/**
	 * Returns the singleton object.
	 */
	public static function instance() {
		static $inst = null;

		if ( null === $inst ) {
			$inst = new PT_CS_Widgets();
		}

		return $inst;
	}

	/**
	 * Constructor is private -> singleton.
	 */
	private function __construct() {
		if ( is_admin() ) {

			// Actions.
			add_action( 'widgets_admin_page', array( $this, 'widget_sidebar_content' ) );
			add_action( 'admin_head-widgets.php', array( $this, 'init_admin_head' ) );
		}
	}

	/**
	 * Adds the additional HTML code to the widgets section.
	 */
	public function widget_sidebar_content() {
		include PT_CS_VIEWS_DIR . 'widgets.php';
	}

	/**
	 * Initialize the admin-head for the widgets page.
	 *
	 * @param string $classes String of classes to add to admin body.
	 */
	public function init_admin_head( $classes ) {
		add_filter( 'admin_body_class', array( $this, 'admin_body_class' ) );
	}

	/**
	 * Return classes to add to the admin body tag.
	 *
	 * @param string $classes String of classes to add to admin body.
	 * @return string
	 */
	public function admin_body_class( $classes ) {
		$classes .= ' no-auto-init ';
		return $classes;
	}
}

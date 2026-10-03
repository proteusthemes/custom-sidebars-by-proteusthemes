<?php
/**
 * Extends the Appearance -> Widgets section. *Singleton*
 *
 * @package pt-cs
 */

// Initialize this class in the main plugin class.
add_action( 'pt-cs/init', array( 'PT_CS_Widgets', 'get_instance' ) );

/**
 * Extends the widgets section to add the custom sidebars UI elements.
 */
class PT_CS_Widgets extends PT_CS_Main {
	/**
	 * Reference to Singleton instance of this class.
	 *
	 * @var Singleton The reference to *Singleton* instance of this class
	 */
	private static $instance;

	/**
	 * Returns the *Singleton* instance of this class.
	 *
	 * @return PT_CS_Widgets the *Singleton* instance.
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

			// Actions.
			add_action( 'widgets_admin_page', array( $this, 'widget_sidebar_content' ) );
			add_action( 'admin_head-widgets.php', array( $this, 'init_admin_head' ) );
		}
	}

	/**
	 * Adds the additional HTML code to the widgets section.
	 */
	public function widget_sidebar_content() {
		if ( function_exists( 'wp_use_widgets_block_editor' ) && wp_use_widgets_block_editor() ) {
			$this->block_editor_notice();
		} else if ( self::is_accessibility_mode() ) {
			$this->accessibility_mode_notice();
		} else {
			include PT_CS_VIEWS_DIR . 'widgets.php';
		}
	}

	/**
	 * Admin notice if the block-based widgets screen is used.
	 */
	public function block_editor_notice() {
	?>
		<div class="notice notice-warning"><p>
			<?php esc_html_e( 'Creating and editing custom sidebars needs the Classic Widgets plugin.', 'custom-sidebars-by-proteusthemes' ); ?>
			<?php if ( current_user_can( 'install_plugins' ) ) : ?>
				<a href="<?php echo esc_url( network_admin_url( 'plugin-install.php?s=classic-widgets&tab=search&type=term' ) ); ?>"><?php esc_html_e( 'Install the Classic Widgets plugin', 'custom-sidebars-by-proteusthemes' ); ?></a>
			<?php endif; ?>
		</p></div>
	<?php
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

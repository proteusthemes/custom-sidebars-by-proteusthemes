<?php
/**
 * Empty widget.
 *
 * @package pt-cs
 */

/**
 * Class for empty widget which extends the WP_Widget class.
 */
class PT_CS_Empty_Widget extends WP_Widget {
	public function __construct() {
		parent::__construct( false, $name = 'CS Empty Widget' );
	}
	public function form( $instance ) {
		// Nothing, just a dummy plugin to display nothing.
	}
	public function update( $new_instance, $old_instance ) {
		// Nothing, just a dummy plugin to display nothing.
	}
	public function widget( $args, $instance ) {
		echo '';
	}
}

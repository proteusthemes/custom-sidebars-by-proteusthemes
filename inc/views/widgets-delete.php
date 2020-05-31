<?php
/**
 * Contents of the Delete-sidebar popup in the widgets screen.
 *
 * This file is included in widgets.php view.
 *
 * @package pt-cs
 */

?>

<div class="wpmui-form">
	<div>
	<?php printf( esc_html__( 'Please confirm that you want to delete the sidebar %1$s.', 'custom-sidebars-by-proteusthemes' ), '<strong class="name"></strong>' ); ?>
	</div>
	<div class="buttons">
		<button type="button" class="button-link btn-cancel"><?php esc_html_e( 'Cancel', 'custom-sidebars-by-proteusthemes' ); ?></button>
		<button type="button" class="button-primary btn-delete"><?php esc_html_e( 'Yes, delete it', 'custom-sidebars-by-proteusthemes' ); ?></button>
	</div>
</div>

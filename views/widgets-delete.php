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
	<?php printf( esc_html__( 'Please confirm that you want to delete the sidebar %1$s.', PT_CS_TD ), '<strong class="name"></strong>' ); ?>
	</div>
	<div class="buttons">
		<button type="button" class="button-link btn-cancel"><?php esc_html_e( 'Cancel', PT_CS_TD ); ?></button>
		<button type="button" class="button-primary btn-delete"><?php esc_html_e( 'Yes, delete it', PT_CS_TD ); ?></button>
	</div>
</div>

<?php
/**
 * Contents of the Delete-sidebar popup in the widgets screen.
 *
 * This file is included in widgets.php.
 */
?>

<div class="wpmui-form">
	<div>
	<?php _e(
		'Please confirm that you want to delete the sidebar <strong class="name"></strong>.', PT_CS_TD
	); ?>
	</div>
	<div class="buttons">
		<button type="button" class="button-link btn-cancel"><?php _e( 'Cancel', PT_CS_TD ); ?></button>
		<button type="button" class="button-primary btn-delete"><?php _e( 'Yes, delete it', PT_CS_TD ); ?></button>
	</div>
</div>
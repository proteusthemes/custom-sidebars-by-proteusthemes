<?php
/**
 * Contents of the Add/Edit sidebar popup in the widgets screen.
 *
 * This file is included in widgets.php.
 *
 * @package pt-cs
 */

?>

<form class="wpmui-form">
	<input type="hidden" name="do" value="save" />
	<input type="hidden" name="sb" id="csb-id" value="" />

	<div class="wpmui-grid-8 no-pad-top">
		<div class="col-3">
			<label for="csb-name"><?php esc_html_e( 'Name', PT_CS_TD ); ?></label>
			<input type="text" name="name" id="csb-name" maxlength="40" placeholder="<?php esc_html_e( 'Sidebar name here...', PT_CS_TD ); ?>" />
			<div class="hint"><?php esc_html_e( 'The name must be unique.', PT_CS_TD ); ?></div>
		</div>
		<div class="col-5">
			<label for="csb-description"><?php esc_html_e( 'Description', PT_CS_TD ); ?></label>
			<input type="text" name="description" id="csb-description" maxlength="200" placeholder="<?php esc_html_e( 'Sidebar description here...', PT_CS_TD ); ?>" />
		</div>
	</div>
	<hr class="csb-more-content" />
	<div class="wpmui-grid-8 csb-more-content">
		<div class="col-8 hint">
			<strong><?php esc_html_e( 'Caution:', PT_CS_TD ); ?></strong>
			<?php esc_html_e( 'Before-after title-widget properties define the html code that will wrap the widgets and their titles in the sidebars. Do not use these fields if you are not sure what you are doing, it can break the design of your site. Leave these fields blank to use the theme sidebars design.', PT_CS_TD ); ?>
		</div>
	</div>
	<div class="wpmui-grid-8 csb-more-content">
		<div class="col-4">
			<label for="csb-before-title"><?php esc_html_e( 'Before Title', PT_CS_TD ); ?></label>
			<textarea rows="4" name="before_title" id="csb-before-title"></textarea>
		</div>
		<div class="col-4">
			<label for="csb-after-title"><?php esc_html_e( 'After Title', PT_CS_TD ); ?></label>
			<textarea rows="4" name="after_title" id="csb-after-title"></textarea>
		</div>
	</div>
	<div class="wpmui-grid-8 csb-more-content">
		<div class="col-4">
			<label for="csb-before-widget"><?php esc_html_e( 'Before Widget', PT_CS_TD ); ?></label>
			<textarea rows="4" name="before_widget" id="csb-before-widget"></textarea>
		</div>
		<div class="col-4">
			<label for="csb-after-widget"><?php esc_html_e( 'After Widget', PT_CS_TD ); ?></label>
			<textarea rows="4" name="after_widget" id="csb-after-widget"></textarea>
		</div>
	</div>
	<div class="buttons">
		<label for="csb-more" class="wpmui-left">
			<input type="checkbox" id="csb-more" />
			<?php esc_html_e( 'Advanced - Edit custom wrapper code', PT_CS_TD ); ?>
		</label>

		<button type="button" class="button-link btn-cancel"><?php esc_html_e( 'Cancel', PT_CS_TD ); ?></button>
		<button type="button" class="button-primary btn-save"><?php esc_html_e( 'Create Sidebar', PT_CS_TD ); ?></button>
	</div>
</form>

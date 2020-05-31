<?php
/**
 * Updates the default widgets page of the admin area.
 * There are some HTML to be added for having all the functionality, so we
 * include it at the beginning of the page, and it's placed later via js.
 *
 * @package pt-cs
 */

?>

<div id="cs-widgets-extra">

	<?php

	/*
	============================================================================
	===== WIDGET head
	============================================================================
	*/
	?>
	<div id="cs-title-options">
		<h2><?php esc_html_e( 'Sidebars', 'custom-sidebars-by-proteusthemes' ); ?></h2>
		<div id="cs-options" class="csb cs-options">
			<button type="button" class="button button-primary cs-action btn-create-sidebar">
				<i class="dashicons dashicons-plus-alt"></i>
				<?php esc_html_e( 'Create a new sidebar', 'custom-sidebars-by-proteusthemes' ); ?>
			</button>
			<?php
			/**
			 * Show additional functions in the widget header.
			 */
			do_action( 'pt-cs/widget_header' );
			?>
		</div>
	</div>


	<?php

	/*
	============================================================================
	===== LANGUAGE
	============================================================================
	*/
	?>
	<script>
	csSidebarsData = {
		'title_edit':      "<?php esc_html_e( 'Edit [Sidebar]', 'custom-sidebars-by-proteusthemes' ); ?>",
		'title_new':       "<?php esc_html_e( 'New Custom Sidebar', 'custom-sidebars-by-proteusthemes' ); ?>",
		'btn_edit':        "<?php esc_html_e( 'Save Changes', 'custom-sidebars-by-proteusthemes' ); ?>",
		'btn_new':         "<?php esc_html_e( 'Create Sidebar', 'custom-sidebars-by-proteusthemes' ); ?>",
		'title_delete':    "<?php esc_html_e( 'Delete Sidebar', 'custom-sidebars-by-proteusthemes' ); ?>",
		'custom_sidebars': "<?php esc_html_e( 'Custom Sidebars', 'custom-sidebars-by-proteusthemes' ); ?>",
		'theme_sidebars':  "<?php esc_html_e( 'Theme Sidebars', 'custom-sidebars-by-proteusthemes' ); ?>",
		'ajax_error':      "<?php esc_html_e( 'Couldn\'t load data from WordPress...', 'custom-sidebars-by-proteusthemes' ); ?>",
		'lbl_replaceable': "<?php esc_html_e( 'This sidebar can be replaced on certain pages', 'custom-sidebars-by-proteusthemes' ); ?>",
		'replace_tip':     "<?php esc_html_e( 'Activate this option to replace the sidebar with one of your custom sidebars.', 'custom-sidebars-by-proteusthemes' ); ?>",
		'filter':          "<?php esc_html_e( 'Filter...', 'custom-sidebars-by-proteusthemes' ); ?>",
		'replaceable':     <?php echo json_encode( (object) PT_CS_Main::get_options( 'modifiable' ) ); ?>
	};
	</script>


	<?php

	/*
	============================================================================
	===== TOOLBAR for custom sidebars
	============================================================================
	*/
	?>
	<div class="cs-custom-sidebar cs-toolbar">
		<a
			class="cs-tool delete-sidebar"
			data-action="delete"
			href="#"
			title="<?php esc_html_e( 'Delete this sidebar.', 'custom-sidebars-by-proteusthemes' ); ?>"
			>
			<i class="dashicons dashicons-trash"></i>
		</a>
		<span class="cs-separator">|</span>
		<a
			class="cs-tool"
			data-action="edit"
			href="#"
			title="<?php esc_html_e( 'Edit this sidebar.', 'custom-sidebars-by-proteusthemes' ); ?>"
			>
			<?php esc_html_e( 'Edit', 'custom-sidebars-by-proteusthemes' ); ?>
		</a>
		<span class="cs-separator">|</span>
	</div>


	<?php /*
	============================================================================
	===== TOOLBAR for theme sidebars
	============================================================================
	*/ ?>
	<div class="cs-theme-sidebar cs-toolbar">
		<label
			for="cs-replaceable"
			class="cs-tool btn-replaceable"
			data-action="replaceable"
			data-on="<?php esc_html_e( 'This sidebar can be replaced on certain pages', 'custom-sidebars-by-proteusthemes' ); ?>"
			data-off="<?php esc_html_e( 'This sidebar will always be same on all pages', 'custom-sidebars-by-proteusthemes' ); ?>"
			>
			<span class="icon"></span>
			<input
				type="checkbox"
				id=""
				class="has-label chk-replaceable"
				/>
			<span class="is-label">
				<?php esc_html_e( 'Allow this sidebar to be replaced', 'custom-sidebars-by-proteusthemes' ); ?>
			</span>
		</label>
	</div>


	<?php

	/*
	============================================================================
	===== DELETE SIDEBAR confirmation
	============================================================================
	*/
	?>
	<div class="cs-delete">
	<?php include PT_CS_VIEWS_DIR . 'widgets-delete.php'; ?>
	</div>


	<?php

	/*
	============================================================================
	===== ADD/EDIT SIDEBAR
	============================================================================
	*/
	?>
	<div class="cs-editor">
	<?php include PT_CS_VIEWS_DIR . 'widgets-editor.php'; ?>
	</div>

 </div>

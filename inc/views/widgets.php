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
		<h2><?php esc_html_e( 'Sidebars', 'pt-cs' ); ?></h2>
		<div id="cs-options" class="csb cs-options">
			<button type="button" class="button button-primary cs-action btn-create-sidebar">
				<i class="dashicons dashicons-plus-alt"></i>
				<?php esc_html_e( 'Create a new sidebar', 'pt-cs' ); ?>
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
		'title_edit':      "<?php esc_html_e( 'Edit [Sidebar]', 'pt-cs' ); ?>",
		'title_new':       "<?php esc_html_e( 'New Custom Sidebar', 'pt-cs' ); ?>",
		'btn_edit':        "<?php esc_html_e( 'Save Changes', 'pt-cs' ); ?>",
		'btn_new':         "<?php esc_html_e( 'Create Sidebar', 'pt-cs' ); ?>",
		'title_delete':    "<?php esc_html_e( 'Delete Sidebar', 'pt-cs' ); ?>",
		'custom_sidebars': "<?php esc_html_e( 'Custom Sidebars', 'pt-cs' ); ?>",
		'theme_sidebars':  "<?php esc_html_e( 'Theme Sidebars', 'pt-cs' ); ?>",
		'ajax_error':      "<?php esc_html_e( 'Couldn\'t load data from WordPress...', 'pt-cs' ); ?>",
		'lbl_replaceable': "<?php esc_html_e( 'This sidebar can be replaced on certain pages', 'pt-cs' ); ?>",
		'replace_tip':     "<?php esc_html_e( 'Activate this option to replace the sidebar with one of your custom sidebars.', 'pt-cs' ); ?>",
		'filter':          "<?php esc_html_e( 'Filter...', 'pt-cs' ); ?>",
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
			title="<?php esc_html_e( 'Delete this sidebar.', 'pt-cs' ); ?>"
			>
			<i class="dashicons dashicons-trash"></i>
		</a>
		<span class="cs-separator">|</span>
		<a
			class="cs-tool"
			data-action="edit"
			href="#"
			title="<?php esc_html_e( 'Edit this sidebar.', 'pt-cs' ); ?>"
			>
			<?php esc_html_e( 'Edit', 'pt-cs' ); ?>
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
			data-on="<?php esc_html_e( 'This sidebar can be replaced on certain pages', 'pt-cs' ); ?>"
			data-off="<?php esc_html_e( 'This sidebar will always be same on all pages', 'pt-cs' ); ?>"
			>
			<span class="icon"></span>
			<input
				type="checkbox"
				id=""
				class="has-label chk-replaceable"
				/>
			<span class="is-label">
				<?php esc_html_e( 'Allow this sidebar to be replaced', 'pt-cs' ); ?>
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

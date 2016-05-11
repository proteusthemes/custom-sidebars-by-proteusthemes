<?php
/**
 * Updates the default widgets page of the admin area.
 * There are some HTML to be added for having all the functionality, so we
 * include it at the begining of the page, and it's placed later via js.
 */
?>

<div id="cs-widgets-extra">

	<?php /*
	============================================================================
	===== WIDGET head
	============================================================================
	*/ ?>
	<div id="cs-title-options">
		<h2><?php _e( 'Sidebars', PT_CS_TD ); ?></h2>
		<div id="cs-options" class="csb cs-options">
			<button type="button" class="button button-primary cs-action btn-create-sidebar">
				<i class="dashicons dashicons-plus-alt"></i>
				<?php _e( 'Create a new sidebar', PT_CS_TD ); ?>
			</button>
			<?php
			/**
			 * Show additional functions in the widget header.
			 */
			do_action( 'cs_widget_header' );
			?>
		</div>
	</div>


	<?php /*
	============================================================================
	===== LANGUAGE
	============================================================================
	*/ ?>
	<script>
	csSidebarsData = {
		'title_edit': "<?php _e( 'Edit [Sidebar]', PT_CS_TD ); ?>",
		'title_new': "<?php _e( 'New Custom Sidebar', PT_CS_TD ); ?>",
		'btn_edit': "<?php _e( 'Save Changes', PT_CS_TD ); ?>",
		'btn_new': "<?php _e( 'Create Sidebar', PT_CS_TD ); ?>",
		'title_delete': "<?php _e( 'Delete Sidebar', PT_CS_TD ); ?>",
		'title_location': "<?php _e( 'Define where you want this sidebar to appear.', PT_CS_TD ); ?>",
		'title_export': "<?php _e( 'Import / Export Sidebars', PT_CS_TD ); ?>",
		'custom_sidebars': "<?php _e( 'Custom Sidebars', PT_CS_TD ); ?>",
		'theme_sidebars': "<?php _e( 'Theme Sidebars', PT_CS_TD ); ?>",
		'ajax_error': "<?php _e( 'Couldn\'t load data from WordPress...', PT_CS_TD ); ?>",
		'lbl_replaceable': "<?php _e( 'This sidebar can be replaced on certain pages', PT_CS_TD ); ?>",
		'replace_tip': "<?php _e( 'Activate this option to replace the sidebar with one of your custom sidebars.', PT_CS_TD ); ?>",
		'filter': "<?php _e( 'Filter...', PT_CS_TD ); ?>",
		'replaceable': <?php echo json_encode( (object) PT_CS_Main::get_options( 'modifiable' ) ); ?>
	};
	</script>


	<?php /*
	============================================================================
	===== TOOLBAR for custom sidebars
	============================================================================
	*/ ?>
	<div class="cs-custom-sidebar cs-toolbar">
		<a
			class="cs-tool delete-sidebar"
			data-action="delete"
			href="#"
			title="<?php _e( 'Delete this sidebar.', PT_CS_TD ); ?>"
			>
			<i class="dashicons dashicons-trash"></i>
		</a>
		<span class="cs-separator">|</span>
		<a
			class="cs-tool"
			data-action="edit"
			href="#"
			title="<?php _e( 'Edit this sidebar.', PT_CS_TD ); ?>"
			>
			<?php _e( 'Edit', PT_CS_TD ); ?>
		</a>
		<span class="cs-separator">|</span>
		<a
			class="cs-tool"
			data-action="location"
			href="#"
			title="<?php _e( 'Where do you want to show the sidebar?', PT_CS_TD ); ?>"
			>
			<?php _e( 'Sidebar Location', PT_CS_TD ); ?>
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
			data-on="<?php _e( 'This sidebar can be replaced on certain pages', PT_CS_TD ); ?>"
			data-off="<?php _e( 'This sidebar will always be same on all pages', PT_CS_TD ); ?>"
			>
			<span class="icon"></span>
			<input
				type="checkbox"
				id=""
				class="has-label chk-replaceable"
				/>
			<span class="is-label">
				<?php _e( 'Allow this sidebar to be replaced', PT_CS_TD ); ?>
			</span>
		</label>
		<span class="cs-separator">|</span>
		<span class="">
			<a
				class="cs-tool"
				data-action="location"
				href="#"
				title="<?php _e( 'Where do you want to show the sidebar?', PT_CS_TD ); ?>"
				>
				<?php _e( 'Sidebar Location', PT_CS_TD ); ?>
			</a>
			<span class="cs-separator">|</span>
		</span>
	</div>


	<?php /*
	============================================================================
	===== DELETE SIDEBAR confirmation
	============================================================================
	*/ ?>
	<div class="cs-delete">
	<?php include PT_CS_VIEWS_DIR . 'widgets-delete.php'; ?>
	</div>


	<?php /*
	============================================================================
	===== ADD/EDIT SIDEBAR
	============================================================================
	*/ ?>
	<div class="cs-editor">
	<?php include PT_CS_VIEWS_DIR . 'widgets-editor.php'; ?>
	</div>


	<?php /*
	============================================================================
	===== LOCATION popup.
	============================================================================
	*/ ?>
	<div class="cs-location">
	<?php include PT_CS_VIEWS_DIR . 'widgets-location.php'; ?>
	</div>

 </div>

<?php
/**
 * Contents of the Location popup in the widgets screen.
 * User can define default locations where the custom sidebar will be used.
 *
 * This file is included in widgets.php.
 *
 * @package pt-cs
 */

$sidebars = PT_CS_Main::get_sidebars( 'theme' );

/**
 * Output the input fields to configure replacements for a single sidebar.
 *
 * @param array  $sidebar Details provided by PT_CS_Main::get_sidebar().
 * @param string $prefix Category specific prefix used for input field ID/Name.
 * @param string $cat_name Used in label: "Replace sidebar for <cat_name>".
 * @param string $class Optinal classname added to the wrapper element.
 */
function _show_replaceable( $sidebar, $prefix, $cat_name, $class = '' ) {
	$base_id = 'cs-' . $prefix;
	$inp_id = $base_id . '-' . $sidebar['id'];
	$inp_name = 'cs[' . $prefix . '][' . $sidebar['id'] . ']';
	$sb_id = $sidebar['id'];
	$class = (empty( $class ) ? '' : ' ' . $class);

	?>
	<div
		class="cs-replaceable <?php echo esc_attr( $sb_id . $class ); ?>"
		data-lbl-used="<?php esc_html_e( 'Replaced by another sidebar:', PT_CS_TD ); ?>"
		>
		<label for="<?php echo esc_attr( $inp_id ); ?>">
			<input type="checkbox"
				id="<?php echo esc_attr( $inp_id ); ?>"
				class="detail-toggle"
				/>
			<?php
			printf(
				esc_html__( 'As %1$s for selected %2$s', PT_CS_TD ),
				'<strong>' . esc_html( $sidebar['name'] ) . '</strong>',
				esc_html( $cat_name )
			);
			?>
		</label>
		<div class="details">
			<select
				class="cs-datalist <?php echo esc_attr( $base_id ); ?>"
				name="<?php echo esc_attr( $inp_name ); ?>[]"
				multiple="multiple"
				placeholder="<?php printf(
					esc_html__( 'Click here to pick available %1$s', PT_CS_TD ),
					esc_html( $cat_name )
				); ?>"
			>
			</select>
		</div>
	</div>
	<?php
}
?>

<form class="frm-location wpmui-form">
	<input type="hidden" name="do" value="set-location" />
	<input type="hidden" name="sb" class="sb-id" value="" />

	<div class="cs-title">
		<h3 class="no-pad-top">
			<span class="sb-name">...</span>
		</h3>
	</div>
	<p>
		<i class="dashicons dashicons-info light"></i>
		<?php
			esc_html_e( 'To attach this sidebar to a unique Post or Page please visit that Post or Page & set it up via the sidebars metabox.', PT_CS_TD );
		?>
	</p>

	<?php
	/**
	 * =========================================================================
	 * Box 1: SINGLE entries (single pages, categories)
	 */
	?>
	<div class="wpmui-box">
		<h3>
			<a href="#" class="toggle" title="<?php esc_html_e( 'Click to toggle' ); ?>"><br></a>
			<span><?php esc_html_e( 'For all Single Entries matching selected criteria', PT_CS_TD ); ?></span>
		</h3>
		<div class="inside">
			<p><?php esc_html_e( 'These replacements will be applied to every single post that matches a certain post type or category.', PT_CS_TD ); ?>

			<div class="cs-half">
			<?php
			/**
			 * ========== SINGLE -- Categories ========== *
			 */
			foreach ( $sidebars as $sb_id => $details ) {
				$cat_name = esc_html__( 'categories', PT_CS_TD );
				_show_replaceable( $details, 'cat', $cat_name );
			}
			?>
			</div>

			<div class="cs-half">
			<?php
			/**
			 * ========== SINGLE -- Post-Type ========== *
			 */
			foreach ( $sidebars as $sb_id => $details ) {
				$cat_name = esc_html__( 'Post Types', PT_CS_TD );
				_show_replaceable( $details, 'pt', $cat_name );
			}
			?>
			</div>

		</div>
	</div>

	<?php
	/**
	 * =========================================================================
	 * Box 2: ARCHIVE pages
	 */
	?>
	<div class="wpmui-box closed">
		<h3>
			<a href="#" class="toggle" title="<?php esc_html_e( 'Click to toggle' ); ?>"><br></a>
			<span><?php esc_html_e( 'For Archives', PT_CS_TD ); ?></span>
		</h3>
		<div class="inside">
			<p><?php esc_html_e( 'These replacements will be applied to Archive Type posts and pages.', PT_CS_TD ); ?>

			<h3 class="wpmui-tabs">
				<a href="#tab-arch" class="tab active"><?php esc_html_e( 'Archive Types', PT_CS_TD ); ?></a>
				<a href="#tab-catg" class="tab"><?php esc_html_e( 'Category Archives', PT_CS_TD ); ?></a>
			</h3>
			<div class="wpmui-tab-contents">
				<div id="tab-arch" class="tab active">
					<?php
					/**
					 * ========== ARCHIVE -- Special ========== *
					 */
					foreach ( $sidebars as $sb_id => $details ) {
						$cat_name = esc_html__( 'Archive Types', PT_CS_TD );
						_show_replaceable( $details, 'arc', $cat_name );
					}
					?>
				</div>
				<div id="tab-catg" class="tab">
					<?php
					/**
					 * ========== ARCHIVE -- Category ========== *
					 */
					foreach ( $sidebars as $sb_id => $details ) {
						$cat_name = esc_html__( 'Category Archives', PT_CS_TD );
						_show_replaceable( $details, 'arc-cat', $cat_name );
					}
					?>
				</div>
			</div>
		</div>
	</div>

	<div class="buttons">
		<button type="button" class="button-link btn-cancel"><?php esc_html_e( 'Cancel', PT_CS_TD ); ?></button>
		<button type="button" class="button-primary btn-save"><?php esc_html_e( 'Save Changes', PT_CS_TD ); ?></button>
	</div>
</form>

<?php
/**
 * Metabox inside posts/pages where user can define custom sidebars for an individual post.
 *
 * Uses:
 *   $selected
 *   $wp_registered_sidebars
 *   $post_id
 *
 * @package pt-cs
 */

$available = $wp_registered_sidebars;
$sidebars = PT_CS_Main::get_options( 'modifiable' );

$is_front = get_option( 'page_on_front' ) === $post_id;
$is_blog  = get_option( 'page_for_posts' ) === $post_id;
?>

<?php if ( $is_front || $is_blog ) : ?>
	<p>
		<strong><?php esc_html_e( 'To change the sidebar for static Front-Page or Posts-Page:', PT_CS_TD ); ?></strong>
		<ul>
			<li><?php printf( esc_html__( 'Go to the %1$sWidgets page%2$s', PT_CS_TD ), '<a href="' . esc_url( admin_url( 'widgets.php' ) ) . '">', '</a>' ); ?></li>
			<li><?php esc_html_e( 'Click on "Sidebar Location"', PT_CS_TD ); ?></li>
			<li><?php esc_html_e( 'Open the "Archive-Types" tab', PT_CS_TD ); ?></li>
			<li><?php esc_html_e( 'Choose "Front-Page" or "Post-Index"', PT_CS_TD ); ?></li>
		</ul>
	</p>

	<?php foreach ( $sidebars as $s ) : ?>
		<input type="hidden" name="cs_replacement_<?php echo esc_attr( $s ); ?>" value="<?php echo esc_attr( $selected[ $s ] ); ?>" />
	<?php endforeach; ?>

<?php else : ?>

	<p>
		<?php esc_html_e( 'Here you can replace the default sidebars. Simply select what sidebar you want to show for this post!', PT_CS_TD ); ?>
	</p>

	<?php if ( ! empty( $sidebars ) ) : ?>
		<?php foreach ( $sidebars as $s ) : ?>
			<?php $sb_name = $available[ $s ]['name']; ?>
			<p>
				<label for="cs_replacement_<?php echo esc_attr( $s ); ?>">
					<b><?php echo esc_html( $sb_name ); ?></b>:
				</label>
				<select name="cs_replacement_<?php echo esc_attr( $s ); ?>"
					id="cs_replacement_<?php echo esc_attr( $s ); ?>"
					class="cs-replacement-field <?php echo esc_attr( $s ); ?>">
					<option value=""></option>
					<?php foreach ( $available as $a ) : ?>
					<option value="<?php echo esc_attr( $a['id'] ); ?>" <?php selected( $selected[ $s ], $a['id'] ); ?>>
						<?php echo esc_html( $a['name'] ); ?>
					</option>
					<?php endforeach; ?>
				</select>
			</p>
		<?php
		endforeach;
	else :
	?>
		<p id="message" class="updated">
			<?php printf( esc_html__( 'All sidebars have been locked, you cannot replace them. Go to %1$sthe widgets page%2$s to unlock a sidebar.', PT_CS_TD ), '<a href="' . esc_url( admin_url( 'widgets.php' ) ) . '">', '</a>' ); ?>
		</p>
		<?php
	endif;

endif;

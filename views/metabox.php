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
?>

<p>
	<?php esc_html_e( 'Here you can replace the default sidebars. Simply select what sidebar you want to show for this post!', 'pt-cs' ); ?>
</p>

<?php if ( ! empty( $sidebars ) ) : ?>
	<?php foreach ( $sidebars as $s ) : ?>
		<?php $sb_name = $available[ $s ]['name']; ?>
		<p>
			<label for="pt_cs_replacement_<?php echo esc_attr( $s ); ?>">
				<b><?php echo esc_html( $sb_name ); ?></b>:
			</label>
			<select name="pt_cs_replacement_<?php echo esc_attr( $s ); ?>"
				id="pt_cs_replacement_<?php echo esc_attr( $s ); ?>"
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
		<?php printf( esc_html__( 'All sidebars have been locked, you cannot replace them. Go to %1$sthe widgets page%2$s to unlock a sidebar.', 'pt-cs' ), '<a href="' . esc_url( admin_url( 'widgets.php' ) ) . '">', '</a>' ); ?>
	</p>
	<?php
endif;

<?php
/**
 * Archives Widget Extended — Settings page template.
 *
 * @package Archives_Widget_Extended
 *
 * @var \AEX\Core\Admin $this Caller context (Admin instance).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap">
	<h1><?php esc_html_e( 'Archives Widget Extended', 'archives-extended' ); ?></h1>

	<p><?php esc_html_e( 'Archives Widget Extended is a drop-in replacement for the native Archives widget, with extra options for custom post types and CSS classes.', 'archives-extended' ); ?></p>

	<h2><?php esc_html_e( 'Using the widget', 'archives-extended' ); ?></h2>
	<p><?php esc_html_e( 'Add the "Archives Extended" widget to any widget area. With its defaults, it produces the same HTML as the stock WordPress Archives widget.', 'archives-extended' ); ?></p>
	<ul style="list-style: disc; margin-left: 1.5em;">
		<li>
			<strong><?php esc_html_e( 'Post type:', 'archives-extended' ); ?></strong>
			<?php esc_html_e( 'Pick which post type the archive list should cover. Only public post types with an archive page are listed.', 'archives-extended' ); ?>
		</li>
		<li>
			<strong><?php esc_html_e( 'Additional container classes:', 'archives-extended' ); ?></strong>
			<?php esc_html_e( 'CSS classes added to the widget root element (alongside widget_archive).', 'archives-extended' ); ?>
		</li>
		<li>
			<strong><?php esc_html_e( 'Additional list item classes:', 'archives-extended' ); ?></strong>
			<?php esc_html_e( 'CSS classes added to every <li> entry in the archives list.', 'archives-extended' ); ?>
		</li>
	</ul>

	<h2><?php esc_html_e( 'Credits', 'archives-extended' ); ?></h2>
	<p>
		<?php
		printf(
			/* translators: 1: plugin name, 2: author name. */
			esc_html__( '%1$s is developed by %2$s.', 'archives-extended' ),
			'<strong>' . esc_html__( 'Archives Widget Extended', 'archives-extended' ) . '</strong>',
			'<a href="https://archives-extended.univocal.co/">Univocal</a>'
		);
		?>
	</p>
	<p><?php esc_html_e( 'Released under the GPL v3 license.', 'archives-extended' ); ?></p>
</div>

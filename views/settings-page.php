<?php
/**
 * Archives Widget Extended — Settings page template.
 *
 * @package Archives_Widget_Extended
 *
 * @var \AEXWS\Core\Admin $this Caller context (Admin instance).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap">
	<h1><?php esc_html_e( 'Archives Widget Extended', 'archives-widget-extended' ); ?></h1>
	<div id="poststuff">
		<div class="aexws-box">
			<img class="aexws-banner-img" src="<?php echo esc_url( AEXWS_PLUGIN_URL . '/media/plugin/banner-1544x500.png' ); ?>" alt="<?php esc_attr_e( 'Archives Widget Extended Banner', 'archives-widget-extended' ); ?>">
		</div>
		<div id="post-body" class="metabox-holder columns-2">

			<!-- main content -->
			<div id="post-body-content">

				<div class="meta-box-sortables ui-sortable">

					<div class="postbox">

						<h2><span><?php esc_html_e( 'A drop-in replacement for the native Archives widget', 'archives-widget-extended' ); ?></span></h2>

						<div class="inside">
							<p>
							<?php
							echo wp_kses(
								__( '<strong>Archives Widget Extended</strong> is available either in <em>Blocks</em> and <em>Classic Editor</em> form, and fully replaces the native WordPress <em>Archive</em> Widget with extra options for custom post types and CSS classes.', 'archives-widget-extended' ),
								array(
									'a'      => array(
										'class' => array(),
										'href'  => array(),
										'rel'   => array(),
										'title' => array(),
									),
									'em'     => array(),
									'strong' => array(),
									'br'     => array(),
								)
							);
							?>
		</p>
							<p>
								<?php
								echo wp_kses(
									__( 'Source code is also available on <a target="_blank" rel="noopener noreferrer" href="https://github.com/wereunivocal/archives-extended">Github</a>.', 'archives-widget-extended' ),
									array(
										'a' => array(
											'class' => array(),
											'href'  => array(),
											'rel'   => array(),
											'title' => array(),
										),
									)
								);
								?>
							</p>
						</div>
						<!-- .inside -->

					</div>
					<div class="postbox">

						<h3><?php esc_html_e( 'Available features', 'archives-widget-extended' ); ?></h3>

						<div class="inside">
							<ul style="list-style: disc; margin-left: 1.5em;">
								<li>
									<strong><?php esc_html_e( 'Support for Custom Post Types:', 'archives-widget-extended' ); ?></strong>
									<?php esc_html_e( 'Works for any content type available in WordPress, as long as their archive is publicly available.', 'archives-widget-extended' ); ?>
								</li>
								<li>
									<strong><?php esc_html_e( 'Additional container and item classes:', 'archives-widget-extended' ); ?></strong>
									<?php
									echo wp_kses(
										__( 'Is it possible to further style the content of each widget by their respective <em>Additional Classes</em> field.', 'archives-widget-extended' ),
										array( 'em' => array() )
									);
									?>
								</li>
							</ul>
						</div>
						<!-- .inside -->

					</div>
					<div class="postbox">

						<h2><span><?php esc_html_e( 'About this plugin', 'archives-widget-extended' ); ?></span></h2>

						<div class="inside">
							<p>
								<?php
								echo wp_kses(
									sprintf(
									/* translators: 1: Plugin Name 2: Current Year 3: Maintainer Address and Name 4: Plugin Repository Link */
										esc_html__( '%1$s is Copyright %2$s %3$s. If you think this plugin is useful, please leave a %4$s on wordpress.org! Thank you!', 'archives-widget-extended' ),
										'<strong>' . esc_html__( 'Archives Widget Extended', 'archives-widget-extended' ) . '</strong>',
										gmdate( 'Y' ),
										'<a href="https://www.univocal.co/">Univocal</a>',
										'<strong><a target="_blank" rel="noopener noreferrer" href="https://univocal.co/aexws-review">5-stars review</a></strong>'
									),
									array(
										'a'      => array(
											'class' => array(),
											'href'  => array(),
											'rel'   => array(),
											'title' => array(),
										),
										'em'     => array(),
										'strong' => array(),
										'br'     => array(),
									)
								);
								?>
					</p>
				</div>
				<!-- .inside -->

			</div>
			<!-- .postbox -->

		</div>
		<!-- .meta-box-sortables .ui-sortable -->

	</div>
	<!-- post-body-content -->

	<!-- sidebar -->
	<div id="postbox-container-1" class="postbox-container">

		<div class="meta-box-sortables">

			<div class="postbox">

				<h2><span>
				<?php
				esc_html_e(
					'Donate',
					'archives-widget-extended'
				);
				?>
						</span></h2>

				<div class="inside">
					<p>
					<?php
					echo wp_kses(
						__( 'This plugin is free and open source with no "pro" or "lite" versions, and it will always remain so, but <strong>a donation is greatly appreciated</strong>.', 'archives-widget-extended' ),
						array(
							'strong' => array(),
							'br'     => array(),
						),
					);
					?>
					</p>
					<p><?php esc_html_e( 'You can buy me a coffee with the following link:', 'archives-widget-extended' ); ?></p>
					<a class="aexws-banner-img" href="https://www.buymeacoffee.com/univocal" target="_blank" rel="noopener noreferrer"><img src="<?php echo esc_url( AEXWS_PLUGIN_URL . '/media/plugin/default-yellow.png' ); ?>" alt="Buy Me a Coffee" style="height: 60px !important;width: 217px !important;"></a>
				</div>
				<!-- .inside -->

			</div>
			<!-- .postbox -->

		</div>
		<!-- .meta-box-sortables -->

	</div>
	<!-- #postbox-container-1 .postbox-container -->

</div>
<!-- #post-body .metabox-holder .columns-2 -->

<br class="clear">
</div>
<!-- #poststuff -->

</div> <!-- .wrap -->

<?php
/**
 * Legacy widget classname parity for the block widget container.
 *
 * A block placed in a classic sidebar is stored as a WP_Widget_Block instance,
 * which derives its container classname from the name of the first block it
 * holds (see WP_Widget_Block::get_dynamic_classname()). Core hardcodes
 * `core/archives` to `widget_block widget_archive`; we claim the same
 * classname through the `widget_block_dynamic_classname` filter so themes
 * styling `.widget_archive` survive the swap.
 *
 * These tests drive the real widget path — dynamic_sidebar() — rather than
 * render_block(), because the classname lives on the widget wrapper and the
 * block's own render callback never sees it.
 *
 * @package Archives_Widget_Extended
 */

namespace AEXWS\Tests\Blocks;

use WP_UnitTestCase;

class BlockWidgetClassnameTest extends WP_UnitTestCase {

	/**
	 * Sidebar id registered for the duration of each test.
	 */
	private const SIDEBAR_ID = 'aex-test-sidebar';

	public function set_up(): void {
		parent::set_up();

		register_sidebar(
			array(
				'id'            => self::SIDEBAR_ID,
				'name'          => 'AEX Test Sidebar',
				'before_widget' => '<aside id="%1$s" class="widget %2$s">',
				'after_widget'  => '</aside>',
			)
		);
	}

	public function tear_down(): void {
		unregister_sidebar( self::SIDEBAR_ID );
		parent::tear_down();
	}

	public function test_our_block_claims_the_archives_legacy_classname(): void {
		$html = $this->render_block_widget( '<!-- wp:aex/archives-widget-extended /-->' );

		$this->assertStringContainsString(
			'class="widget widget_block widget_archive"',
			$html,
			'Our block widget did not claim the widget_archive classname.'
		);
	}

	public function test_our_block_matches_core_archives_wrapper_classes(): void {
		$theirs = $this->wrapper_classes( $this->render_block_widget( '<!-- wp:archives /-->' ) );
		$ours   = $this->wrapper_classes( $this->render_block_widget( '<!-- wp:aex/archives-widget-extended /-->' ) );

		$this->assertSame(
			$theirs,
			$ours,
			'Block widget wrapper classes diverged from core/archives.'
		);
	}

	/**
	 * The filter is global — it runs for every block widget on the site, so a
	 * block we do not own must come back untouched.
	 */
	public function test_filter_leaves_other_blocks_untouched(): void {
		$html = $this->render_block_widget( '<!-- wp:paragraph --><p>Hello</p><!-- /wp:paragraph -->' );

		$this->assertSame(
			array( 'widget', 'widget_block', 'widget_text' ),
			$this->wrapper_classes( $html ),
			'Paragraph block widget lost its core classname.'
		);
	}

	/**
	 * Widget content holding no block at all yields a null block name; the
	 * filter must tolerate it rather than fataling on the string type hint.
	 */
	public function test_filter_tolerates_blockless_widget_content(): void {
		$html = $this->render_block_widget( 'Just some freeform text.' );

		$this->assertSame(
			array( 'widget', 'widget_block' ),
			$this->wrapper_classes( $html ),
			'Blockless widget content did not fall through to the bare classname.'
		);
	}

	/**
	 * Stores a single block widget holding $content and renders the sidebar.
	 *
	 * @param string $content Serialized block content for the widget instance.
	 */
	private function render_block_widget( string $content ): string {
		update_option( 'widget_block', array( 2 => array( 'content' => $content ) ) );
		wp_set_sidebars_widgets( array( self::SIDEBAR_ID => array( 'block-2' ) ) );

		// wp_get_sidebars_widgets() and the widget factory both memoize; reset
		// the registry so the options written above are the ones rendered.
		wp_get_sidebars_widgets();
		$GLOBALS['wp_widget_factory']->widgets['WP_Widget_Block']->_register();

		ob_start();
		dynamic_sidebar( self::SIDEBAR_ID );
		return (string) ob_get_clean();
	}

	/**
	 * Extracts the sorted class list of the widget wrapper element.
	 *
	 * @return string[] Class tokens in sorted order.
	 */
	private function wrapper_classes( string $html ): array {
		$this->assertMatchesRegularExpression(
			'/<aside[^>]*\sclass="([^"]*)"/',
			$html,
			'Expected a widget wrapper <aside> in the sidebar output.'
		);

		preg_match( '/<aside[^>]*\sclass="([^"]*)"/', $html, $matches );

		$classes = preg_split( '/\s+/', trim( $matches[1] ) );
		if ( false === $classes ) {
			$classes = array();
		}
		$classes = array_values( array_filter( $classes, static fn ( $token ) => '' !== $token ) );
		sort( $classes );

		return $classes;
	}
}

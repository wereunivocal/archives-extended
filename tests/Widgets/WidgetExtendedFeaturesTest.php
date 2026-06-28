<?php
/**
 * Extended-feature tests for AEXWS_Widget.
 *
 * Verifies the three plugin-specific options: post_type selection,
 * extra container classes, and per-item classes.
 *
 * @package Archives_Widget_Extended
 */

namespace AEXWS\Tests\Widgets;

use AEXWS\Tests\Support\ArchivesFixtures;
use AEXWS\Tests\Support\HtmlAssertions;
use AEXWS\Widgets\AEXWS_Widget;
use WP_UnitTestCase;

class WidgetExtendedFeaturesTest extends WP_UnitTestCase {

	use ArchivesFixtures;
	use HtmlAssertions;

	public static function set_up_before_class(): void {
		parent::set_up_before_class();
		self::register_test_cpt();
	}

	public static function tear_down_after_class(): void {
		self::unregister_test_cpt();
		parent::tear_down_after_class();
	}

	public function set_up(): void {
		parent::set_up();
		$this->seed_posts( 'post' );
		$this->seed_posts( self::aex_test_cpt_slug() );
	}

	public function test_custom_post_type_archives_are_rendered(): void {
		$html = $this->render(
			array( 'post_type' => self::aex_test_cpt_slug() )
		);

		// Pretty permalinks aren't enabled in the test env, so the CPT
		// shows up as a query parameter on the archive URL.
		$this->assertStringContainsString(
			'post_type=' . self::aex_test_cpt_slug(),
			$html,
			'Expected at least one CPT archive link to appear in the output.'
		);
	}

	public function test_extra_classes_are_appended_to_widget_root(): void {
		$html = $this->render(
			array( 'extra_classes' => 'custom-wrapper another-class' )
		);

		$this->assertRootElementHasClasses(
			array( 'widget_archive', 'custom-wrapper', 'another-class' ),
			$html
		);
	}

	public function test_item_classes_are_applied_to_every_list_item(): void {
		$html = $this->render(
			array( 'item_classes' => 'archive-link list-item' )
		);

		$this->assertEveryListItemHasClasses(
			array( 'archive-link', 'list-item' ),
			$html
		);
	}

	public function test_item_classes_are_not_leaked_outside_the_widget(): void {
		// Render once with item_classes, then once without.
		$this->render(
			array( 'item_classes' => 'should-not-leak' )
		);
		$html_after = $this->render( array() );

		$this->assertStringNotContainsString(
			'should-not-leak',
			$html_after,
			'item_classes filter leaked beyond the widget invocation.'
		);
	}

	/**
	 * @param array<string, mixed> $instance Extra instance settings to merge over defaults.
	 */
	private function render( array $instance ): string {
		$widget = new AEXWS_Widget();
		$widget->_set( 1 );

		$args = array(
			'before_widget' => '<section id="archives-1" class="widget widget_archive">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		);

		ob_start();
		$widget->widget( $args, $instance );
		return (string) ob_get_clean();
	}
}

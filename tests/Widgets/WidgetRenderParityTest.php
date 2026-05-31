<?php
/**
 * Output-parity tests: AEXWS_Widget vs the core WP_Widget_Archives.
 *
 * @package Archives_Widget_Extended
 */

namespace AEXWS\Tests\Widgets;

use AEXWS\Tests\Support\ArchivesFixtures;
use AEXWS\Tests\Support\HtmlAssertions;
use AEXWS\Widgets\AEXWS_Widget;
use WP_UnitTestCase;
use WP_Widget;
use WP_Widget_Archives;

class WidgetRenderParityTest extends WP_UnitTestCase {

	use ArchivesFixtures;
	use HtmlAssertions;

	public function set_up(): void {
		parent::set_up();
		$this->seed_posts( 'post' );
	}

	public function test_list_variant_matches_core_with_default_options(): void {
		$this->assert_parity( array() );
	}

	public function test_list_variant_matches_core_when_count_is_enabled(): void {
		$this->assert_parity( array( 'count' => 1 ) );
	}

	public function test_dropdown_variant_matches_core_with_default_options(): void {
		$this->assert_parity( array( 'dropdown' => 1 ) );
	}

	public function test_dropdown_variant_matches_core_when_count_is_enabled(): void {
		$this->assert_parity(
			array(
				'dropdown' => 1,
				'count'    => 1,
			)
		);
	}

	/**
	 * @param array<string, mixed> $instance Widget instance settings.
	 */
	private function assert_parity( array $instance ): void {
		$args = $this->sidebar_args();

		$theirs = $this->render_widget( new WP_Widget_Archives(), $args, $instance );
		$ours   = $this->render_widget( new AEXWS_Widget(), $args, $instance );

		// Our widget uses `archives_extended` as id_base; core uses `archives`.
		// That namespacing is the only legitimate structural difference for
		// the default-options output, so normalize it before structural compare.
		$ours_normalized = str_replace( 'archives_extended', 'archives', $ours );

		$this->assertHtmlStructurallyEquals(
			$theirs,
			$ours_normalized,
			'Output diverged from core WP_Widget_Archives'
		);
	}

	private function render_widget( WP_Widget $widget, array $args, array $instance ): string {
		$widget->_set( 1 );
		ob_start();
		$widget->widget( $args, $instance );
		return (string) ob_get_clean();
	}

	/**
	 * @return array{before_widget:string, after_widget:string, before_title:string, after_title:string}
	 */
	private function sidebar_args(): array {
		return array(
			'before_widget' => '<section id="archives-1" class="widget widget_archive">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		);
	}
}

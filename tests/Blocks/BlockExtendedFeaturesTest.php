<?php
/**
 * Extended-feature tests for aex/archives-extended block render.
 *
 * @package Archives_Widget_Extended
 */

namespace AEXWS\Tests\Blocks;

use AEXWS\Tests\Support\ArchivesFixtures;
use AEXWS\Tests\Support\HtmlAssertions;
use WP_Block_Type_Registry;
use WP_UnitTestCase;

class BlockExtendedFeaturesTest extends WP_UnitTestCase {

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
		$this->skip_if_block_not_registered();
		$this->seed_posts( 'post' );
		$this->seed_posts( self::aex_test_cpt_slug() );
	}

	public function test_custom_post_type_archives_are_rendered(): void {
		$html = $this->render(
			array( 'postType' => self::aex_test_cpt_slug() )
		);

		// Pretty permalinks aren't enabled in the test env, so the CPT
		// shows up as a query parameter on the archive URL.
		$this->assertStringContainsString(
			'post_type=' . self::aex_test_cpt_slug(),
			$html,
			'Expected at least one CPT archive link to appear in the block output.'
		);
	}

	public function test_extra_classes_are_appended_to_wrapper(): void {
		$html = $this->render(
			array( 'extraClasses' => 'block-wrapper-class second-class' )
		);

		$this->assertRootElementHasClasses(
			array(
				'wp-block-archives-list',
				'wp-block-aex-archives-extended-list',
				'block-wrapper-class',
				'second-class',
			),
			$html
		);
	}

	public function test_item_classes_are_applied_to_every_list_item(): void {
		$html = $this->render(
			array( 'itemClasses' => 'block-li-class another-li-class' )
		);

		$this->assertEveryListItemHasClasses(
			array( 'block-li-class', 'another-li-class' ),
			$html
		);
	}

	public function test_item_classes_are_not_leaked_outside_the_block(): void {
		$this->render(
			array( 'itemClasses' => 'should-not-leak-block' )
		);
		$html_after = $this->render( array() );

		$this->assertStringNotContainsString(
			'should-not-leak-block',
			$html_after,
			'Block item_classes filter leaked beyond the render invocation.'
		);
	}

	/**
	 * @param array<string, mixed> $attrs Block attributes.
	 */
	private function render( array $attrs ): string {
		return (string) render_block(
			array(
				'blockName'    => 'aex/archives-extended',
				'attrs'        => $attrs,
				'innerBlocks'  => array(),
				'innerHTML'    => '',
				'innerContent' => array(),
			)
		);
	}

	private function skip_if_block_not_registered(): void {
		if ( ! WP_Block_Type_Registry::get_instance()->is_registered( 'aex/archives-extended' ) ) {
			$this->markTestSkipped(
				'Block aex/archives-extended is not registered. '
				. 'Run `npm install && npm run build` inside Block/archives-extended/ before running this suite.'
			);
		}
	}
}

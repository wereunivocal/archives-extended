<?php
/**
 * Output-parity tests: aex/archives-extended block vs core/archives.
 *
 * The wrapper element on our block deliberately carries an extra
 * `wp-block-aex-archives-extended-*` class, and the dropdown ID is
 * namespaced under our slug. Both are normalized away before comparing
 * structurally with core's output.
 *
 * @package Archives_Widget_Extended
 */

namespace AEXWS\Tests\Blocks;

use AEXWS\Tests\Support\ArchivesFixtures;
use AEXWS\Tests\Support\HtmlAssertions;
use WP_Block_Type_Registry;
use WP_UnitTestCase;

class BlockRenderParityTest extends WP_UnitTestCase {

	use ArchivesFixtures;
	use HtmlAssertions;

	public function set_up(): void {
		parent::set_up();
		$this->skip_if_block_not_registered();
		$this->seed_posts( 'post' );
	}

	public function test_list_variant_matches_core_with_default_options(): void {
		$this->assert_parity( array() );
	}

	public function test_list_variant_matches_core_when_count_is_enabled(): void {
		$this->assert_parity( array( 'showPostCounts' => true ) );
	}

	public function test_dropdown_variant_matches_core_with_default_options(): void {
		$this->assert_parity( array( 'displayAsDropdown' => true ) );
	}

	public function test_dropdown_variant_matches_core_when_count_is_enabled(): void {
		$this->assert_parity(
			array(
				'displayAsDropdown' => true,
				'showPostCounts'    => true,
			)
		);
	}

	/**
	 * @param array<string, mixed> $attrs Block attributes shared between the two renders.
	 */
	private function assert_parity( array $attrs ): void {
		$theirs = $this->render_block_named( 'core/archives', $attrs );
		$ours   = $this->render_block_named( 'aex/archives-extended', $attrs );

		$ours_normalized = $this->normalize_namespace( $ours );

		// Numeric suffixes on `wp-block-archives-N` differ between renders
		// because `wp_unique_id()` is global. Strip them to a stable placeholder.
		$theirs_normalized = $this->normalize_unique_ids( $theirs );
		$ours_normalized   = $this->normalize_unique_ids( $ours_normalized );

		$this->assertHtmlStructurallyEquals(
			$theirs_normalized,
			$ours_normalized,
			'Block output diverged from core/archives'
		);
	}

	/**
	 * Renders a block by name with the supplied attributes.
	 *
	 * @param string               $name  Fully-qualified block name (e.g. `core/archives`).
	 * @param array<string, mixed> $attrs Block attributes.
	 */
	private function render_block_named( string $name, array $attrs ): string {
		return (string) render_block(
			array(
				'blockName'    => $name,
				'attrs'        => $attrs,
				'innerBlocks'  => array(),
				'innerHTML'    => '',
				'innerContent' => array(),
			)
		);
	}

	private function normalize_namespace( string $html ): string {
		// Drop the `-list` / `-dropdown` suffix variant the wrapper carries.
		$html = (string) preg_replace(
			'/\s?wp-block-aex-archives-extended-(?:list|dropdown)\b/',
			'',
			$html
		);
		// Rewrite our id namespace to core's.
		return (string) str_replace(
			'wp-block-aex-archives-extended-',
			'wp-block-archives-',
			$html
		);
	}

	private function normalize_unique_ids( string $html ): string {
		return (string) preg_replace( '/wp-block-archives-\d+/', 'wp-block-archives-N', $html );
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

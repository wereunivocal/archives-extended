<?php
/**
 * Attribute validation tests for the aex/archives-widget-extended block.
 *
 * Exercises the private resolve_type / resolve_post_type / sanitize_class_list
 * methods indirectly, via render_block.
 *
 * @package Archives_Widget_Extended
 */

namespace AEXWS\Tests\Blocks;

use AEXWS\Tests\Support\ArchivesFixtures;
use AEXWS\Tests\Support\HtmlAssertions;
use WP_Block_Type_Registry;
use WP_UnitTestCase;

class BlockAttributeValidationTest extends WP_UnitTestCase {

	use ArchivesFixtures;
	use HtmlAssertions;

	public function set_up(): void {
		parent::set_up();
		$this->skip_if_block_not_registered();
		$this->seed_posts( 'post' );
	}

	public function test_unknown_post_type_falls_back_to_post(): void {
		$with_unknown = $this->render( array( 'postType' => 'this-cpt-does-not-exist' ) );
		$with_post    = $this->render( array( 'postType' => 'post' ) );

		$with_unknown_normalized = $this->normalize_unique_ids( $with_unknown );
		$with_post_normalized    = $this->normalize_unique_ids( $with_post );

		$this->assertSame(
			$with_post_normalized,
			$with_unknown_normalized,
			'An unknown postType should produce the same output as postType=post.'
		);
	}

	public function test_unknown_type_falls_back_to_monthly(): void {
		$with_unknown = $this->render( array( 'type' => 'not-a-real-type' ) );
		$with_monthly = $this->render( array( 'type' => 'monthly' ) );

		$this->assertSame(
			$this->normalize_unique_ids( $with_monthly ),
			$this->normalize_unique_ids( $with_unknown ),
			'An unknown type should produce the same output as type=monthly.'
		);
	}

	public function test_known_type_is_preserved(): void {
		$yearly  = $this->render( array( 'type' => 'yearly' ) );
		$monthly = $this->render( array( 'type' => 'monthly' ) );

		$this->assertNotSame(
			$this->normalize_unique_ids( $monthly ),
			$this->normalize_unique_ids( $yearly ),
			'A valid `type=yearly` should not produce the same output as `type=monthly`.'
		);
	}

	public function test_invalid_class_tokens_are_stripped(): void {
		$html = $this->render( array( 'extraClasses' => 'good !!! more-good <bad>' ) );

		$this->assertStringContainsString( 'good', $html );
		$this->assertStringContainsString( 'more-good', $html );
		$this->assertStringNotContainsString( '!!!', $html );
		$this->assertStringNotContainsString( '<bad>', $html );
	}

	public function test_duplicate_class_tokens_are_collapsed(): void {
		$html = $this->render( array( 'extraClasses' => 'dup dup dup-other dup' ) );

		$tokens = preg_split( '/\s+/', trim( $this->root_class_attribute( $html ) ) );
		$count  = count( array_filter( (array) $tokens, static fn ( $t ) => 'dup' === $t ) );

		$this->assertSame(
			1,
			$count,
			'Expected the wrapper class attribute to contain `dup` exactly once after de-duplication.'
		);
	}

	/**
	 * Renders the aex/archives-widget-extended block with the supplied attributes.
	 *
	 * @param array<string, mixed> $attrs Block attributes.
	 */
	private function render( array $attrs ): string {
		return (string) render_block(
			array(
				'blockName'    => 'aex/archives-widget-extended',
				'attrs'        => $attrs,
				'innerBlocks'  => array(),
				'innerHTML'    => '',
				'innerContent' => array(),
			)
		);
	}

	private function normalize_unique_ids( string $html ): string {
		return (string) preg_replace( '/wp-block-(?:aex-)?archives-(?:widget-extended-)?\d+/', 'wp-block-archives-N', $html );
	}

	private function root_class_attribute( string $html ): string {
		if ( preg_match( '/<[a-z]+[^>]*\sclass="([^"]*)"/i', $html, $matches ) ) {
			return (string) $matches[1];
		}
		return '';
	}

	private function skip_if_block_not_registered(): void {
		if ( ! WP_Block_Type_Registry::get_instance()->is_registered( 'aex/archives-widget-extended' ) ) {
			$this->markTestSkipped(
				'Block aex/archives-widget-extended is not registered. '
				. 'Run `npm install && npm run build` inside Block/archives-widget-extended/ before running this suite.'
			);
		}
	}
}

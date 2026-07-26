<?php
/**
 * Structural HTML comparison helpers for the Archives Widget Extended suite.
 *
 * @package Archives_Widget_Extended
 */

namespace AEXWS\Tests\Support;

use DOMAttr;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;

trait HtmlAssertions {

	/**
	 * Asserts two HTML fragments are structurally equivalent.
	 *
	 * Compares tag tree, attributes (class lists are compared as sets),
	 * and text content (whitespace-normalized). The bodies of <script>
	 * elements are intentionally ignored: their tag presence is asserted,
	 * but inline JS may differ in formatting between WP versions and
	 * between our renderer and core's.
	 */
	protected function assertHtmlStructurallyEquals( string $expected, string $actual, string $message = '' ): void {
		$expected_doc = $this->parse_html_fragment( $expected );
		$actual_doc   = $this->parse_html_fragment( $actual );

		$this->assert_dom_nodes_equal(
			$expected_doc->documentElement,
			$actual_doc->documentElement,
			'<root>',
			$message
		);
	}

	/**
	 * Asserts every <li> directly inside the first <ul> of $html carries every class in $classes.
	 *
	 * @param string[] $classes Expected class tokens.
	 */
	protected function assertEveryListItemHasClasses( array $classes, string $html, string $message = '' ): void {
		$doc = $this->parse_html_fragment( $html );
		$ul  = $doc->getElementsByTagName( 'ul' )->item( 0 );
		$this->assertNotNull( $ul, '' !== $message ? $message : 'Expected a <ul> in the rendered output.' );

		$items = 0;
		foreach ( $ul->childNodes as $child ) {
			if ( ! $child instanceof DOMElement || 'li' !== strtolower( $child->tagName ) ) {
				continue;
			}
			++$items;
			$item_classes = preg_split( '/\s+/', trim( (string) $child->getAttribute( 'class' ) ) );
			foreach ( $classes as $expected_class ) {
				$this->assertContains(
					$expected_class,
					$item_classes,
					'' !== $message ? $message : "Expected <li> to carry class \"{$expected_class}\"."
				);
			}
		}

		$this->assertGreaterThan(
			0,
			$items,
			'' !== $message ? $message : 'Expected at least one <li> in the rendered output.'
		);
	}

	/**
	 * Asserts the first element of $html carries every class in $classes.
	 *
	 * @param string[] $classes Expected class tokens.
	 */
	protected function assertRootElementHasClasses( array $classes, string $html, string $message = '' ): void {
		$doc  = $this->parse_html_fragment( $html );
		$root = $doc->documentElement->firstChild;

		while ( $root && ! $root instanceof DOMElement ) {
			$root = $root->nextSibling;
		}

		$this->assertNotNull(
			$root,
			'' !== $message ? $message : 'Expected a root element in the rendered output.'
		);

		$root_classes = preg_split( '/\s+/', trim( (string) $root->getAttribute( 'class' ) ) );
		foreach ( $classes as $expected_class ) {
			$this->assertContains(
				$expected_class,
				$root_classes,
				'' !== $message ? $message : "Expected root element to carry class \"{$expected_class}\"."
			);
		}
	}

	/**
	 * Strips inline <script>...</script> blocks from an HTML fragment.
	 *
	 * Core's archives dropdown emits an inline navigation script; the
	 * WordPress Plugin Directory disallows raw inline <script> output, so
	 * our renderers rely on an enqueued module instead and no longer carry
	 * the tag at all. Callers doing parity comparisons against core strip
	 * both sides with this before the structural compare.
	 */
	protected function strip_inline_scripts( string $html ): string {
		return (string) preg_replace( '#<script\b[^>]*>.*?</script>#s', '', $html );
	}

	/**
	 * Parses an HTML fragment into a DOMDocument with a synthetic <aex-root> wrapper.
	 *
	 * The wrapper lets us compare fragments with multiple top-level elements
	 * (e.g. a <nav> followed by a script) without DOMDocument adding an
	 * <html><body> tree of its own.
	 */
	private function parse_html_fragment( string $html ): DOMDocument {
		$doc                     = new DOMDocument();
		$doc->preserveWhiteSpace = false;
		$previous                = libxml_use_internal_errors( true );
		$doc->loadHTML(
			'<?xml encoding="UTF-8"?><aex-root>' . $html . '</aex-root>',
			LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED
		);
		libxml_clear_errors();
		libxml_use_internal_errors( $previous );
		return $doc;
	}

	private function assert_dom_nodes_equal( DOMNode $expected, DOMNode $actual, string $path, string $message ): void {
		$this->assertSame(
			$expected->nodeType,
			$actual->nodeType,
			$this->compose_message( $message, "node type mismatch at {$path}" )
		);

		if ( $expected instanceof DOMElement && $actual instanceof DOMElement ) {
			$tag = strtolower( $expected->tagName );
			$this->assertSame(
				$tag,
				strtolower( $actual->tagName ),
				$this->compose_message( $message, "tag mismatch at {$path}" )
			);
			$this->assert_dom_attributes_equal( $expected, $actual, "{$path}/{$tag}", $message );

			if ( 'script' === $tag ) {
				return;
			}

			$expected_children = $this->meaningful_children( $expected );
			$actual_children   = $this->meaningful_children( $actual );

			$this->assertCount(
				count( $expected_children ),
				$actual_children,
				$this->compose_message(
					$message,
					sprintf(
						'child count mismatch at %s/%s (expected %d, got %d)',
						$path,
						$tag,
						count( $expected_children ),
						count( $actual_children )
					)
				)
			);

			foreach ( $expected_children as $i => $expected_child ) {
				$this->assert_dom_nodes_equal( $expected_child, $actual_children[ $i ], "{$path}/{$tag}[{$i}]", $message );
			}
			return;
		}

		if ( $expected instanceof DOMText && $actual instanceof DOMText ) {
			$this->assertSame(
				$this->normalize_whitespace( $expected->nodeValue ),
				$this->normalize_whitespace( $actual->nodeValue ),
				$this->compose_message( $message, "text mismatch at {$path}" )
			);
		}
	}

	/**
	 * @return DOMNode[]
	 */
	private function meaningful_children( DOMNode $node ): array {
		$children = array();
		foreach ( $node->childNodes as $child ) {
			if ( $child instanceof DOMText && '' === $this->normalize_whitespace( $child->nodeValue ) ) {
				continue;
			}
			$children[] = $child;
		}
		return $children;
	}

	private function assert_dom_attributes_equal( DOMElement $expected, DOMElement $actual, string $path, string $message ): void {
		$expected_attrs = $this->normalize_attributes( $expected );
		$actual_attrs   = $this->normalize_attributes( $actual );

		$this->assertSame(
			$expected_attrs,
			$actual_attrs,
			$this->compose_message( $message, "attribute mismatch at {$path}" )
		);
	}

	/**
	 * @return array<string, string>
	 */
	private function normalize_attributes( DOMElement $element ): array {
		$attrs = array();
		foreach ( $element->attributes as $attr ) {
			if ( ! $attr instanceof DOMAttr ) {
				continue;
			}
			$name  = strtolower( $attr->name );
			$value = $attr->value;
			if ( 'class' === $name ) {
				$tokens = preg_split( '/\s+/', trim( $value ) );
				if ( false === $tokens ) {
					$tokens = array();
				}
				$tokens = array_filter( $tokens, static fn ( $t ) => '' !== $t );
				// Treat class as a set: dedupe so callers comparing class
				// lists don't fail on cosmetic duplicates introduced by
				// namespace normalization.
				$tokens = array_values( array_unique( $tokens ) );
				sort( $tokens );
				$value = implode( ' ', $tokens );
			}
			$attrs[ $name ] = $value;
		}
		ksort( $attrs );
		return $attrs;
	}

	private function normalize_whitespace( ?string $text ): string {
		if ( null === $text ) {
			return '';
		}
		return trim( (string) preg_replace( '/\s+/', ' ', $text ) );
	}

	private function compose_message( string $base, string $detail ): string {
		return '' === $base ? $detail : "{$base}\n{$detail}";
	}
}

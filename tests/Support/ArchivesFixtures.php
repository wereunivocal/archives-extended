<?php
/**
 * Shared fixtures for Archives Widget Extended tests.
 *
 * @package Archives_Widget_Extended
 */

namespace AEXWS\Tests\Support;

trait ArchivesFixtures {

	/**
	 * Slug used for the temporary custom post type registered during tests.
	 *
	 * Kept as a static method (not a const) so the trait stays compatible
	 * with PHP 8.1, which does not allow constants inside traits.
	 */
	protected static function aex_test_cpt_slug(): string {
		return 'aex_test_cpt';
	}

	/**
	 * Registers a public CPT with has_archive=true for use in extended-feature tests.
	 *
	 * Idempotent: calling twice is a no-op.
	 */
	protected static function register_test_cpt(): void {
		if ( post_type_exists( self::aex_test_cpt_slug() ) ) {
			return;
		}
		register_post_type(
			self::aex_test_cpt_slug(),
			array(
				'public'       => true,
				'has_archive'  => true,
				'label'        => 'AEX Test CPT',
				'labels'       => array(
					'name'          => 'AEX Test CPT',
					'singular_name' => 'AEX Test CPT',
				),
				'rewrite'      => array( 'slug' => 'aex-test-cpt' ),
				'show_in_rest' => true,
			)
		);
	}

	/**
	 * Removes the test CPT registered by `register_test_cpt()`.
	 */
	protected static function unregister_test_cpt(): void {
		if ( post_type_exists( self::aex_test_cpt_slug() ) ) {
			unregister_post_type( self::aex_test_cpt_slug() );
		}
	}

	/**
	 * Seeds one published post per month for the three supplied dates.
	 *
	 * The date list is intentionally explicit: `wp_get_archives()` groups by
	 * month, so spreading posts across three distinct months guarantees at
	 * least three list items in the archive output regardless of when the
	 * test runs.
	 *
	 * @param string $post_type Post type slug (must exist before calling).
	 * @return int[] Created post IDs in creation order.
	 */
	protected function seed_posts( string $post_type = 'post' ): array {
		$dates = array(
			'2024-01-15 10:00:00',
			'2024-02-15 10:00:00',
			'2024-03-15 10:00:00',
		);

		$ids = array();
		foreach ( $dates as $i => $date ) {
			$ids[] = (int) self::factory()->post->create(
				array(
					'post_type'     => $post_type,
					'post_status'   => 'publish',
					'post_title'    => sprintf( '%s seed %d', $post_type, $i + 1 ),
					'post_date'     => $date,
					'post_date_gmt' => $date,
				)
			);
		}
		return $ids;
	}
}

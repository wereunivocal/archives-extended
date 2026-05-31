<?php
/**
 * PHPUnit bootstrap for the Archives Widget Extended plugin.
 *
 * Designed to run inside the `@wordpress/env` tests-cli container, against the
 * `wp-phpunit/wp-phpunit` test framework provided via Composer.
 *
 * @package Archives_Widget_Extended
 */

require_once dirname( __DIR__ ) . '/vendor/autoload.php';

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = dirname( __DIR__ ) . '/vendor/wp-phpunit/wp-phpunit';
}

if ( ! file_exists( "{$_tests_dir}/includes/functions.php" ) ) {
	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite -- WP_Filesystem isn't loaded yet at bootstrap time.
	fwrite( STDERR, "Could not find {$_tests_dir}/includes/functions.php — did you run `composer install`?\n" );
	exit( 1 );
}

if ( ! getenv( 'WP_TESTS_CONFIG_FILE_PATH' ) ) {
	// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv -- Required to hand off the config path to wp-phpunit's bootstrap.
	putenv( 'WP_TESTS_CONFIG_FILE_PATH=' . __DIR__ . '/wp-tests-config.php' );
}

if ( ! getenv( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH' ) ) {
	// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv -- Required to hand off the polyfills path to wp-phpunit's bootstrap.
	putenv( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH=' . dirname( __DIR__ ) . '/vendor/yoast/phpunit-polyfills' );
}

require_once "{$_tests_dir}/includes/functions.php";

tests_add_filter(
	'muplugins_loaded',
	static function (): void {
		require dirname( __DIR__ ) . '/archives-extended.php';
	}
);

require "{$_tests_dir}/includes/bootstrap.php";

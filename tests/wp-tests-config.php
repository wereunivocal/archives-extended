<?php
/**
 * WordPress test config for the Archives Widget Extended suite.
 *
 * Resolves all settings from environment variables so the same file works
 * inside the `@wordpress/env` tests-cli container, on the GitHub Actions
 * runner, or on any future remote test box.
 *
 * Defaults match the `tests-cli` container defaults exposed by `@wordpress/env`.
 *
 * @package Archives_Widget_Extended
 */

/**
 * Returns the env var value or the supplied fallback when unset/empty.
 *
 * Equivalent to `getenv($name) ?: $fallback` but avoids the short ternary
 * banned by `Universal.Operators.DisallowShortTernary`.
 */
function aex_env_or( string $name, string $fallback ): string {
	$value = getenv( $name );
	if ( false === $value || '' === $value ) {
		return $fallback;
	}
	return $value;
}

define( 'ABSPATH', aex_env_or( 'WP_ABSPATH', '/var/www/html/' ) );

define( 'DB_NAME', aex_env_or( 'WORDPRESS_DB_NAME', 'tests-wordpress' ) );
define( 'DB_USER', aex_env_or( 'WORDPRESS_DB_USER', 'root' ) );
define( 'DB_PASSWORD', aex_env_or( 'WORDPRESS_DB_PASSWORD', 'password' ) );
define( 'DB_HOST', aex_env_or( 'WORDPRESS_DB_HOST', 'tests-mysql' ) );
define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );

$table_prefix = 'wptests_'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

define( 'WP_TESTS_DOMAIN', 'example.org' );
define( 'WP_TESTS_EMAIL', 'admin@example.org' );
define( 'WP_TESTS_TITLE', 'Archives Widget Extended Tests' );

define( 'WP_PHP_BINARY', 'php' );

define( 'WPLANG', '' );

define( 'WP_DEFAULT_THEME', 'default' );

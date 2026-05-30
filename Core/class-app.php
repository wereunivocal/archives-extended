<?php
/**
 * Core functions entry point.
 *
 * @package Archives_Widget_Extended
 */

namespace AEXWS\Core;

use AEXWS\Blocks\ArchivesExtendedBlock;
use AEXWS\Widgets\AEXWS_Widget;

/**
 *  Main initialization class
 */
class App {

	/**
	 *
	 * private singleton instance
	 *
	 * @var App|null
	 */
	private static ?App $instance = null;

	/**
	 *
	 * Singleton utility method
	 *
	 * @return self|null
	 */
	public static function get_instance(): ?App {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	/**
	 * Private constructor to enforce singleton
	 * fixme: is this still required in PHP 8.1?
	 */
	private function __construct() {}

	/**
	 * Resolve a Vite entry point to its built JS and CSS URLs via the manifest.
	 *
	 * @param string $entry The source entry path, e.g. 'assets/scripts/main.js'.
	 * @return array{js: string, css: string[]|null}|null Null when the manifest or entry is missing.
	 */
	public static function vite_asset( string $entry ): ?array {
		static $manifest = null;

		if ( null === $manifest ) {
			$path     = AEXWS_PLUGIN_DIR . 'dist/.vite/manifest.json';
			$manifest = file_exists( $path )
				? json_decode( file_get_contents( $path ), true ) ?: array() // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents, Universal.Operators.DisallowShortTernary.Found -- Local Vite manifest, not a remote URL.
				: array();
		}

		if ( ! isset( $manifest[ $entry ] ) ) {
			return null;
		}

		$item    = $manifest[ $entry ];
		$css_all = array();

		// Collect CSS from imported chunks first (so they load before the entry CSS).
		if ( ! empty( $item['imports'] ) ) {
			foreach ( $item['imports'] as $import_key ) {
				if ( isset( $manifest[ $import_key ]['css'] ) ) {
					foreach ( $manifest[ $import_key ]['css'] as $css_file ) {
						$css_all[] = AEXWS_PLUGIN_URL . 'dist/' . $css_file;
					}
				}
			}
		}

		// Collect CSS from the entry itself.
		if ( ! empty( $item['css'] ) ) {
			foreach ( $item['css'] as $css_file ) {
				$css_all[] = AEXWS_PLUGIN_URL . 'dist/' . $css_file;
			}
		}

		return array(
			'js'  => AEXWS_PLUGIN_URL . 'dist/' . $item['file'],
			'css' => ! empty( $css_all ) ? $css_all : null,
		);
	}

	/**
	 * Initialize the plugin
	 */
	public function init(): void {
		add_action(
			'widgets_init',
			static function (): void {
				register_widget( AEXWS_Widget::class );
			}
		);

		( new ArchivesExtendedBlock() )->init();
		( new Admin() )->init();
	}
}

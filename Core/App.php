<?php

namespace AEX\Core;

use AEX\Blocks\ArchivesExtendedBlock;
use AEX\Core\Admin;
use AEX\Widgets\ArchivesExtended;

class App {

	private static $instance = null;

	public static function getInstance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		// Private constructor to enforce singleton
	}

	/**
	 * Resolve a Vite entry point to its built JS and CSS URLs via the manifest.
	 *
	 * Collects CSS from the entry itself and from any shared chunks it imports,
	 * so that code-split dependencies (e.g. MapLibre) have their styles included.
	 *
	 * @param string $entry The source entry path, e.g. 'assets/scripts/main.js'.
	 * @return array{js: string, css: string[]|null}|null Null when the manifest or entry is missing.
	 */
	public static function vite_asset( string $entry ): ?array {
		static $manifest = null;

		if ( null === $manifest ) {
			$path     = AEX_PLUGIN_DIR . 'dist/.vite/manifest.json';
			$manifest = file_exists( $path )
				? json_decode( file_get_contents( $path ), true ) ?: array()
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
						$css_all[] = AEX_PLUGIN_URL . 'dist/' . $css_file;
					}
				}
			}
		}

		// Collect CSS from the entry itself.
		if ( ! empty( $item['css'] ) ) {
			foreach ( $item['css'] as $css_file ) {
				$css_all[] = AEX_PLUGIN_URL . 'dist/' . $css_file;
			}
		}

		return array(
			'js'  => AEX_PLUGIN_URL . 'dist/' . $item['file'],
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
				register_widget( ArchivesExtended::class );
			}
		);

		( new ArchivesExtendedBlock() )->init();
		( new Admin() )->init();
	}
}

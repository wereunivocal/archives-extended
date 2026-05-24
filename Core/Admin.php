<?php
/**
 * Admin menu and settings page wiring.
 *
 * @package Archives_Widget_Extended
 */

namespace AEX\Core;

/**
 * Admin menu handler for the Archives Widget Extended settings page.
 */
class Admin {

	/**
	 * Hook suffix returned by add_options_page(), used to gate asset enqueues.
	 *
	 * @var string|null
	 */
	private ?string $page_hook = null;

	/**
	 * Registers admin-side hooks. No-op outside the admin context.
	 */
	public function init(): void {
		if ( ! is_admin() ) {
			return;
		}

		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_settings_assets' ) );
	}

	/**
	 * Registers the Settings → Archives Extended submenu page.
	 */
	public function add_admin_menu(): void {
		$this->page_hook = add_options_page(
			__( 'Archives Widget Extended', 'archives-extended' ),
			__( 'Archives Extended', 'archives-extended' ),
			'manage_options',
			AEX_PFX,
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Renders the settings page by including the view template.
	 */
	public function render_settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		include AEX_PLUGIN_DIR . 'views/settings-page.php';
	}

	/**
	 * Enqueues the admin asset bundle for the settings page only.
	 *
	 * @param string $hook_suffix Current admin page hook suffix.
	 */
	public function enqueue_settings_assets( string $hook_suffix ): void {
		if ( null === $this->page_hook || $hook_suffix !== $this->page_hook ) {
			return;
		}

		$admin = App::vite_asset( 'assets/scripts/admin.js' );
		if ( ! $admin ) {
			return;
		}

		wp_enqueue_script_module( 'aex-admin', $admin['js'], array(), AEX_VERSION );
		if ( $admin['css'] ) {
			foreach ( $admin['css'] as $index => $css_url ) {
				wp_enqueue_style( 'aex-admin-' . $index, $css_url, array(), AEX_VERSION );
			}
		}
	}
}

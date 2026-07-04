<?php
/**
 * Admin menu and settings page wiring.
 *
 * @package Archives_Widget_Extended
 */

namespace AEXWS\Core;

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
	 * Registers plugin hooks for both frontend and admin contexts.
	 */
	public function init(): void {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );

		if ( ! is_admin() ) {
			return;
		}

		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_settings_assets' ) );
	}

	/**
	 * Registers the Settings → Archives Widget Extended submenu page.
	 */
	public function add_admin_menu(): void {
		$this->page_hook = add_options_page(
			__( 'Archives Widget Extended', 'archives-extended' ),
			__( 'Archives Widget Extended', 'archives-extended' ),
			'manage_options',
			AEXWS_PFX,
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

		include AEXWS_PLUGIN_DIR . 'views/settings-page.php';
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

		wp_enqueue_script_module( 'aex-admin', $admin['js'], array(), AEXWS_VERSION );
		if ( $admin['css'] ) {
			foreach ( $admin['css'] as $index => $css_url ) {
				wp_enqueue_style( 'aex-admin-' . $index, $css_url, array(), AEXWS_VERSION );
			}
		}
	}

	/**
	 * Enqueues the admin asset bundle on the frontend when the widget is active.
	 */
	public function enqueue_frontend_assets(): void {
		if ( ! is_active_widget( false, false, 'archives_extended' ) ) {
			return;
		}

		$asset = App::vite_asset( 'assets/scripts/admin.js' );
		if ( ! $asset ) {
			return;
		}

		wp_enqueue_script_module( 'aex-admin', $asset['js'], array(), AEXWS_VERSION );
	}
}

<?php
/**
 * Plugin Name: Archives Widget Extended
 * Plugin URI: https://archives-widget-extended.univocal.co/
 * Description: An extended version of the Archives Widget, with additional options for custom Post Types and additional CSS Classes
 * Version: 1.0.3
 * Author: Univocal
 * License: GPL v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: archives-widget-extended
 * Requires at least: 6.8
 * Tested up to: 7.0
 * Requires PHP: 8.1
 *
 * @package Archives_Widget_Extended
 */

use AEXWS\Core\App;


// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants
define( 'AEXWS_VERSION', '1.0.3' );
define( 'AEXWS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'AEXWS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'AEXWS_PFX', 'archives-widget-extended' );

// Autoload dependencies
if ( file_exists( AEXWS_PLUGIN_DIR . 'vendor/autoload.php' ) ) {
	require_once AEXWS_PLUGIN_DIR . 'vendor/autoload.php';
}

// Initialize the plugin
add_action(
	'plugins_loaded',
	function () {
		$plugin = App::get_instance();
		$plugin->init();
	}
);

<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://pixelwatt.com
 * @since             1.0.0-beta2
 * @package           Cmb2_Roadway_Segments
 *
 * @wordpress-plugin
 * Plugin Name:       CMB2 Roadway Segments
 * Plugin URI:        https://robclark.io
 * Description:       This plugin adds a new CMB2 fieldtype for drawing roadway segments onto a map and provides a shortcode for display. This plugin requires CMB2.
 * Version:           1.0.0-beta2
 * Author:            Rob Clark
 * Author URI:        https://robclark.io
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       cmb2-roadway-segments
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-cmb2-roadway-segments-activator.php
 */
function activate_cmb2_roadway_segments() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-cmb2-roadway-segments-activator.php';
	Cmb2_Roadway_Segments_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-cmb2-roadway-segments-deactivator.php
 */
function deactivate_cmb2_roadway_segments() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-cmb2-roadway-segments-deactivator.php';
	Cmb2_Roadway_Segments_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_cmb2_roadway_segments' );
register_deactivation_hook( __FILE__, 'deactivate_cmb2_roadway_segments' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-cmb2-roadway-segments.php';

/**
 * Include template tags
 */
include_once( plugin_dir_path( __FILE__ ) . 'public/template-tags.php' );

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_cmb2_roadway_segments() {

	$plugin = new Cmb2_Roadway_Segments();
	$plugin->run();

}
run_cmb2_roadway_segments();

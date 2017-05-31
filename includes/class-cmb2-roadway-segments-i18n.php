<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://pixelwatt.com
 * @since      1.0.0
 *
 * @package    Cmb2_Roadway_Segments
 * @subpackage Cmb2_Roadway_Segments/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Cmb2_Roadway_Segments
 * @subpackage Cmb2_Roadway_Segments/includes
 * @author     PixelWatt <hello@pixelwatt.com>
 */
class Cmb2_Roadway_Segments_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'cmb2-roadway-segments',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}

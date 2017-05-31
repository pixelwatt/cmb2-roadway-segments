=== CMB2 Roadway Segments ===
Contributors: pixelwatt
Donate link: https://pixelwatt.com
Tags: cmb2, maps
Requires at least: 4.7.5
Tested up to: 4.7.5
Stable tag: 0.9.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin adds a new CMB2 fieldtype for drawing roadway segments onto a map and provides a shortcode for display.

== Description ==

This plugin extends CMB2 by adding a new fieldtype called 'snapmap' that allows the user to highlight segments of roadway using Google's snap-to-road API. It also allows the user to drop a map pin in the same field. This data can then be displayed on the front-end using the `[snapmap]` shortcode.

This plugin requires CMB2 and a Google Maps API key.

The Google Maps API key must have access to the following APIs:
* Google Maps JavaScript API
* Google Maps Roads API
* Google Places API Web Service 

= Features =

* Allows developers to integrate Google Maps with Wordpress via CMB2.
* Allows site admins to provide custom maps styles and specify which map controls to include on the front-end.
* Provides an option to exclude the Google Maps JS API if already loaded.

= Adding to CMB2 =

`$cmb_demo->add_field( array(
	'name' => 'Segment',
    	'desc' => '',
    	'id' => $prefix . 'segment',
    'limit_drawing' => false,
    	'type' => 'snapmap',
) );`

...or to only allow map markers (disable segments), set 'limit_drawing' to true...

`$cmb_demo->add_field( array(
	'name' => 'Segment',
    	'desc' => '',
    	'id' => $prefix . 'segment',
    'limit_drawing' => true,
    	'type' => 'snapmap',
) );`

= Displaying =

Use the `[snapmap]` shortcode to display the map on the front-end. The shortcode accepts the following arguments:

* **width:** Specify a width for the map (defaults to 100%)
* **height:** Specify a height for the map (defaults to 400px)
* **zoom:** Specify the map zoom level to start on (defaults to 16)

== Installation ==

1. Upload `cmb2-roadway-segments.php` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Add a snapmap field to a CMB2 metabox.
1. Place `[snapmap]` anywhere in a post's content to render a map.

== Frequently Asked Questions ==

= Does this plugin support custom map markers? =

Not yet, but it will.

= Can I display a map of all map markers? =

Also not yet, but this is an upcoming feature.

== Changelog ==

= 0.9.0 =
* Initial release.
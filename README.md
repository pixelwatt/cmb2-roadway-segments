# CMB2 Roadway Segments

![CMB2 Roadway Segments Banner](https://pixelwatt.com/assets/cmb2rs_banner.jpg)

This plugin adds a new CMB2 fieldtype for drawing roadway segments onto a map and provides a shortcode for display.

![In Action](https://pixelwatt.com/assets/snapmap.gif)

## Description

This plugin extends CMB2 by adding a new fieldtype called 'snapmap' that allows the user to highlight segments of roadway using Google's snap-to-road API. It also allows the user to drop a map pin in the same field. This data can then be displayed on the front-end using the `[snapmap]` shortcode.

This plugin requires CMB2 and a Google Maps API key.

The Google Maps API key must have access to the following APIs:
* Google Maps JavaScript API
* Google Maps Roads API
* Google Places API Web Service 

### Features

* Allows developers to integrate Google Maps with Wordpress via CMB2.
* Allows site admins to provide custom maps styles and specify which map controls to include on the front-end.
* Provides an option to exclude the Google Maps JS API if already loaded.

### Adding to CMB2

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

### Displaying

Use the `[snapmap]` shortcode to display the map on the front-end. The shortcode accepts the following arguments:

* **width:** Specify a width for the map (defaults to 100%)
* **height:** Specify a height for the map (defaults to 400px)
* **zoom:** Specify the map zoom level to start on (defaults to 16)

## Screenshots

1. Plugin settings.
2. The CMB2 field.
3. Front-end display via shortcode.

## Installation

1. Upload the `cmb2-roadway-segments` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Add a snapmap field to a CMB2 metabox.
4. Place `[snapmap]` anywhere in a post's content to render a map.

## Frequently Asked Questions

### Does this plugin support custom map markers?

Not yet, but it will.

### Can I display a map of all map markers?

Also not yet, but this is an upcoming feature.

## Changelog

### 0.9.4
* Added the ability to draw circles on the map when the "limit_drawing" argument is not set
* Added fields to specify circle stroke and fill colors to the plugin's settings page
* Fixed invalid use of array keys in the shortcode function

### 0.9.3
* Added new 'disable_snap' field argument. When used, the snapToRoads API will not be called when drawing polylines
* Wrapped conditionals using the limit_drawing array key with isset() to ensure that the editor correctly renders

### 0.9.2
* Fixed invalid use of array keys

### 0.9.0
* Initial release.
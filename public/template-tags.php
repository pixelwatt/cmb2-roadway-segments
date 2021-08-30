<?php

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

if ( ! class_exists( 'CMB2_RS_Map' ) ) {
	class CMB2_RS_Map {
		protected $plugin_options = array();
		protected $geo = array();
	}
}

if ( ! function_exists( 'snapmap_build_single' ) ) {
	function snapmap_build_single( $id = '', $width = '100%', $height = '400px', $zoom = '16', $center = false, $marker = false, $mapquery = false, $mapstyle = false, $terrain = false, $uid = '', $attach = false ) {
		$plugin_options = get_option( 'cmb2-roadway-segments' );

		if ( empty( $id ) ) {
			$id = get_the_ID();
		}

		$api_key = $plugin_options['apikey'];
		$enqueue_maps = $plugin_options['enqueue'];
		$styles = $plugin_options['mapstyle'];
		$strokecolor = $plugin_options['strokecolor'];
		$circlestroke = $plugin_options['circlestroke'];
		$circlefill = $plugin_options['circlefill'];

		$controls['fullscreen'] = $plugin_options['fullscreen'];
		$controls['streetview'] = $plugin_options['streetview'];
		$controls['maptype'] = $plugin_options['maptype'];

		$output = '';

		if ( ! empty( $api_key ) ) {

			$map_prefix = get_post_meta( $id, 'cmb2_roadway_segments_prefix', true );

			if ( ! empty( $mapquery ) ) {
				$locations = get_posts( $mapquery );
			} else {
				$location = get_post_meta( $id, $map_prefix, true );
			}

			$output .= '
				' . ( empty( $enqueue_maps ) ? ( empty( $uid ) ? '<script src="https://maps.googleapis.com/maps/api/js?key=' . $api_key . '&amp;libraries=geometry"></script>' : '' ) : '' ) . '
				<div id="map' . $uid . '" style="width: ' . $width . '; height: ' . $height . '; margin-bottom: 30px;"></div>
				<script>
				
				' . ( $attach ? 'var card' . $uid . ' = document.getElementById(\'' . $attach['id'] . '\');' : '' ) . '
				
				function initMap' . $uid . '() {
				<!-- / Styles a map in night mode. -->
				var map' . $uid . ' = new google.maps.Map(document.getElementById("map' . $uid . '"), {
				' . ( $center ? 'center: {lat: ' . $center['lat'] . ', lng: ' . $center['lng'] . '},' : 'center: {lat: ' . $location['lat'] . ', lng: ' . $location['lng'] . '},' ) . '
				zoom: ' . $zoom . ',
				scrollwheel: false,
				' . ( $controls['maptype'] ? 'mapTypeControl: true,' : 'mapTypeControl: false,' ) . '
				' . ( $controls['streetview'] ? 'streetViewControl: true,' : 'streetViewControl: false,' ) . '
				' . ( $controls['fullscreen'] ? 'fullscreenControl: true,' : 'fullscreenControl: false,' ) . '
				zoomControl: true,
				' . ( $terrain ? 'mapTypeId: \'terrain\',' : '' ) . '
				rotateControl: false' . ( ! empty( $styles ) ? ',
				' : '' );

			if ( $mapstyle ) {
				$output .= 'styles: ' . $mapstyle;
			} elseif ( ! empty( $styles ) ) {
				$output .= 'styles: ' . $styles;
			}

			$output .= '});';

			if ( $marker ) {
				$output .= '
					var image = {
					url: \'' . $marker['url'] . '\',
					' . ( isset( $marker['size'] ) ? 'size: new google.maps.Size(' . $marker['size'] . '),' : '' ) . '
					origin: new google.maps.Point(' . $marker['origin'] . '),
					anchor: new google.maps.Point(' . $marker['anchor'] . ')' . ( isset( $marker['scaledSize'] ) ? ',' : '' ) . '
					' . ( isset( $marker['scaledSize'] ) ? 'scaledSize: new google.maps.Size(' . $marker['scaledSize'] . ')' : '' ) . '
					};
				';
			}

			if ( $attach ) {
				$output .= 'map' . $uid . '.controls[google.maps.ControlPosition.' . $attach['position'] . '].push(card' . $uid . '); 
				  ';
			}

			if ( $mapquery ) {
				$i = 1;
				foreach ( $locations as $pin ) {
					$map_prefix = get_post_meta( $pin->ID, 'cmb2_roadway_segments_prefix', true );
					$place = get_post_meta( $pin->ID, $map_prefix, true );

					if ( ! empty( $place['polygon_array'] ) ) {
						$output .= '
							var decodedPolygon' . $i . ' = google.maps.geometry.encoding.decodePath(\'' . $place['polygon_array'] . '\');
							var originalPolygon' . $i . ' = new google.maps.Polygon({
							  paths: decodedPolygon' . $i . ',
							  ' . ( ! empty( $circlestroke ) ? 'strokeColor: \'' . $circlestroke . '\',' : 'strokeColor: \'#FF0000\',' ) . '
							  strokeOpacity: 0.8,
							  strokeWeight: 2,
							  ' . ( ! empty( $circlefill ) ? 'fillColor: \'' . $circlefill . '\',' : 'fillColor: \'#FF0000\',' ) . '
							  fillOpacity: 0.35,
							  map: map
							});
							originalPolygon' . $i . '.setMap(map' . $uid . ');
						';
					}
					if ( ! empty( $place['array'] ) ) {
						$output .= '
							var decoded' . $i . ' = jQuery.parseJSON(\'' . $place['array'] . '\');
							var snappedPolyline' . $i . ' = new google.maps.Polyline({
								path: decoded' . $i . ',
								' . ( ! empty( $strokecolor ) ? 'strokeColor: \'' . $strokecolor . '\',' : 'strokeColor: \'#F6A623\',' ) . '
								strokeWeight: 5
							  });
							snappedPolyline' . $i . '.setMap(map' . $uid . ');
						';
					}
					if ( ( ! empty( $place['circle_radius'] ) ) && ( ! empty( $place['circle_center'] ) ) ) {
						$output .= '
							var decodedCircleCenter' . $i . ' = jQuery.parseJSON(\'' . $place['circle_center'] . '\');
							var originalCircle' . $i . ' = new google.maps.Circle({
								' . ( ! empty( $circlestroke ) ? 'strokeColor: \'' . $circlestroke . '\',' : 'strokeColor: \'#FF0000\',' ) . '
								strokeOpacity: 0.8,
								strokeWeight: 2,
								' . ( ! empty( $circlefill ) ? 'fillColor: \'' . $circlefill . '\',' : 'fillColor: \'#FF0000\',' ) . '
								fillOpacity: 0.35,
								map: map' . $uid . ',
								center: decodedCircleCenter' . $i . ',
								radius: ' . $place['circle_radius'] . '
							});
							originalCircle' . $i . '.setMap(map' . $uid . ');
						';
					}
					$i++;
				}
			} else {
				$i = 1;
				if ( ! empty( $location['polygon_array'] ) ) {
					$output .= '
						var decodedPolygon' . $i . ' = google.maps.geometry.encoding.decodePath(\'' . $location['polygon_array'] . '\');
						var originalPolygon' . $i . ' = new google.maps.Polygon({
						  paths: decodedPolygon' . $i . ',
						  ' . ( ! empty( $circlestroke ) ? 'strokeColor: \'' . $circlestroke . '\',' : 'strokeColor: \'#FF0000\',' ) . '
						  strokeOpacity: 0.8,
						  strokeWeight: 2,
						  ' . ( ! empty( $circlefill ) ? 'fillColor: \'' . $circlefill . '\',' : 'fillColor: \'#FF0000\',' ) . '
						  fillOpacity: 0.35,
						  map: map' . $uid . '
						});
						originalPolygon' . $i . '.setMap(map' . $uid . ');
					';
				}
				if ( ! empty( $location['array'] ) ) {
					$output .= '
						var decoded' . $i . ' = jQuery.parseJSON(\'' . $location['array'] . '\');
						var snappedPolyline' . $i . ' = new google.maps.Polyline({
							path: decoded' . $i . ',
							' . ( ! empty( $strokecolor ) ? 'strokeColor: \'' . $strokecolor . '\',' : 'strokeColor: \'#F6A623\',' ) . '
							strokeWeight: 5
						  });
						snappedPolyline' . $i . '.setMap(map' . $uid . ');
					';
				}
				if ( ( ! empty( $location['circle_radius'] ) ) && ( ! empty( $location['circle_center'] ) ) ) {
					$output .= '
						var decodedCircleCenter' . $i . ' = jQuery.parseJSON(\'' . $location['circle_center'] . '\');
						var originalCircle' . $i . ' = new google.maps.Circle({
							  ' . ( ! empty( $circlestroke ) ? 'strokeColor: \'' . $circlestroke . '\',' : 'strokeColor: \'#FF0000\',' ) . '
							  strokeOpacity: 0.8,
							  strokeWeight: 2,
							  ' . ( ! empty( $circlefill ) ? 'fillColor: \'' . $circlefill . '\',' : 'fillColor: \'#FF0000\',' ) . '
							  fillOpacity: 0.35,
							  map: map' . $uid . ',
							  center: decodedCircleCenter' . $i . ',
							  radius: ' . $location['circle_radius'] . '
						});
						originalCircle' . $i . '.setMap(map' . $uid . ');
					';
				}
			}

			$output .= 'var locations = [';

			if ( $mapquery ) {
				$i = 1;
				foreach ( $locations as $pin ) {
					$map_prefix = get_post_meta( $pin->ID, 'cmb2_roadway_segments_prefix', true );
					$place = get_post_meta( $pin->ID, $map_prefix, true );
					$tooltip = ( ! empty( $place['tooltip'] ) ? $place['tooltip'] : get_the_title( $pin->ID ) . ' (<a href="' . get_the_permalink( $pin->ID ) . '">More Details</a>)' );
					if ( ! empty( $place ) ) {
						$output .= '[\'' . addslashes( $tooltip ) . '\',' . $place['lat'] . ', ' . $place['lng'] . ', \'' . $i . '\'],';
						$i++;
					}
				}
			} else {
				$tooltip = ( ! empty( $location['tooltip'] ) ? $location['tooltip'] : get_the_title( $id ) );
				$output .= '[\'' . addslashes( $tooltip ) . '\',' . $location['lat'] . ', ' . $location['lng'] . ', \'1\']';
			}
			$output .= '
			];
			
			var infowindow = new google.maps.InfoWindow();

			var marker, i;
			var markers = [];
			
			for (i = 0; i < locations.length; i++) {  
			  marker = new google.maps.Marker({
				position: new google.maps.LatLng(locations[i][1], locations[i][2]),
				map: map' . $uid . ',
				' . ( $marker ? 'icon: image,' : '' ) . '
				animation: google.maps.Animation.DROP
			  });
			
			  markers.push(marker);
			
			  google.maps.event.addListener(marker, \'click\', (function(marker, i) {
				return function() {
				  infowindow.setContent(locations[i][0]);
				  infowindow.open(map' . $uid . ', marker);
				}
			  })(marker, i));
			  

			}
			
			
			';

			$output .= '
				
				}
				google.maps.event.addDomListener(window, \'load\', initMap' . $uid . ');
			  </script>
			  
			  
			
			';

		}

		return $output;
	}
}

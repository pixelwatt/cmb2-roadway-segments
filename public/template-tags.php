<?php

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

if ( ! class_exists( 'CMB2_RS_Map' ) ) {
	class CMB2_RS_Map {
		protected $plugin_options = array();
		protected $map_options = array();
		protected $geo = array();

		function __construct() {
			// Set plugin options
			$this->plugin_options = get_option( 'cmb2-roadway-segments' );

			// Set defaults for map options
			$this->map_options = array(
				'id'       => '',
				'class'    => 'cmb2-roadway-segments-map',
				'width'    => '100%',
				'height'   => '400px',
				'zoom'     => '16',
				'center'   => false,
				'marker'   => false,
				'mapstyle' => false,
				'terrain'  => false,
				'uid'      => '',
				'attach'   => false,
				'bounds'   => false,
				'overlay'  => false,
			);

			// Set up arrays for geo
			$this->geo['polygons'] = array();
			$this->geo['polylines'] = array();
			$this->geo['circles'] = array();
			$this->geo['markers'] = array();
		}

		public function set_options( $args ) {
			$this->map_options = wp_parse_args( $args, $this->map_options );
			return;
		}

		public function set_plugin_options( $args ) {
			$this->plugin_options = wp_parse_args( $args, $this->plugin_options );
			return;
		}

		public function add_circle( $lat, $lng, $radius ) {
			$this->geo['circles'][] = array(
				'lat'    => $lat,
				'lng'    => $lng,
				'radius' => $radius,
			);
			return;
		}

		public function add_marker( $lat, $lng, $tooltip ) {
			$this->geo['markers'][] = array(
				'lat'     => $lat,
				'lng'     => $lng,
				'tooltip' => $tooltip,
			);
			return;
		}

		public function add_polygon( $path, $tooltip ) {
			$this->geo['polygons'][] = array(
				'path'     => $path,
				'tooltip'  => $tooltip,
			);
			return;
		}

		public function build_map() {
			$api_key = $this->plugin_options['apikey'];
			$enqueue_maps = $this->plugin_options['enqueue'];
			$styles = $this->plugin_options['mapstyle'];
			$strokecolor = $this->plugin_options['strokecolor'];
			$circlestroke = $this->plugin_options['circlestroke'];
			$circlefill = $this->plugin_options['circlefill'];

			$controls['fullscreen'] = $this->plugin_options['fullscreen'];
			$controls['streetview'] = $this->plugin_options['streetview'];
			$controls['maptype'] = $this->plugin_options['maptype'];

			$output = '';

			if ( ! empty( $api_key ) ) {
				$output .= '
					' . ( empty( $enqueue_maps ) ? ( empty( $this->map_options['uid'] ) ? '<script src="https://maps.googleapis.com/maps/api/js?key=' . $api_key . '&amp;libraries=geometry"></script>' : '' ) : '' ) . '
					<div id="map' . $this->map_options['uid'] . '" style="width: ' . $this->map_options['width'] . '; height: ' . $this->map_options['height'] . '; margin-bottom: 30px;"></div>
					<script>
					
					' . ( $this->map_options['attach'] ? 'var card' . $this->map_options['uid'] . ' = document.getElementById(\'' . $this->map_options['attach']['id'] . '\');' : '' ) . '
					
					function initMap' . $this->map_options['uid'] . '() {
					<!-- / Styles a map in night mode. -->
					var map' . $this->map_options['uid'] . ' = new google.maps.Map(document.getElementById("map' . $this->map_options['uid'] . '"), {
					' . ( $this->map_options['center'] ? 'center: {lat: ' . $this->map_options['center']['lat'] . ', lng: ' . $this->map_options['center']['lng'] . '},' : 'center: {lat: 35.045148, lng: -85.311511},' ) . '
					zoom: ' . $this->map_options['zoom'] . ',
					scrollwheel: false,';
				if ( isset( $this->map_options['bounds'] ) ) {
					$output .= '
					restriction: {
				      latLngBounds: {
						    north: ' . $this->map_options['bounds']['north'] . ',
						    south: ' . $this->map_options['bounds']['south'] . ',
						    east: ' . $this->map_options['bounds']['east'] . ',
						    west: ' . $this->map_options['bounds']['west'] . ',
					  },
				      strictBounds: true,
				    },';
				}
				$output .= ( $controls['maptype'] ? 'mapTypeControl: true,' : 'mapTypeControl: false,' ) . '
					' . ( $controls['streetview'] ? 'streetViewControl: true,' : 'streetViewControl: false,' ) . '
					' . ( $controls['fullscreen'] ? 'fullscreenControl: true,' : 'fullscreenControl: false,' ) . '
					zoomControl: true,
					' . ( $this->map_options['terrain'] ? 'mapTypeId: \'terrain\',' : '' ) . '
					rotateControl: false' . ( ! empty( $styles ) ? ',
					' : '' );

				if ( $this->map_options['mapstyle'] ) {
					$output .= 'styles: ' . $this->map_options['mapstyle'];
				} elseif ( ! empty( $styles ) ) {
					$output .= 'styles: ' . $styles;
				}

				$output .= '});';

				if ( isset( $this->map_options['overlay'] ) ) {
					$output .= '
						var imageBounds = {
						    north: ' . $this->map_options['overlay']['north'] . ',
						    south: ' . $this->map_options['overlay']['south'] . ',
						    east: ' . $this->map_options['overlay']['east'] . ',
						    west: ' . $this->map_options['overlay']['west'] . ',
						};
						  
						var historicalOverlay = new google.maps.GroundOverlay(
						    \'' . $this->map_options['overlay']['image'] . '\',
						    imageBounds
						);
						historicalOverlay.setMap(map' . $this->map_options['uid'] . ');
					';
				}

				if ( $this->map_options['marker'] ) {
					$output .= '
						var image = {
						url: \'' . $this->map_options['marker']['url'] . '\',
						' . ( isset( $this->map_options['marker']['size'] ) ? 'size: new google.maps.Size(' . $this->map_options['marker']['size'] . '),' : '' ) . '
						origin: new google.maps.Point(' . $this->map_options['marker']['origin'] . '),
						anchor: new google.maps.Point(' . $this->map_options['marker']['anchor'] . ')' . ( isset( $this->map_options['marker']['scaledSize'] ) ? ',' : '' ) . '
						' . ( isset( $this->map_options['marker']['scaledSize'] ) ? 'scaledSize: new google.maps.Size(' . $this->map_options['marker']['scaledSize'] . ')' : '' ) . '
						};
					';
				}
	
				if ( $this->map_options['attach'] ) {
					$output .= 'map' . $this->map_options['uid'] . '.controls[google.maps.ControlPosition.' . $this->map_options['attach']['position'] . '].push(card' . $this->map_options['uid'] . '); 
					  ';
				}

				if ( ! empty( $this->geo['polygons'] ) ) {
					$i = 1;
					foreach ( $this->geo['polygons'] as $polygon ) {
						$output .= '
							var decodedPolygon' . $i . ' = JSON.parse(\'' . $polygon['path'] . '\');
							var originalPolygon' . $i . ' = new google.maps.Polygon({
							  paths: decodedPolygon' . $i . ',
							  ' . ( ! empty( $circlestroke ) ? 'strokeColor: \'' . $circlestroke . '\',' : 'strokeColor: \'#FF0000\',' ) . '
							  strokeOpacity: 0.8,
							  strokeWeight: 2,
							  ' . ( ! empty( $circlefill ) ? 'fillColor: \'' . $circlefill . '\',' : 'fillColor: \'#FF0000\',' ) . '
							  fillOpacity: 0.35,
							  map: map' . $this->map_options['uid'] . ',
							});
							originalPolygon' . $i . '.setMap(map' . $this->map_options['uid'] . ');
						';
						$i++;
					}
				}

				if ( ! empty( $this->geo['circles'] ) ) {
					$i = 1;
					foreach ( $this->geo['circles'] as $circle ) {
						$output .= '
							var originalCircle' . $i . ' = new google.maps.Circle({
								' . ( ! empty( $circlestroke ) ? 'strokeColor: \'' . $circlestroke . '\',' : 'strokeColor: \'#FF0000\',' ) . '
								strokeOpacity: 0.4,
								strokeWeight: 2,
								' . ( ! empty( $circlefill ) ? 'fillColor: \'' . $circlefill . '\',' : 'fillColor: \'#FF0000\',' ) . '
								fillOpacity: 0.15,
								map: map' . $this->map_options['uid'] . ',
								center: {lat: ' . $circle['lat'] . ', lng: ' . $circle['lng'] . '},
								radius: ' . $circle['radius'] . '
							});
							originalCircle' . $i . '.setMap(map' . $this->map_options['uid'] . ');
						';
						$i++;
					}
				}

				$output .= 'var locations = [';
				if ( ! empty( $this->geo['markers'] ) ) {
					$i = 1;
					foreach ( $this->geo['markers'] as $marker ) {
						$output .= '[\'' . addslashes( $marker['tooltip'] ) . '\',' . $marker['lat'] . ', ' . $marker['lng'] . ', \'' . $i . '\'],';
						$i++;
					}
				}

				$output .= '
				];
				
				var infowindow = new google.maps.InfoWindow();

				var marker, i;
				var markers = [];
				
				for (i = 0; i < locations.length; i++) {  
				marker = new google.maps.Marker({
					position: new google.maps.LatLng(locations[i][1], locations[i][2]),
					map: map' . $this->map_options['uid'] . ',
					' . ( $this->map_options['marker'] ? 'icon: image,' : '' ) . '
					animation: google.maps.Animation.DROP
				});
				
				markers.push(marker);
				
				google.maps.event.addListener(marker, \'click\', (function(marker, i) {
					return function() {
					infowindow.setContent(locations[i][0]);
					infowindow.open(map' . $this->map_options['uid'] . ', marker);
					}
				})(marker, i));
				

				}
				
				
				';

				$output .= '
					
					}
					google.maps.event.addDomListener(window, \'load\', initMap' . $this->map_options['uid'] . ');
				</script>
				';
			}

			return $output;
		}
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

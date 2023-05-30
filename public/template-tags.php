<?php

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

function cmb2_rs_check_array_key( $item, $key ) {
	$output = false;
	if ( is_array( $item ) ) {
		if ( array_key_exists( $key, $item ) ) {
			if ( ! empty( $item["{$key}"] ) ) {
				$output = true;
			}
		}
	}
	return $output;
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
				'geolocation' => false,
				'geolocation_bounds' => false,
				'geolocation_options' => array(
					'marker' => array(
						'url' => '',
						'size' => '',
						'anchor' => '',
						'origin' => '',
						'scaledSize' => '',
					),
					'btn_content' => '',
				),
				'width'    => '100%',
				'height'   => '400px',
				'zoom'     => '16',
				'center'   => false,
				'marker'   => false,
				'markers'   => array(),
				'mapstyle' => false,
				'terrain'  => false,
				'uid'      => '',
				'attach'   => false,
				'bounds'   => false,
				'overlay'  => false,
				'mapTypeId' => '',
				'domlisteners'  => false,
			);

			/*
				If adding multiple markers, each marker should be added as an array to 'markers' using the following format:
				array(
					'name' => 'myMarker',
					'url' => '',
					'size' => '',
					'anchor' => '',
					'origin' => '',
					'scaledSize' => '',
				)
			*/

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

		public function add_marker( $lat, $lng, $tooltip, $image = '\'\'', $domid = '\'\'' ) {
			$this->geo['markers'][] = array(
				'lat'     => $lat,
				'lng'     => $lng,
				'tooltip' => $tooltip,
				'image'   => $image,
				'domid'   => $domid,
			);
			return;
		}

		public function add_polygon( $path, $tooltip, $opts = array() ) {
			$this->geo['polygons'][] = array(
				'path'     => $path,
				'tooltip'  => $tooltip,
				'opts'	   => $opts,
			);
			/*
				$opts = array(
					'strokeColor' => '#FF0000',
					'strokeOpacity' => 0.4,
					'strokeWeight' => 2,
					'fillColor' => '#FF0000',
					'fillOpacity' => 0.15,
				);
			*/
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

			$mapcenter = '';
			if ( $this->map_options['center'] ) {
				$mapcenter = 'center: {lat: ' . $this->map_options['center']['lat'] . ', lng: ' . $this->map_options['center']['lng'] . '},';
			} elseif ( ! empty( $this->plugin_options['mapcenter'] ) ) { 
				$coords = explode( ',', $this->plugin_options['mapcenter'] );
				$mapcenter = 'center: {lat: ' . $coords[0] . ', lng: ' . $coords[1] . '},';
			} else {
				$mapcenter = 'center: {lat: 35.045148, lng: -85.311511},';
			}

			$output = '';

			if ( ! empty( $api_key ) ) {
				$output .= '
					' . ( false != $this->map_options['geolocation'] ? '<a class="cmb2-rs-glcontrol" id="glcontrol' . $this->map_options['uid'] . '">' . $this->map_options['geolocation_options']['btn_content'] . '<span>Show My Location</span></a>' : '' ) . '
					' . ( empty( $enqueue_maps ) ? ( empty( $this->map_options['uid'] ) ? '<script src="https://maps.googleapis.com/maps/api/js?key=' . $api_key . '&amp;libraries=geometry"></script>' : '' ) : '' ) . '
					<div id="map' . $this->map_options['uid'] . '" style="width: ' . $this->map_options['width'] . '; height: ' . $this->map_options['height'] . '; margin-bottom: 30px;"></div>
					<script>';
				if ( false != $this->map_options['domlisteners'] ) {
					if ( ! empty( $this->geo['markers'] ) ) {
						foreach ( $this->geo['markers'] as $marker ) {
							if ( '\'\'' != $marker['domid'] ) {
								$output .= '
								const ' . $marker['domid'] . ' = document.getElementById("' . $marker['domid'] . '");';
							}
						}
					}
				}
				$output .= '
					' . ( $this->map_options['attach'] ? 'var card' . $this->map_options['uid'] . ' = document.getElementById(\'' . $this->map_options['attach']['id'] . '\');' : '' ) . '
					' . ( false != $this->map_options['geolocation'] ? 'var glcontrol' . $this->map_options['uid'] . ' = document.getElementById(\'glcontrol' . $this->map_options['uid'] . '\');' : '' ) . '
					
					function initMap' . $this->map_options['uid'] . '() {
					<!-- / Styles a map in night mode. -->
					var map' . $this->map_options['uid'] . ' = new google.maps.Map(document.getElementById("map' . $this->map_options['uid'] . '"), {
					' . $mapcenter . '
					zoom: ' . $this->map_options['zoom'] . ',
					scrollwheel: false,
					' . ( ! empty( $this->map_options['mapTypeId'] ) ? 'mapTypeId: \'' . $this->map_options['mapTypeId'] . '\',' : '' );
				if ( isset( $this->map_options['bounds'] ) ) {
					if ( is_array( $this->map_options['bounds'] ) ) {
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
				}
				$output .= ( $controls['maptype'] ? 'mapTypeControl: true,' : 'mapTypeControl: false,' ) . '
					' . ( $controls['streetview'] ? 'streetViewControl: true,' : 'streetViewControl: false,' ) . '
					' . ( $controls['fullscreen'] ? 'fullscreenControl: true,' : 'fullscreenControl: false,' ) . '
					zoomControl: true,
					' . ( $this->map_options['terrain'] ? 'mapTypeId: \'terrain\',' : '' ) . '
					rotateControl: false' . ( $this->map_options['mapstyle'] ? ',
					' : ( ! empty( $styles ) ? ',
					' : '' ) );

				if ( $this->map_options['mapstyle'] ) {
					$output .= 'styles: ' . $this->map_options['mapstyle'];
				} elseif ( ! empty( $styles ) ) {
					$output .= 'styles: ' . $styles;
				}

				$output .= '});';

				if ( isset( $this->map_options['overlay'] ) ) {
					if ( is_array( $this->map_options['overlay'] ) ) {
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

				if ( 0 < count( $this->map_options['markers'] ) ) {
					foreach( $this->map_options['markers'] as $marker ) {
						$output .= '
							var ' . $marker['name'] . ' = {
								url: \'' . $marker['url'] . '\',
								' . ( isset( $marker['size'] ) ? 'size: new google.maps.Size(' . $marker['size'] . '),' : '' ) . '
								origin: new google.maps.Point(' . $marker['origin'] . '),
								anchor: new google.maps.Point(' . $marker['anchor'] . ')' . ( isset( $marker['scaledSize'] ) ? ',' : '' ) . '
								' . ( isset( $marker['scaledSize'] ) ? 'scaledSize: new google.maps.Size(' . $marker['scaledSize'] . ')' : '' ) . '
							};
						';
					}
				}

				if ( ! empty( $this->map_options['geolocation_options']['marker']['url'] ) ) {
					$output .= '
						var geoimage = {
						url: \'' . $this->map_options['geolocation_options']['marker']['url'] . '\',
						' . ( isset( $this->map_options['geolocation_options']['marker']['size'] ) ? 'size: new google.maps.Size(' . $this->map_options['geolocation_options']['marker']['size'] . '),' : '' ) . '
						origin: new google.maps.Point(' . $this->map_options['geolocation_options']['marker']['origin'] . '),
						anchor: new google.maps.Point(' . $this->map_options['geolocation_options']['marker']['anchor'] . ')' . ( isset( $this->map_options['geolocation_options']['marker']['scaledSize'] ) ? ',' : '' ) . '
						' . ( isset( $this->map_options['geolocation_options']['marker']['scaledSize'] ) ? 'scaledSize: new google.maps.Size(' . $this->map_options['geolocation_options']['marker']['scaledSize'] . ')' : '' ) . '
						};
					';
				}
	
				if ( $this->map_options['attach'] ) {
					$output .= 'map' . $this->map_options['uid'] . '.controls[google.maps.ControlPosition.' . $this->map_options['attach']['position'] . '].push(card' . $this->map_options['uid'] . '); 
					  ';
				}
				if ( $this->map_options['geolocation'] ) {
					$output .= '
						map' . $this->map_options['uid'] . '.controls[google.maps.ControlPosition.TOP_RIGHT].push(glcontrol' . $this->map_options['uid'] . '); 
						glcontrol' . $this->map_options['uid'] . '.addEventListener("click", () => {
							if ( glcontrol' . $this->map_options['uid'] . '.classList.contains(\'enabled\') ) {
								glcontrol' . $this->map_options['uid'] . '.classList.remove(\'enabled\');
								if (glmarker) {
									glmarker.setMap(null);
								}
								clearTimeout(autoUpdate);
							} else {
								glcontrol' . $this->map_options['uid'] . '.classList.add(\'enabled\');
								autoUpdate();
							}
							
						});
						var glmarker;
						function autoUpdate() {
						  	navigator.geolocation.getCurrentPosition(function(position) {
					';
					
						if ( is_array( $this->map_options['geolocation_bounds'] ) ) {
							$output .= '
								if ( Number(position.coords.latitude) <= ' . $this->map_options['geolocation_bounds']['north'] . ' && Number(position.coords.latitude) >= ' . $this->map_options['geolocation_bounds']['south'] . ' && Number(position.coords.longitude) <= ' . $this->map_options['geolocation_bounds']['east'] . ' && Number(position.coords.longitude) >= ' . $this->map_options['geolocation_bounds']['west'] . ' ) {
							';
						}
					
					$output .= '
						    	var newPoint = new google.maps.LatLng(position.coords.latitude,position.coords.longitude);
						    	if ( glcontrol' . $this->map_options['uid'] . '.classList.contains(\'enabled\') ) {
								    if (glmarker) {
								      // Marker already created - Move it
								      glmarker.setPosition(newPoint);
								      glmarker.setMap(map' . $this->map_options['uid'] . ');
								    }
								    else {
								      // Marker does not exist - Create it
								      glmarker = new google.maps.Marker({
								        position: newPoint,
								        map: map' . $this->map_options['uid'] . ',
										' . ( ! empty( $this->map_options['geolocation_options']['marker']['url'] ) ? 'icon: geoimage,' : ( $this->map_options['marker'] ? 'icon: image,' : '' ) ) . '
								      });
								      map' . $this->map_options['uid'] . '.setCenter(newPoint);

								    }
							    	setTimeout(autoUpdate, 5000);
								}
					';
					
						if ( is_array( $this->map_options['geolocation_bounds'] ) ) {
							$output .= '
								} else {
									clearTimeout(autoUpdate);
									alert("You are currently outside of map bounds!");
									glcontrol' . $this->map_options['uid'] . '.classList.remove(\'enabled\');
									
								}
							';
						}
					
					$output .= '
							console.log("Function fired.");
						  	});
						}

					  ';
				}

				if ( ! empty( $this->geo['polygons'] ) ) {
					$i = 1;
					foreach ( $this->geo['polygons'] as $polygon ) {
						//echo '<pre>' . print_r($polygon, true) . '</pre>';
						$itemStrokeColor = ( cmb2_rs_check_array_key( $polygon['opts'], 'strokeColor' ) ? $polygon['opts']['strokeColor'] : ( ! empty( $circlestroke ) ? $circlestroke : '#FF0000' ) );
						$itemStrokeOpacity = ( cmb2_rs_check_array_key( $polygon['opts'], 'strokeOpacity' ) ? $polygon['opts']['strokeOpacity'] : '0.8' );
						$itemStrokeWeight = ( cmb2_rs_check_array_key( $polygon['opts'], 'strokeWeight' ) ? $polygon['opts']['strokeWeight'] : '2' );
						$itemFillColor = ( cmb2_rs_check_array_key( $polygon['opts'], 'fillColor' ) ? $polygon['opts']['fillColor'] : ( ! empty( $circlefill ) ? $circlefill : '#FF0000' ) );
						$itemFillOpacity = ( cmb2_rs_check_array_key( $polygon['opts'], 'fillOpacity' ) ? $polygon['opts']['fillOpacity'] : '0.35' );
						$output .= '
							var decodedPolygon' . $i . ' = JSON.parse(\'' . $polygon['path'] . '\');
							var originalPolygon' . $i . ' = new google.maps.Polygon({
							  paths: decodedPolygon' . $i . ',
							  strokeColor: \'' . $itemStrokeColor . '\',
							  strokeOpacity: ' . $itemStrokeOpacity . ',
							  strokeWeight: ' . $itemStrokeWeight . ',
							  fillColor: \'' . $itemFillColor . '\',
							  fillOpacity: ' . $itemFillOpacity . ',
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
						$output .= '[\'' . addslashes( $marker['tooltip'] ) . '\',' . $marker['lat'] . ', ' . $marker['lng'] . ', ' . $marker['image'] . ', ' . $marker['domid'] . ', \'' . $i . '\'],';
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
						icon: ' . ( $this->map_options['marker'] ? 'image' : 'locations[i][3]' ) . ',
						animation: google.maps.Animation.DROP
					});
				
					markers.push(marker);
				
					google.maps.event.addListener(marker, \'click\', (function(marker, i) {
						return function() {
							if ( \'false\' != locations[i][0] ) {
								infowindow.setContent(locations[i][0]);
								infowindow.open(map' . $this->map_options['uid'] . ', marker);
							}
						}
					})(marker, i));

				';
				if ( false != $this->map_options['domlisteners'] ) {
					$output .= '
						if ( \'\' != locations[i][4] ) {
						google.maps.event.addDomListener(locations[i][4], \'click\', (function(marker, i) {
							return function() {
								if ( \'false\' != locations[i][0] ) {
									infowindow.setContent(locations[i][0]);
									infowindow.open(map' . $this->map_options['uid'] . ', marker);
								}
							}
						})(marker, i));
					}
					';
				}
				$output .= '
				

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

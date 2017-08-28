<?php 
 
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

if ( ! function_exists( 'snapmap_build_single' ) ) {
	function snapmap_build_single($id='',$width='100%',$height='400px',$zoom='16', $center=false, $marker=false, $mapquery=false, $mapstyle=false) {
	    $plugin_options = get_option( 'cmb2-roadway-segments' );
	    
	    if (empty($id)) {
            $id = get_the_ID();
        }
        
        $api_key = $plugin_options['apikey'];
	    $enqueue_maps = $plugin_options['enqueue'];
	    $styles = $plugin_options['mapstyle'];
	    $strokecolor = $plugin_options['strokecolor'];
	    
	    $controls['fullscreen'] = $plugin_options['fullscreen'];
	    $controls['streetview'] = $plugin_options['streetview'];
	    $controls['maptype'] = $plugin_options['maptype'];
	    
	    //fullscreen
	    //streetview
	    //maptype
        
	    $output = '';
        
        if ( !empty($api_key) ) {
            
            $map_prefix = get_post_meta( $id, 'cmb2_roadway_segments_prefix', true );
            
            if (!empty($mapquery)) {
                $locations = get_posts($mapquery);
            } else {
                $location = get_post_meta( $id, $map_prefix, true );
            }
            
            
            $output .= '
                <div id="map" style="width: '.$width.'; height: '.$height.'; margin-bottom: 30px;"></div>
                <script>
                
                
                
                function initMap() {
                <!-- / Styles a map in night mode. -->
                var map = new google.maps.Map(document.getElementById("map"), {
                '.( $center ? 'center: {lat: '.$center[lat].', lng: '.$center[lng].'},' : 'center: {lat: '.$location[lat].', lng: '.$location[lng].'},' ).'
                zoom: '.$zoom.',
                scrollwheel: false,
                '.( $controls['maptype'] ? 'mapTypeControl: true,' : 'mapTypeControl: false,' ).'
                '.( $controls['streetview'] ? 'streetViewControl: true,' : 'streetViewControl: false,' ).'
                '.( $controls['fullscreen'] ? 'fullscreenControl: true,' : 'fullscreenControl: false,' ).'
                zoomControl: true,
                rotateControl: false'.( !empty($styles) ? ',
                ' : '' );
                
                if ($mapstyle) {
                    $output .= 'styles: '.$mapstyle;
                } elseif (!empty($styles)) {
                    $output .= 'styles: '.$styles;
                }
                
                $output .= '});';
                
                
                
                if ($marker) {
                    $output .= '
                        var image = {
                        url: \''.$marker['url'].'\',
                        size: new google.maps.Size('.$marker['size'].'),
                        origin: new google.maps.Point('.$marker['origin'].'),
                        anchor: new google.maps.Point('.$marker['anchor'].')
                        };
                    ';
                }
                
                
                if (!empty($location['array'])) {
                        $output .= '
                            var decoded = jQuery.parseJSON(\''.$location['array'].'\');
                            var snappedPolyline = new google.maps.Polyline({
                                path: decoded,
                                '.( !empty($strokecolor) ? 'strokeColor: \''.$strokecolor.'\',' : 'strokeColor: \'#F6A623\',' ).'
                                strokeWeight: 5
                              });
                            snappedPolyline.setMap(map);
                        ';
                    }
                
                
                
                $output .= 'var locations = [';
                
                if ($mapquery) {
                    
                    foreach ($locations as $pin) {
                        $map_prefix = get_post_meta( $pin->ID, 'cmb2_roadway_segments_prefix', true );
                        $place = get_post_meta( $pin->ID, $map_prefix, true );
                        $i = 1;
                        if (!empty($place)) {
                            $output .= '[\''.addslashes($place[tooltip]).'\','.$place[lat].', '.$place[lng].', \''.$i.'\'],';
                            $i++;
                        }
                    }
                    
                } else {
                
                    $output .= '[\''.addslashes($location[tooltip]).'\','.$location[lat].', '.$location[lng].', \'1\']';
                
                }
                
                $output .= '
                ];
                
                var infowindow = new google.maps.InfoWindow();

                var marker, i;
                var markers = [];
                
                for (i = 0; i < locations.length; i++) {  
                  marker = new google.maps.Marker({
                    position: new google.maps.LatLng(locations[i][1], locations[i][2]),
                    map: map,
                    '.( $marker ? 'icon: image,' : '' ).'
                    animation: google.maps.Animation.DROP
                  });
                
                  markers.push(marker);
                
                  google.maps.event.addListener(marker, \'click\', (function(marker, i) {
                    return function() {
                      infowindow.setContent(locations[i][0]);
                      infowindow.open(map, marker);
                    }
                  })(marker, i));
                  
                  

                }
                
                
                ';
                
                $output .= '
                
                }
              </script>
              '.( empty($enqueue_maps) ? '<script async defer src="https://maps.googleapis.com/maps/api/js?key='.$api_key.'&amp;callback=initMap"></script>' : '' ).'
              
            
            ';
            
            
            
        }
        
        return $output;
	}
}
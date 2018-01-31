<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://pixelwatt.com
 * @since      1.0.0
 *
 * @package    Cmb2_Roadway_Segments
 * @subpackage Cmb2_Roadway_Segments/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Cmb2_Roadway_Segments
 * @subpackage Cmb2_Roadway_Segments/public
 * @author     PixelWatt <hello@pixelwatt.com>
 */
class Cmb2_Roadway_Segments_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->wp_cmb2_segment_options = get_option($this->plugin_name);
		
		add_filter( 'cmb2_render_snapmap', array( $this, 'render_snapmap' ), 10, 5 );
		add_filter( 'cmb2_sanitize_snapmap', array( $this, 'sanitize_snapmap' ), 10, 4 );
		add_shortcode( 'snapmap', array( $this, 'snapmap_shortcode' ) );

	}
	
	public function snapmap_shortcode( $atts ) {
    	    $a = shortcode_atts( array(
            'id' => '',
            'width' => '100%',
            'height' => '400px',
            'zoom'  => '16'
        ), $atts );
        
        if (empty($a['id'])) {
            $a['id'] = get_the_ID();
        }
        
        $api_key = $this->wp_cmb2_segment_options['apikey'];
	    $enqueue_maps = $this->wp_cmb2_segment_options['enqueue'];
	    $styles = $this->wp_cmb2_segment_options['mapstyle'];
	    $strokecolor = $this->wp_cmb2_segment_options['strokecolor'];
	    
	    $controls['fullscreen'] = $this->wp_cmb2_segment_options['fullscreen'];
	    $controls['streetview'] = $this->wp_cmb2_segment_options['streetview'];
	    $controls['maptype'] = $this->wp_cmb2_segment_options['maptype'];
	    
	    //fullscreen
	    //streetview
	    //maptype
        
	    $output = '';
        
        if ( !empty($api_key) ) {
            
            $map_prefix = get_post_meta( $a['id'], 'cmb2_roadway_segments_prefix', true );
            
            $location = get_post_meta( $a['id'], $map_prefix, true );
            
            $output .= '
                <div id="map" style="width: '.$a['width'].'; height: '.$a['height'].'; margin-bottom: 30px;"></div>
                <script>
                
                
                
                function initMap() {
                <!-- / Styles a map in night mode. -->
                var map = new google.maps.Map(document.getElementById("map"), {
                center: {lat: '.$location['lat'].', lng: '.$location['lng'].'},
                zoom: '.$a['zoom'].',
                scrollwheel: false,
                '.( $controls['maptype'] ? 'mapTypeControl: true,' : 'mapTypeControl: false,' ).'
                '.( $controls['streetview'] ? 'streetViewControl: true,' : 'streetViewControl: false,' ).'
                '.( $controls['fullscreen'] ? 'fullscreenControl: true,' : 'fullscreenControl: false,' ).'
                zoomControl: true,
                rotateControl: false'.( !empty($styles) ? ',
                ' : '' );
                
                if (!empty($styles)) {
                    $output .= 'styles: '.$styles;
                }
                
                $output .= '});';
                
            
                
                
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
                
                $output .= '[\''.addslashes($location[tooltip]).'\','.$location[lat].', '.$location[lng].', \'1\']';
                
                $output .= '
                ];
                
                var infowindow = new google.maps.InfoWindow();

                var marker, i;
                var markers = [];
                
                for (i = 0; i < locations.length; i++) {  
                  marker = new google.maps.Marker({
                    position: new google.maps.LatLng(locations[i][1], locations[i][2]),
                    map: map,
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

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Cmb2_Roadway_Segments_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Cmb2_Roadway_Segments_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/cmb2-roadway-segments-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Cmb2_Roadway_Segments_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Cmb2_Roadway_Segments_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		//wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/cmb2-roadway-segments-public.js', array( 'jquery' ), $this->version, false );

	}
	
	
	
	
	
	public function render_snapmap( $field, $value, $object_id, $object_type, $field_type ) {
	    
	    $api_key = $this->wp_cmb2_segment_options['apikey'];
	    $enqueue_maps = $this->wp_cmb2_segment_options['enqueue'];
	    $strokecolor = $this->wp_cmb2_segment_options['strokecolor'];
    	    
    	    if (!empty($api_key)) {
    	        
    	        $this->setup_admin_scripts($api_key);
    	        
    	        		$value = wp_parse_args( $value, array(
            		'array' => '',
            		'lat' => '',
            		'lng'      => '',
            		'latlng'     => '',
            		'tooltip'     => '',
            	) );
    	        
    	        $location = get_post_meta( $field->object_id, $field->args['id'], true );
    	        
    	        echo '
    	            <script>
    	            
                    var apiKey = \''.$api_key.'\';

                    var map;
                    var drawingManager;
                    var placeIdArray = [];
                    var polylines = [];
                    var snappedCoordinates = [];
                    var snappedPolyline;
                    var mapmarker;
                    
                    
                    function initialize() {
                        var mapOptions = {';
                        if (!empty($value['lat'])) {
                            echo '
                            zoom: 16,
                            center: {lat: '.$value['lat'].', lng: '.$value['lng'].'},
                            ';
                        } else {
                            echo '
                            zoom: 13,
                            center: {lat: 34.725337, lng: -86.585254},
                            ';
                        }
                        echo '
                            scrollwheel: false,
                            mapTypeControl: false,
                            streetViewControl: false,
                            fullscreenControl: true
                        };    
                        
                        var card = document.getElementById(\'pac-card\');
                        var input = document.getElementById(\'pac-input\');
                        var types = document.getElementById(\'type-selector\');
                        var strictBounds = document.getElementById(\'strict-bounds-selector\');
                        
                        map = new google.maps.Map(document.getElementById(\'snapmap-map\'), mapOptions);
                        
                        map.controls[google.maps.ControlPosition.TOP_RIGHT].push(card);

                        var autocomplete = new google.maps.places.Autocomplete(input);

                        autocomplete.bindTo(\'bounds\', map);

                        
                
                        autocomplete.addListener(\'place_changed\', function() {
                          
                          var place = autocomplete.getPlace();
                          
                
                          // If the place has a geometry, then present it on a map.
                          if (place.geometry.viewport) {
                            map.fitBounds(place.geometry.viewport);
                          } else {
                            map.setCenter(place.geometry.location);
                            map.setZoom(17);  // Why 17? Because it looks good.
                          }
                          
                
                          var address = \'\';
                          if (place.address_components) {
                            address = [
                              (place.address_components[0] && place.address_components[0].short_name || \'\'),
                              (place.address_components[1] && place.address_components[1].short_name || \'\'),
                              (place.address_components[2] && place.address_components[2].short_name || \'\')
                            ].join(\' \');
                          }
                
                          
                        });
                
                        // Sets a listener on a radio button to change the filter type on Places
                        // Autocomplete.
                        function setupClickListener(id, types) {
                          var radioButton = document.getElementById(id);
                          radioButton.addEventListener(\'click\', function() {
                            autocomplete.setTypes(types);
                          });
                        }
                
                        setupClickListener(\'changetype-all\', []);
                        setupClickListener(\'changetype-address\', [\'address\']);
                        setupClickListener(\'changetype-establishment\', [\'establishment\']);
                        setupClickListener(\'changetype-geocode\', [\'geocode\']);
                
                        document.getElementById(\'use-strict-bounds\')
                            .addEventListener(\'click\', function() {
                              console.log(\'Checkbox clicked! New state=\' + this.checked);
                              autocomplete.setOptions({strictBounds: this.checked});
                            });

                        
                        
                        
                        
                        ';
                        
                        if (!empty($location['array'])) {
                            echo '
                                var decoded = jQuery.parseJSON(\''.$location['array'].'\');
                                var originalPolyline = new google.maps.Polyline({
                                    path: decoded,
                                    strokeColor: \'red\',
                                    strokeWeight: 5
                                  });
                                originalPolyline.setMap(map);
                            ';
                        }
                        
                        if (!empty($value['lat'])) {
                            echo '
                                var originalMarker = new google.maps.Marker({
                                position: {lat: '.$value['lat'].', lng: '.$value['lng'].'},
                                map: map
                                });
                            ';
                        }
                        
                        echo '
                        
                        var mapmarkerdisplay = new google.maps.Marker({
                            position: mapmarker,
                            map: null
                        });
                        
                        // Enables the polyline drawing control. Click on the map to start drawing a
                          // polyline. Each click will add a new vertice. Double-click to stop drawing.
                          drawingManager = new google.maps.drawing.DrawingManager({
                            drawingMode: null,
                            drawingControl: true,
                            drawingControlOptions: {
                              position: google.maps.ControlPosition.TOP_LEFT,
                              
                              '.( $field->args['limit_drawing'] ? 'drawingModes: [\'marker\']' : 'drawingModes: [\'marker\', \'polyline\']' ).'
                            },
                            polylineOptions: {
                              strokeColor: \'#696969\',
                              strokeWeight: 1
                            }
                          });
                          drawingManager.setMap(map);
                          
                          drawingManager.addListener(\'polylinecomplete\', function(poly) {
                            '.( !empty($value['array']) ? 'originalPolyline.setMap(null);' : '' ).'
                            var path = poly.getPath();
                            polylines.push(poly);
                            poly.setMap(null);
                            placeIdArray = [];
                            runSnapToRoad(path);
                          });
                          
                          drawingManager.addListener(\'markercomplete\', function(marker) {
                              '.( !empty($value['lat']) ? 'originalMarker.setMap(null);' : '' ).'
                              if (mapmarker !== \'\') { marker.setMap(null); }
                              mapmarker = marker.getPosition();
                              
                              mapmarkerdisplay.setPosition(mapmarker);
                              mapmarkerdisplay.setMap(map);
                              jQuery("input[name=\''.$field->args['id'].'[lat]\']").val(marker.position.lat());
                              jQuery("input[name=\''.$field->args['id'].'[lng]\']").val(marker.position.lng());
                              jQuery("input[name=\''.$field->args['id'].'[latlng]\']").val(JSON.stringify(mapmarker));
                          });


                    }
                    
                    function runSnapToRoad(path) {
                      var pathValues = [];
                      for (var i = 0; i < path.getLength(); i++) {
                        pathValues.push(path.getAt(i).toUrlValue());
                      }
                    
                      jQuery.get(\'https://roads.googleapis.com/v1/snapToRoads\', {
                        interpolate: true,
                        key: apiKey,
                        path: pathValues.join(\'|\')
                      }, function(data) {
                        if (typeof snappedPolyline !== \'undefined\') { snappedPolyline.setMap(null); }
                        processSnapToRoadResponse(data);
                        drawSnappedPolyline();
                      });
                      
                    }
                    
                    // Store snapped polyline returned by the snap-to-road service.
                    function processSnapToRoadResponse(data) {
                      snappedCoordinates = [];
                      placeIdArray = [];
                      for (var i = 0; i < data.snappedPoints.length; i++) {
                        var latlng = new google.maps.LatLng(
                            data.snappedPoints[i].location.latitude,
                            data.snappedPoints[i].location.longitude);
                        snappedCoordinates.push(latlng);
                        placeIdArray.push(data.snappedPoints[i].placeId);
                      }
                      
                      jQuery("textarea[name=\''.$field->args['id'].'[array]\']").val(JSON.stringify(snappedCoordinates));
                    }
                    
                    // Draws the snapped polyline (after processing snap-to-road response).
                    function drawSnappedPolyline() {
                        
                      snappedPolyline = new google.maps.Polyline({
                        path: snappedCoordinates,
                        strokeColor: \'#23c3e9\',
                        strokeWeight: 5
                      });
                    
                      snappedPolyline.setMap(map);
                      polylines.push(snappedPolyline);
                    }



                    
                    jQuery(window).load(initialize);
                    
                </script>
    	        ';
    	        
    	        echo '
    	        
    	            <style>
                  
                </style>

    	        
    	            <div class="pac-card" id="pac-card">
                  <div>
                    <div id="title">
                      Zoom to Location...
                    </div>
                    <div id="type-selector" class="pac-controls">
                      <input type="radio" name="type" id="changetype-all" checked="checked">
                      <label for="changetype-all">All</label>
            
                      <input type="radio" name="type" id="changetype-establishment">
                      <label for="changetype-establishment">Establishments</label>
            
                      <input type="radio" name="type" id="changetype-address">
                      <label for="changetype-address">Addresses</label>
            
                      <input type="radio" name="type" id="changetype-geocode">
                      <label for="changetype-geocode">Geocodes</label>
                    </div>
                    <div id="strict-bounds-selector" class="pac-controls">
                      <input type="checkbox" id="use-strict-bounds" value="">
                      <label for="use-strict-bounds">Strict Bounds</label>
                    </div>
                  </div>
                  <div id="pac-container">
                    <input id="pac-input" type="text"
                        placeholder="Enter a location">
                  </div>
                </div>
    	        
    	            <div id="snapmap-map" style="height: 500px;"></div>
    	            
    	            <div id="infowindow-content">
                  <img src="" width="16" height="16" id="place-icon">
                  <span id="place-name"  class="title"></span><br>
                  <span id="place-address"></span>
                </div>

    	            
    	        '; 
    	        ?>
                
            <div class="cmb2-roadway-segments-fields" id="accordion">
                
                <h3>Marker Options</h3>
                
                <div class="cmb2-roadway-segments-manual">
            
                    <div class="marker-lat-field"><p><label for="<?php echo $field_type->_id( '_tooltip' ); ?>">Marker Tooltip Label</label></p>
                    		<?php echo $field_type->input( array(
                    			'name'  => $field_type->_name( '[tooltip]' ),
                    			'id'    => $field_type->_id( '_tooltip' ),
                    			'value' => $value['tooltip'],
                    			'desc'  => '',
                    		) ); ?>
                    	</div>
                    	<div class="marker-lat-field"><p><label for="<?php echo $field_type->_id( '_lat' ); ?>">Marker Latitude (Manual Entry)</label></p>
                    		<?php echo $field_type->input( array(
                    			'name'  => $field_type->_name( '[lat]' ),
                    			'id'    => $field_type->_id( '_lat' ),
                    			'value' => $value['lat'],
                    			'desc'  => '',
                    		) ); ?>
                    	</div>
                    	<div class="marker-lng-field"><p><label for="<?php echo $field_type->_id( '_lng' ); ?>">Marker Longitude (Manual Entry)</label></p>
                    		<?php echo $field_type->input( array(
                    			'name'  => $field_type->_name( '[lng]' ),
                    			'id'    => $field_type->_id( '_lng' ),
                    			'value' => $value['lng'],
                    			'desc'  => '',
                    		) ); ?>
                    	</div>
                    	
                </div>
                
                <h3>Debug Info</h3>
                
                <div class="cmb2-roadway-segments-manual">
                
                    <div class="marker-lat-field"><p><label for="<?php echo $field_type->_id( '_latlng' ); ?>">Map Marker JSON (Will refresh on save is using manual entry)</label></p>   
                    		<?php echo $field_type->input( array(
                    			'name'  => $field_type->_name( '[latlng]' ),
                    			'id'    => $field_type->_id( '_latlng' ),
                    			'value' => $value['latlng'],
                    			'desc'  => '',
                    			'disabled' => ''
                    		) ); ?>
                    </div>
                	
                	
                    	<?php 
                        if ($field->args['limit_drawing']) { 
                            //Do nothing
                        } else { ?>
                            <div class="marker-lat-field"><p><label for="<?php echo $field_type->_id( '_array' ); ?>">Processed Road Segment JSON (Will refresh on save is using manual entry)</label></p>
                            		<?php echo $field_type->textarea( array(
                            			'name'  => $field_type->_name( '[array]' ),
                            			'id'    => $field_type->_id( '_array' ),
                            			'value' => $value['array'],
                            			'desc'  => ''
                            		) ); ?>
                            </div>
                            	
                    	<?php 
                        	} 
                    ?>
                    
                    
                
                </div>
                    
            </div>
            
            <?php echo $field_type->input( array(
        			'name'  => $field_type->_name( '[prefix]' ),
        			'id'    => $field_type->_id( '_prefix' ),
        			'value' => ( empty($value['prefix']) ? $field->args['id'] : $value['prefix'] ),
        			'type'  => 'hidden'
        		) ); ?>
        		
	        <?php
        	    
    	    } else {
        	    
        	    echo 'Valid API key required.';
    	    }
	    
	    
    
    }
    
    public function sanitize_snapmap( $override_value, $value, $object_id, $field_args ) {
        
        if ( (!empty($value['lat'])) && (!empty($value['lng'])) ) {
            $value['latlng'] = '{"lat":'.$value['lat'].',"lng":'.$value['lng'].'}';
        } else {
            $value['latlng'] = '';    
        }
        
        update_post_meta( $object_id, 'cmb2_roadway_segments_prefix', $value['prefix'] );
        
        return $value;
        
    }
	
	public function setup_admin_scripts($api_key) {
         
         if($api_key){
            $enqueue_maps = $this->wp_cmb2_segment_options['enqueue'];
            
            if (!$enqueue_maps == 1) {
                // Lets load up some Google Maps JS
        	        wp_enqueue_script( 'google-maps-drawing', 'https://maps.googleapis.com/maps/api/js?libraries=drawing,places&key='.$api_key, array('jquery'), '1.0.0', false ); 
        	    }         
          }
	}

}

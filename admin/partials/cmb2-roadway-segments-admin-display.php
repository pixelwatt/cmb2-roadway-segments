<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://pixelwatt.com
 * @since      1.0.0
 *
 * @package    Cmb2_Roadway_Segments
 * @subpackage Cmb2_Roadway_Segments/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<div class="wrap">

    <h2><?php echo esc_html(get_admin_page_title()); ?></h2>

    <form method="post" name="dislay_options" action="options.php">
        <?php 
             //Grab all options
             $options = get_option($this->plugin_name);

             // Cleanup
             $enqueue = $options['enqueue'];
             $apikey = $options['apikey'];
             $fullscreen = $options['fullscreen'];
             $streetview = $options['streetview'];
             $maptype = $options['maptype'];
             $mapstyle = $options['mapstyle'];
             $strokecolor = $options['strokecolor'];
             $circlestroke = ( isset($options['circlestroke']) ? $options['circlestroke'] : '' );
             $circlefill = ( isset($options['circlefill']) ? $options['circlefill'] : '' );
             
             settings_fields($this->plugin_name);
             do_settings_sections($this->plugin_name);
        ?>
        
        
        <!-- remove some meta and generators from the <head> -->
        
            <h2 class="title">Google Maps API</h2>
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label for="<?php echo $this->plugin_name; ?>-apikey">API Key</label>
                            </th>
                            <td>
                                <fieldset>
                                <legend class="screen-reader-text"><span><?php _e('Google Maps API Key', $this->plugin_name); ?></span></legend>
                                <input type="text" class="regular-text" id="<?php echo $this->plugin_name; ?>-apikey" name="<?php echo $this->plugin_name; ?>[apikey]" value="<?php if(!empty($apikey)) echo $apikey; ?>" />
                                <p class="description">A valid Google Maps API key is required to use this plugin.</p>
                                </fieldset>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                Javascript
                            </th>
                            <td>
                                <fieldset>
                                <legend class="screen-reader-text"><span>Google Maps JS</span></legend>
                                <label for="<?php echo $this->plugin_name; ?>-enqueue">
                                    <input type="checkbox" id="<?php echo $this->plugin_name; ?>-enqueue" name="<?php echo $this->plugin_name; ?>[enqueue]" value="1" <?php checked($enqueue, 1); ?>/>
                                    <span><?php _e('<strong>Do not enqueue Google Maps JS.</strong>', $this->plugin_name); ?></span>
                                </label>
                                <p class="description">Only use if another copy of the v3 API is being loaded by another plugin with the drawing and places API. API key still required for roadway snapping.</p>
                                </fieldset>
                            </td>
                        </tr>
                    </tbody>
                </table>
            
            <h2 class="title">Front-End Display</h2>
            
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row">
                                Map Controls
                            </th>
                            <td>
                                <fieldset>
                                    <legend class="screen-reader-text"><span>Include Full-Screen Control</span></legend>
                                    <label for="<?php echo $this->plugin_name; ?>-fullscreen">
                                        <input type="checkbox" id="<?php echo $this->plugin_name; ?>-enqueue" name="<?php echo $this->plugin_name; ?>[fullscreen]" value="1" <?php checked($fullscreen, 1); ?>/>
                                        <span><?php _e('Include Full-Screen Control', $this->plugin_name); ?></span>
                                    </label>
                                </fieldset>
                                <fieldset>
                                    <legend class="screen-reader-text"><span>Include Street View Control</span></legend>
                                    <label for="<?php echo $this->plugin_name; ?>-streetview">
                                        <input type="checkbox" id="<?php echo $this->plugin_name; ?>-streetview" name="<?php echo $this->plugin_name; ?>[streetview]" value="1" <?php checked($streetview, 1); ?>/>
                                        <span><?php _e('Include Street View Control', $this->plugin_name); ?></span>
                                    </label>
                                </fieldset>
                                <fieldset>
                                    <legend class="screen-reader-text"><span>Include Map-Type Control</span></legend>
                                    <label for="<?php echo $this->plugin_name; ?>-maptype">
                                        <input type="checkbox" id="<?php echo $this->plugin_name; ?>-maptype" name="<?php echo $this->plugin_name; ?>[maptype]" value="1" <?php checked($maptype, 1); ?>/>
                                        <span><?php _e('Include Map-Type Control', $this->plugin_name); ?></span>
                                    </label>
                                </fieldset>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                Polyline Stroke Color
                            </th>
                            <td>
                                <input type="text" class="iris-picker" id="<?php echo $this->plugin_name; ?>-strokecolor" name="<?php echo $this->plugin_name; ?>[strokecolor]" value="<?php if(!empty($strokecolor)) { echo $strokecolor; } ?>" />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                Cirlce / Polygon Stroke Color
                            </th>
                            <td>
                                <input type="text" class="iris-picker" id="<?php echo $this->plugin_name; ?>-circlestroke" name="<?php echo $this->plugin_name; ?>[circlestroke]" value="<?php if(!empty($circlestroke)) { echo $circlestroke; } ?>" />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                Cirlce / Polygon Fill Color
                            </th>
                            <td>
                                <input type="text" class="iris-picker" id="<?php echo $this->plugin_name; ?>-circlefill" name="<?php echo $this->plugin_name; ?>[circlefill]" value="<?php if(!empty($circlefill)) { echo $circlefill; } ?>" />
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <fieldset>
                    <h2 class="title">Custom Map Styles</h2>
                    <textarea class="large-text code" id="<?php echo $this->plugin_name; ?>-mapstyle" name="<?php echo $this->plugin_name; ?>[mapstyle]" rows="10"><?php if(!empty($mapstyle)) echo $mapstyle; ?></textarea>
                </fieldset>
            
                
                
        
        
        <?php submit_button('Save all changes', 'primary','submit', TRUE); ?>
        
    </form>
    
</div>

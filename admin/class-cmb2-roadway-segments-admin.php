<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://pixelwatt.com
 * @since      1.0.0
 *
 * @package    Cmb2_Roadway_Segments
 * @subpackage Cmb2_Roadway_Segments/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Cmb2_Roadway_Segments
 * @subpackage Cmb2_Roadway_Segments/admin
 * @author     PixelWatt <hello@pixelwatt.com>
 */
class Cmb2_Roadway_Segments_Admin {

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/cmb2-roadway-segments-admin.css', array(), $this->version, 'all' );
		wp_enqueue_style( '//code.jquery.com/ui/1.11.4/themes/base/jquery-ui.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
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
         wp_enqueue_script( 'iris' );
        wp_enqueue_script('jquery-ui-accordion');
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/cmb2-roadway-segments-admin.js', array( 'jquery', 'jquery-ui-accordion' ), $this->version, true );

	}
	
	public function add_plugin_admin_menu() {
    
        /*
         * Add a settings page for this plugin to the Settings menu.
         *
         * NOTE:  Alternative menu locations are available via WordPress administration menu functions.
         *
         *        Administration Menus: http://codex.wordpress.org/Administration_Menus
         *
         */
        add_options_page( 'CMB2 Roadway Segments Options', 'CMB2 Segments', 'manage_options', $this->plugin_name, array($this, 'display_plugin_setup_page')
        );
    }
    
    
    public function add_action_links( $links ) {
        /*
        *  Documentation : https://codex.wordpress.org/Plugin_API/Filter_Reference/plugin_action_links_(plugin_file_name)
        */
       $settings_link = array(
        '<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_name ) . '">' . __('Settings', $this->plugin_name) . '</a>',
       );
       return array_merge(  $settings_link, $links );
    
    }
    
    
    public function display_plugin_setup_page() {
        include_once( 'partials/cmb2-roadway-segments-admin-display.php' );
    }
    
    
    public function validate($input) {
        // All checkboxes inputs        
        $valid = array();
    
        //Cleanup
        $valid['apikey'] = $input['apikey'];
        $valid['enqueue'] = (isset($input['enqueue']) && !empty($input['enqueue'])) ? 1 : 0;
        $valid['fullscreen'] = (isset($input['fullscreen']) && !empty($input['fullscreen'])) ? 1 : 0;
        $valid['streetview'] = (isset($input['streetview']) && !empty($input['streetview'])) ? 1 : 0;
        $valid['maptype'] = (isset($input['maptype']) && !empty($input['maptype'])) ? 1 : 0;
        $valid['mapstyle'] = $input['mapstyle'];
        $valid['strokecolor'] = $input['strokecolor'];
        
    
        return $valid;
    }
    
    
    public function options_update() {
        register_setting($this->plugin_name, $this->plugin_name, array($this, 'validate'));
    }

}

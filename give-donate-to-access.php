<?php
/**
 * Plugin Name: Give Addon - Donate To Accesss
 * Plugin URI: https://github.com/contriveitup/give-add-on-donate-to-access
 * Description: This Give plugin Add-on ask users to donate in order to access certain material, whether downloads, or a membership form, or specific content.
 * Version: 1.0
 * Author: ContriveItUp
 * Author URI: https://github.com/contriveitup
 * Text Domain: give-dta
 * Domain Path: /languages
 * GitHub Plugin URI: https://github.com/contriveitup/give-add-on-donate-to-access
 * License: GPL2
 */

 
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( ! class_exists( 'Give_Donate_To_Access' ) ) :

	/**
	 * Main Add-On class
	 * 
	 * @since 1.0
	 */
	class Give_Donate_To_Access {

		/**
		 * Main Class Instance
		 * 
		 * @since 1.0
		 * @access protected
		 * 
		 * @var Main class instanace Give_Donate_To_Access()
		 */ 
		protected static $instance;


		/**
		 * Singleton Method
		 * 
		 * Makes sure only one instance of the class is returned
		 * 
		 * @since 1.0
		 * @access public
		 * 
		 * @return Class Instance 
		 */
		public static function give_dta_instance() {

	        if ( ! isset( self::$instance ) ) {
	            self::$instance = new self();
	        }

	        return self::$instance;
	    }


		/**
		 * Class Constructor
		 */
		public function __construct() {
			$this->give_dta_hooks();
			$this->give_dta_constants();
			$this->give_dta_includes();
		}


		/**
		 * Throw error on object clone
		 *
		 * The whole idea of the singleton design pattern is that there is a single
		 * object, therefore we don't want the object to be cloned.
		 *
		 * @since  1.0
		 * @access protected
		 *
		 * @return void
		 */
		public function __clone() {
			// Cloning instances of the class is forbidden.
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'give' ), '1.0' );
		}


		/**
		 * WordPress Hooks
		 * 
		 * This contains plugin specific WordPress Hooks/Actions
		 * 
		 * @since 1.0
		 * 
		 * @return viod
		 */
		public function give_dta_hooks() {
			//Registration hook
			register_activation_hook( __FILE__, array( $this, 'give_dta_install' ) );
			add_action( 'plugins_loaded', array( $this, 'give_dta_plugin_init' ) );
			add_filter( "plugin_action_links_" . plugin_basename(__FILE__), array( $this, 'give_dta_plugin_add_settings_link' ) );
		}


		/**
		 * Plugin Init
		 * 
		 * Things required at the initialization of the plugin or when the plugin is loaded
		 * Uses plugin_loaded WordPress Hook
		 * 
		 * @since 1.0
		 * 
		 * @return void
		 */
		public function give_dta_plugin_init() {
			
		}


		/**
		 * Add Settings link to the plugin page
		 * 
		 * @since 1.0
		 * @param $links array
		 * 
		 * @return array
		 */
		public function give_dta_plugin_add_settings_link( $links ) {
			 $mylinks = array(
			 '<a href="' . admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=donateaccess' ) . '">Settings</a>',
			 );
			return array_merge( $links, $mylinks );
		}

		/**
		 * WP Registration Hook
		 * 
		 * Runs when the plugin is activated
		 * 
		 * @since 1.0
		 * @static
		 */
		public function give_dta_install() {
            
            //Check if Main Give plugin is activated 
            if( ! function_exists( 'Give' ) ) {
            	deactivate_plugins( basename( __FILE__ ) );
            	$message = __( '<p>This Add-On requires <strong>Give</strong> core plugin to be installed and activated.</p>', 'give-dta' );
            	wp_die(
            		$message,
            		'Plugin Activation Error',  
            		array( 'response' => 200, 'back_link' => TRUE ) );
            }
     	}


     	/**
     	 * PLugin Constants
     	 * 
     	 * Required constants to be used by the plugin.
     	 * 
     	 * @since 1.0
     	 * 
     	 * @return string
     	 */
     	private function give_dta_constants() {

     		// Plugin Folder Path
			if ( ! defined( 'GIVE_DTA_PLUGIN_DIR' ) ) {
				define( 'GIVE_DTA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
			}

			// Plugin Folder URL
			if ( ! defined( 'GIVE_DTA_PLUGIN_URL' ) ) {
				define( 'GIVE_DTA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
			}

			// Plugin Basename aka: "give-donate-to-access/give-donate-to-access.php"
			if ( ! defined( 'GIVE_DTA_PLUGIN_BASENAME' ) ) {
				define( 'GIVE_DTA_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
			}

			// Plugin Root File
			if ( ! defined( 'GIVE_DTA_PLUGIN_FILE' ) ) {
				define( 'GIVE_DTA_PLUGIN_FILE', __FILE__ );
			}
     	}


     	/**
     	 * Plugin Files
     	 * 
     	 * Include plugin files to run different plugin functionality
     	 * 
     	 * @since 1.0
     	 * 
     	 * @return void 
     	 */
     	public function give_dta_includes() {

     		require_once GIVE_DTA_PLUGIN_DIR . 'includes/class-functions.php';
     		require_once GIVE_DTA_PLUGIN_DIR . 'includes/class-init-functions.php';

     		require_once GIVE_DTA_PLUGIN_DIR . 'includes/admin/class-give-donate-to-access-settings.php';

     		require_once GIVE_DTA_PLUGIN_DIR . 'includes/shortcodes.php';
     	}

	} // End class Give_Donate_To_Access

endif; // End if class_exists check

//Plugin instance
Give_Donate_To_Access::give_dta_instance();
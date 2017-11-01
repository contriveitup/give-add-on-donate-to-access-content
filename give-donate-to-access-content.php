<?php
/**
 * Plugin Name: Give Addon - Donate To Accesss Content
 * Plugin URI: https://github.com/contriveitup/give-add-on-donate-to-access-content
 * Description: This Give plugin Add-on ask users to donate in order to access content of a post or page. It can also restrict compelete website to chosen post, page, category page, post types and much more...
 * Version: 1.0
 * Author: ContriveItUp
 * Author URI: https://github.com/contriveitup
 * Text Domain: give-dta
 * Domain Path: /languages
 * GitHub Plugin URI: https://github.com/contriveitup/give-add-on-donate-to-access-content
 * License: GPL2
 */

 
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Main Add-On class
 * 
 * @since 1.0
 */
final class Give_Donate_To_Access_Content {

	/**
	 * Main Class Instance
	 * 
	 * @since 1.0
	 * @access private
	 * 
	 * @var Main class instanace Give_Donate_To_Access_Content()
	 */ 
	private static $instance;


	/**
	 * [$frontend_functions]
	 * 
	 * Save instance of Give_Donate_To_Access_Functions class
	 * 
	 * @since  1.0
	 * @access public
	 * 
	 * @var [object]
	 */
	public $frontend_functions;

	/**
	 * [$give]
	 * 
	 * Save core Give plugin class instance
	 * 
	 * @var [public]
	 */
	public $give;


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
	public static function give_dtac_instance() {

        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }


	/**
	 * Class Constructor
	 */
	public function __construct() {

		$this->give = Give();
		$this->give_dtac_hooks();
		$this->give_dtac_constants();
		$this->give_dtac_includes();
		$this->give_dtac_setup();
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
	public function give_dtac_hooks() {
		//Registration hook
		register_activation_hook( __FILE__, array( $this, 'give_dtac_install' ) );
		add_filter( "plugin_action_links_" . plugin_basename(__FILE__), array( $this, 'give_dtac_plugin_add_settings_link' ) );
	}


	/**
	 * Add Settings link to the plugin page
	 * 
	 * @since 1.0
	 * @param $links array
	 * 
	 * @return array
	 */
	public function give_dtac_plugin_add_settings_link( $links ) {
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
	public function give_dtac_install() {
        
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
 	private function give_dtac_constants() {

 		// Plugin Folder Path
		if ( ! defined( 'GIVE_DTAC_PLUGIN_DIR' ) ) {
			define( 'GIVE_DTAC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		}

		// Plugin Folder URL
		if ( ! defined( 'GIVE_DTAC_PLUGIN_URL' ) ) {
			define( 'GIVE_DTAC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}

		// Plugin Basename aka: "give-donate-to-access/give-donate-to-access.php"
		if ( ! defined( 'GIVE_DTAC_PLUGIN_BASENAME' ) ) {
			define( 'GIVE_DTAC_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
		}

		// Plugin Root File
		if ( ! defined( 'GIVE_DTAC_PLUGIN_FILE' ) ) {
			define( 'GIVE_DTAC_PLUGIN_FILE', __FILE__ );
		}

		// Plugin Root File
		if ( ! defined( 'GIVE_DTAC_PLUGIN_VERSION' ) ) {
			define( 'GIVE_DTAC_PLUGIN_VERSION', 1.0 );
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
 	public function give_dtac_includes() {


 		//General
 		require_once GIVE_DTAC_PLUGIN_DIR . 'includes/functions.php';

 		//Frontend
	 	require_once GIVE_DTAC_PLUGIN_DIR . 'includes/frontend/class-functions.php';
 		require_once GIVE_DTAC_PLUGIN_DIR . 'includes/frontend/class-hooks.php';
 		require_once GIVE_DTAC_PLUGIN_DIR . 'includes/frontend/shortcodes.php';

 		//Admin
 		require_once GIVE_DTAC_PLUGIN_DIR . 'includes/admin/class-give-donate-to-access-settings.php';
 		
 	}


 	public function give_dtac_setup() {

 		/**
 		 * Fires before plugin setup
 		 * 
 		 * @since  1.0
 		 */
 		do_action( 'give_dtac_before_plugin_setup' );

 		//Frontend
 		$this->frontend_functions = new Give_Donate_To_Access_Content_Functions();

 		/**
 		 * Fires after plugin setup
 		 * 
 		 * @since  1.0
 		 */
 		do_action( 'give_dtac_after_plugin_setup' );
 	}

} // End class Give_Donate_To_Access_Content


/**
 * [GIVE_DTAC description]
 */
function GIVE_DTAC(){
	return Give_Donate_To_Access_Content::give_dtac_instance();
}
add_action( 'plugins_loaded', 'GIVE_DTAC' );
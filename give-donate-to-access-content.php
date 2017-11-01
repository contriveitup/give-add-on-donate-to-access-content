<?php
/**
 * Plugin Name: Give Addon - Donate To Accesss Content
 * Plugin URI: https://github.com/contriveitup/give-add-on-donate-to-access-content
 * Description: This Give plugin Add-on ask users to donate in order to access content of a post or page. It can also restrict compelete website to chosen post, page, category page, post types and much more...
 * Version: 1.0
 * Author: ContriveItUp
 * Author URI: https://github.com/contriveitup
 * Text Domain: give-dtac
 * Domain Path: /languages
 * GitHub Plugin URI: https://github.com/contriveitup/give-add-on-donate-to-access-content
 * License: GPL3
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
	 * Notices (array).
	 *
	 * @since 1.0
	 *
	 * @var [array]
	 */
	public $admin_notices = array();


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

		$this->give_dtac_hooks();
		$this->give_dtac_constants();

		if( function_exists( 'Give' ) ) {
			$this->give = Give();	
			$this->load_textdomain();
			$this->give_dtac_includes();
			$this->give_dtac_setup();
		}
		
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
		add_action( 'admin_notices', array( $this, 'give_dtca_admin_notices' ) );
		add_action( 'admin_init', array( $this, 'give_dtac_install' ) );
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

        	$this->add_admin_notice( 'prompt_connect', 'error', sprintf( __( '<strong>Activation Error:</strong> You must have the <a href="%s" target="_blank">Give</a> core plugin installed and activated for Give Donate to Access Content Add-On to Work.', 'give-dtca' ), 'https://givewp.com' ) );

        	deactivate_plugins( GIVE_DTAC_PLUGIN_BASENAME );

        	if ( isset( $_GET['activate'] ) ) {
				unset( $_GET['activate'] );
			}
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
 	 * [add_admin_notice]
 	 * 
 	 * Capture Admin Notices in an array
 	 * 
 	 * @since  1.0
 	 * 
 	 * @param [string] 	$slug    [message slug]
 	 * @param [string] 	$class   [message class like error, etc..]
 	 * @param [string] 	$message [the error or notice message]
 	 * 
 	 * @return  array 
 	 */
 	public function add_admin_notice( $slug, $class, $message ) {
		$this->admin_notices[ $slug ] = array(
			'class'   => $class,
			'message' => $message
		);
	}


	/**
	 * [give_dtca_admin_notices]
	 * 
	 * Add notices to admin_notices WP hook
	 * 
	 * @since  1.0
	 * 
	 * @return [HTML] 
	 */
 	public function give_dtca_admin_notices(){

 		$allowed_tags = array(
			'a'      => array(
				'href'  => array(),
				'title' => array()
			),
			'br'     => array(),
			'em'     => array(),
			'strong' => array(),
		);

		foreach ( (array) $this->admin_notices as $key => $admin_notice ) {
			echo "<div class='" . esc_attr( $admin_notice['class'] ) . "'><p>";
			echo wp_kses( $admin_notice['message'], $allowed_tags );
			echo "</p></div>";
		}
 	}


 	/**
	 * Loads the plugin language files.
	 *
	 * @since  v1.0
	 * @access private
	 * @uses   dirname()
	 * @uses   plugin_basename()
	 * @uses   apply_filters()
	 * @uses   load_textdomain()
	 * @uses   get_locale()
	 * @uses   load_plugin_textdomain()
	 */
	private function load_textdomain() {

		// Set filter for plugin's languages directory.
		$give_lang_dir = apply_filters( 'give_languages_directory', dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

		// Traditional WordPress plugin locale filter.
		$locale = apply_filters( 'plugin_locale', get_locale(), 'give-donate-to-access-content' );
		$mofile = sprintf( '%1$s-%2$s.mo', 'give-donate-to-access-content', $locale );

		// Setup paths to current locale file.
		$mofile_local  = $give_lang_dir . $mofile;
		$mofile_global = WP_LANG_DIR . '/give-donate-to-access-content/' . $mofile;

		if ( file_exists( $mofile_global ) ) {
			// Look in global /wp-content/languages/give-donate-to-access-content folder.
			load_textdomain( 'give-donate-to-access-content', $mofile_global );
		} elseif ( file_exists( $mofile_local ) ) {
			// Look in local /wp-content/plugins/give-donate-to-access-content/languages/ folder.
			load_textdomain( 'give-donate-to-access-content', $mofile_local );
		} else {
			// Load the default language files
			load_plugin_textdomain( 'give-donate-to-access-content', false, $give_lang_dir );
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
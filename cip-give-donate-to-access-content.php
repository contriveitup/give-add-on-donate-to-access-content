<?php
/**
 * Plugin Name: Give Addon - Donate To Access Content
 * Plugin URI: https://github.com/contriveitup/give-add-on-donate-to-access-content
 * Description: Give plugin Add-on ask users to donate in order to access content of a post or page. It can also restrict entire website to chosen post, page, category page, post types and much more...
 * Version: 2.1.0
 * Author: ContriveItUp
 * Author URI: https://github.com/contriveitup
 * Text Domain: dtac-give
 * Domain Path: /languages
 * GitHub Plugin URI: https://github.com/contriveitup/give-add-on-donate-to-access-content
 * License: GPL3
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Main Add-On class
 *
 * @since 1.0.0
 */
final class Donate_To_Access_Content_Give_Addon {

	/**
	 * Main Class Instance.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 *
	 * @var object
	 */
	private static $instance;

	/**
	 * Save core Give plugin class instance.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	public $give;

	/**
	 * Notices (array).
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	public $admin_notices = array();

	/**
	 * Singleton Method.
	 *
	 * Makes sure only one instance of the class is returned.
	 *
	 * @since 1.0.0
	 *
	 * @return object
	 */
	public static function dtac_give_instance() {

		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Class Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function __construct() {

		$this->dtac_give_hooks();
		$this->dtac_give_constants();

		if ( function_exists( 'Give' ) ) {

			$this->give = Give();
			$this->load_textdomain();
			$this->dtac_give_includes();
			$this->dtac_give_setup();
		}
	}

	/**
	 * Throw error on object clone
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object, therefore we don't want the object to be cloned.
	 *
	 * @since  1.0.0
	 * @access protected
	 *
	 * @return void
	 */
	private function __clone() {
		// Cloning instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'dtac-give' ), '1.0' );
	}

	/**
	 * Throw error on object wakeup
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object, therefore we don't want the object to be cloned.
	 *
	 * @since  1.0.0
	 * @access protected
	 *
	 * @return void
	 */
	private function __wakeup() {
		// Cloning instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'dtac-give' ), '1.0' );
	}

	/**
	 * WordPress Hooks.
	 *
	 * This contains plugin specific WordPress Hooks/Actions.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function dtac_give_hooks() : void {

		// Registration hook.
		add_action( 'admin_notices', array( $this, 'give_dtca_admin_notices' ) );
		add_action( 'admin_init', array( $this, 'dtac_give_install' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'dtac_give_plugin_add_settings_link' ) );
	}

	/**
	 * Add Settings link to the plugin page.
	 *
	 * @since 1.0.0
	 *
	 * @param array $links Setting links array.
	 *
	 * @return array
	 */
	public function dtac_give_plugin_add_settings_link( array $links ) : array {

		$dtac_links = array(
			'<a href="' . esc_url( admin_url( 'options-general.php?page=dtac' ) ) . '">' . esc_html__( 'Settings', 'dtac-give' ) . '</a>',
		);

		return array_merge( $dtac_links, $links );
	}

	/**
	 * WP Registration Hook.
	 *
	 * Runs when the plugin is activated.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function dtac_give_install() : void {

		// Check if Main Give plugin is activated.
		if ( ! function_exists( 'Give' ) ) {

			$this->add_admin_notice(
				'prompt_connect',
				'error',
				sprintf(
					__(
						'Activation Error: You must have the <a href="%s" target="_blank" title="Download Give WP Plugin">Give</a> core plugin installed and activated for Give Donate to Access Content Add-On to Work.',
						'dtac-give'
					),
					'https://givewp.com'
				)
			);

			deactivate_plugins( DTAC_GIVE_PLUGIN_BASENAME );

			if ( isset( $_GET['activate'] ) ) {
				unset( $_GET['activate'] );
			}
		}
	}

	/**
	 * PLugin Constants.
	 *
	 * Required constants to be used by the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function dtac_give_constants() : void {

		// Plugin Folder Path.
		if ( ! defined( 'DTAC_GIVE_PLUGIN_DIR' ) ) {
			define( 'DTAC_GIVE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		}

		// Plugin Folder URL.
		if ( ! defined( 'DTAC_GIVE_PLUGIN_URL' ) ) {
			define( 'DTAC_GIVE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}

		// Plugin Basename aka: "give-donate-to-access/give-donate-to-access.php".
		if ( ! defined( 'DTAC_GIVE_PLUGIN_BASENAME' ) ) {
			define( 'DTAC_GIVE_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
		}

		// Plugin Root File.
		if ( ! defined( 'DTAC_GIVE_PLUGIN_FILE' ) ) {
			define( 'DTAC_GIVE_PLUGIN_FILE', __FILE__ );
		}

		// Plugin Version.
		if ( ! defined( 'DTAC_GIVE_PLUGIN_VERSION' ) ) {
			define( 'DTAC_GIVE_PLUGIN_VERSION', '2.0.0' );
		}
	}

	/**
	 * Capture Admin Notices in an array.
	 *
	 * @since  1.0.0
	 *
	 * @param string $slug    message slug.
	 * @param string $class   message class like error, etc..
	 * @param string $message The error or notice message.
	 *
	 * @return  void
	 */
	public function add_admin_notice( string $slug, string $class, string $message ) : void {
		$this->admin_notices[ $slug ] = array(
			'class'   => $class,
			'message' => $message,
		);
	}

	/**
	 * Add notices to admin_notices WP hook.
	 *
	 * @since  1.0.0
	 *
	 * @return void
	 */
	public function give_dtca_admin_notices() : void {

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

			echo '<div class="' . esc_attr( $admin_notice['class'] ) . '"><p>';
			echo wp_kses( $admin_notice['message'], $allowed_tags );
			echo '</p></div>';
		}
	}

	/**
	 * Loads the plugin language files.
	 *
	 * @since  1.0.0
	 *
	 * @access private
	 *
	 * @return void
	 */
	private function load_textdomain() : void {

		// Set filter for plugin's languages directory.
		$give_lang_dir = apply_filters( 'give_languages_directory', dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

		// Traditional WordPress plugin locale filter.
		$locale  = apply_filters( 'plugin_locale', get_locale(), 'give-donate-to-access-content' );
		$mo_file = sprintf( '%1$s-%2$s.mo', 'give-donate-to-access-content', $locale );

		// Setup paths to current locale file.
		$mo_file_local  = $give_lang_dir . $mo_file;
		$mo_file_global = WP_LANG_DIR . '/give-donate-to-access-content/' . $mo_file;

		if ( file_exists( $mo_file_global ) ) {

			// Look in global /wp-content/languages/give-donate-to-access-content folder.
			load_textdomain( 'give-donate-to-access-content', $mo_file_global );

		} elseif ( file_exists( $mo_file_local ) ) {

			// Look in local /wp-content/plugins/give-donate-to-access-content/languages/ folder.
			load_textdomain( 'give-donate-to-access-content', $mo_file_local );

		} else {

			// Load the default language files.
			load_plugin_textdomain( 'give-donate-to-access-content', false, $give_lang_dir );
		}
	}

	/**
	 * Include plugin files to run plugin's functionality.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function dtac_give_includes() : void {

		// General.
		require_once DTAC_GIVE_PLUGIN_DIR . 'includes/functions.php';

		// Composer autoload.
		require_once DTAC_GIVE_PLUGIN_DIR . 'vendor/autoload.php';
	}

	/**
	 * Plugin setup.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function dtac_give_setup() : void {

		/**
		 * Fires before plugin setup
		 *
		 * @since  1.0
		 */
		do_action( 'dtac_give_before_plugin_setup' );

		// Load Admin Modules.
		new DTAC\Admin\Settings();

		// Load Frontend Modules.
		new DTAC\Frontend\Hooks();
		new DTAC\Frontend\Restrict_Content();
		new DTAC\Frontend\Shortcodes();

		// Load Setup Modules.
		new \DTAC\Setup\Enqueue_Scripts();

		/**
		 * Fires after plugin setup
		 *
		 * @since  1.0
		 */
		do_action( 'dtac_give_after_plugin_setup' );
	}

} // End class Donate_To_Access_Content_Give_Addon

/**
 * Initialize main class instance.
 *
 * @since 2.0.0
 */
add_action( 'plugins_loaded', [ 'Donate_To_Access_Content_Give_Addon', 'dtac_give_instance' ] );

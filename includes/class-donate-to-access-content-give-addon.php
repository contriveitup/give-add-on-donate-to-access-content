<?php

/**
 * Main add-on class.
 *
 * Loaded only after the bootstrap confirms PHP 8.1+.
 *
 * @package DTAC_Give
 *
 * @since 1.0.0
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
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'dtac-give' ), '1.0' );
	}

	/**
	 * Throw error on object wakeup
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object, therefore we don't want the object to be cloned.
	 *
	 * @since  3.0.0 Method visibility is public for PHP 8+ compatibility.
	 * @since  1.0.0
	 * @access public
	 *
	 * @return void
	 */
	public function __wakeup() {
		// Cloning instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'dtac-give' ), '1.0' );
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
	public function dtac_give_hooks(): void {

		// Registration hook.
		add_action( 'admin_notices', array( $this, 'give_dtca_admin_notices' ) );
		add_action( 'admin_init', array( $this, 'dtac_give_install' ) );
		add_filter( 'plugin_action_links_' . DTAC_GIVE_PLUGIN_BASENAME, array( $this, 'dtac_give_plugin_add_settings_link' ) );
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
	public function dtac_give_plugin_add_settings_link( array $links ): array {

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
	public function dtac_give_install(): void {

		// Check if Main Give plugin is activated.
		if ( ! function_exists( 'Give' ) ) {

			$this->add_admin_notice(
				'prompt_connect',
				'error',
				sprintf(
					/* translators: %s: GiveWP download URL */
					__(
						'Activation Error: You must have the <a href="%s" target="_blank" title="Download Give WP Plugin">Give</a> core plugin installed and activated for Give Donate to Access Content Add-On to Work.',
						'dtac-give'
					),
					'https://givewp.com'
				)
			);

			deactivate_plugins( DTAC_GIVE_PLUGIN_BASENAME );

            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Core activation flag, not submitted form data.
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
	private function dtac_give_constants(): void {

		// Plugin Folder Path.
		if ( ! defined( 'DTAC_GIVE_PLUGIN_DIR' ) ) {
			define( 'DTAC_GIVE_PLUGIN_DIR', plugin_dir_path( DTAC_GIVE_PLUGIN_FILE ) );
		}

		// Plugin Folder URL.
		if ( ! defined( 'DTAC_GIVE_PLUGIN_URL' ) ) {
			define( 'DTAC_GIVE_PLUGIN_URL', plugin_dir_url( DTAC_GIVE_PLUGIN_FILE ) );
		}

		// Plugin Basename aka: "give-donate-to-access/give-donate-to-access.php".
		if ( ! defined( 'DTAC_GIVE_PLUGIN_BASENAME' ) ) {
			define( 'DTAC_GIVE_PLUGIN_BASENAME', plugin_basename( DTAC_GIVE_PLUGIN_FILE ) );
		}

		// Plugin Root File is defined in the PHP-safe bootstrap.

		// Plugin Version.
		if ( ! defined( 'DTAC_GIVE_PLUGIN_VERSION' ) ) {
			define( 'DTAC_GIVE_PLUGIN_VERSION', '3.0.0' );
		}
	}

	/**
	 * Capture Admin Notices in an array.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug         Message slug.
	 * @param string $notice_class Message class like error, etc.
	 * @param string $message      The error or notice message.
	 *
	 * @return void
	 */
	public function add_admin_notice( string $slug, string $notice_class, string $message ): void {
		$this->admin_notices[ $slug ] = array(
			'class'   => $notice_class,
			'message' => $message,
		);
	}

	/**
	 * Add notices to admin_notices WP hook.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function give_dtca_admin_notices(): void {

		$allowed_tags = array(
			'a'      => array(
				'href'  => array(),
				'title' => array(),
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
	 * @since 1.0.0
	 *
	 * @access private
	 *
	 * @return void
	 */
	private function load_textdomain(): void {

		$lang_dir = dirname( DTAC_GIVE_PLUGIN_BASENAME ) . '/languages/';

		load_plugin_textdomain( 'dtac-give', false, $lang_dir );
	}

	/**
	 * Include plugin files to run plugin's functionality.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function dtac_give_includes(): void {

		// Composer autoload.
		include_once DTAC_GIVE_PLUGIN_DIR . 'vendor/autoload.php';

		// General.
		include_once DTAC_GIVE_PLUGIN_DIR . 'includes/functions.php';
	}

	/**
	 * Plugin setup.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function dtac_give_setup(): void {

		/**
		 * Fires before plugin setup
		 *
		 * @since 1.0
		 */
		do_action( 'dtac_give_before_plugin_setup' );

		// Load Admin Modules.
		new DTAC\Admin\Settings();
		new DTAC\Admin\Metabox();
		new DTAC\Admin\Insights();

		// Load Frontend Modules.
		new DTAC\Frontend\Hooks();
		new DTAC\Frontend\Restrict_Content();
		new DTAC\Frontend\Shortcodes();
		new DTAC\Frontend\Leak_Protection();
		new DTAC\Frontend\Blocks();
		new DTAC\Frontend\Magic_Link();

		// Load Setup Modules.
		new \DTAC\Setup\Enqueue_Scripts();

		if ( defined( 'WP_CLI' ) && WP_CLI && class_exists( '\\WP_CLI' ) && class_exists( '\\DTAC\\CLI\\Seed_Command' ) ) {
			\WP_CLI::add_command( 'dtac', '\\DTAC\\CLI\\Seed_Command' );
		}

		/**
		 * Fires after plugin setup
		 *
		 * @since 1.0
		 */
		do_action( 'dtac_give_after_plugin_setup' );
	}
}

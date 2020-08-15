<?php
/**
 * Admin Settings
 *
 * @package DTAC_Give
 *
 * @since 1.0.0
 */

namespace DTAC\Admin;

use DTAC\Admin\Functions;
use DTAC\Interfaces\InterfaceFormSettings;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Settings' ) ) :

	/**
	 * Plugin Admin Settings
	 */
	class Settings extends Functions implements InterfaceFormSettings {

		/**
		 * Admin Page Title.
		 *
		 * @since 2.0.0
		 *
		 * @var string
		 */
		private $page_title = 'DTAC Settings Page';

		/**
		 * Admin Menu Title.
		 *
		 * @since 2.0.0
		 *
		 * @var string
		 */
		private $menu_title = 'DTAC';

		/**
		 * Admin Menu Slug.
		 *
		 * @since 2.0.0
		 *
		 * @var string
		 */
		private $menu_slug = 'dtac';

		/**
		 * Class Constructor
		 *
		 * @since 1.0.0
		 */
		public function __construct() {

			add_action( 'admin_menu', array( $this, 'dtac_settings_page' ) );
		}

		/**
		 * Add settings page to Settings WP Menu.
		 *
		 * @since 2.0.0
		 *
		 * @return void
		 */
		public function dtac_settings_page() : void {

			add_options_page(
				$this->page_title,
				$this->menu_title,
				'manage_options',
				$this->menu_slug,
				[ $this, 'dtac_settings_page_html' ]
			);
		}

		/**
		 * Include settings page HTML file.
		 *
		 * @since 2.0.0
		 *
		 * @return void
		 */
		public function dtac_settings_page_html() {

			$this->include_file( 'settings', 'html' );
		}

		/**
		 * Restrict Content Default Message.
		 *
		 * Used in dtac_give_settings function.
		 *
		 * @since 1.0.0
		 *
		 * @return string
		 */
		public function dtac_give_restrict_message_content() {

			$message = '<p>' . esc_html__( 'Please make a donation in order to view the content. Click on this link %%donation_form_url%% to make the donation', 'dtac-give' ) . '</p>';

			return $message;
		}

		/**
		 * Admin Settings.
		 *
		 * @since 1.0.0
		 *
		 * @return array
		 */
		public function dtac_form_settings() : array {

			$settings = array();

			$this->dtac_give_get_custom_tax();

			$settings = array(
				array(
					'name'    => esc_html__( 'Restrict Whole Website', 'dtac-give' ),
					'desc'    => __( "Selecting 'Yes' to this option will restrict the complete website expect for the Donation Form page.<br/><strong>To get this to work properly please provide a Give Donation 			   		Form ID below</strong>", 'dtac-give' ),
					'id'      => 'dtac_give_restrict_website',
					'type'    => 'select',
					'default' => 'no',
					'class'   => '',
					'options' => $this->dtac_give_settings_array( 'yes_no' ),
				),
				array(
					'name'    => esc_html__( 'Allow Pages', 'dtac-give' ),
					'desc'    => __( 'Please select pages you wish to give access to when you are restricting the whole website.<br/><strong>By Default: The Donation Form page whose ID has been mentioned below 			   		will always be given access to</strong>', 'dtac-give' ),
					'id'      => 'dtac_give_access_to_pages',
					'type'    => 'multi-select',
					'class'   => 'select2',
					'default' => array(),
					'options' => $this->dtac_give_get_pages_posts(),
				),
				array(
					'name'    => esc_html__( 'Give Donation Form ID', 'dtac-give' ),
					'desc'    => __( 'Please enter a Give Donation Form ID. <br/>This form will be the form that a user will be redirected to in order to make the donation and access the pages,posts,etc... 				 selected here to restrict.', 'dtac-give' ),
					'id'      => 'dtac_give_restrict_access_give_form_id',
					'type'    => 'text',
					'class'   => array( 'input', 'is-fullwidth' ),
					'default' => '1',
				),
				array(
					'name'    => esc_html__( 'Restrict Content Message', 'dtac-give' ),
					'desc'    => __( 'This message will appear instead of restricted content, if you choose to display a message instead of Donation form in the shortcode<br/>Please use %%donation_form_url%% to 				display the URL to donation form.<br/>The Donation form link will go to the form whose ID will be given in the shortcode', 'dtac-give' ),
					'id'      => 'dtac_give_restrict_message',
					'type'    => 'wysiwyg',
					'default' => $this->dtac_give_restrict_message_content(),
				),
				array(
					'name'    => esc_html__( 'Restrict Access To?', 'give' ),
					'desc'    => __( 'Restrict Access to complete page, post, category, etc..', 'dtac-give' ),
					'id'      => 'dtac_give_restrict_access_to',
					'type'    => 'multi-select',
					'class'   => 'select2',
					'default' => 'none',
					'options' => $this->dtac_give_settings_array( 'restrict_access_to' ),
				),
				array(
					'name'    => esc_html__( 'Restrict Pages', 'dtac-give' ),
					'desc'    => __( 'Please select the pages you wish to restrict', 'dtac-give' ),
					'id'      => 'dtac_give_restrict_access_to_pages',
					'type'    => 'multi-select',
					'class'   => 'select2',
					'default' => array(),
					'options' => $this->dtac_give_get_pages_posts(),
				),
				array(
					'name'    => esc_html__( 'Restrict Posts', 'dtac-give' ),
					'desc'    => __( 'Please select the posts you wish to restrict', 'dtac-give' ),
					'id'      => 'dtac_give_restrict_access_to_posts',
					'type'    => 'multi-select',
					'class'   => 'select2',
					'default' => array(),
					'options' => $this->dtac_give_get_pages_posts( 'posts' ),
				),
				array(
					'name'    => esc_html__( 'Restrict Custom Post Types', 'dtac-give' ),
					'desc'    => __( 'Please select the custom posts types you wish to restrict', 'dtac-give' ),
					'id'      => 'dtac_give_restrict_access_to_cpt',
					'type'    => 'multi-select',
					'class'   => 'select2',
					'default' => array(),
					'options' => $this->dtac_give_get_custom_post_types(),
				),
				array(
					'name'    => esc_html__( 'Restrict Categories', 'dtac-give' ),
					'desc'    => __( 'Please select the categories you wish to restrict', 'dtac-give' ),
					'id'      => 'dtac_give_restrict_access_to_cats',
					'type'    => 'multi-select',
					'class'   => 'select2',
					'default' => array(),
					'options' => $this->dtac_give_get_categories()
				),
				array(
					'name'    => esc_html__( 'Restrict Custom Taxonomies', 'dtac-give' ),
					'desc'    => __( 'Please select custom Taxonomies you wish to restrict', 'dtac-give' ),
					'id'      => 'dtac_give_restrict_access_to_custom_tax',
					'type'    => 'multi-select',
					'class'   => 'select2',
					'default' => array(),
					'options' => $this->dtac_give_get_custom_tax(),
				),
			);

			$settings = apply_filters( 'dtac_give_admin_settings', $settings );

			return $settings;
		}

	} // End class Settings.

endif; // End if class_exists check.

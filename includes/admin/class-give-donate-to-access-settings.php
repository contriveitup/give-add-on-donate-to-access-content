<?php
/**
 * Admin Settings
 * 
 * @since 1.0
 */
 
 // Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( ! class_exists( 'Give_Donate_To_Access_Content_Admin_Settings' ) ) :

	class Give_Donate_To_Access_Content_Admin_Settings extends Give_Donate_To_Access_Content_Admin_Functions {

		/**
		 * Core Give settings page ID
		 * 
		 * @since 1.0
		 * @access public
		 */
		public $give_settings_page_id;


		/**
		 * Settings Page ID
		 * 
		 * @since 1.0
		 * @access public
		 */
		public $give_dtac_settings_page_id;


		/**
		 * Class Constructor
		 */
		public function __construct() {

			$this->give_settings_page_id 		= 'give-settings';
			$this->give_dtac_settings_page_id 	= 'donateaccess';
 
			/**
			 * Add Tabs/Sections to Give settings page
			 */
			add_filter( "{$this->give_settings_page_id}_tabs_array", array( $this, 'give_dtac_add_settings_tab' ), 100 );
			add_action( "{$this->give_settings_page_id}_sections_{$this->give_dtac_settings_page_id}_page", array( $this, 'give_dtac_output_sections' ) );
			add_action( "{$this->give_settings_page_id}_settings_{$this->give_dtac_settings_page_id}_page", array( $this, 'give_dtac_output' ) );
			add_action( "{$this->give_settings_page_id}_save_{$this->give_dtac_settings_page_id}", array( $this, 'give_dtac_save' ) );
			
		}
		

		/**
		 * Add Settings Tab
		 * 
		 * @since 1.0
		 * @param array $pages Array of already created pages
		 * 
		 * @return array $pages
		 */
		public function give_dtac_add_settings_tab( $pages ) {

			$pages[ $this->give_dtac_settings_page_id ] = __( 'Donate To Access', 'give-dtac' );

			return $pages;
		}


		/**
		 * Display Settings Section
		 * 
		 * @since 1.0
		 * 
		 * @return void HTML
		 */
		public function give_dtac_output_sections() {

			$current_section = $this->give_dtac_current_section();

			$sections = $this->give_dtac_get_sections();

			echo '<ul class="subsubsub">';

			// Get section keys.
			$array_keys = array_keys( $sections );

			foreach ( $sections as $id => $label ) {
				echo '<li><a href="' . admin_url( 'edit.php?post_type=give_forms&page=' . $this->give_settings_page_id . '&tab=' . $this->give_dtac_settings_page_id . '&section=' . sanitize_title( $id ) ) . '" class="' . ( $current_section == $id ? 'current' : '' ) . '">' . $label . '</a> ' . ( end( $array_keys ) == $id ? '' : '|' ) . ' </li>';
			}

			echo '</ul><br class="clear" /><hr>';


		}


		/**
		 * Get all sections
		 * 
		 * @since 1.0
		 * 
		 * @return array Section's ID and Label
		 */
		public function give_dtac_get_sections() {

			$give_dtac_sections = array(
				'access-control' => __( 'Access Control', 'give-dtac' )
			);

			return apply_filters( 'give_get_sections_donateaccess', $give_dtac_sections );
		}


		/**
		 * Get current loaded section
		 * 
		 * @since 1.0
		 * 
		 * @return string Current Section or Default 
		 */
		public function give_dtac_current_section() {

			$default_current_section = 'access-control';

			$current_section = '';

			$current_section = empty( $_REQUEST['section'] ) ? $default_current_section : urldecode( $_REQUEST['section'] );

			return $current_section;
		}


		/**
		 * Restrict Content Default Message
		 * 
		 * Used in give_dtac_settings function
		 * 
		 * @since 1.0
		 * 
		 * @return string Message
		 */
		public function give_dtac_restrict_message_content() {

			$message = '';

			$message = '<p>Please make a donation in order to view the content. Click on this link %%donation_form_url%% to make the donation<p>';

			return $message;

		}


		/**
		 * Admin Settings
		 * 
		 * @since 1.0
		 * 
		 * @return array Settings
		 */
		public function give_dtac_settings() {

			$settings = array();

			$this->give_dtac_get_custom_tax();

			$settings = array(
							array(
								'id'   => 'give_dtac_section_1',
								'type' => 'title'
							),
							array(
								'name'    => __( 'Restrict Whole Website', 'give-dtac' ),
								'desc'    => __( "Selecting 'Yes' to this option will restrcit the complete website expect for the Donation Form page.<br><strong>To get this to work properly please provide a Give Donation Form ID below</strong>", 'give-dtac' ),
								'id'      => 'give_dtac_restrict_website',
								'type'    => 'select',
								'default' => 'no',
								'options' => $this->give_dtac_settings_array( 'yes_no' )
							),
							array(
								'name'    => __( 'Allow Pages', 'give-dtac' ),
								'desc'    => __( "Please select pages you wish to give access to when you are restricting the whole website.<br/><strong>By Default: The Donation Form page whose ID has been mentioned below will always be given access to</strong>", 'give-dtac' ),
								'id'      => 'give_dtac_access_to_pages',
								'type'    => 'multiselect',
								'class'	  => 'give-dtac-select2',
								'default' => '',
								'options' => $this->give_dtac_get_pages_posts()
							),
							array(
								'name'    => __( 'Give Donation Form ID', 'give-dtac' ),
								'desc'    => __( 'Please enter a Give Donation Form ID. <br/>This form will be the form that a user will be redirected to in order to make the donation and access the pages,posts,etc... selected here to restrict.', 'give-dtac' ),
								'id'      => 'give_dtac_restrict_access_give_form_id',
								'type'    => 'text',
								'default' => '1'
							),
							array(
								'name'    => __( 'Restrict Content Message', 'give-dtac' ),
								'desc'    => __( 'This message will appear instead of restrcited content, if you choose to display a message instead of Donation form in the shortcode<br/>Please use %%donation_form_url%% to display the URL to donation form.<br/>The Donation form link will go to the form whose ID will be given in the shortcode', 'give-dtac' ),
								'id'      => 'give_dtac_restrict_message',
								'type'    => 'wysiwyg',
								'default' => $this->give_dtac_restrict_message_content(),
							),
							array(
								'name'    => __( 'Restrict Access To?', 'give' ),
								'desc'    => __( 'Restrict Access to complete page, post, category, etc..', 'give-dtac' ),
								'id'      => 'give_dtac_restrict_access_to',
								'type'    => 'multiselect',
								'class'	  => 'give-dtac-select2',
								'default' => 'none',
								'options' => $this->give_dtac_settings_array( 'restrict_access_to' )
							),
							array(
								'name'    => __( 'Restrict Pages', 'give-dtac' ),
								'desc'    => __( "Please select the pages you wish to restrict", 'give-dtac' ),
								'id'      => 'give_dtac_restrict_access_to_pages',
								'type'    => 'multiselect',
								'class'	  => 'give-dtac-select2',
								'default' => '',
								'options' => $this->give_dtac_get_pages_posts()
							),
							array(
								'name'    => __( 'Restrict Posts', 'give-dtac' ),
								'desc'    => __( "Please select the posts you wish to restrict", 'give-dtac' ),
								'id'      => 'give_dtac_restrict_access_to_posts',
								'type'    => 'multiselect',
								'class'	  => 'give-dtac-select2',
								'default' => '',
								'options' => $this->give_dtac_get_pages_posts( 'posts' )
							),
							array(
								'name'    => __( 'Restrict Custom Post Types', 'give-dtac' ),
								'desc'    => __( "Please select the custom posts types you wish to restrict", 'give-dtac' ),
								'id'      => 'give_dtac_restrict_access_to_cpt',
								'type'    => 'multiselect',
								'class'	  => 'give-dtac-select2',
								'default' => '',
								'options' => $this->give_dtac_get_custom_post_types()
							),
							array(
								'name'    => __( 'Restrict Categories', 'give-dtac' ),
								'desc'    => __( "Please select the categories you wish to restrict", 'give-dtac' ),
								'id'      => 'give_dtac_restrict_access_to_cats',
								'type'    => 'multiselect',
								'class'	  => 'give-dtac-select2',
								'default' => '',
								'options' => $this->give_dtac_get_categories()
							),
							array(
								'name'    => __( 'Restrict Custom Taxonomies', 'give-dtac' ),
								'desc'    => __( "Please select custom Taxonomies you wish to restrict", 'give-dtac' ),
								'id'      => 'give_dtac_restrict_access_to_custom_tax',
								'type'    => 'multiselect',
								'class'	  => 'give-dtac-select2',
								'default' => '',
								'options' => $this->give_dtac_get_custom_tax()
							),
							
							
							/******* DO not remove the following lines *******/
	                        array(
	                            'name'  => __( 'Advanced Settings Docs Link', 'give-dtac' ),
	                            'id'    => 'advanced_settings_docs_link',
	                            'url'   => esc_url( 'http://docs.givewp.com/settings-advanced' ),
	                            'title' => __( 'Advanced Settings', 'give-dtac' ),
	                            'type'  => 'give_docs_link',
	                        ),
							array(
								'id'   => 'give_dtac_control',
								'type' => 'sectionend'
							)
							/************************************************/
						);

			$settings = apply_filters( 'give_dtac_admin_settings', $settings );

			return $settings;
		}


		/**
		 * Output the settings.
		 *
		 * @since  1.0
		 * 
		 * @return void
		 */
		public function give_dtac_output() {

			$settings = $this->give_dtac_settings();

			Give_Admin_Settings::output_fields( $settings, 'give_dtac_settings' );
		}


		/**
		 * Save settings.
		 *
		 * @since  1.0
		 * 
		 * @return void
		 */
		public function give_dtac_save() {
			$settings        = $this->give_dtac_settings();
			$current_section = $this->give_dtac_current_section();

			Give_Admin_Settings::save_fields( $settings, 'give_dtac_settings' );

			/**
			 * Trigger Action
			 *
			 * @since 1.0
			 */
			do_action( 'give_dtac_update_options_' . $this->give_dtac_settings_page_id . '_' . $current_section );
		}

	} // End class Give_Donate_To_Access_Content_Admin_Settings

endif; //end if class_exists check

new Give_Donate_To_Access_Content_Admin_Settings();
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

if( ! class_exists( 'Donate_To_Access_Content_Give_Admin_Settings' ) ) :

	class Donate_To_Access_Content_Give_Admin_Settings extends Donate_To_Access_Content_Give_Admin_Functions {

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
		public $dtac_give_settings_page_id;


		/**
		 * Class Constructor
		 */
		public function __construct() {

			$this->give_settings_page_id 		= 'give-settings';
			$this->dtac_give_settings_page_id 	= 'donateaccess';
 
			/**
			 * Add Tabs/Sections to Give settings page
			 */
			add_filter( "{$this->give_settings_page_id}_tabs_array", array( $this, 'dtac_give_add_settings_tab' ), 99 );
			add_action( "{$this->give_settings_page_id}_sections_{$this->dtac_give_settings_page_id}_page", array( $this, 'dtac_give_output_sections' ) );
			add_action( "{$this->give_settings_page_id}_settings_{$this->dtac_give_settings_page_id}_page", array( $this, 'dtac_give_output' ) );
			add_action( "{$this->give_settings_page_id}_save_{$this->dtac_give_settings_page_id}", array( $this, 'dtac_give_save' ) );
			
		}
		

		/**
		 * Add Settings Tab
		 * 
		 * @since 1.0
		 * @param array $pages Array of already created pages
		 * 
		 * @return array $pages
		 */
		public function dtac_give_add_settings_tab( $pages ) {

			$pages[ $this->dtac_give_settings_page_id ] = __( 'Donate To Access', 'dtac-give' );

			return $pages;
		}


		/**
		 * Display Settings Section
		 * 
		 * @since 1.0
		 * 
		 * @return void HTML
		 */
		public function dtac_give_output_sections() {

			$current_section = $this->dtac_give_current_section();

			$sections = $this->dtac_give_get_sections();

			echo '<ul class="subsubsub">';

			// Get section keys.
			$array_keys = array_keys( $sections );

			foreach ( $sections as $id => $label ) {
				echo '<li><a href="' . admin_url( 'edit.php?post_type=give_forms&page=' . $this->give_settings_page_id . '&tab=' . $this->dtac_give_settings_page_id . '&section=' . sanitize_title( $id ) ) . '" class="' . ( $current_section == $id ? 'current' : '' ) . '">' . $label . '</a> ' . ( end( $array_keys ) == $id ? '' : '|' ) . ' </li>';
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
		public function dtac_give_get_sections() {

			$dtac_give_sections = array(
				'access-control' => __( 'Access Control', 'dtac-give' )
			);

			return apply_filters( 'give_get_sections_donateaccess', $dtac_give_sections );
		}


		/**
		 * Get current loaded section
		 * 
		 * @since 1.0
		 * 
		 * @return string Current Section or Default 
		 */
		public function dtac_give_current_section() {

			$default_current_section = 'access-control';

			$current_section = '';

			$current_section = empty( $_REQUEST['section'] ) ? $default_current_section : urldecode( $_REQUEST['section'] );

			return $current_section;
		}


		/**
		 * Restrict Content Default Message
		 * 
		 * Used in dtac_give_settings function
		 * 
		 * @since 1.0
		 * 
		 * @return string Message
		 */
		public function dtac_give_restrict_message_content() {

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
		public function dtac_give_settings() {

			$settings = array();

			$this->dtac_give_get_custom_tax();

			$settings = array(
							array(
								'id'   => 'dtac_give_section_1',
								'type' => 'title'
							),
							array(
								'name'    => __( 'Restrict Whole Website', 'dtac-give' ),
								'desc'    => __( "Selecting 'Yes' to this option will restrcit the complete website expect for the Donation Form page.<br><strong>To get this to work properly please provide a Give Donation Form ID below</strong>", 'dtac-give' ),
								'id'      => 'dtac_give_restrict_website',
								'type'    => 'select',
								'default' => 'no',
								'options' => $this->dtac_give_settings_array( 'yes_no' )
							),
							array(
								'name'    => __( 'Allow Pages', 'dtac-give' ),
								'desc'    => __( "Please select pages you wish to give access to when you are restricting the whole website.<br/><strong>By Default: The Donation Form page whose ID has been mentioned below will always be given access to</strong>", 'dtac-give' ),
								'id'      => 'dtac_give_access_to_pages',
								'type'    => 'multiselect',
								'class'	  => 'dtac-give-select2',
								'default' => '',
								'options' => $this->dtac_give_get_pages_posts()
							),
							array(
								'name'    => __( 'Give Donation Form ID', 'dtac-give' ),
								'desc'    => __( 'Please enter a Give Donation Form ID. <br/>This form will be the form that a user will be redirected to in order to make the donation and access the pages,posts,etc... selected here to restrict.', 'dtac-give' ),
								'id'      => 'dtac_give_restrict_access_give_form_id',
								'type'    => 'text',
								'default' => '1'
							),
							array(
								'name'    => __( 'Restrict Content Message', 'dtac-give' ),
								'desc'    => __( 'This message will appear instead of restrcited content, if you choose to display a message instead of Donation form in the shortcode<br/>Please use %%donation_form_url%% to display the URL to donation form.<br/>The Donation form link will go to the form whose ID will be given in the shortcode', 'dtac-give' ),
								'id'      => 'dtac_give_restrict_message',
								'type'    => 'wysiwyg',
								'default' => $this->dtac_give_restrict_message_content(),
							),
							array(
								'name'    => __( 'Restrict Access To?', 'give' ),
								'desc'    => __( 'Restrict Access to complete page, post, category, etc..', 'dtac-give' ),
								'id'      => 'dtac_give_restrict_access_to',
								'type'    => 'multiselect',
								'class'	  => 'dtac-give-select2',
								'default' => 'none',
								'options' => $this->dtac_give_settings_array( 'restrict_access_to' )
							),
							array(
								'name'    => __( 'Restrict Pages', 'dtac-give' ),
								'desc'    => __( "Please select the pages you wish to restrict", 'dtac-give' ),
								'id'      => 'dtac_give_restrict_access_to_pages',
								'type'    => 'multiselect',
								'class'	  => 'dtac-give-select2',
								'default' => '',
								'options' => $this->dtac_give_get_pages_posts()
							),
							array(
								'name'    => __( 'Restrict Posts', 'dtac-give' ),
								'desc'    => __( "Please select the posts you wish to restrict", 'dtac-give' ),
								'id'      => 'dtac_give_restrict_access_to_posts',
								'type'    => 'multiselect',
								'class'	  => 'dtac-give-select2',
								'default' => '',
								'options' => $this->dtac_give_get_pages_posts( 'posts' )
							),
							array(
								'name'    => __( 'Restrict Custom Post Types', 'dtac-give' ),
								'desc'    => __( "Please select the custom posts types you wish to restrict", 'dtac-give' ),
								'id'      => 'dtac_give_restrict_access_to_cpt',
								'type'    => 'multiselect',
								'class'	  => 'dtac-give-select2',
								'default' => '',
								'options' => $this->dtac_give_get_custom_post_types()
							),
							array(
								'name'    => __( 'Restrict Categories', 'dtac-give' ),
								'desc'    => __( "Please select the categories you wish to restrict", 'dtac-give' ),
								'id'      => 'dtac_give_restrict_access_to_cats',
								'type'    => 'multiselect',
								'class'	  => 'dtac-give-select2',
								'default' => '',
								'options' => $this->dtac_give_get_categories()
							),
							array(
								'name'    => __( 'Restrict Custom Taxonomies', 'dtac-give' ),
								'desc'    => __( "Please select custom Taxonomies you wish to restrict", 'dtac-give' ),
								'id'      => 'dtac_give_restrict_access_to_custom_tax',
								'type'    => 'multiselect',
								'class'	  => 'dtac-give-select2',
								'default' => '',
								'options' => $this->dtac_give_get_custom_tax()
							),
							
							
							/******* DO not remove the following lines *******/
	                        array(
	                            'name'  => __( 'Advanced Settings Docs Link', 'dtac-give' ),
	                            'id'    => 'advanced_settings_docs_link',
	                            'url'   => esc_url( 'http://docs.givewp.com/settings-advanced' ),
	                            'title' => __( 'Advanced Settings', 'dtac-give' ),
	                            'type'  => 'give_docs_link',
	                        ),
							array(
								'id'   => 'dtac_give_control',
								'type' => 'sectionend'
							)
							/************************************************/
						);

			$settings = apply_filters( 'dtac_give_admin_settings', $settings );

			return $settings;
		}


		/**
		 * Output the settings.
		 *
		 * @since  1.0
		 * 
		 * @return void
		 */
		public function dtac_give_output() {

			$settings = $this->dtac_give_settings();

			Give_Admin_Settings::output_fields( $settings, 'dtac_give_settings' );
		}


		/**
		 * Save settings.
		 *
		 * @since  1.0
		 * 
		 * @return void
		 */
		public function dtac_give_save() {
			$settings        = $this->dtac_give_settings();
			$current_section = $this->dtac_give_current_section();

			Give_Admin_Settings::save_fields( $settings, 'dtac_give_settings' );

			/**
			 * Trigger Action
			 *
			 * @since 1.0
			 */
			do_action( 'dtac_give_update_options_' . $this->dtac_give_settings_page_id . '_' . $current_section );
		}

	} // End class Donate_To_Access_Content_Give_Admin_Settings

endif; //end if class_exists check

new Donate_To_Access_Content_Give_Admin_Settings();
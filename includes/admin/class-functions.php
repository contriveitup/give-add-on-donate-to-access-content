<?php
/**
 * Custom Functions for Admin Area
 * 
 * @since  1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
* Class Give_Donate_To_Access_Content_Admin_Functions
*/
class Give_Donate_To_Access_Content_Admin_Functions {
		
	/**
	 * [__construct]
	 * 
	 * Class Constructor
	 */
	public function __construct(){
		
	}


	/**
	 * [give_dtac_settings_array]
	 * 
	 * Return an array of required items to be used in different places
	 * in Admin
	 * 
	 * @since  1.0
	 * 
	 * @param  [string] $key [name of array key which will return the array of items needed]
	 * 
	 * @return [array]
	 */
	protected function give_dtac_settings_array( $key ) {

		$setting_options = array(

			'restrict_access_to' => array(
										'pages' => 'Pages',
										'posts' => 'Posts',
										'cats' 	=> 'Categories',
										'cpt' 	=> 'Post Types',
										'ctax'	=> 'Custom Taxonomies'
									),
			'yes_no' 			=> array(
										'yes' 	=> 'Yes',
										'no' 	=> 'No',
									),

		);

		$setting_options = apply_filters( 'give_dtac_admin_array', $setting_options, $setting_options );

		return $setting_options[ $key ];
	}


	/**
	 * [give_dtac_get_pages_posts]
	 * 
	 * Get all pages & posts that have a published status
	 * 
	 * @since  1.0
	 * 
	 * @param  string $get [What to get? Pages or Posts]
	 * 
	 * @return [array]
	 */
	protected function give_dtac_get_pages_posts( $get = 'pages' ) {

		$result = array();

		$pages = ( 'pages' == $get ? get_pages() : get_posts() ); 

	  	foreach ( $pages as $page ) {

	  		$result[$page->ID] = $page->post_title;
		  	
	  	}

	  	return $result;
	}



	/**
	 * [give_dtac_get_custom_post_types]
	 * 
	 * Get all public custom post types
	 * 
	 * @since  1.0
	 * 
	 * @return [array]
	 */
	protected function give_dtac_get_custom_post_types() {

		$result = array();

		$args = array(
		   'public'   => true,
		   '_builtin' => false
		);
		$args = apply_filters( 'give_dtac_cpt_args', $args, $args );

		$output 	= apply_filters( 'give_dtac_cpt_output_parameter', 'names' ); // names or objects, note names is the default
		$operator 	= apply_filters( 'give_dtac_cpt_operator', 'and' ); // 'and' or 'or'

		$post_types = get_post_types( $args, $output, $operator ); 

		foreach ( $post_types  as $post_type ) {

		   $result[$post_type] = $post_type;
		}

	  	return $result;
	}


	/**
	 * [give_dtac_get_categories]
	 * 
	 * Get all WordPress categories
	 * 
	 * @since  1.0
	 * 
	 * @return [array]
	 */
	protected function give_dtac_get_categories() {

		$result = array();

		$cats = get_terms( 'category', 'orderby=count&hide_empty=0' );

		foreach ( $cats as $key => $cat ) {
			
			$result[$cat->term_id] = $cat->name;
		}

	  	return $result;
	}


	/**
	 * [give_dtac_get_custom_tax]
	 * 
	 * Get all registered and public custom taxonomies
	 * 
	 * @since  1.0
	 * 
	 * @return [array]
	 */
	protected function give_dtac_get_custom_tax() {

		$result = array();

		$taxonomies = give_dtac_get_custom_taxs();

		if  ( $taxonomies ):

			foreach ( $taxonomies  as $taxonomy ) {

				$args =  array( 'taxonomy' => $taxonomy->name ); 

				$terms = get_terms( $args );

				foreach ($terms as $key => $term) {

					$result[$term->term_id] = $term->name . ' ( ' . $taxonomy->name . ' )';
				}
			}

		endif;

		return $result;
	}

}// End class Give_Donate_To_Access_Content_Admin_Functions
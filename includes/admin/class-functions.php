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
* Class Donate_To_Access_Content_Give_Admin_Functions
*/
class Donate_To_Access_Content_Give_Admin_Functions {
		
	/**
	 * [__construct]
	 * 
	 * Class Constructor
	 */
	public function __construct(){
		
	}


	/**
	 * [dtac_give_settings_array]
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
	protected function dtac_give_settings_array( $key ) {

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

		$setting_options = apply_filters( 'dtac_give_admin_array', $setting_options, $setting_options );

		return $setting_options[ $key ];
	}


	/**
	 * [dtac_give_get_pages_posts]
	 * 
	 * Get all pages & posts that have a published status
	 * 
	 * @since  1.0
	 * 
	 * @param  string $get [What to get? Pages or Posts]
	 * 
	 * @return [array]
	 */
	protected function dtac_give_get_pages_posts( $get = 'pages' ) {

		$result = array();

		$pages = ( 'pages' == $get ? get_pages() : get_posts() ); 

	  	foreach ( $pages as $page ) {

	  		$result[$page->ID] = $page->post_title;
		  	
	  	}

	  	return $result;
	}



	/**
	 * [dtac_give_get_custom_post_types]
	 * 
	 * Get all public custom post types
	 * 
	 * @since  1.0
	 * 
	 * @return [array]
	 */
	protected function dtac_give_get_custom_post_types() {

		$result = array();

		$args = array(
		   'public'   => true,
		   '_builtin' => false
		);
		$args = apply_filters( 'dtac_give_cpt_args', $args, $args );

		$output 	= apply_filters( 'dtac_give_cpt_output_parameter', 'names' ); // names or objects, note names is the default
		$operator 	= apply_filters( 'dtac_give_cpt_operator', 'and' ); // 'and' or 'or'

		$post_types = get_post_types( $args, $output, $operator ); 

		foreach ( $post_types  as $post_type ) {

		   $result[$post_type] = $post_type;
		}

	  	return $result;
	}


	/**
	 * [dtac_give_get_categories]
	 * 
	 * Get all WordPress categories
	 * 
	 * @since  1.0
	 * 
	 * @return [array]
	 */
	protected function dtac_give_get_categories() {

		$result = array();

		$cats = get_terms( 'category', 'orderby=count&hide_empty=0' );

		foreach ( $cats as $key => $cat ) {
			
			$result[$cat->term_id] = $cat->name;
		}

	  	return $result;
	}


	/**
	 * [dtac_give_get_custom_tax]
	 * 
	 * Get all registered and public custom taxonomies
	 * 
	 * @since  1.0
	 * 
	 * @return [array]
	 */
	protected function dtac_give_get_custom_tax() {

		$result = array();

		$taxonomies = dtac_give_get_custom_taxs();

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

}// End class Donate_To_Access_Content_Give_Admin_Functions
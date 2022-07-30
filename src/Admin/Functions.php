<?php
/**
 * Custom Functions for Admin Area
 *
 * @since 1.0.0
 */

namespace DTAC\Admin;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Class AdminFunctions
 *
 * @since 1.0.0
 */
abstract class Functions {

	/**
	 * Return an array of required items to be used in different places
	 * in Admin.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key Name of array key which will return the array of items needed.
	 *
	 * @return array
	 */
	protected function dtac_give_settings_array( string $key ) : array {

		$setting_options = array(
			'restrict_access_to' => array(
				'pages' => 'Pages',
				'posts' => 'Posts',
				'cats'  => 'Categories',
				'cpt'   => 'Post Types',
				'ctax'  => 'Custom Taxonomies',
			),
			'yes_no' => array(
				'yes' => 'Yes',
				'no'  => 'No',
			),
		);

		$setting_options = apply_filters( 'dtac_give_admin_array', $setting_options, $setting_options );

		return $setting_options[ $key ];
	}

	/**
	 * Get all pages & posts that have a published status.
	 *
	 * @since 1.0.0
	 *
	 * @param string $get What to get? Pages or Posts.
	 *
	 * @return array
	 */
	protected function dtac_give_get_pages_posts( string $get = 'pages' ) : array {

		$result = array();
		$pages  = ( 'pages' == $get ? get_pages() : get_posts() );

		foreach ( $pages as $page ) {
			$result[ $page->ID ] = $page->post_title;
		}

		return $result;
	}

	/**
	 * Get all public custom post types.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	protected function dtac_give_get_custom_post_types() : array {

		$result = array();

		$args = array(
			'public'   => true,
			'_builtin' => false,
		);

		$args = apply_filters( 'dtac_give_cpt_args', $args, $args );

		$output   = apply_filters( 'dtac_give_cpt_output_parameter', 'names' ); // names or objects, note names is the default.
		$operator = apply_filters( 'dtac_give_cpt_operator', 'and' ); // 'and' or 'or'.

		$post_types = get_post_types( $args, $output, $operator );

		foreach ( $post_types  as $post_type ) {
			$result[ $post_type ] = $post_type;
		}

		return $result;
	}

	/**
	 * Get all WordPress categories.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	protected function dtac_give_get_categories() : array {

		$result = array();

		$cats = get_terms( 'category', 'orderby=count&hide_empty=0' );

		foreach ( $cats as $key => $cat ) {
			$result[ $cat->term_id ] = $cat->name;
		}

		return $result;
	}

	/**
	 * Get all registered and public custom taxonomies.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	protected function dtac_give_get_custom_tax() : array {

		$result = array();

		$taxonomies = dtac_give_get_custom_taxs();

		if ( $taxonomies ) :

			foreach ( $taxonomies  as $taxonomy ) {

				$args  =  array( 'taxonomy' => $taxonomy->name );
				$terms = get_terms( $args );

				foreach ( $terms as $key => $term ) {
					$result[ $term->term_id ] = $term->name . ' ( ' . $taxonomy->name . ' )';
				}
			}

		endif;

		return $result;
	}

	/**
	 * Return includes folder path.
	 *
	 * @since 2.0.0
	 *
	 * @param string $file             Name of the file.
	 * @param string $sub_directory    Name of the sub directory. Defaults to empty string.
	 * @param string $parent_directory Name of the directory defaults to 'admin'.
	 *
	 * @return void
	 */
	protected function include_file( string $file, string $sub_directory = '', string $parent_directory = 'admin' ) : void {

		$sub_directory = ( '' !== $sub_directory ) ? $sub_directory . '/' : '';

		$file = DTAC_GIVE_PLUGIN_DIR . 'includes/' . $parent_directory . '/' . $sub_directory . $file . '.php';

		if ( file_exists( $file ) ) {
			require $file;
		}
	}

	/**
	 * Fetch Give Forms by post type from DB.
	 *
	 * @since 2.1.0
	 *
	 * @return array
	 */
	protected function dtac_get_give_forms() : array {

		$result = array();

		$args = array ( 'post_type'   => 'give_forms' );
		$args = apply_filters( 'dtac_give_get_form_args', $args, $args );

		$give_forms = get_posts( $args );

		if ( dtac_is_valid_array( $give_forms ) ) {
			foreach( $give_forms as $give_form ) {
				$result[ $give_form->ID ] = $give_form->post_title;
			}
		}

		return $result;
	}
} // End class AdminFunctions.

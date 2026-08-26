<?php

/**
 * PHPUnit bootstrap for plugin unit tests that do not boot WordPress.
 *
 * @package DTAC_Give
 */

if (! defined('ABSPATH')) {
	define('ABSPATH', __DIR__ . '/');
}

if (! defined('HOUR_IN_SECONDS')) {
	define('HOUR_IN_SECONDS', 3600);
}

if (! defined('DAY_IN_SECONDS')) {
	define('DAY_IN_SECONDS', 86400);
}

if (! defined('COOKIEPATH')) {
	define('COOKIEPATH', '/');
}

if (! defined('COOKIE_DOMAIN')) {
	define('COOKIE_DOMAIN', '');
}

if (! function_exists('absint')) {
	/**
	 * @param mixed $value Raw value.
	 * @return int
	 */
	function absint($value)
	{
		return abs((int) $value);
	}
}

if (! function_exists('sanitize_text_field')) {
	/**
	 * @param mixed $value Raw value.
	 * @return string
	 */
	function sanitize_text_field($value)
	{
		return trim(strip_tags((string) $value));
	}
}

if (! function_exists('sanitize_key')) {
	/**
	 * @param mixed $value Raw value.
	 * @return string
	 */
	function sanitize_key($value)
	{
		$value = strtolower((string) $value);
		$value = preg_replace('/[^a-z0-9_\-]/', '', $value);

		return is_string($value) ? $value : '';
	}
}

if (! function_exists('sanitize_email')) {
	/**
	 * @param mixed $value Raw value.
	 * @return string
	 */
	function sanitize_email($value)
	{
		$value = strtolower(trim((string) $value));

		return is_email($value) ? $value : '';
	}
}

if (! function_exists('is_email')) {
	/**
	 * @param mixed $value Raw value.
	 * @return bool
	 */
	function is_email($value)
	{
		return (bool) filter_var($value, FILTER_VALIDATE_EMAIL);
	}
}

if (! function_exists('wp_unslash')) {
	/**
	 * @param mixed $value Raw value.
	 * @return mixed
	 */
	function wp_unslash($value)
	{
		return is_string($value) ? stripslashes($value) : $value;
	}
}

if (! function_exists('wp_parse_url')) {
	/**
	 * @param string $url       URL.
	 * @param int    $component Component.
	 * @return mixed
	 */
	function wp_parse_url($url, $component = -1)
	{
		return parse_url($url, $component);
	}
}

if (! function_exists('wp_parse_str')) {
	/**
	 * @param string $string Query string.
	 * @param array  $array  Parsed output.
	 * @return void
	 */
	function wp_parse_str($string, &$array)
	{
		parse_str($string, $array);
	}
}

if (! function_exists('wp_get_referer')) {
	/**
	 * @return string|false
	 */
	function wp_get_referer()
	{
		return false;
	}
}

if (! function_exists('esc_url_raw')) {
	/**
	 * @param string $url URL.
	 * @return string
	 */
	function esc_url_raw($url)
	{
		return $url;
	}
}

if (! function_exists('is_ssl')) {
	/**
	 * @return bool
	 */
	function is_ssl()
	{
		return false;
	}
}

if (! function_exists('is_singular')) {
	/**
	 * @return bool
	 */
	function is_singular()
	{
		return false;
	}
}

if (! function_exists('get_queried_object_id')) {
	/**
	 * @return int
	 */
	function get_queried_object_id()
	{
		return 0;
	}
}

if (! function_exists('get_queried_object')) {
	/**
	 * @return null
	 */
	function get_queried_object()
	{
		return null;
	}
}

if (! function_exists('apply_filters')) {
	/**
	 * @param string $hook  Hook name.
	 * @param mixed  $value Filtered value.
	 * @return mixed
	 */
	function apply_filters($hook, $value, ...$args)
	{
		unset($hook, $args);

		return $value;
	}
}

if (! function_exists('esc_html__')) {
	/**
	 * @param string $text   Text.
	 * @param string $domain Text domain.
	 * @return string
	 */
	function esc_html__($text, $domain = '')
	{
		return $text;
	}
}

if (! function_exists('_doing_it_wrong')) {
	/**
	 * @return void
	 */
	function _doing_it_wrong() {}
}

if (! class_exists('WP_Post')) {
	/**
	 * Minimal post stub.
	 */
	class WP_Post
	{
		/**
		 * @var int
		 */
		public $ID = 0;

		/**
		 * @var string
		 */
		public $post_content = '';

		/**
		 * @var string
		 */
		public $post_type = 'post';

		/**
		 * @var string
		 */
		public $post_excerpt = '';

		/**
		 * @var string
		 */
		public $post_title = '';

		/**
		 * @var string
		 */
		public $post_date = '';

		/**
		 * @var string
		 */
		public $post_date_gmt = '';
	}
}

if (! class_exists('WP_Term')) {
	/**
	 * Minimal term stub.
	 */
	class WP_Term
	{
		/**
		 * @var int
		 */
		public $term_id = 0;

		/**
		 * @var string
		 */
		public $name = '';
	}
}

if (! isset($GLOBALS['dtac_give_test_settings'])) {
	$GLOBALS['dtac_give_test_settings'] = array();
}

if (! isset($GLOBALS['dtac_give_test_posts'])) {
	$GLOBALS['dtac_give_test_posts'] = array();
}

if (! isset($GLOBALS['dtac_give_test_terms'])) {
	$GLOBALS['dtac_give_test_terms'] = array();
}

if (! isset($GLOBALS['dtac_give_test_post_meta'])) {
	$GLOBALS['dtac_give_test_post_meta'] = array();
}

if (! isset($GLOBALS['dtac_give_test_term_objects'])) {
	$GLOBALS['dtac_give_test_term_objects'] = array();
}

if (! function_exists('get_option')) {
	/**
	 * @param string $option  Option name.
	 * @param mixed  $default Default.
	 * @return mixed
	 */
	function get_option($option, $default = false)
	{
		if ('dtac_give_settings' === $option) {
			return isset($GLOBALS['dtac_give_test_settings']) ? $GLOBALS['dtac_give_test_settings'] : $default;
		}

		return $default;
	}
}

if (! function_exists('get_post')) {
	/**
	 * @param int $post_id Post ID.
	 * @return WP_Post|null
	 */
	function get_post($post_id)
	{
		$post_id = (int) $post_id;

		if (isset($GLOBALS['dtac_give_test_posts'][$post_id])) {
			$post = $GLOBALS['dtac_give_test_posts'][$post_id];

			if ($post instanceof WP_Post) {
				return $post;
			}

			$stub                = new WP_Post();
			$stub->ID            = $post_id;
			$stub->post_content  = isset($post->post_content) ? (string) $post->post_content : '';
			$stub->post_type     = isset($post->post_type) ? (string) $post->post_type : 'post';
			$stub->post_excerpt  = isset($post->post_excerpt) ? (string) $post->post_excerpt : '';
			$stub->post_title    = isset($post->post_title) ? (string) $post->post_title : '';
			$stub->post_date     = isset($post->post_date) ? (string) $post->post_date : '';
			$stub->post_date_gmt = isset($post->post_date_gmt) ? (string) $post->post_date_gmt : '';

			return $stub;
		}

		return null;
	}
}

if (! function_exists('get_post_type')) {
	/**
	 * @param int $post_id Post ID.
	 * @return string|false
	 */
	function get_post_type($post_id)
	{
		$post = get_post($post_id);

		return $post instanceof WP_Post ? $post->post_type : false;
	}
}

if (! function_exists('get_post_meta')) {
	/**
	 * @param int    $post_id Post ID.
	 * @param string $key     Meta key.
	 * @param bool   $single  Single value.
	 * @return mixed
	 */
	function get_post_meta($post_id, $key, $single = true)
	{
		$post_id = (int) $post_id;

		if (! isset($GLOBALS['dtac_give_test_post_meta'][$post_id][$key])) {
			return $single ? '' : array();
		}

		return $GLOBALS['dtac_give_test_post_meta'][$post_id][$key];
	}
}

if (! function_exists('has_block')) {
	/**
	 * @param string  $name Block name.
	 * @param WP_Post $post Post.
	 * @return bool
	 */
	function has_block($name, $post = null)
	{
		$content = '';

		if ($post instanceof WP_Post) {
			$content = (string) $post->post_content;
		}

		return false !== strpos($content, '<!-- wp:' . $name);
	}
}

if (! function_exists('get_the_title')) {
	/**
	 * @param int $post_id Post ID.
	 * @return string
	 */
	function get_the_title($post_id)
	{
		$post = get_post($post_id);

		return $post instanceof WP_Post ? (string) $post->post_title : '';
	}
}

if (! function_exists('get_term')) {
	/**
	 * @param int $term_id Term ID.
	 * @return WP_Term|null
	 */
	function get_term($term_id)
	{
		$term_id = (int) $term_id;

		return isset($GLOBALS['dtac_give_test_term_objects'][$term_id])
			? $GLOBALS['dtac_give_test_term_objects'][$term_id]
			: null;
	}
}

if (! function_exists('get_permalink')) {
	/**
	 * @param int $post_id Post ID.
	 * @return string
	 */
	function get_permalink($post_id)
	{
		return 'https://example.test/?p=' . absint($post_id);
	}
}

if (! function_exists('get_term_link')) {
	/**
	 * @param int $term_id Term ID.
	 * @return string
	 */
	function get_term_link($term_id)
	{
		return 'https://example.test/?cat=' . absint($term_id);
	}
}

if (! function_exists('home_url')) {
	/**
	 * @param string $path Path.
	 * @return string
	 */
	function home_url($path = '/')
	{
		return 'https://example.test' . $path;
	}
}

if (! function_exists('esc_html')) {
	/**
	 * @param string $text Text.
	 * @return string
	 */
	function esc_html($text)
	{
		return $text;
	}
}

if (! function_exists('esc_url')) {
	/**
	 * @param string $url URL.
	 * @return string
	 */
	function esc_url($url)
	{
		return $url;
	}
}

if (! function_exists('has_shortcode')) {
	/**
	 * @param string $content    Content.
	 * @param string $shortcode  Tag.
	 * @return bool
	 */
	function has_shortcode($content, $shortcode)
	{
		return false !== strpos((string) $content, '[' . $shortcode);
	}
}

if (! function_exists('has_term')) {
	/**
	 * @param mixed  $term     Term ID or IDs.
	 * @param string $taxonomy Taxonomy.
	 * @param int    $post_id  Post ID.
	 * @return bool
	 */
	function has_term($term, $taxonomy, $post_id)
	{
		$key = (int) $post_id . '|' . (string) $taxonomy;

		if (empty($GLOBALS['dtac_give_test_terms'][$key])) {
			return false;
		}

		$assigned = array_map('intval', (array) $GLOBALS['dtac_give_test_terms'][$key]);
		$needles  = array_map('intval', (array) $term);

		return (bool) array_intersect($needles, $assigned);
	}
}

if (! function_exists('get_post_types')) {
	/**
	 * @param array  $args   Query args.
	 * @param string $output Output type.
	 * @return array
	 */
	function get_post_types($args = array(), $output = 'names')
	{
		unset($args, $output);

		return array(
			'post'       => 'post',
			'page'       => 'page',
			'give_forms' => 'give_forms',
			'attachment' => 'attachment',
		);
	}
}

if (! function_exists('get_taxonomies')) {
	/**
	 * @return array
	 */
	function get_taxonomies($args = array(), $output = 'names')
	{
		unset($args, $output);

		return array();
	}
}

if (! function_exists('is_user_logged_in')) {
	/**
	 * @return bool
	 */
	function is_user_logged_in()
	{
		return false;
	}
}

if (! function_exists('get_current_user_id')) {
	/**
	 * @return int
	 */
	function get_current_user_id()
	{
		return 0;
	}
}

if (! function_exists('has_filter')) {
	/**
	 * @return bool
	 */
	function has_filter()
	{
		return false;
	}
}

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/includes/functions.php';

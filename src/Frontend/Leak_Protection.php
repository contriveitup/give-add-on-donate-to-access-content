<?php

/**
 * Prevent restricted content leaking through REST, feeds, search, and oEmbed.
 *
 * @package DTAC_Give
 *
 * @since 3.0.0
 */

namespace DTAC\Frontend;

use DTAC\Frontend\Functions;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Filter public content surfaces that bypass the frontend redirect gate.
 *
 * @since 3.0.0
 */
class Leak_Protection extends Functions
{



	/**
	 * Class constructor.
	 *
	 * @since 3.0.0
	 */
	public function __construct()
	{

		parent::__construct();

		add_filter('rest_pre_dispatch', array($this, 'maybe_block_rest_item'), 10, 3);
		add_filter('the_posts', array($this, 'filter_the_posts'), 10, 2);
		add_filter('the_content_feed', array($this, 'filter_feed_content'));
		add_filter('the_excerpt_rss', array($this, 'filter_feed_excerpt'));
		add_filter('get_the_excerpt', array($this, 'filter_search_excerpt'), 10, 2);
		add_filter('oembed_request_post_id', array($this, 'filter_oembed_post_id'), 10, 2);
		add_filter('oembed_response_data', array($this, 'filter_oembed_response'), 10, 4);
	}

	/**
	 * Block or redact a single REST item for guests.
	 *
	 * @since 3.0.0
	 *
	 * @param mixed            $result  Dispatch result.
	 * @param \WP_REST_Server  $server  REST server.
	 * @param \WP_REST_Request $request REST request.
	 *
	 * @return mixed
	 */
	public function maybe_block_rest_item($result, $server, $request)
	{

		unset($server);

		if (! $request instanceof \WP_REST_Request) {
			return $result;
		}

		$route = (string) $request->get_route();

		if (! preg_match('#^/wp/v2/(?P<type>[a-z0-9_-]+)/(?P<id>\d+)#', $route, $matches)) {
			return $result;
		}

		$post_id = absint($matches['id']);

		if ($post_id <= 0 || dtac_give_current_user_can_edit_post($post_id)) {
			return $result;
		}

		if (! dtac_give_is_post_restricted($post_id) || dtac_give_visitor_can_view_post($post_id)) {
			return $result;
		}

		if ('excerpt' === dtac_give_get_leak_mode()) {
			return $result;
		}

		return new \WP_Error(
			'dtac_give_restricted',
			esc_html__('This content is restricted until a donation is made.', 'dtac-give'),
			array('status' => 401)
		);
	}

	/**
	 * Hide restricted posts from feeds, search, and REST collections.
	 *
	 * @since 3.0.0
	 *
	 * @param array          $posts Posts.
	 * @param \WP_Query|null $query Query.
	 *
	 * @return array
	 */
	public function filter_the_posts($posts, $query = null)
	{

		if (! is_array($posts) || empty($posts)) {
			return $posts;
		}

		if (function_exists('is_admin') && is_admin()) {
			return $posts;
		}

		$should_filter = false;

		if ($query instanceof \WP_Query && ($query->is_feed() || $query->is_search())) {
			$should_filter = true;
		}

		if (defined('REST_REQUEST') && REST_REQUEST) {
			if (function_exists('current_user_can') && current_user_can('edit_posts')) {
				return $posts;
			}

			$should_filter = true;
		}

		if (! $should_filter) {
			return $posts;
		}

		if ('excerpt' === dtac_give_get_leak_mode() && ! ($query instanceof \WP_Query && $query->is_feed())) {
			return $posts;
		}

		$filtered = array();

		foreach ($posts as $post) {
			if (! $post instanceof \WP_Post) {
				$filtered[] = $post;
				continue;
			}

			if (dtac_give_current_user_can_edit_post((int) $post->ID)) {
				$filtered[] = $post;
				continue;
			}

			if (dtac_give_is_post_restricted((int) $post->ID) && ! dtac_give_visitor_can_view_post((int) $post->ID)) {
				continue;
			}

			$filtered[] = $post;
		}

		return $filtered;
	}

	/**
	 * Replace restricted feed content.
	 *
	 * @since 3.0.0
	 *
	 * @param string $content Feed content.
	 *
	 * @return string
	 */
	public function filter_feed_content($content)
	{

		$post_id = get_the_ID();

		if (! $post_id || ! dtac_give_is_post_restricted((int) $post_id) || dtac_give_visitor_can_view_post((int) $post_id)) {
			return $content;
		}

		if ('excerpt' === dtac_give_get_leak_mode()) {
			return $this->restricted_excerpt((int) $post_id);
		}

		return $this->restricted_message();
	}

	/**
	 * Replace restricted RSS excerpts.
	 *
	 * @since 3.0.0
	 *
	 * @param string $excerpt Feed excerpt.
	 *
	 * @return string
	 */
	public function filter_feed_excerpt($excerpt)
	{

		$post_id = get_the_ID();

		if (! $post_id || ! dtac_give_is_post_restricted((int) $post_id) || dtac_give_visitor_can_view_post((int) $post_id)) {
			return $excerpt;
		}

		if ('excerpt' === dtac_give_get_leak_mode()) {
			return $this->restricted_excerpt((int) $post_id);
		}

		return $this->restricted_message();
	}

	/**
	 * Redact search excerpts for restricted posts.
	 *
	 * @since 3.0.0
	 *
	 * @param string        $excerpt Excerpt.
	 * @param \WP_Post|null $post    Post.
	 *
	 * @return string
	 */
	public function filter_search_excerpt($excerpt, $post = null)
	{

		if (! is_search()) {
			return $excerpt;
		}

		$post_id = 0;

		if ($post instanceof \WP_Post) {
			$post_id = (int) $post->ID;
		} elseif (get_the_ID()) {
			$post_id = (int) get_the_ID();
		}

		if ($post_id <= 0 || ! dtac_give_is_post_restricted($post_id) || dtac_give_visitor_can_view_post($post_id)) {
			return $excerpt;
		}

		if ('excerpt' === dtac_give_get_leak_mode()) {
			return $this->restricted_excerpt($post_id);
		}

		return $this->restricted_message();
	}

	/**
	 * Hide oEmbed discovery for restricted posts in hide mode.
	 *
	 * @since 3.0.0
	 *
	 * @param int    $post_id Post ID.
	 * @param string $url     Requested URL.
	 *
	 * @return int
	 */
	public function filter_oembed_post_id($post_id, $url)
	{

		unset($url);

		$post_id = absint($post_id);

		if ($post_id <= 0 || 'excerpt' === dtac_give_get_leak_mode() || dtac_give_current_user_can_edit_post($post_id)) {
			return $post_id;
		}

		if (dtac_give_is_post_restricted($post_id) && ! dtac_give_visitor_can_view_post($post_id)) {
			return 0;
		}

		return $post_id;
	}

	/**
	 * Redact oEmbed payload for restricted posts.
	 *
	 * @since 3.0.0
	 *
	 * @param array    $data   oEmbed data.
	 * @param \WP_Post $post   Post.
	 * @param int      $width  Width.
	 * @param int      $height Height.
	 *
	 * @return array
	 */
	public function filter_oembed_response($data, $post, $width, $height)
	{

		unset($width, $height);

		if (! is_array($data) || ! $post instanceof \WP_Post) {
			return $data;
		}

		$post_id = (int) $post->ID;

		if (dtac_give_current_user_can_edit_post($post_id) || ! dtac_give_is_post_restricted($post_id) || dtac_give_visitor_can_view_post($post_id)) {
			return $data;
		}

		$message = $this->restricted_message();

		if ('excerpt' === dtac_give_get_leak_mode()) {
			$message = $this->restricted_excerpt($post_id);
		}

		$data['title']       = get_the_title($post);
		$data['html']        = wp_kses_post($message);
		$data['excerpt']     = wp_strip_all_tags($message);
		$data['author_name'] = '';

		return $data;
	}

	/**
	 * Public message used when content is fully hidden.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	private function restricted_message(): string
	{

		$message = esc_html__('This content is restricted until a donation is made.', 'dtac-give');

		/**
		 * Filter the placeholder shown where restricted content is redacted.
		 *
		 * Applies to feeds, search excerpts, and oEmbed payloads.
		 *
		 * @since 3.0.0
		 *
		 * @param string $message Placeholder text.
		 * @param int    $post_id Post ID being redacted, or 0.
		 */
		return (string) apply_filters('dtac_give_restricted_message', $message, (int) get_the_ID());
	}

	/**
	 * Excerpt-only replacement for restricted posts.
	 *
	 * @since 3.0.0
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return string
	 */
	private function restricted_excerpt(int $post_id): string
	{

		$post = get_post($post_id);

		if (! $post instanceof \WP_Post) {
			return $this->restricted_message();
		}

		$excerpt = $post->post_excerpt;

		if ('' === trim((string) $excerpt)) {
			/**
			 * Filter the word count used to auto-generate a restricted teaser.
			 *
			 * @since 3.0.0
			 *
			 * @param int $words   Number of words.
			 * @param int $post_id Post ID.
			 */
			$words = absint(apply_filters('dtac_give_restricted_excerpt_length', 40, $post_id));

			$excerpt = wp_trim_words(wp_strip_all_tags((string) $post->post_content), $words, '&hellip;');
		}

		/**
		 * Filter the teaser shown for restricted posts in `excerpt` leak mode.
		 *
		 * @since 3.0.0
		 *
		 * @param string $excerpt Teaser HTML.
		 * @param int    $post_id Post ID.
		 */
		return wp_kses_post((string) apply_filters('dtac_give_restricted_excerpt', $excerpt, $post_id));
	}
}

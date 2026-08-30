<?php

/**
 * Plugin Shortcodes
 *
 * In this file you can find all the shortcodes used by this add-on.
 *
 * @package DTAC_Give
 *
 * @since 1.0.0
 */

namespace DTAC\Frontend;

use DTAC\Frontend\Functions;

// Exit if accessed directly.
defined('ABSPATH') || exit;

if (! class_exists('Shortcodes')) {

	/**
	 * Register and Run plugin shortcodes.
	 *
	 * @since 1.0.0
	 */
	class Shortcodes extends Functions
	{



		/**
		 * Class constructor.
		 *
		 * @since 1.0.0
		 */
		public function __construct()
		{

			parent::__construct();

			add_shortcode('cip_donate_to_access_content', array($this, 'donate_to_access_give_shortcode_func'));
			add_shortcode('dtac_my_unlocked_content', array($this, 'my_unlocked_content_shortcode'));
		}

		/**
		 * Restrict Access Shortcode.
		 *
		 * This shortcode will restrict access to the content until a donation is made.
		 *
		 * Usage : [cip_donate_to_access_content form_id=1 show='form|message'] Content to be restricted goes here [/cip_donate_to_access_content]
		 *
		 * Form ID is necessary and it will show a donation form by default when the content is restricted
		 *
		 * @since 1.0.0
		 *
		 * @param array|string $atts    Shortcode Attributes.
		 * @param string|null  $content Shortcode content.
		 *
		 * @return string|void
		 */
		public function donate_to_access_give_shortcode_func($atts, $content = '')
		{

			$atts    = is_array($atts) ? $atts : array();
			$content = is_string($content) ? $content : '';

			$defaults = array(
				'form_id' => '', // Give donation form ID.
				'show'    => 'form', // Options form or message.
			);

			/**
			 * Filter the default attributes of the restrict-content shortcode.
			 *
			 * @since 3.0.0
			 *
			 * @param array $defaults Default attributes.
			 * @param array $atts     Raw attributes as authored.
			 */
			$defaults = (array) apply_filters('dtac_give_shortcode_default_atts', $defaults, $atts);

			$a = shortcode_atts($defaults, $atts);

			/**
			 * Filter the parsed attributes of the restrict-content shortcode.
			 *
			 * @since 3.0.0
			 *
			 * @param array $a    Parsed attributes.
			 * @param array $atts Raw attributes as authored.
			 */
			$a = (array) apply_filters('dtac_give_shortcode_atts', $a, $atts);

			// Revert back if a form id is not provided.
			if ('' === $a['form_id']) {
				return;
			}

			$current_page_id = dtac_give_get_current_object_id();
			$restrict        = dtac_give_get_restriction_output((int) $a['form_id'], (string) $a['show'], $current_page_id);
			$output          = $this->dtac_give_check_access($content, $restrict);

			/**
			 * Filter the rendered output of the restrict-content shortcode.
			 *
			 * @since 3.0.0
			 *
			 * @param string $output  Rendered output: the content or the gate.
			 * @param array  $a       Parsed attributes.
			 * @param string $content Wrapped shortcode content.
			 */
			return (string) apply_filters('dtac_give_shortcode_output', $output, $a, $content);
		}

		/**
		 * List content the current donor has unlocked.
		 *
		 * Usage: [dtac_my_unlocked_content]
		 *
		 * @since 3.0.0
		 *
		 * @return string
		 */
		public function my_unlocked_content_shortcode(): string
		{

			return dtac_give_get_unlocked_content_html();
		}
	}
}

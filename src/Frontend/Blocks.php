<?php

/**
 * Gutenberg blocks.
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
 * Register restricted-content and unlocked-content blocks.
 *
 * @since 3.0.0
 */
class Blocks extends Functions
{



	/**
	 * Class constructor.
	 *
	 * @since 3.0.0
	 */
	public function __construct()
	{

		parent::__construct();

		add_action('init', array($this, 'register'));
	}

	/**
	 * Register block types.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function register(): void
	{

		if (! function_exists('register_block_type')) {
			return;
		}

		register_block_type(
			'dtac/restricted-content',
			array(
				'api_version'     => 2,
				'title'           => esc_html__('Restricted Content', 'dtac-give'),
				'description'     => esc_html__('Hide inner content until a qualifying donation is made.', 'dtac-give'),
				'category'        => 'widgets',
				'editor_script'   => 'dtac-give-blocks',
				'attributes'      => $this->restricted_block_attributes(),
				'render_callback' => array($this, 'render_restricted'),
			)
		);

		register_block_type(
			'dtac/my-unlocked-content',
			array(
				'api_version'     => 2,
				'title'           => esc_html__('My Unlocked Content', 'dtac-give'),
				'description'     => esc_html__('List content the current donor has unlocked.', 'dtac-give'),
				'category'        => 'widgets',
				'editor_script'   => 'dtac-give-blocks',
				'render_callback' => array($this, 'render_unlocked'),
			)
		);
	}

	/**
	 * Attribute schema for the restricted-content block.
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	private function restricted_block_attributes(): array
	{

		$attributes = array(
			'formId' => array(
				'type'    => 'number',
				'default' => 0,
			),
			'show'   => array(
				'type'    => 'string',
				'default' => 'form',
			),
		);

		/**
		 * Filter the attribute schema of the restricted-content block.
		 *
		 * Editor controls for new attributes must be added in JavaScript.
		 *
		 * @since 3.0.0
		 *
		 * @param array $attributes Block attribute schema.
		 */
		return (array) apply_filters('dtac_give_restricted_block_attributes', $attributes);
	}

	/**
	 * Server-render the restricted-content block.
	 *
	 * @since 3.0.0
	 *
	 * @param array  $attributes Block attributes.
	 * @param string $content    InnerBlocks HTML.
	 *
	 * @return string
	 */
	public function render_restricted($attributes, $content = ''): string
	{

		$attributes = is_array($attributes) ? $attributes : array();
		$content    = is_string($content) ? $content : '';
		$form_id    = isset($attributes['formId']) ? absint($attributes['formId']) : 0;
		$show       = isset($attributes['show']) ? sanitize_text_field((string) $attributes['show']) : 'form';
		$post_id    = dtac_give_get_current_object_id();
		$restrict   = dtac_give_get_restriction_output($form_id, $show, $post_id);
		$output     = $this->dtac_give_check_access($content, $restrict);

		/**
		 * Filter the rendered output of the restricted-content block.
		 *
		 * @since 3.0.0
		 *
		 * @param string $output     Rendered output: the content or the gate.
		 * @param array  $attributes Block attributes.
		 * @param string $content    InnerBlocks HTML.
		 */
		return (string) apply_filters('dtac_give_restricted_block_output', $output, $attributes, $content);
	}

	/**
	 * Server-render the unlocked-content list block.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function render_unlocked(): string
	{

		return dtac_give_get_unlocked_content_html();
	}
}

<?php

/**
 * Per-post restriction metabox.
 *
 * @package DTAC_Give
 *
 * @since 3.0.0
 */

namespace DTAC\Admin;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Restrict-with-donation controls on public post types.
 *
 * @since 3.0.0
 */
class Metabox
{



	/**
	 * Class constructor.
	 *
	 * @since 3.0.0
	 */
	public function __construct()
	{

		add_action('init', array($this, 'register_meta'));
		add_action('add_meta_boxes', array($this, 'register'));
		add_action('save_post', array($this, 'save'), 10, 2);
	}

	/**
	 * Register restriction meta so the block editor can persist it.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function register_meta(): void
	{

		if (! function_exists('register_post_meta')) {
			return;
		}

		$keys  = dtac_give_post_meta_keys();
		$types = dtac_give_restrictable_post_types();
		$auth  = static function ($allowed, $meta_key, $object_id) {
			unset($allowed, $meta_key);

			return current_user_can('edit_post', (int) $object_id);
		};

		$meta = array(
			$keys['restrict']    => 'string',
			$keys['form_id']     => 'integer',
			$keys['min_amount']  => 'string',
			$keys['expiry_days'] => 'integer',
		);

		foreach ($types as $post_type) {
			foreach ($meta as $meta_key => $type) {
				register_post_meta(
					$post_type,
					$meta_key,
					array(
						'single'            => true,
						'type'              => $type,
						'show_in_rest'      => true,
						'auth_callback'     => $auth,
						'sanitize_callback' => static function ($value) use ($type) {
							if ('integer' === $type) {
								return absint($value);
							}

							return sanitize_text_field((string) $value);
						},
					)
				);
			}
		}
	}

	/**
	 * Register the metabox on public post types.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function register(): void
	{

		foreach (dtac_give_restrictable_post_types() as $post_type) {
			add_meta_box(
				'dtac_give_restrict',
				esc_html__('Restrict with donation', 'dtac-give'),
				array($this, 'render'),
				$post_type,
				'side',
				'default'
			);
		}
	}

	/**
	 * Output metabox fields.
	 *
	 * @since 3.0.0
	 *
	 * @param \WP_Post $post Post object.
	 *
	 * @return void
	 */
	public function render($post): void
	{

		if (! $post instanceof \WP_Post) {
			return;
		}

		$post_id = (int) $post->ID;
		$keys    = dtac_give_post_meta_keys();
		$mode    = dtac_give_get_post_restriction_mode($post_id);
		$form_id = absint(get_post_meta($post_id, $keys['form_id'], true));
		$minimum = get_post_meta($post_id, $keys['min_amount'], true);
		$expiry  = get_post_meta($post_id, $keys['expiry_days'], true);
		$forms   = $this->get_give_forms();

		wp_nonce_field('dtac_give_metabox', 'dtac_give_metabox_nonce');
?>
		<p>
			<label for="dtac_give_restrict"><?php esc_html_e('Restrict this content', 'dtac-give'); ?></label>
			<select name="dtac_give_restrict" id="dtac_give_restrict" class="widefat">
				<option value="" <?php selected($mode, ''); ?>><?php esc_html_e('Inherit from settings', 'dtac-give'); ?></option>
				<option value="yes" <?php selected($mode, 'yes'); ?>><?php esc_html_e('Yes', 'dtac-give'); ?></option>
				<option value="no" <?php selected($mode, 'no'); ?>><?php esc_html_e('No', 'dtac-give'); ?></option>
			</select>
		</p>
		<p>
			<label for="dtac_give_form_id"><?php esc_html_e('Give donation form', 'dtac-give'); ?></label>
			<select name="dtac_give_form_id" id="dtac_give_form_id" class="widefat">
				<option value="0"><?php esc_html_e('Use default form', 'dtac-give'); ?></option>
				<?php foreach ($forms as $id => $title) : ?>
					<option value="<?php echo esc_attr((string) $id); ?>" <?php selected($form_id, (int) $id); ?>>
						<?php echo esc_html((string) $title); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
			<label for="dtac_give_min_amount"><?php esc_html_e('Minimum donation amount', 'dtac-give'); ?></label>
			<input type="text" class="widefat" name="dtac_give_min_amount" id="dtac_give_min_amount" value="<?php echo esc_attr(is_scalar($minimum) ? (string) $minimum : ''); ?>" />
		</p>
		<p>
			<label for="dtac_give_expiry_days"><?php esc_html_e('Access expires after (days)', 'dtac-give'); ?></label>
			<input type="number" class="widefat" min="0" step="1" name="dtac_give_expiry_days" id="dtac_give_expiry_days" value="<?php echo esc_attr(is_scalar($expiry) ? (string) $expiry : ''); ?>" />
			<span class="description"><?php esc_html_e('Leave empty to inherit the global setting. 0 means never expires.', 'dtac-give'); ?></span>
		</p>
<?php
	}

	/**
	 * Persist per-post restriction meta.
	 *
	 * @since 3.0.0
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 *
	 * @return void
	 */
	public function save($post_id, $post): void
	{

		$post_id = absint($post_id);

		if ($post_id <= 0 || ! $post instanceof \WP_Post) {
			return;
		}

		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}

		if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
			return;
		}

		if (! isset($_POST['dtac_give_metabox_nonce']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['dtac_give_metabox_nonce'])), 'dtac_give_metabox')) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return;
		}

		$post_type = get_post_type_object($post->post_type);

		if (! $post_type || empty($post_type->cap->edit_post) || ! current_user_can($post_type->cap->edit_post, $post_id)) {
			return;
		}

		if (! in_array($post->post_type, dtac_give_restrictable_post_types(), true)) {
			return;
		}

		$keys = dtac_give_post_meta_keys();
		$mode = isset($_POST['dtac_give_restrict']) ? sanitize_text_field(wp_unslash($_POST['dtac_give_restrict'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing

		if (in_array($mode, array('yes', 'no'), true)) {
			update_post_meta($post_id, $keys['restrict'], $mode);
		} else {
			delete_post_meta($post_id, $keys['restrict']);
		}

		$form_id = isset($_POST['dtac_give_form_id']) ? absint(wp_unslash($_POST['dtac_give_form_id'])) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing

		if ($form_id > 0) {
			update_post_meta($post_id, $keys['form_id'], $form_id);
		} else {
			delete_post_meta($post_id, $keys['form_id']);
		}

		if (isset($_POST['dtac_give_min_amount']) && '' !== trim((string) wp_unslash($_POST['dtac_give_min_amount']))) { // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			update_post_meta(
				$post_id,
				$keys['min_amount'],
				dtac_give_sanitize_amount(wp_unslash($_POST['dtac_give_min_amount'])) // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			);
		} else {
			delete_post_meta($post_id, $keys['min_amount']);
		}

		if (isset($_POST['dtac_give_expiry_days']) && '' !== trim((string) wp_unslash($_POST['dtac_give_expiry_days']))) { // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			update_post_meta($post_id, $keys['expiry_days'], absint(wp_unslash($_POST['dtac_give_expiry_days']))); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		} else {
			delete_post_meta($post_id, $keys['expiry_days']);
		}
	}

	/**
	 * Published Give forms for the picker.
	 *
	 * @since 3.0.0
	 *
	 * @return array<int,string>
	 */
	private function get_give_forms(): array
	{

		return dtac_give_get_give_forms_for_picker();
	}
}

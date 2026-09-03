<?php

/**
 * Admin settings page markup.
 *
 * @package DTAC_Give
 *
 * @since 2.0.0
 */

use DTAC\Controllers\Form\Form;

defined('ABSPATH') || exit;

$form = new Form(new DTAC\Admin\Settings());

new DTAC\Controllers\Form\Process();

if (function_exists('settings_errors')) {
	settings_errors('dtac_give_settings');
}
?>
<div class="wrap donate-to-access-content-admin-page">
	<div class="dtac-settings">
		<header class="dtac-settings-hero">
			<p class="dtac-settings-hero__eyebrow"><?php esc_html_e('GiveWP add-on', 'dtac-give'); ?></p>
			<h1 class="dtac-settings-hero__title"><?php esc_html_e('Donate to Access Content', 'dtac-give'); ?></h1>
			<p class="dtac-settings-hero__lede"><?php esc_html_e('Restrict posts, pages, and other content until a visitor donates. Saved settings keep the same option keys used by the shortcode, blocks, and unlock flow.', 'dtac-give'); ?></p>
		</header>

		<div class="dtac-settings-layout">
			<main class="dtac-settings-main">
				<div class="dtac-card dtac-card--form">
					<?php $form->output(); ?>
				</div>
				<div class="dtac-card dtac-card--howto">
					<?php \DTAC\Admin\How_To_Use::render(); ?>
				</div>
			</main>

			<aside class="dtac-settings-sidebar">
				<div class="dtac-card">
					<?php \DTAC\Admin\Insights::render_summary(); ?>
				</div>
				<div class="dtac-card dtac-card--faq">
					<?php \DTAC\Admin\Faq::render(); ?>
				</div>
				<div class="dtac-card dtac-card--cross-sell">
					<?php \DTAC\Admin\Cross_Sell::render(); ?>
				</div>
			</aside>
		</div>
	</div>
</div>
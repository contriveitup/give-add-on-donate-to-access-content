<?php
use DTAC\Controllers\Form\Form;

$form = new Form( new DTAC\Admin\Settings() );

new DTAC\Controllers\Form\Process();
?>
<div class="donate-to-access-content-admin-page mt-5">
	<div class="container is-fluid">
		<h2 class="is-size-3 mb-3">Donate to Access Content - Settings</h2>
		<p class="mb-5 is-size-6">Here you can manage how the plugin works and what content it should restrict for a user unless they make a donation.</p>
		<div class="columns">
			<div class="column is-two-thirds">
				<div class="box">
					<?php $form->output(); ?>
				</div>
			</div>
		</div>
	</div>
</div>
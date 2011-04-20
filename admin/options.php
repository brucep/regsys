
<div class="wrap">
	<div id="icon-options-general" class="icon32"><br></div>
	<h2>NSEvent Options</h2>

	<form method="post" action="options.php">
		<?php settings_fields('nsevent' ); ?>
		<?php $options = get_option('nsevent'); ?>

		<h3><?php _e('Registration', 'nsevent'); ?></h3>

		<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php _e('Current Event', 'nsevent'); ?></th>
				<td>
					<select name="nsevent[current_event_id]" class="postform">
<?php foreach ($events as $event): ?>
						<option class="level-0" value="<?php echo (int) $event->get_id(); ?>"<?php if (isset($options['current_event_id']) and $options['current_event_id'] == $event->get_id()) echo ' selected="selected"'; ?>><?php echo esc_attr($event->get_name()); ?></option>
<?php endforeach; ?>
					</select>
					<span class="description"><?php _e('The event currently used by the registration form.', 'nsevent'); ?></span>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row"><?php _e('Registration Testing', 'nsevent'); ?></th>
				<td>
					<fieldset>
						<legend class="screen-reader-text"><span><?php _e('Registration Testing', 'nsevent'); ?></span></legend>
						<label for="enable_xmlrpc">
							<input name="nsevent[registration_testing]" type="checkbox" value="1"<?php if (isset($options['registration_testing']) and $options['registration_testing']) echo ' checked="checked"'; ?>>
							<?php _e('Only "capable" users will be able to access the registration form.', 'nsevent'); ?>
						</label>
						<br>
					</fieldset>
				</td>
			</tr>
		</table>

		<h3><?php _e('PayPal', 'nsevent'); ?></h3>

		<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php _e('PayPal Business Address', 'nsevent'); ?></th>
				<td>
					<input type="text" name="nsevent[paypal_business]" value="<?php if (isset($options['paypal_business'])) echo esc_attr($options['paypal_business']); ?>" class="regular-text">
					<span class="description"><?php _e('The email address used to receive payments via PayPal. (If this is not set, then the PayPal payment option will not be available.)', 'nsevent'); ?></span>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row"><?php _e('PayPal Fee', 'nsevent'); ?></th>
				<td>
					<input type="text" name="nsevent[paypal_fee]" value="<?php if (isset($options['paypal_fee'])) echo (int) $options['paypal_fee']; ?>" class="regular-text">
					<span class="description"><?php _e('The processing fee, if any, for payments made via PayPal.', 'nsevent'); ?></span>
				</td>
			</tr>
		</table>

		<h3><?php _e('Confirmation Email', 'nsevent'); ?></h3>

		<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php _e('Confirmation Email Address', 'nsevent'); ?></th>
				<td>
					<input type="text" name="nsevent[confirmation_email_address]" value="<?php if (isset($options['confirmation_email_address'])) echo esc_attr($options['confirmation_email_address']); ?>" class="regular-text">
					<span class="description"><?php _e('This email address will appear on confirmation emails.', 'nsevent'); ?></span>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row"><?php _e('Confirmation Email Bcc', 'nsevent'); ?></th>
				<td>
					<input type="text" name="nsevent[confirmation_email_bcc]" value="<?php if (isset($options['confirmation_email_bcc'])) echo esc_attr($options['confirmation_email_bcc']); ?>" class="regular-text">
					<span class="description"><?php _e('The email address will receive a copy of confirmation emails.', 'nsevent'); ?></span>
				</td>
			</tr>
		</table>

		<p class="submit"><input type="submit" class="button-primary" value="<?php _e('Save Changes'); ?>"></p>
	</form>
</div>

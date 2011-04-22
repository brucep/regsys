<?php if (!get_post_meta($post->ID, 'nsevent_registration_form', true)) { get_header(); } ?>

				<div id="nsevent-registration-form-accepted-mail" <?php post_class('nsevent-registration-form'); ?>>
					<h1 class="entry-title"><?php printf(__('Registration Accepted for %s', 'nsevent'), esc_html($event->get_name())); ?></h1>

					<div id="accepted" class="<?php echo $early_class; if ($vip) echo ' vip'; ?>">
						<p><?php _e('Your registration has been recorded.', 'nsevent'); ?></p>
<?php if ($total_cost > 0): ?>
						<p><?php printf('Please mail your check for <strong>$%1$d</strong> (made out to <em>%2$s</em>, and postmarked no later than <em>%3$s</em>), along with your name and email address, to:', $dancer->get_price_total(), esc_html($options['payable_to']), $event->get_date_postmark_by('F jS')); ?></p>

						<address style="font-weight: bold;"><?php echo nl2br(esc_html($options['mailing_address'])); ?></address>
<?php endif; ?>
					</div>
				</div>

<?php if (!get_post_meta($post->ID, 'nsevent_registration_form', true)) { get_footer(); } ?>

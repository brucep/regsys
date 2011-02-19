<?php if (!get_post_meta($post->ID, 'nsevent_registration_form', true)) { get_header(); } ?>

				<div id="nsevent-registration-form-accepted-mail" <?php post_class('nsevent-registration-form'); ?>>
					<h1 class="entry-title"><?php printf(__('Registration Accepted for %s', 'nsevent'), esc_html($event->name)); ?></h1>

					<div id="accepted" class="<?php echo $early_bird_class; if ($vip) echo ' vip'; ?>">
						<p><?php _e('Your registration has been recorded.', 'nsevent'); ?></p>
<?php if ($total_cost > 0): ?>
						<p><?php printf('Please mail your check for <strong>$%d</strong> (made out to <em>%s</em>, and postmarked no later than <em>%s</em>), along with your name and email address, to:', $dancer->total_cost(), 'Naptown Stomp', date('F jS', $event->postmark_by($early_bird))); ?></p>

						<address style="font-weight: bold;"><?php _e('Naptown Stomp<br />P.O. Box 1051<br />Indianapolis, IN 46206', 'nsevent'); ?></address>
<?php endif; ?>
					</div>
				</div>

<?php if (!get_post_meta($post->ID, 'nsevent_registration_form', true)) { get_footer(); } ?>

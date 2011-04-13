<?php if (!get_post_meta($post->ID, 'nsevent_registration_form', true)) { get_header(); } ?>

				<div id="nsevent-registration-form-confirm" <?php post_class('nsevent-registration-form'); ?>>
					<h1 class="entry-title"><?php printf(__('Confirm Registration for %s', 'nsevent'), esc_html($event->get_name())); ?></h1>

					<form action="<?php echo get_permalink(); ?>" method="post" <form action="<?php echo get_permalink(); ?>" method="post" class="<?php echo $early_class; if ($vip) { echo ' vip'; } ?>">
						<div class="field"><span class="label"><?php _e('Name', 'nsevent'); ?>:</span> <?php echo esc_html($dancer->get_name()); ?></div>
						<?php NSEvent_FormInput::hidden('first_name'); echo "\n"; ?>
						<?php NSEvent_FormInput::hidden('last_name'); echo "\n"; ?>
						
						<div class="field"><span class="label"><?php _e('Email Address', 'nsevent'); ?>:</span> <?php echo esc_html($dancer->get_email()); ?></div>
						<?php NSEvent_FormInput::hidden('email'); echo "\n"; ?>
						
						<div class="field"><span class="label"><?php _e('Position', 'nsevent'); ?>:</span> <?php echo esc_html($dancer->get_position()); ?></div>
						<?php NSEvent_FormInput::hidden('position'); echo "\n"; ?>
<?php if ($event->has_levels()): ?>
						<div class="field"><span class="label"><?php _e('Level', 'nsevent'); ?>:</span> <?php echo esc_html($event->get_level_for_index($dancer->get_level())); ?></div>
						<?php NSEvent_FormInput::hidden('level'); echo "\n"; ?>
<?php endif; ?>

<?php if ($event->has_discount() and !$vip): ?>
<?php 	if ($_POST['payment_discount'] != 0): ?>
						<div class="field"><?php _e('&#10004;&nbsp;', 'nsevent'); echo esc_html($event->get_discount_name($_POST['payment_discount'])); ?></div>
<?php 	endif; ?>
						<?php NSEvent_FormInput::hidden('payment_discount'); echo "\n"; ?>
<?php endif; ?>			

<?php if ($event->has_volunteers() and $dancer->is_volunteer() and !$vip): ?>
						<div class="field"><?php _e('&#10004;&nbsp;', 'nsevent'); _e("I'm interested in volunteering.", 'nsevent'); printf('&nbsp;(%s)', esc_html($_POST['volunteer_phone'])); ?></div>
						<?php NSEvent_FormInput::hidden('status'); echo "\n"; ?>
						<?php NSEvent_FormInput::hidden('volunteer_phone'); echo "\n"; ?>
<?php endif; ?>

<?php if ($vip): ?>
						<div class="field"><?php _e('&#10004;&nbsp;', 'nsevent'); _e('VIP', 'nsevent'); ?></div>
						<?php NSEvent_FormInput::hidden('vip'); echo "\n"; ?>
						<?php NSEvent_FormInput::hidden('discount', array('value' => 'vip')); echo "\n"; ?>
<?php endif; ?>

<?php foreach (NSEvent::$validated_items as $key => $item): ?>
						<div class="field"><?php _e('&#10004;&nbsp;', 'nsevent'); echo esc_html($item->get_name()); if (isset($_POST['item_meta'][$key])) printf(' <span class="item-meta">(%s)</span>', esc_html(ucfirst($_POST['item_meta'][$key])));?></div>
<?php 	if ($key == NSEvent::$validated_package_id): ?>
						<?php NSEvent_FormInput::hidden('package', array('value' => $key)); echo "\n"; ?>
<?php 	else: ?>
						<?php NSEvent_FormInput::hidden(sprintf('items[%d]', $key), array('value' => $_POST['items'][$key])); echo "\n"; ?>
<?php 	endif; ?>
<?php 	if (isset($_POST['item_meta'][$key])): ?>
						<?php NSEvent_FormInput::hidden(sprintf('item_meta[%d]', $key), array('value' => $_POST['item_meta'][$key])); echo "\n"; ?>
<?php 	endif; ?>
<?php endforeach; ?>

<?php if (isset($_POST['housing_needed'])): ?>
						<div class="field"><?php _e('&#10004;&nbsp;', 'nsevent'); _e('Housing Needed', 'nsevent'); ?></div>
						<?php NSEvent_FormInput::hidden('housing_needed'); echo "\n"; ?>
<?php 	foreach(array('car', 'no_smoking', 'no_pets', 'nights', 'gender', 'comment') as $field): ?>
						<?php NSEvent_FormInput::hidden('housing_needed_'.$field); echo "\n"; ?>
<?php 	endforeach; ?>
<?php elseif (isset($_POST['housing_provider'])): ?>
						<div class="field"><?php _e('&#10004;&nbsp;', 'nsevent'); _e('Housing Provider', 'nsevent'); ?></div>
						<?php NSEvent_FormInput::hidden('housing_provider'); echo "\n"; ?>
<?php 	foreach(array('available', 'smoking', 'pets', 'nights', 'gender', 'comment') as $field): ?>
						<?php NSEvent_FormInput::hidden('housing_provider_'.$field); echo "\n"; ?>
<?php 	endforeach; ?>
<?php endif; ?>

						<div class="field"><?php _e('&#10004;&nbsp;', 'nsevent'); echo ($_POST['payment_method'] === 'PayPal') ? _e('PayPal', 'nsevent') : _e('Mail', 'nsevent'); ?></div>
						<div class="field"><span class="label"><?php _e('Total Amount', 'nsevent'); ?>:</span> <?php printf('$%d', $total_cost); ?></div>
						<?php NSEvent_FormInput::hidden('payment_method'); echo "\n"; ?>
						<?php NSEvent_FormInput::hidden('confirmed'); echo "\n"; ?>
						
						<div id="submit"><input type="submit" value="<?php _e('Confirm&hellip;', 'nsevent'); ?>" /></div>
					</form>
				</div>

<?php if (!get_post_meta($post->ID, 'nsevent_registration_form', true)) { get_footer(); } ?>

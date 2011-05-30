<?php if (!get_post_meta($post->ID, 'nsevent_registration_form', true)) { get_header(); } ?>

				<div id="nsevent-registration-form-confirm" <?php post_class('nsevent-registration-form'); ?>>
					<h1 class="entry-title"><?php printf(__('Confirm Registration for %s', 'nsevent'), esc_html($event->get_name())); ?></h1>

					<form action="<?php echo get_permalink(); ?>" method="post"<?php if ($vip) echo ' class="vip"'; ?>>
						<?php NSEvent_FormInput::hidden('first_name');       echo "\n"; ?>
						<?php NSEvent_FormInput::hidden('last_name');        echo "\n"; ?>
						<?php NSEvent_FormInput::hidden('email');            echo "\n"; ?>
						<?php NSEvent_FormInput::hidden('confirm_email');    echo "\n"; ?>
						<?php NSEvent_FormInput::hidden('mobile_phone');     echo "\n"; ?>
						<?php NSEvent_FormInput::hidden('position');         echo "\n"; ?>
						<?php NSEvent_FormInput::hidden('level');            echo "\n"; ?>
						<?php NSEvent_FormInput::hidden('payment_discount'); echo "\n"; ?>
						<?php NSEvent_FormInput::hidden('status');           echo "\n"; ?>
						<?php NSEvent_FormInput::hidden('volunteer_phone');  echo "\n"; ?>
						<?php NSEvent_FormInput::hidden('payment_method');   echo "\n"; ?>
						<?php NSEvent_FormInput::hidden('confirmed');        echo "\n"; ?>
<?php if ($vip): ?>
						<?php NSEvent_FormInput::hidden('vip'); echo "\n"; ?>
<?php endif; ?>

<?php foreach (NSEvent::$validated_items as $key => $item): ?>
<?php 	if ($key == NSEvent::$validated_package_id): ?>
						<?php NSEvent_FormInput::hidden('package', array('value' => $key)); echo "\n"; ?>
<?php 	else: ?>
						<?php NSEvent_FormInput::hidden(sprintf('items[%d]', $key), array('value' => $_POST['items'][$key])); echo "\n"; ?>
<?php 	endif; ?>
<?php 	if (isset($_POST['item_meta'][$key])): ?>
						<?php NSEvent_FormInput::hidden(sprintf('item_meta[%d]', $key), array('value' => $_POST['item_meta'][$key])); echo "\n"; ?>
<?php 	endif; ?>
<?php endforeach; ?>

<?php if ($dancer->needs_housing()): ?>
						<?php NSEvent_FormInput::hidden('housing_type_needed'); echo "\n"; ?>
<?php 	foreach(array('from_scene', 'smoke', 'pets', 'nights', 'gender', 'bedtime', 'comment') as $field): ?>
						<?php NSEvent_FormInput::hidden(sprintf('housing_needed[housing_%s]', $field)); echo "\n"; ?>
<?php 	endforeach; ?>
<?php elseif ($dancer->is_housing_provider()): ?>
						<?php NSEvent_FormInput::hidden('housing_type_provider'); echo "\n"; ?>
<?php 	foreach(array('spots_available', 'smoke', 'pets', 'nights', 'gender', 'bedtime', 'comment') as $field): ?>
						<?php NSEvent_FormInput::hidden(sprintf('housing_provider[housing_%s]', $field)); echo "\n"; ?>
<?php 	endforeach; ?>
<?php endif; ?>

						<table>
							<tr>
								<td class="label">Name</td>
								<td class="value"><?php echo esc_html($dancer->get_name()); ?></td>
								<td class="price"><?php if ($vip) { echo 'VIP'; } ?></td>
							</tr>
							<tr>
								<td class="label">Email Address</td>
								<td class="value"><?php echo esc_html($dancer->get_email()); ?></td>
								<td class="price"></td>
							</tr>
							<tr>
								<td class="label">Mobile Phone Number</td>
								<td class="value"><?php echo esc_html($dancer->get_mobile_phone()); ?></td>
								<td class="price"></td>
							</tr>
							<tr>
								<td class="label">Position</td>
								<td class="value"><?php echo esc_html($dancer->get_position()); ?></td>
								<td class="price"></td>
							</tr>
<?php if ($event->has_levels()): ?>
							<tr>
								<td class="label">Level</td>
								<td class="value"><?php echo esc_html($event->get_level_for_index($dancer->get_level())); ?></td>
								<td class="price"></td>
							</tr>
<?php endif; ?>
<?php if ($dancer->received_discount()): ?>
							<tr>
								<td class="label">Discount</td>
								<td class="value"><?php printf('Student or member of %s', esc_html($event->get_discount_org_name())); ?></td>
								<td class="price"></td>
							</tr>
<?php endif; ?>
<?php if ($event->has_volunteers() and $dancer->is_volunteer()): ?>
							<tr>
								<td class="label">Volunteer</td>
								<td class="value"></td>
								<td class="price"></td>
							</tr>
<?php endif; ?>
<?php if ($dancer->needs_housing()): ?>
							<tr>
								<td class="label">Housing Needed</td>
								<td class="value"></td>
								<td class="price"></td>
							</tr>
<?php elseif ($dancer->is_housing_provider()): ?>
							<tr>
								<td class="label">Housing Provider</td>
								<td class="value"></td>
								<td class="price"></td>
							</tr>
<?php endif; ?>
<?php foreach (NSEvent::$validated_items as $key => $item): ?>
							<tr>
								<td class="label"><?php echo esc_html($item->get_name()); ?></td>
								<td class="value"><?php if (isset($_POST['item_meta'][$key])) { echo esc_html(ucfirst($_POST['item_meta'][$key])); } ?></td>
								<td class="price"><?php printf('$%d', $item->get_price_for_prereg($_POST['payment_discount'])); ?></td>
							</tr>
<?php endforeach; ?>
							<tr>
								<td class="label">Total Amount Owed</td>
								<td class="value"><?php if ($total_cost > 0) { if ($_POST['payment_method'] === 'PayPal') { echo 'Pay with PayPal'; } else { echo 'Pay by mail'; } } ?></td>
								<td class="price"><strong><?php printf('$%d', $total_cost); ?><strong></td>
							</tr>
						<table>

						<div id="submit"><input type="submit" value="<?php _e('Confirm Registration', 'nsevent'); ?>" /></div>
				</div>

<?php if (!get_post_meta($post->ID, 'nsevent_registration_form', true)) { get_footer(); } ?>

<?php

$packages     = $event->get_items_where(array(':preregistration' => 1, ':type' => 'package'));
$competitions = $event->get_items_where(array(':preregistration' => 1, ':type' => 'competition'));
$shirts       = $event->get_items_where(array(':preregistration' => 1, ':type' => 'shirt'));

?>
<?php if (!get_post_meta($post->ID, 'nsevent_registration_form', true)) { get_header(); } ?>

				<div id="nsevent-registration-form-info" <?php post_class('nsevent-registration-form'); ?>>
					<h1 class="entry-title"><?php printf(__('Register for %s', 'nsevent'), esc_html($event->get_name())); ?></h1>
					<div class="entry-content">
						<?php the_content(); ?>
					</div>

					<form action="<?php echo get_permalink(); ?>" method="post" class="<?php echo $early_class; if ($vip) { echo ' vip'; } ?>">
<?php if (!$vip): ?>
						<div id="pricing-dates">
<?php 	if ($event->get_date_early_end()): ?>
<?php   	if ($event->is_early_bird()): ?>
								<?php printf(__('Early Bird prices end at %s on %s.', 'nsevent'), $event->get_date_early_end('h:i A (T)'), $event->get_date_early_end('F jS')); ?><br />
<?php   	else: ?>
								<?php _e('Early Bird prices are no longer available.', 'nsevent'); ?><br />
<?php   	endif; ?>
<?php 	endif; ?>
							<?php printf(__('Preregistration prices end at %s on %s.', 'nsevent'), $event->get_date_prereg_end('h:i A (T)'), $event->get_date_prereg_end('F jS')); ?><br />
							<?php printf(__('(Refunds are not available after %s.)', 'nsevent'), $event->get_date_refund_end('F jS')); ?>
						</div>
<?php endif; ?>


<?php # PERSONAL INFO ################################################## ?>
						<h2><?php _e('Personal Info', 'nsevent'); ?> <span><?php _e('(All Fields Required)', 'nsevent'); ?></span></h2>
						<fieldset id="personal">
							<?php echo NSEvent_FormValidation::get_error('first_name'), "\n"; ?>
							<div class="field text"><?php NSEvent_FormInput::text('first_name', array('maxlength' => 100, 'label' => __('First Name', 'nsevent'))); ?></div>

							<?php echo NSEvent_FormValidation::get_error('last_name'), "\n"; ?>
							<div class="field text"><?php NSEvent_FormInput::text('last_name',  array('maxlength' => 100, 'label' => __('Last Name', 'nsevent'))); ?></div>

							<?php echo NSEvent_FormValidation::get_error('email'), "\n"; ?>
							<div class="field text"><?php NSEvent_FormInput::text('email',      array('maxlength' => 100, 'label' => __('Email Address', 'nsevent'), 'type' => 'email')); ?></div>

							<?php echo NSEvent_FormValidation::get_error('confirm_email'), "\n"; ?>
							<div class="field text" style='padding-bottom:1em'><?php NSEvent_FormInput::text('confirm_email', array('maxlength' => 100, 'label' => __('Confirm Email Address', 'nsevent'), 'type' => 'email')); ?></div>

							<?php echo NSEvent_FormValidation::get_error('position'), "\n"; ?>
							<div class="field" id="position">
								<div class="field-label"><?php _e('Position', 'nsevent'); ?></div>

<?php if ($event->limit_per_position and $event->limit_per_position <= $event->count_dancers('position', 1)): ?>
								<div class="radio"><?php NSEvent_FormInput::radio('position', array('value' => 1, 'label' => __('(Registrations for leads are no longer being accepted.)', 'nsevent'), 'disabled' => true)); ?></div>
<?php else: ?>
								<div class="radio"><?php NSEvent_FormInput::radio('position', array('value' => 1, 'label' => __('Lead', 'nsevent'), 'default' => true)); ?></div>								
<?php endif; ?>

<?php if ($event->limit_per_position and $event->limit_per_position <= $event->count_dancers('position', 2)): ?>
								<div class="radio"><?php NSEvent_FormInput::radio('position', array('value' => 2, 'label' => __('(Registrations for follows are no longer being accepted.)', 'nsevent'), 'disabled' => true)); ?></div>
<?php else: ?>
								<div class="radio"><?php NSEvent_FormInput::radio('position', array('value' => 2, 'label' => __('Follow', 'nsevent'))); ?></div>
<?php endif; ?>
							</div>

<?php if ($event->has_levels()): ?>
							<?php echo NSEvent_FormValidation::get_error('level'), "\n"; ?>
							<div class="field" id="level">
								<div class="field-label">Level</div>
<?php 	foreach ($event->get_levels() as $level => $label): ?>
								<div class="radio"><?php NSEvent_FormInput::radio('level', array('value' => $level, 'label' => $label, 'default' => !($level - 1))); ?></div>
<?php 	endforeach; ?>
							</div>
<?php endif; # levels ?>

<?php if ($event->has_discount() and !$vip): ?>
							<?php echo NSEvent_FormValidation::get_error('payment_discount'), "\n"; ?>
							<div class="field" id="discount">
								<div class="field-label"><?php if ($event->discount_label) echo esc_html($event->discount_label); else _e('Member or Student?', 'nsevent'); echo ' ', __('(Prices will adjust automatically.)', 'nsevent'); ?></div>
<?php 	if ($event->discount1): ?>
								<div class="radio"><?php NSEvent_FormInput::radio('payment_discount', array('value' => 1, 'id' => 'discount1', 'label' => $event->discount1)); ?></div>
<?php 	endif; ?>
<?php 	if ($event->discount2): ?>
								<div class="radio"><?php NSEvent_FormInput::radio('payment_discount', array('value' => 2, 'id' => 'discount2', 'label' => $event->discount2)); ?></div>
<?php 	endif; ?>
								<div class="radio"><?php NSEvent_FormInput::radio('payment_discount', array('value' => 0, 'id' => 'discount0', 'label' => __('None of these apply to me.', 'nsevent'), 'default' => true)); ?></div>
<?php 	if ($event->discount_note):?>
								<div class="caption">
									<p><?php echo esc_html($event->discount_note); ?></p>
								</div>
<?php 	endif; ?>
							</div>
<?php endif; # discounts ?>

<?php if ($event->has_volunteers() and !$vip): ?>
							<?php echo NSEvent_FormValidation::get_error('status'), "\n"; ?>
							<?php echo NSEvent_FormValidation::get_error('volunteer_phone'), "\n"; ?>
							<div class="field"><?php NSEvent_FormInput::checkbox('status', array('value' => 1, 'label' => sprintf(__("I'm interested in volunteering. (Volunteers will receive $%d for every (one hour) shift worked!)", 'nsevent'), 5))); ?><br /><?php NSEvent_FormInput::text('volunteer_phone', array('type' => 'tel', 'size' => 14, 'label' => 'Mobile Phone Number: ')); ?></div>
<?php endif; # volunteers ?>
<?php if ($vip): ?>
							<?php NSEvent_FormInput::hidden('vip'); echo "\n"; ?>
<?php endif; # vip ?>
						</fieldset>


<?php # PACKAGES ####################################################### ?>
<?php if ($packages): ?>
						<h2><?php _e('Packages', 'nsevent'); ?> <span><?php _e('(Select One)', 'nsevent'); ?></span></h2>
						<fieldset id="packages">
							<?php echo NSEvent_FormValidation::get_error('package'), "\n"; ?>
							<table cellspacing="0">
								<thead>
									<tr>		
										<th width="25%">Event</th>
<?php 	if (!$vip): ?>
<?php 		if ($event->is_early_bird()): ?>
										<th class="price">Early Bird</th>
<?php 		endif; ?>
										<th class="price">Preregistered</th>
										<th class="price">At Door</th>
<?php 	endif; ?>
										<th width="42%">Description</th>
									</tr>
								</thead>
								<tbody>
<?php 	foreach ($packages as $index => $item): if ($item->is_expired()) continue; ?>
									<tr>
										<td><?php NSEvent_FormInput::radio('package', array('value' => $item->get_id(), 'default' => !$index, 'label' => $item->get_name())); ?></td>
<?php 		if (!$vip): ?>
<?php 			if ($event->is_early_bird()): ?>
										<td class="price <?php echo $early_class; ?>">
											<div class="price_early"><?php printf('$%d', $item->get_price_for_discount(false, true)); ?></div>
											<div class="price_early_discount1 no_show">$<?php printf('$%d', $item->get_price_for_discount(1, true)); ?></div>
											<div class="price_early_discount2 no_show">$<?php printf('$%d', $item->get_price_for_discount(2, true)); ?></div>
										</td>
<?php 			endif; ?>
										<td class="price">
											<div class="price_prereg"><?php printf('$%d', $item->get_price_for_discount(false)); ?></div>
											<div class="price_prereg_discount1 no_show">$<?php printf('$%d', $item->get_price_for_discount(1)); ?></div>
											<div class="price_prereg_discount2 no_show">$<?php printf('$%d', $item->get_price_for_discount(2)); ?></div>
										</td>
										<td class="price">
											<div class="price_door"><?php echo ($item->get_price_for_discount(false, 'door')) ? sprintf('$%d', $item->get_price_for_discount(false, 'door')) : '&mdash;'; ?></div>
											<div class="price_door_discount1 no_show"><?php echo ($item->get_price_for_discount(1, 'door')) ? sprintf('$%d', $item->get_price_for_discount(1, 'door')) : '&mdash;'; ?></div>
											<div class="price_door_discount2 no_show"><?php echo ($item->get_price_for_discount(2, 'door')) ? sprintf('$%d', $item->get_price_for_discount(2, 'door')) : '&mdash;'; ?></div>
										</td>
<?php 		endif; ?>
										<td class="description"><?php echo esc_html($item->get_description()); ?></td>
									</tr>
<?php 	endforeach; ?>
									<tr>
										<td colspan="<?php echo ($event->is_early_bird()) ? 4 : 3; ?>"><?php NSEvent_FormInput::radio('package', array('value' => 0, 'label' => 'N/A')); ?></td>
										<td class="description"><?php if ($vip) _e('<strong>VIPs:</strong> To help us track attendance, please choose the most suitable package for yourself.', 'nsevent'); ?></td>
									</tr>
								</tbody>
							</table>
						</fieldset>
<?php endif; # packages ?>


<?php # COMPETITIONS ################################################### ?>
<?php if ($competitions): ?>
						<h2><?php _e('Competitions', 'nsevent'); ?></h2>
						<fieldset id="competitions">
							<table cellspacing="0">
								<thead>
									<tr>		
										<th width="25%"><?php _e('Competition', 'nsevent'); ?></th>
<?php 	if ($event->is_early_bird() and !$vip): ?>
										<th class="price"><?php _e('Early Bird', 'nsevent'); ?></th>
<?php 	endif; ?>
										<th class="price"><?php _e('Preregistered', 'nsevent'); ?></th>
										<th width="53%"><?php _e('Information', 'nsevent'); ?></th>
									</tr>
								</thead>
								<tbody>
<?php 	foreach ($competitions as $index => $item): if ($item->is_expired()) continue; ?>
<?php 		if ($item->count_openings()): ?>
									<?php echo NSEvent_FormValidation::get_error('item_'.$item->get_id(), sprintf('<tr class="nsevent-validation-error"><td colspan="%d">', ($event->get_date_early_end()) ? 4 : 3), '</td></tr>'), "\n"; ?>
									<tr>
										<td><?php NSEvent_FormInput::checkbox(sprintf('items[%d]', $item->get_id()), array('value' => $item->get_id(), 'default' => !$index, 'label' => $item->get_name())); ?></td>
<?php 			if (!$vip): ?>
<?php 				if ($event->is_early_bird()): ?>
											<td class="price <?php echo $early_class; ?>">
												<div class="price_early"><?php printf('$%d', $item->get_price_for_discount(false, true)); ?></div>
												<div class="price_early_discount1 no_show">$<?php printf('$%d', $item->get_price_for_discount(1, true)); ?></div>
												<div class="price_early_discount2 no_show">$<?php printf('$%d', $item->get_price_for_discount(2, true)); ?></div>
											</td>
<?php 				endif; ?>
											<td class="price">
												<div class="price_prereg"><?php printf('$%d', $item->get_price_for_discount(false)); ?></div>
												<div class="price_prereg_discount1 no_show">$<?php printf('$%d', $item->get_price_for_discount(1)); ?></div>
												<div class="price_prereg_discount2 no_show">$<?php printf('$%d', $item->get_price_for_discount(2)); ?></div>
											</td>
<?php 			else: ?>
										<td class="price">
											<div class="price_vip">$<?php printf('$%d', $item->get_price_for_discount('vip')); ?></div>
										</td>
<?php 			endif; ?>
										<td class="description">
<?php 			if ($item->get_limit_total()): ?>
											<?php printf(__('Openings: %1$d', 'nsevent'), $item->count_openings()); if ($item->get_meta() == 'partner_name') echo _n(' couple', ' couples', $item->count_openings(), 'nsevent'); ?><br />
<?php 			elseif ($item->get_limit_per_position()): ?>
											<?php printf(__('Openings: %1$d lead(s), %2$d follow(s)', 'nsevent'), $item->count_openings(1), $item->count_openings(2)); ?><br />
<?php 			endif; #limit ?>
<?php 			if ($item->get_meta() == 'position'): ?>
											<span class="inline_radio"><?php _e('Register as:', 'nsevent'); ?>&nbsp;<?php NSEvent_FormInput::radio(sprintf('item_meta[%d]', $item->get_id()), array('value' => 'lead', 'label' => __('Lead', 'nsevent'), 'disabled' => !$item->count_openings('lead'))); ?>&nbsp;<?php NSEvent_FormInput::radio(sprintf('item_meta[%d]', $item->get_id()), array('value' => 'follow', 'label' => __('Follow', 'nsevent'), 'disabled' => !$item->count_openings('follow'))); ?></span>
<?php 			elseif ($item->get_meta() == 'partner_name'): ?>
											<?php NSEvent_FormInput::text(sprintf('item_meta[%d]', $item->get_id()), array('label' => 'Partner:&nbsp;', 'placeholder' => __('Partner\'s name', 'nsevent'))); ?><br />
											<?php echo __('(Only one of you need to sign up.)', 'nsevent'), "\n"; ?>
<?php 			elseif ($item->get_meta() == 'team_members'): ?>
											<?php echo $item->get_description(); ?><br />
											<?php NSEvent_FormInput::textarea(sprintf('item_meta[%d]', $item->get_id()), array('rows' => 6)); echo "\n"; ?>
<?php 			endif; # meta ?>
										</td>
									</tr>
<?php 		else: ?>
									<tr class="limit-reached">
										<td><?php echo esc_html($item->name); ?></td>
										<td class="description" colspan="<?php echo ($event->get_date_early_end()) ? 3 : 2; ?>"><?php _e('There are no more openings for this competition.', 'nsevent'); ?></td>
									</tr>
<?php 		endif; ?>
<?php 	endforeach; ?>
								</tbody>
							</table>
						</fieldset>
<?php endif; # competitions ?>


<?php # SHIRTS ######################################################### ?>
<?php if ($shirts and time() < $event->get_date_refund_end()): ?>
						<h2><?php _e('T-Shirts', 'nsevent'); ?>&nbsp;<span><?php _e('(Optional)', 'nsevent'); ?></span></h2>
						<fieldset id="shirt">
							<table cellspacing="0">
								<thead>
									<tr>
										<th width="25%"><?php _e('Shirt Style', 'nsevent'); ?></th>
<?php 	if (!$vip): ?>
<?php 		if ($event->is_early_bird()): ?>
										<th><?php _e('Early Bird', 'nsevent'); ?></th>
<?php 		endif; ?>
										<th><?php _e('Preregistered', 'nsevent'); ?></th>
										<th><?php _e('At Door', 'nsevent'); ?></th>
<?php 	else: ?>
										<th><?php _e('Preregistered', 'nsevent'); ?></th>
<?php 	endif; ?>
										<th width="42%"><?php _e('Size', 'nsevent'); ?></th>
									</tr>
								</thead>
								<tbody>
<?php 	foreach ($shirts as $item):  if ($item->get_date_expires()) continue; ?>
									<?php NSEvent_FormValidation::get_error('item_'.$item->get_id(), sprintf('<tr class="nsevent-validation-error"><td colspan="">', ($event->get_date_early_end()) ? 5 : 4), '</td></tr>'); echo "\n"; ?>
									<tr>
										<td><?php echo esc_html($item->get_name()); ?></td>
<?php 			if (!$vip): ?>
<?php 				if ($event->is_early_bird()): ?>
										<td class="price <?php echo $early_class; ?>">
											<div class="price_early"><?php printf('$%d', $item->get_price_for_discount(false, true)); ?></div>
											<div class="price_early_discount1 no_show">$<?php printf('$%d', $item->get_price_for_discount(1, true)); ?></div>
											<div class="price_early_discount2 no_show">$<?php printf('$%d', $item->get_price_for_discount(2, true)); ?></div>
										</td>
<?php 				endif; ?>
										<td class="price">
											<div class="price_prereg"><?php printf('$%d', $item->get_price_for_discount(false)); ?></div>
											<div class="price_prereg_discount1 no_show">$<?php printf('$%d', $item->get_price_for_discount(1)); ?></div>
											<div class="price_prereg_discount2 no_show">$<?php printf('$%d', $item->get_price_for_discount(2)); ?></div>
										</td>
										<td class="price">
											<div class="price_door"><?php echo ($item->get_price_for_discount(false, 'door')) ? sprintf('$%d', $item->get_price_for_discount(false, 'door')) : '&mdash;'; ?></div>
											<div class="price_door_discount1 no_show"><?php echo ($item->get_price_for_discount(1, 'door')) ? sprintf('$%d', $item->get_price_for_discount(1, 'door')) : '&mdash;'; ?></div>
											<div class="price_door_discount2 no_show"><?php echo ($item->get_price_for_discount(2, 'door')) ? sprintf('$%d', $item->get_price_for_discount(2, 'door')) : '&mdash;'; ?></div>
										</td>
<?php 			else: ?>
										<td class="price">
											<div class="price_vip">$<?php printf('$%d', $item->get_price_for_discount('vip')); ?></div>
										</td>
<?php 			endif; ?>
										<td class="size">
<?php
			// $options = array('none' => __('None', 'nsevent'), __('Shirt Size', 'nsevent') => array());
			// foreach (explode(',', $item->description) as $size):
			// 	$options[__('Shirt Size', 'nsevent')][$size] = ucfirst($size);
			// NSEvent_FormInput::select(sprintf('items[%d]', $item->get_id()), $options);
?>
											<select name="<?php printf('items[%d]', $item->get_id()); ?>">
												<option value="none"<?php echo NSEvent_FormInput::_set_select(sprintf('items[%d]', $item->get_id()), 'none', true); ?>><?php _e('None', 'nsevent'); ?></option>
												<optgroup label="<?php _e('Shirt Size', 'nsevent'); ?>">
<?php 		foreach (explode(',', $item->get_description()) as $size): ?>
													<option value="<?php echo esc_attr($size); ?>"<?php echo NSEvent_FormInput::_set_select(sprintf('items[%d]', $item->get_id()), esc_attr($size)); ?>><?php echo esc_html(ucfirst($size)); ?></option>
<?php 		endforeach; ?>
												</optgroup>
											</select>
										</td>
									</tr>
<?php 	endforeach; ?>
								</tbody>
							</table>
<?php 	if (!$vip): ?>
							<div class="field">
								<div class="caption">
									<p><?php _e('Note: At-The-Door quantities will be limited.', 'nsevent'); ?></p>
								</div>
							</div>
<?php 	endif; ?>
<?php 	if ($event->get_shirt_description()): ?>
							<div class="field" id="shirt-description">
								<div class="caption">
									<?php echo $event->get_shirt_description(); ?>
								</div>
							</div>
<?php 	endif; ?>
						</fieldset>
<?php endif; # shirts ?>


<?php # HOUSING ######################################################## ?>
<?php if ($event->has_housing()): ?>
						<h2><?php NSEvent_FormInput::checkbox('housing_type_provider', array('label' => __('I Can Provide Housing', 'nsevent'))); ?>&nbsp;<span><?php _e('(Optional)', 'nsevent'); ?></span></h2>
						<fieldset id="housing_type_provider_fields" class="no_show">
							<?php echo NSEvent_FormValidation::get_error('housing_provider[housing_spots_available]'), "\n"; ?>
							<div class="field"><?php printf(__('I can provide housing for %s person(s).', 'nsevent'), NSEvent_FormInput::text('housing_provider[housing_spots_available]', array('size' => 2, 'placeholder' => '#'), false)); ?></div>

							<?php echo NSEvent_FormValidation::get_error('housing_provider[housing_smoke]'), "\n"; ?>
							<?php echo NSEvent_FormValidation::get_error('housing_provider[housing_pets]'), "\n"; ?>
							<div class="field">
								<div class="checkbox"><?php NSEvent_FormInput::checkbox('housing_provider[housing_smoke]', array('label' => __('I smoke.', 'nsevent'))); ?></div>
								<div class="checkbox"><?php NSEvent_FormInput::checkbox('housing_provider[housing_pets]',  array('label' => __('I have pets.', 'nsevent'))); ?></div>
							</div>

							<?php echo NSEvent_FormValidation::get_error('housing_provider[housing_nights]'), "\n"; ?>
							<div class="field">
								<div class="field-label"><?php _e('I can providing housing on:', 'nsevent'); ?></div>
<?php 	foreach ($event->get_housing_nights() as $index => $night): ?>
								<div class="checkbox"><?php NSEvent_FormInput::checkbox(sprintf('housing_provider[housing_nights][%d]', $index), array('value' => $index, 'label' => __($night, 'nsevent'))); ?></div>
<?php 	endforeach; ?>
							</div>

							<?php echo NSEvent_FormValidation::get_error('housing_provider[housing_gender]'), "\n"; ?>
							<div class="field">
								<div class="field-label"><?php _e('I prefer to house:', 'nsevent'); ?></div>
								<div class="radio"><label><?php NSEvent_FormInput::radio('housing_provider[housing_gender]', array('value' => 3, 'id' => 'housing_provider[housing_gender_3]', 'label' => __('Boys and/or Girls', 'nsevent'), 'default' => true)); ?></div>
								<div class="radio"><label><?php NSEvent_FormInput::radio('housing_provider[housing_gender]', array('value' => 1, 'id' => 'housing_provider[housing_gender_1]', 'label' => __('Boys only', 'nsevent'))); ?></div>
								<div class="radio"><label><?php NSEvent_FormInput::radio('housing_provider[housing_gender]', array('value' => 2, 'id' => 'housing_provider[housing_gender_2]', 'label' => __('Girls only', 'nsevent'))); ?></div>
							</div>

							<?php echo NSEvent_FormValidation::get_error('housing_provider[housing_bedtime]'), "\n"; ?>
							<div class="field">
								<div class="field-label"><?php _e('Bedtime preference:', 'nsevent'); ?></div>
								<div class="radio"><label><?php NSEvent_FormInput::radio('housing_provider[housing_bedtime]', array('value' => 0, 'id' => 'housing_provider[housing_bedtime_0]', 'label' => __('No Preference', 'nsevent'), 'default' => true)); ?></div>
								<div class="radio"><label><?php NSEvent_FormInput::radio('housing_provider[housing_bedtime]', array('value' => 1, 'id' => 'housing_provider[housing_bedtime_1]', 'label' => __('Early Bird', 'nsevent'))); ?></div>
								<div class="radio"><label><?php NSEvent_FormInput::radio('housing_provider[housing_bedtime]', array('value' => 2, 'id' => 'housing_provider[housing_bedtime_2]', 'label' => __('Night Owl', 'nsevent'))); ?></div>
							</div>

							<?php echo NSEvent_FormValidation::get_error('housing_provider[housing_comment]'), "\n"; ?>
							<div class="field textarea">
								<label for="housing_provider_comment"><?php _e('Comments:', 'nsevent'); ?></label><br />
								<?php NSEvent_FormInput::textarea('housing_provider[housing_comment]', array('rows' => 6)); echo "\n"; ?>
							</div>
						</fieldset>


						<h2><?php NSEvent_FormInput::checkbox('housing_type_needed', array('label' => __('I Need Housing', 'nsevent'))); ?>&nbsp;<span><?php _e('(Optional)', 'nsevent'); ?></span></h2>
						<fieldset id="housing_type_needed_fields" class="no_show">
							<?php echo NSEvent_FormValidation::get_error('housing_needed[housing_from_scene]'), "\n"; ?>
							<div class="field"><?php echo __('I am from: ', 'nsevent'), NSEvent_FormInput::text('housing_needed[housing_from_scene]', array('size' => 30, 'placeholder' => 'Scene, area, nearest major city, etc.'), false); ?></div>

							<?php echo NSEvent_FormValidation::get_error('housing_needed[housing_smoke]'), "\n"; ?>
							<?php echo NSEvent_FormValidation::get_error('housing_needed[housing_pets]'), "\n"; ?>
							<div class="field">
								<div class="checkbox"><?php NSEvent_FormInput::checkbox('housing_needed[housing_smoke]', array('label' => __('I would prefer no smoking.', 'nsevent'))); ?></div>
								<div class="checkbox"><?php NSEvent_FormInput::checkbox('housing_needed[housing_pets]',  array('label' => __('I would prefer no pets.', 'nsevent'))); ?></div>
							</div>

							<?php echo NSEvent_FormValidation::get_error('housing_needed[housing_nights]'), "\n"; ?>
							<div class="field">
								<div class="field-label"><?php _e('I need housing for:', 'nsevent'); ?></div>
<?php 	foreach ($event->get_housing_nights() as $index => $night): ?>
								<div class="checkbox"><?php NSEvent_FormInput::checkbox(sprintf('housing_needed[housing_nights][%d]', $index), array('value' => $index, 'label' => __($night, 'nsevent'))); ?></div>
<?php 	endforeach; ?>
							</div>

							<?php echo NSEvent_FormValidation::get_error('housing_needed[housing_gender]'), "\n"; ?>
							<div class="field">
								<div class="field-label"><?php _e('I prefer to be housed with:', 'nsevent'); ?></div>
								<div class="radio"><label><?php NSEvent_FormInput::radio('housing_needed[housing_gender]', array('value' => 3, 'id' => 'housing_needed[housing_gender_3]', 'label' => __('Boys and/or Girls', 'nsevent'), 'default' => true)); ?></div>
								<div class="radio"><label><?php NSEvent_FormInput::radio('housing_needed[housing_gender]', array('value' => 1, 'id' => 'housing_needed[housing_gender_1]', 'label' => __('Boys only', 'nsevent'))); ?></div>
								<div class="radio"><label><?php NSEvent_FormInput::radio('housing_needed[housing_gender]', array('value' => 2, 'id' => 'housing_needed[housing_gender_2]', 'label' => __('Girls only', 'nsevent'))); ?></div>
							</div>

							<?php echo NSEvent_FormValidation::get_error('housing_needed[housing_bedtime]'), "\n"; ?>
							<div class="field">
								<div class="field-label"><?php _e('Bedtime preference:', 'nsevent'); ?></div>
								<div class="radio"><label><?php NSEvent_FormInput::radio('housing_needed[housing_bedtime]', array('value' => 0, 'id' => 'housing_needed[housing_bedtime_0]', 'label' => __('No Preference', 'nsevent'), 'default' => true)); ?></div>
								<div class="radio"><label><?php NSEvent_FormInput::radio('housing_needed[housing_bedtime]', array('value' => 1, 'id' => 'housing_needed[housing_bedtime_1]', 'label' => __('Early Bird', 'nsevent'))); ?></div>
								<div class="radio"><label><?php NSEvent_FormInput::radio('housing_needed[housing_bedtime]', array('value' => 2, 'id' => 'housing_needed[housing_bedtime_2]', 'label' => __('Night Owl', 'nsevent'))); ?></div>
							</div>

							<?php echo NSEvent_FormValidation::get_error('housing_needed[housing_comment]'), "\n"; ?>
							<div class="field textarea">
								<label for="housing_needed_comment">Comments:</label><br />
								<?php NSEvent_FormInput::textarea('housing_needed[housing_comment]', array('rows' => 6)); echo "\n"; ?>
							</div>
						</fieldset>
<?php endif; # housing ?>


<?php # PAYMENT METHOD ################################################# ?>
						<h2><?php _e('Payment Method', 'nsevent'); ?></h2>
						<fieldset id="payment">
							<?php echo NSEvent_FormValidation::get_error('payment_method'), "\n"; ?>
							<div class="field">
								<div class="radio"><?php NSEvent_FormInput::radio('payment_method', array('value' => 'PayPal', 'label' => sprintf(__('PayPal%s', 'nsevent'), (empty($options['paypal_fee']) ? '' : sprintf(__(' ($%d processing fee)', 'nsevent'), $options['paypal_fee']))))); ?></div>
								<div class="radio"><?php NSEvent_FormInput::radio('payment_method', array('value' => 'Mail', 'default' => true, 'label' => sprintf(__('Mail (Check must be postmarked by %s.)', 'nsevent'), date('F jS', $event->get_date_postmark_by())))); ?></div>

								<div class="caption">
									<p><?php printf(__('(Refunds are not available after %s.)', 'nsevent'), $event->get_date_refund_end('F jS')); ?></p>
<?php if (!empty($event->payment_note)): ?>
									<p><?php echo esc_html($event->payment_note); ?></p>
<?php endif; ?>
								</div>
							</div>
						</fieldset>


						<div id="submit"><input id="nsevent-submit" type="submit" value="<?php _e('Confirm&hellip;', 'nsevent'); ?>" /></div>
					</form>
				</div>

<?php if (!get_post_meta($post->ID, 'nsevent_registration_form', true)) { get_footer(); } ?>

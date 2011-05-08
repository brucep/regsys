<?php $dancers = $event->get_dancers(); ?>

<div class="wrap" id="nsevent"><div id="packet-printout">
	<h2 class="no-print"><?php echo $event->get_request_link('index-event', sprintf(__('Reports for %s', 'nsevent'), $event->get_name())); ?></h2>

	<h3 class="no-print" style="margin-bottom: 2em;"><?php _e('Packet Printouts', 'nsevent'); ?></h3>

<?php if ($dancers): ?>
<?php 	foreach ($dancers as $dancer): ?>

	<div class="dancer">
		<h4>
			<?php echo esc_html($dancer->get_name_last_first()), "\n"; ?>
<?php 		if (!$dancer->is_vip()): ?>
			<span class="paid-owed"><?php echo ($dancer->get_payment_confirmed()) ? __('Paid &#x2714;', 'nsevent') : sprintf(__('Owed: $%d', 'nsevent'), $dancer->get_payment_owed()); ?></span>
<?php 		else: ?>
			<span class="paid-owed"><?php _e('VIP', 'nsevent'); ?></span>
<?php 		endif; ?>
		</h4>

		<h5><?php _e('Personal Info', 'nsevent'); ?></h5>
		<ul>
			<li><?php echo __('Position:', 'nsevent'), ' ', esc_html($dancer->get_position()); ?></li>
<?php 		if ($event->has_levels()): ?>
			<li><?php echo __('Level:', 'nsevent'), ' ', esc_html($event->get_level_for_index($dancer->get_level())); ?></li>
<?php 		endif; ?>
<?php 		if ($dancer->is_volunteer()): ?>
			<li><?php _e('Volunteer', 'nsevent'); ?></li>
<?php 		endif; ?>
		</ul>

		<h5><?php _e('Registrations', 'nsevent'); ?></h5>
<?php 		if ($dancer->get_registered_items()): ?>
		<ul>
<?php 			foreach ($dancer->get_registered_items() as $item): ?>
			<li><?php echo esc_html($item->get_name()); if ($item->get_registered_meta()) printf(' (%s)', esc_html(ucfirst($item->get_registered_meta()))); ?></li>
<?php 			endforeach; ?>
		</ul>
<?php 		else: ?>
		<p><?php _e('There are no registrations for this dancer.', 'nsevent'); ?></p>
<?php 		endif; ?>
<?php 		if ($event->has_housing() and $dancer->needs_housing()): ?>
		<h5><?php echo esc_html($dancer->get_housing_type()); ?></h5>
		<ul>
<?php 			if ($dancer->get_housing_prefers_no_smoke()): ?>
			<li><?php _e('Prefers no smoking', 'nsevent'); ?></li>
<?php 			endif; ?>
<?php 			if ($dancer->get_housing_prefers_no_pets()): ?>
			<li><?php _e('Prefers no pets', 'nsevent'); ?></li>
<?php 			endif; ?>
			<li><?php echo esc_html($dancer->get_housing_gender()); ?></li>
			<li><?php echo esc_html($dancer->get_housing_nights($event->get_housing_nights())); ?></li>
		</ul>
<?php 		endif; ?>
	</div>
<?php 	endforeach; ?>
<?php endif; ?>
</div>
</div>

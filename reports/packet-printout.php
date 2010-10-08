<?php

$dancers = NSEvent_Dancer::find_all();

?>

<div class="wrap" id="nsevent"><div id="packet-printout">
	<h2 class="no-print"><?php $event->report_link('index-event', sprintf(__('Reports for %s', 'nsevent'), $event->name)); ?></h2>

	<h3 class="no-print" style="margin-bottom: 2em;"><?php _e('Packet Printouts', 'nsevent'); ?></h3>
<?php foreach ($dancers as $dancer): ?>

	<div class="dancer">
		<h4>
			<?php echo esc_html($dancer->name(True)), "\n"; ?>
<?php if (!$dancer->is_vip()): ?>
			<span class="paid-owed"><?php echo ($dancer->payment_confirmed) ? __('Paid &#x2714;', 'nsevent') : sprintf(__('Owed: $%d', 'nsevent'), $dancer->amount_owed); ?></span>
<?php else: ?>
			<span class="paid-owed"><?php _e('VIP', 'nsevent'); ?></span>
<?php endif; ?>
		</h4>
		
		<h5><?php _e('Personal Info', 'nsevent'); ?></h5>
		<ul>
			<li><?php echo __('Position:', 'nsevent'), ' ', esc_html($dancer->position()); ?></li>
<?php if ($event->levels()): ?>
			<li><?php echo __('Level:', 'nsevent'), ' ', esc_html($dancer->level()); ?></li>
<?php endif; ?>
<?php if ($dancer->is_volunteer()): ?>
			<li><?php _e('Volunteer', 'nsevent'); ?></li>
<?php endif; ?>
		</ul>
		
		<h5><?php _e('Registrations', 'nsevent'); ?></h5>
<?php if ($dancer->registrations()): ?>
		<ul>
<?php 	foreach($dancer->registrations() as $reg): ?>
			<li><?php echo esc_html($reg->item()->name); if ($reg->item_meta) printf(' (%s)', esc_html(ucfirst($reg->item_meta))); ?></li>
<?php 	endforeach; ?>
		</ul>
<?php else: ?>
	<p><?php _e('There are no registrations for this dancer.', 'nsevent'); ?></p>
<?php endif; ?>
<?php if ($event->has_housing and $dancer->populate_housing_info()): ?>

<?php if ($dancer->populate_housing_info() and !isset($dancer->available)): ?>
	<h5><?php echo esc_html($dancer->housing_type); ?></h5>
	<ul>
<?php 	if (isset($dancer->available)): ?>
		<li><?php printf('Available: %d', $dancer->available); ?></li>
<?php 		if ($dancer->smoking): ?>
		<li><?php _e('Smokes', 'nsevent'); ?></li>
<?php 		endif; ?>
<?php 		if ($dancer->pets): ?>
		<li><?php _e('Has pets', 'nsevent'); ?></li>
<?php 		endif; ?>
<?php 	else: ?>
<?php 		if ($dancer->car): ?>
		<li><?php _e('Has car', 'nsevent'); ?></li>
<?php 		endif; ?>
<?php 		if ($dancer->no_smoking): ?>
		<li><?php _e('Prefers no smoking', 'nsevent'); ?></li>
<?php 		endif; ?>
<?php 		if ($dancer->no_pets): ?>
		<li><?php _e('Prefers no pets', 'nsevent'); ?></li>
<?php 		endif; ?>
<?php 	endif; ?>
		<li><?php echo esc_html($dancer->housing_gender()); ?></li>
		<li><?php echo esc_html($dancer->housing_nights()); ?></li>
	</ul>
<?php endif; ?>
<?php endif; ?>
	</div>
<?php endforeach; ?>
</div></div>


<div class="wrap" id="nsevent">
	<h2><?php printf(__('Reports for %s', 'nsevent'), esc_html($event->get_name())); ?></h2>

	<h3><?php _e('Choose Report', 'nsevent'); ?></h3>

	<ul>
		<li><a href="<?php echo $event->get_request_href('numbers');           ?>"><strong><?php _e('Attendance&nbsp;/&nbsp;Numbers', 'nsevent'); ?></strong></a></li>
		<li><a href="<?php echo $event->get_request_href('competitions');      ?>"><strong><?php _e('Competitions',      'nsevent'); ?></strong></a></li>
		<li><a href="<?php echo $event->get_request_href('dancers');           ?>"><strong><?php _e('Dancers',           'nsevent'); ?></strong></a></li>
<?php if ($event->get_housing_nights()): ?>
		<li><a href="<?php echo $event->get_request_href('housing-needed');    ?>"><strong><?php _e('Housing Needed',    'nsevent'); ?></strong></a></li>
		<li><a href="<?php echo $event->get_request_href('housing-providers'); ?>"><strong><?php _e('Housing Providers', 'nsevent'); ?></strong></a></li>
<?php endif; ?>
		<li><a href="<?php echo $event->get_request_href('money');             ?>"><strong><?php _e('Money',             'nsevent'); ?></strong></a></li>
		<li><a href="<?php echo $event->get_request_href('packet-printout');   ?>"><strong><?php _e('Packet Printout',   'nsevent'); ?></strong></a></li>
		<li><a href="<?php echo $event->get_request_href('reg-list');          ?>"><strong><?php _e('Registration List', 'nsevent'); ?></strong></a></li>
<?php if ($event->has_vip()): ?>
		<li><a href="<?php echo $event->get_request_href('reg-list', array('vip-only' => 'true')); ?>"><strong><?php _e('VIP Payment Confirmation', 'nsevent'); ?></strong></a></li>
<?php endif; ?>
<?php if ($event->has_volunteers()): ?>
		<li><a href="<?php echo $event->get_request_href('volunteers'); ?>"><strong><?php _e('Volunteers', 'nsevent'); ?></strong></a></li>
<?php endif; ?>
	</ul>
</div>

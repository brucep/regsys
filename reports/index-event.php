
<div class="wrap" id="nsevent">
	<h2><?php printf(__('Reports for %s', 'nsevent'), esc_html($event->name)); ?></h2>

	<h3><?php _e('Choose Report', 'nsevent'); ?></h3>

	<ul>
		<li><a href="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=nsevent&amp;event_id=<?php echo $event->id; ?>&amp;request=numbers"><strong><?php _e('Attendance&nbsp;/&nbsp;Numbers', 'nsevent'); ?></strong></a></li>
		<li><a href="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=nsevent&amp;event_id=<?php echo $event->id; ?>&amp;request=competitions"><strong><?php _e('Competitions', 'nsevent'); ?></strong></a></li>
		<li><a href="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=nsevent&amp;event_id=<?php echo $event->id; ?>&amp;request=dancers"><strong><?php _e('Dancers', 'nsevent'); ?></strong></a></li>
<?php if ($event->has_housing): ?>
		<li><a href="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=nsevent&amp;event_id=<?php echo $event->id; ?>&amp;request=housing-needed"><strong><?php _e('Housing Needed', 'nsevent'); ?></strong></a></li>
		<li><a href="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=nsevent&amp;event_id=<?php echo $event->id; ?>&amp;request=housing-providers"><strong><?php _e('Housing Providers', 'nsevent'); ?></strong></a></li>
<?php endif; ?>
		<li><a href="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=nsevent&amp;event_id=<?php echo $event->id;?>&amp;request=money"><strong><?php _e('Money', 'nsevent'); ?></strong></a></li>
		<li><a href="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=nsevent&amp;event_id=<?php echo $event->id;?>&amp;request=packet-printout"><strong><?php _e('Packet Printout', 'nsevent'); ?></strong></a></li>
		<li><a href="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=nsevent&amp;event_id=<?php echo $event->id;?>&amp;request=reg-list"><strong><?php _e('Registration List', 'nsevent'); ?></strong></a></li>
<?php if ($event->has_vip): ?>
		<li><a href="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=nsevent&amp;event_id=<?php echo $event->id;?>&amp;request=reg-list&amp;vip-only"><strong><?php _e('VIP Payment Confirmation', 'nsevent'); ?></strong></a></li>
<?php endif; ?>
<?php if ($event->has_volunteers): ?>
		<li><a href="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=nsevent&amp;event_id=<?php echo $event->id; ?>&amp;request=volunteers"><strong><?php _e('Volunteers', 'nsevent'); ?></strong></a></li>
<?php endif; ?>
	</ul>
</div>


<?php $dancers = $event->get_dancers(); ?>

<div class="wrap" id="nsevent">
	<h2><?php echo $event->get_request_link('index-event', sprintf(__('Reports for %s', 'nsevent'), $event->get_name())); ?></h2>

	<h3>
		<?php _e('Dancers', 'nsevent'); echo "\n"; ?>
<?php if ($dancers): ?>
		<a href="<?php printf('%s/%s/reports/email-csv.php?event_id=%d&amp;request=dancers', WP_PLUGIN_URL, basename(dirname(dirname(__FILE__))), $event->get_id()); ?>" class="button add-new-h3"><?php _e('Download Email Addresses', 'nsevent'); ?></a>
<?php endif; ?>
	</h3>

<?php if ($dancers): ?>
	<table class="widefat page fixed report">
		<thead>
			<tr>
				<th class="manage-column column-title" width="19%"><div><?php _e('Name', 'nsevent'); ?></div></th>
				<th class="manage-column" width="27%"><div><?php _e('Email Address', 'nsevent'); ?></div></th>
				<th class="manage-column" width="13%"><div><?php _e('Mobile Phone Number', 'nsevent'); ?></div></th>
				<th class="manage-column" width="8%"><div><?php _e('Position', 'nsevent'); ?></div></th>
				<th class="manage-column" width="15%"><div><?php _e('Level', 'nsevent'); ?></div></th>
				<th class="manage-column" width="18%"><div><?php _e('Date Registered', 'nsevent'); ?></div></th>
			</tr>
		</thead>

		<tfoot>
			<tr>
				<th class="manage-column column-title"><?php _e('Name', 'nsevent'); ?></th>
				<th class="manage-column"><?php _e('Email Address', 'nsevent'); ?></th>
				<th class="manage-column"><?php _e('Mobile Phone Number', 'nsevent'); ?></th>
				<th class="manage-column"><?php _e('Position', 'nsevent'); ?></th>
				<th class="manage-column"><?php _e('Level', 'nsevent'); ?></th>
				<th class="manage-column"><?php _e('Date Registered', 'nsevent'); ?></th>
			</tr>
		</tfoot>

		<tbody>
<?php 	$i = 1; ?>
<?php 	foreach ($dancers as $dancer): ?>
			<tr class="vcard<?php if (!($i++ % 2)) echo ' alternate'; ?>">
				<td class="column-title dancer-name"><?php if (current_user_can('administrator')): echo $event->get_request_link('dancer', $dancer->get_name_last_first(), array('dancer' => (int) $dancer->get_id())); else: echo esc_html($dancer->get_name_last_first()); endif; ?><?php if ($dancer->is_vip()) echo ' [VIP]'; ?></td>
				<td><a href="mailto:<?php printf('%s <%s>?subject=%s', $dancer->get_name(), $dancer->get_email(), $event->get_name()); ?>"><span class="email"><?php echo esc_html($dancer->get_email()); ?></span></a></td>
				<td><?php echo esc_html($dancer->get_mobile_phone()); ?></td>
				<td><?php echo esc_html($dancer->get_position()); ?></td>
				<td><?php echo ($event->get_level_for_index($dancer->get_level())) ? esc_html($event->get_level_for_index($dancer->get_level())) : '&mdash;'; ?></td>
				<td style="font-family: monospace"><?php echo $dancer->get_date_registered('Y-m-d, h:i A'); ?></td>
			</tr>
<?php 	endforeach; ?>
			</tbody>
		</table>
<?php else: ?>
	<p><?php _e('There are no registered dancers for this event&hellip;', 'nsevent'); ?><p>
<?php endif; ?>
</div>

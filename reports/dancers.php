<?php

$dancers = NSEvent_Dancer::find_all();

?>

<div class="wrap" id="nsevent">
	<h2><?php $event->report_link('index-event', sprintf(__('Reports for %s', 'nsevent'), $event->name)); ?></h2>

	<h3>
		<?php _e('Dancers', 'nsevent'); echo "\n"; ?>
		<a href="<?php printf('%s/%s/reports/email-csv.php?event_id=%d&amp;request=dancers', WP_PLUGIN_URL, basename(dirname(dirname(__FILE__))), $event->id); ?>" class="button add-new-h3"><?php _e('Download Email Addresses', 'nsevent'); ?></a>
	</h3>

	<table class="widefat page fixed report">
		<thead>
			<tr>
				<th class="manage-column column-title" width="20%"><div><?php _e('Name', 'nsevent'); ?></div></th>
				<th class="manage-column" width="30%"><div><?php _e('Email Address', 'nsevent'); ?></div></th>
				<th class="manage-column" width="10%"><div><?php _e('Position', 'nsevent'); ?></div></th>
				<th class="manage-column" width="20%"><div><?php _e('Level', 'nsevent'); ?></div></th>
				<th class="manage-column" width="20%"><div><?php _e('Date Registered', 'nsevent'); ?></div></th>
			</tr>
		</thead>

		<tfoot>
			<tr>
				<th class="manage-column column-title"><?php _e('Name', 'nsevent'); ?></th>
				<th class="manage-column"><?php _e('Email Address', 'nsevent'); ?></th>
				<th class="manage-column"><?php _e('Position', 'nsevent'); ?></th>
				<th class="manage-column"><?php _e('Level', 'nsevent'); ?></th>
				<th class="manage-column"><?php _e('Date Registered', 'nsevent'); ?></th>
			</tr>
		</tfoot>

		<tbody>
<?php if ($dancers): $i = 1; foreach($dancers as $dancer): ?>
			<tr class="vcard<?php if (!($i % 2)) echo ' alternate'; ?>">
				<td class="column-title dancer-name"><?php if (!current_user_can('administrator')): echo esc_html($dancer->name(True)); else: $event->report_link('dancer', $dancer->name(True), $dancer->id); endif; ?><?php if ($dancer->is_vip()) echo ' [VIP]'; ?></td>
				<td><a href="mailto:<?php printf('%s <%s>?subject=%s', $dancer->name(), $dancer->email, $event->name); ?>"><span class="email"><?php echo esc_html($dancer->email); ?></span></a></td>
				<td><?php echo ($dancer->position()) ? esc_html($dancer->position()) : '&mdash;'; ?></td>
				<td><?php echo ($dancer->level()) ? esc_html($dancer->level()) : '&mdash;'; ?></td>
				<td style="font-family: monospace"><?php echo date('Y-m-d, h:i A', $dancer->date_registered); ?></td>
			</tr>
<?php $i++; endforeach; else: ?>
				<tr><td colspan="3"><?php _e('There are no registered dancers for this event&hellip;', 'nsevent'); ?></td></tr>
<?php endif; ?>
		</tbody>
	</table>
</div>

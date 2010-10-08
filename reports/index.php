<?php

$events = NSEvent_Event::find_all();
$options = get_option('nsevent');

?>

<div class="wrap" id="nsevent">
	<h2><?php _e('Events', 'nsevent'); ?><?php if (current_user_can('administrator')): ?><a href="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=nsevent&amp;request=event-edit" class="button add-new-h2"><?php _e('Add New Event', 'nsevent'); ?></a><?php endif; ?></h2>

	<h3><?php _e('Choose Event', 'nsevent'); ?></h3>

	<table class="widefat page fixed">
		<thead>
			<tr>
				<th class="manage-column column-title"><?php _e('Title'); ?></th>
				<th class="manage-column"><?php _e('Early Registration End Date'); ?></th>
				<th class="manage-column"><?php _e('Preregistration End Date', 'nsevent'); ?></th>
<?php if (current_user_can('administrator')): ?>
				<th class="manage-column" width="10%"><?php _e('Edit Event', 'nsevent'); ?></th>
<?php endif; ?>
			</tr>
		</thead>

		<tfoot>
			<tr>
				<th class="manage-column column-title" style=""><?php _e('Title', 'nsevent'); ?></th>
				<th class="manage-column" style=""><?php _e('Early Registration End Date', 'nsevent'); ?></th>
				<th class="manage-column" style=""><?php _e('Preregistration End Date', 'nsevent'); ?></th>
<?php if (current_user_can('administrator')): ?>
				<th class="manage-column"><?php _e('Edit Event', 'nsevent'); ?></th>
<?php endif; ?>
			</tr>
		</tfoot>

		<tbody>
<?php if ($events): $i = 1; foreach($events as $event): ?>
			<tr class="<?php if (!($i % 2)) echo 'alternate'; if (isset($options['current_event_id']) and $options['current_event_id'] == $event->id) echo ' current-event'; ?>">
				<td class="column-title"><strong><a class="row-title" href="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=nsevent&amp;event_id=<?php echo (int) $event->id ?>&amp;request=index-event"><?php echo esc_html($event->name); ?></a><strong></td>
				<td><?php echo ($event->early_end) ? date('Y-m-d', $event->early_end) : '&mdash;';?></td>
				<td><?php echo date('Y-m-d', $event->prereg_end); ?></td>
<?php if (current_user_can('administrator')): ?>
				<td class="manage-column"><?php $event->report_link('event-edit', __('Edit Event', 'nsevent'), $event->id); ?></td>
<?php endif; ?>
			</tr>
<?php $i++; endforeach; else: ?>
				<tr><td colspan="3"><?php _e('No events have been created yet&hellip;', 'nsevent'); ?></td></tr>
<?php endif; ?>
		</tbody>
	</table>
</div>

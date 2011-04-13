<?php

$events = NSEvent_Model_Event::get_events();
$options = get_option('nsevent');

?>

<div class="wrap" id="nsevent">
	<h2><?php _e('Events', 'nsevent'); ?><?php if (current_user_can('administrator')): ?><a href="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=nsevent&amp;event_id=add&amp;request=event-edit" class="button add-new-h2"><?php _e('Add New Event', 'nsevent'); ?></a><?php endif; ?></h2>

	<h3><?php _e('Choose Event', 'nsevent'); ?></h3>

<?php if ($events): ?>
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
<?php 	if (current_user_can('administrator')): ?>
				<th class="manage-column"><?php _e('Edit Event', 'nsevent'); ?></th>
<?php 	endif; ?>
			</tr>
		</tfoot>

		<tbody>
<?php 	$i = 1; ?>
<?php 	foreach ($events as $event): ?>
			<tr class="<?php if (!($i++ % 2)) echo 'alternate'; if (isset($options['current_event_id']) and $options['current_event_id'] == $event->get_id()) echo ' current-event'; ?>">
				<td class="column-title"><strong><a class="row-title" href="<?php echo $event->get_request_href('index-event'); ?>"><?php echo esc_html($event->get_name()); ?></a><strong></td>
				<td><?php echo ($event->get_date_early_end()) ? $event->get_date_early_end('Y-m-d') : '&mdash;';?></td>
				<td><?php echo $event->get_date_prereg_end('Y-m-d'); ?></td>
<?php 		if (current_user_can('administrator')): ?>
				<td class="manage-column"><?php echo $event->get_request_link('event-edit', __('Edit Event', 'nsevent')); ?></td>
<?php 		endif; ?>
			</tr>
<?php 	endforeach; ?>
		</tbody>
	</table>
<?php else: ?>
	<p><?php _e('No events have been created yet&hellip;', 'nsevent'); ?><p>
<?php endif; ?>
</div>

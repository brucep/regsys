<?php $items = $event->get_items_where(array(':type' => 'competition')); ?>

<div class="wrap" id="nsevent">
	<h2><?php echo $event->get_request_link('index-event', sprintf(__('Reports for %s', 'nsevent'), $event->get_name())); ?></h2>

	<h3>
		<?php _e('Competitions', 'nsevent'); echo "\n"; ?>
<?php if ($items): ?>
		<a href="<?php printf('%s/%s/reports/email-csv.php?event_id=%d&amp;request=competitors', WP_PLUGIN_URL, basename(dirname(dirname(__FILE__))), $event->get_id()); ?>" class="button add-new-h3"><?php _e('Download Email Addresses', 'nsevent'); ?></a>
<?php endif; ?>
	</h3>

<?php if ($items): ?>
<?php 	foreach ($items as $item): ?>
	<h4>
		<?php echo esc_html($item->get_name()), "\n"; ?>
<?php 		if ($item->get_meta() !== 'position'): ?>
		<?php printf(' (%d)'."\n", $event->count_registrations_where(array(':item_id' => $item->get_id()))); ?>
<?php		else: ?>
		<?php
		 		printf(' (%3$d %1$s, %4$d %2$s)'."\n",
					_n('lead', 'leads', $event->count_registrations_where(array(':item_id' => $item->get_id(), ':item_meta' => 'lead'), 'nsevent')),
					_n('follow', 'follows', $event->count_registrations_where(array(':item_id' => $item->get_id(), ':item_meta' => 'follow'), 'nsevent')),
					$event->count_registrations_where(array(':item_id' => $item->get_id(), ':item_meta' => 'lead')),
					$event->count_registrations_where(array(':item_id' => $item->get_id(), ':item_meta' => 'follow'))); ?>
<?php		endif; ?>
	</h4>
	<table class="widefat page fixed report">
		<thead>
			<tr>
				<th class="manage-column column-title"><div><?php _e('Name', 'nsevent'); ?></div></th>
<?php 		if ($item->get_meta() == 'position'): ?>
				<th class="manage-column"><div><?php _e('Position', 'nsevent'); ?></div></th>
<?php 		elseif ($item->get_meta() == 'partner_name'): ?>
				<th class="manage-column"><div><?php _e('Partner', 'nsevent'); ?></div></th>
<?php 		elseif ($item->get_meta() == 'team_members'): ?>
				<th class="manage-column"><div><?php _e('Team Members', 'nsevent'); ?></div></th>
<?php 		endif; ?>
			</tr>
		</thead>

		<tfoot>
			<tr>
				<th class="manage-column column-title"><?php _e('Name', 'nsevent'); ?></th>
<?php 		if ($item->get_meta() == 'position'): ?>
				<th class="manage-column"><?php _e('Position', 'nsevent'); ?></th>
<?php 		elseif ($item->get_meta() == 'partner_name'): ?>
				<th class="manage-column"><?php _e('Partner', 'nsevent'); ?></th>
<?php 		elseif ($item->get_meta() == 'team_members'): ?>
				<th class="manage-column"><?php _e('Team Members', 'nsevent'); ?></th>
<?php 		endif; ?>
			</tr>
		</tfoot>

		<tbody>
<?php 		$registered_dancers = $item->get_registered_dancers(); ?>
<?php 		if ($registered_dancers): $i = 1; ?>
<?php 			foreach ($registered_dancers as $registered_dancer): ?>
			<tr class="<?php if (!($i++ % 2)) echo ' alternate'; ?>">
				<td class="column-title"><strong><?php echo $event->get_request_link('dancer', $registered_dancer->get_name(), array('dancer' => $registered_dancer->get_id())); ?><strong></td>
<?php 				if (in_array($item->get_meta(), array('position', 'partner_name', 'team_members'))): ?>
				<td><?php echo esc_html(ucwords($registered_dancer->item_meta)); ?></td>
<?php 				endif; ?>			
			</tr>
<?php 			endforeach; ?>
<?php 		else: ?>
			<tr><td colspan="2"><?php _e('There are no registered dancers for this competition&hellip;', 'nsevent'); ?></td></tr>
<?php 		endif; ?>
		</tbody>
	</table>
<?php 	endforeach; ?>
<?php else: ?>
	<p><?php _e('There aren\'t any competitions for this event&hellip;', 'nsevent'); ?></p>
<?php endif; ?>
</div>

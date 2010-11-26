<?php

$items = NSEvent_Item::find_by(array(':type' => 'competition'));

?>

<div class="wrap" id="nsevent">
	<h2><?php $event->request_link('index-event', sprintf(__('Reports for %s', 'nsevent'), $event->name)); ?></h2>

	<h3>
		<?php _e('Competitions', 'nsevent'); echo "\n"; ?>
		<a href="<?php printf('%s/%s/reports/email-csv.php?event_id=%d&amp;request=competitors', WP_PLUGIN_URL, basename(dirname(dirname(__FILE__))), $event->id); ?>" class="button add-new-h3"><?php _e('Download Email Addresses', 'nsevent'); ?></a>
	</h3>

<?php if ($items): ?>
<?php 	foreach($items as $item): ?>
	<h4>
		<?php echo esc_html($item->name), "\n"; ?>
<?php 		if ($item->has_meta !== 'position'): ?>
		<?php printf(' (%d)'."\n", NSEvent_Registration::count_for_item($item->id)); ?>
<?php		else: ?>
		<?php
		 		printf(' (%3$d %1$s, %4$d %2$s)'."\n",
					_n('lead', 'leads', NSEvent_Registration::count_for_item($item->id, 'lead'), 'nsevent'),
					_n('follow', 'follows', NSEvent_Registration::count_for_item($item->id, 'follow'), 'nsevent'),
					NSEvent_Registration::count_for_item($item->id, 'lead'),
					NSEvent_Registration::count_for_item($item->id, 'follow')); ?>
<?php		endif; ?>
	</h4>
	<table class="widefat page fixed report">
		<thead>
			<tr>
				<th class="manage-column column-title"><div><?php _e('Name', 'nsevent'); ?></div></th>
<?php 		if ($item->has_meta == 'position'): ?>
				<th class="manage-column"><div><?php _e('Position', 'nsevent'); ?></div></th>
<?php 		elseif ($item->has_meta == 'partner_name'): ?>
				<th class="manage-column"><div><?php _e('Partner', 'nsevent'); ?></div></th>
<?php 		elseif ($item->has_meta == 'team_members'): ?>
				<th class="manage-column"><div><?php _e('Team Members', 'nsevent'); ?></div></th>
<?php 		endif; ?>
			</tr>
		</thead>

		<tfoot>
			<tr>
				<th class="manage-column column-title"><?php _e('Name', 'nsevent'); ?></th>
<?php 		if ($item->has_meta == 'position'): ?>
				<th class="manage-column"><?php _e('Position', 'nsevent'); ?></th>
<?php 		elseif ($item->has_meta == 'partner_name'): ?>
				<th class="manage-column"><?php _e('Partner', 'nsevent'); ?></th>
<?php 		elseif ($item->has_meta == 'team_members'): ?>
				<th class="manage-column"><?php _e('Team Members', 'nsevent'); ?></th>
<?php 		endif; ?>
			</tr>
		</tfoot>

		<tbody>
<?php 		$registered_dancers = $item->registered_dancers(); ?>
<?php 		if ($registered_dancers): $i = 1; foreach ($registered_dancers as $registered_dancer): ?>
			<tr class="<?php if (!($i % 2)) echo ' alternate'; ?>">
				<td class="column-title"><strong><?php $event->request_link('dancer', $registered_dancer->name(), array('dancer' => (int) $registered_dancer->id)); ?><strong></td>
<?php 			if (in_array($item->has_meta, array('position', 'partner_name', 'team_members'))): ?>
				<td><?php echo esc_html(ucwords($registered_dancer->item_meta)); ?></td>
<?php 			endif; ?>			
			</tr>
<?php 		$i++; endforeach; else: ?>
			<tr><td colspan="2"><?php _e('There are no registered dancers for this competition&hellip;', 'nsevent'); ?></td></tr>
<?php 		endif; ?>
		</tbody>
	</table>
<?php 	endforeach; ?>
<?php else: ?>
	<p><?php _e('There aren\'t any competitions for this event&hellip;', 'nsevent'); ?></p>
<?php endif; ?>
</div>

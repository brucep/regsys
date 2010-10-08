<?php

$dancers = NSEvent_Dancer::find_all();
$items = NSEvent_Item::find_all();

?>

<div class="wrap" id="nsevent">
	<h2><?php $event->report_link('index-event', sprintf(__('Reports for %s', 'nsevent'), $event->name)); ?></h2>

	<h3><?php _e('Money', 'nsevent'); ?></h3>

	<table class="widefat page fixed">
		<thead>
			<tr>
				<th class="manage-column column-title" width="18%"><?php _e('Name', 'nsevent'); ?></th>
<?php foreach($items as $item): ?>
				<th class="manage-column"><?php echo esc_html($item->name); ?></th>
<?php endforeach; ?>
				<th class="manage-column"><?php _e('Total', 'nsevent'); ?></th>
				<th class="manage-column">Method</th>
			</tr>
		</thead>

		<tfoot>
			<tr>
				<th class="manage-column column-title"><?php _e('Name', 'nsevent'); ?></th>
<?php foreach($items as $item): ?>
				<th class="manage-column"><?php echo esc_html($item->name); ?></th>
<?php endforeach; ?>
				<th class="manage-column"><?php _e('Total', 'nsevent'); ?></th>
				<th class="manage-column">Method</th>
			</tr>
		</tfoot>

		<tbody>
<?php if ($dancers): $i = 0; ?>
<?php 	foreach($dancers as $dancer): ?>
<?php 		if ($dancer->total_cost() === 0) continue; $i++; ?>
			<tr class="<?php if (!($i % 2)) echo ' alternate'; ?>">
				<!-- <a href="mailto:<?php echo rawurlencode(sprintf('%s <%s>', $dancer->name(), $dancer->email)); ?>"> -->
				<td class="column-title"><strong><?php $event->report_link('dancer', $dancer->name(True), $dancer->id); ?><strong></td>
<?php 		foreach($items as $item): ?>
				<td><?php if ($dancer->cost_for_registered_item($item->id)) printf('$%d', $dancer->cost_for_registered_item($item->id)); ?></td>
<?php 		endforeach; ?>
				<td><?php printf('$%d', $dancer->total_cost()); ?></td>
				<td><?php echo esc_html($dancer->payment_method); ?></td>
			</tr>
<?php endforeach; ?>
			<tr class="<?php if (!(($i + 1) % 2)) echo ' alternate'; ?>" style="background-color: rgba(0, 255, 0, 0.1);">
				<td class="column-title"><strong><?php _e('Total', 'nsevent'); ?><strong></td>
<?php 	foreach($items as $item): ?>
				<td><?php printf('$%d', $item->total_money_from_registrations()); ?></td>
<?php 	endforeach; ?>
				<td><?php printf('$%d', $event->total_money_from_registrations()); ?></td>
			</tr>
<?php else: ?>
			<tr><td colspan="<?php echo (count($items) + 2); ?>"><?php _e('There are no registered dancers for this event&hellip;', 'nsevent'); ?></td></tr>
<?php endif; ?>
		</tbody>
	</table>
</div>

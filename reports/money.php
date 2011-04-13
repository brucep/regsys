<?php

$dancers = $event->get_dancers();
$items   = $event->get_items();

?>

<div class="wrap" id="nsevent">
	<h2><?php echo $event->get_request_link('index-event', sprintf(__('Reports for %s', 'nsevent'), $event->get_name())); ?></h2>

	<h3><?php _e('Money', 'nsevent'); ?></h3>

<?php if ($dancers): ?>
	<table class="widefat page fixed">
		<thead>
			<tr>
				<th class="manage-column column-title" width="18%"><?php _e('Name', 'nsevent'); ?></th>
<?php 	foreach ($items as $item): ?>
				<th class="manage-column"><?php echo esc_html($item->get_name()); ?></th>
<?php 	endforeach; ?>
				<th class="manage-column"><?php _e('Total', 'nsevent'); ?></th>
				<th class="manage-column">Method</th>
			</tr>
		</thead>

		<tfoot>
			<tr>
				<th class="manage-column column-title"><?php _e('Name', 'nsevent'); ?></th>
<?php 	foreach ($items as $item): ?>
				<th class="manage-column"><?php echo esc_html($item->get_name()); ?></th>
<?php 	endforeach; ?>
				<th class="manage-column"><?php _e('Total', 'nsevent'); ?></th>
				<th class="manage-column">Method</th>
			</tr>
		</tfoot>

		<tbody>
<?php 	$i = 0; ?>
<?php 	foreach ($dancers as $dancer): ?>
<?php 		if ($dancer->get_price_total() === 0) continue; $i++; ?>
			<tr class="<?php if (!($i % 2)) echo ' alternate'; ?>">
				<td class="column-title"><strong><?php echo $event->get_request_link('dancer', $dancer->get_name_last_first(), array('dancer' => (int) $dancer->get_id())); ?><strong></td>
<?php 		foreach ($items as $item): ?>
				<td><?php if ($dancer->get_price_for_registered_item($item->get_id())) printf('$%d', $dancer->get_price_for_registered_item($item->get_id())); ?></td>
<?php 		endforeach; ?>
				<td><?php printf('$%d', $dancer->get_price_total()); ?></td>
				<td><?php echo esc_html($dancer->get_payment_method()); ?></td>
			</tr>
<?php 	endforeach; ?>
			<tr class="<?php if (!(($i + 1) % 2)) echo ' alternate'; ?>" style="background-color: rgba(0, 255, 0, 0.1);">
				<td class="column-title"><strong><?php _e('Total', 'nsevent'); ?><strong></td>
<?php 	foreach ($items as $item): ?>
				<td><?php printf('$%d', $item->get_total_money_from_registrations()); ?></td>
<?php 	endforeach; ?>
				<td><?php printf('$%d', $event->get_total_money_from_registrations()); ?></td>
				<td>&mdash;</td>
			</tr>
		</tbody>
	</table>
<?php else: ?>
	<p><?php _e('There are no registered dancers for this event&hellip;', 'nsevent'); ?></p>
<?php endif; ?>
</div>

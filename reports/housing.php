<?php

if ($_GET['request'] === 'housing-needed')
{
	$dancers_housing = NSEvent_Dancer::get_housing_needed();
	$housing_count = NSEvent_Dancer::count_housing_needed();
	$housing_title = __('Housing Needed', 'nsevent');
}
elseif ($_GET['request'] === 'housing-providers')
{
	$dancers_housing = NSEvent_Dancer::get_housing_providers();
	$housing_count = NSEvent_Dancer::count_housing_available();
	$housing_title = __('Housing Providers', 'nsevent');
}
else
	throw new Exception(__('Cheatin&#8217; uh?'));

?>

<div class="wrap" id="nsevent">
	<h2><?php $event->request_link('index-event', sprintf(__('Reports for %s', 'nsevent'), $event->name)); ?></h2>

	<h3>
		<?php printf('%s (%d)', $housing_title, $housing_count); echo "\n"; ?>
		<a href="<?php printf('%s/%s/reports/housing-csv.php?event_id=%d&amp;request=%s', WP_PLUGIN_URL, basename(dirname(dirname(__FILE__))), $event->id, $_GET['request']); ?>" class="button add-new-h3"><?php _e('Download Housing Info', 'nsevent'); ?></a>
<?php if ($_GET['request'] === 'housing-needed'): ?>
		<a href="<?php printf('%s/%s/reports/housing-csv.php?event_id=%d&amp;request=%s', WP_PLUGIN_URL, basename(dirname(dirname(__FILE__))), $event->id, 'housing-needed-email'); ?>" class="button add-new-h3"><?php _e('Download Email Addresses', 'nsevent'); ?></a>
<?php endif; ?>
	</h3>

<?php if ($dancers_housing): ?>
	<table class="widefat page fixed report">
		<thead>
			<tr>
				<th class="manage-column column-title" width="20%"><div><?php _e('Name', 'nsevent'); ?></div></th>
<?php 	foreach ($event->nights() as $night): ?>
				<th class="manage-column"><div><?php echo esc_html($night); ?></div></th>
<?php 	endforeach; ?>
				<th class="manage-column" width="10%"><div><?php _e('Gender', 'nsevent'); ?></div></th>
<?php 	if ($_GET['request'] == 'housing-needed'): ?>
				<th class="manage-column" width="5%"><div><?php _e('Has Car', 'nsevent'); ?></div></th>
				<th class="manage-column" width="6%"><div><?php _e('No Pets', 'nsevent'); ?></div></th>
				<th class="manage-column" width="9%"><div><?php _e('No Smoking', 'nsevent'); ?></div></th>
<?php 	else: ?>
				<th class="manage-column" width="9%"><div><?php _e('Available', 'nsevent'); ?></div></th>
				<th class="manage-column" width="6%"><div><?php _e('Has Pets', 'nsevent'); ?></div></th>
				<th class="manage-column" width="8%"><div><?php _e('Smokes', 'nsevent'); ?></div></th>
<?php 	endif; ?>
				<th class="manage-column" width="20%"><div><?php _e('Comment', 'nsevent'); ?></div></th>
				<th class="manage-column" width="20%"><div><?php _e('Date Registered', 'nsevent'); ?></div></th>
			</tr>
		</thead>

		<tfoot>
			<tr>
				<th class="manage-column column-title"><?php _e('Name', 'nsevent'); ?></th>
<?php 	foreach ($event->nights() as $night): ?>
				<th class="manage-column"><?php echo esc_html($night); ?></th>
<?php 	endforeach; ?>
				<th class="manage-column"><?php _e('Gender', 'nsevent'); ?></th>
<?php 	if ($_GET['request'] == 'housing-needed'): ?>
				<th class="manage-column"><?php _e('Has Car', 'nsevent'); ?></th>
				<th class="manage-column"><?php _e('No Pets', 'nsevent'); ?></th>
				<th class="manage-column"><?php _e('No Smoking', 'nsevent'); ?></th>
<?php 	else: ?>
				<th class="manage-column"><?php _e('Available', 'nsevent'); ?></th>
				<th class="manage-column"><?php _e('Has Pets', 'nsevent'); ?></th>
				<th class="manage-column"><?php _e('Smokes', 'nsevent'); ?></th>
<?php 	endif; ?>
				<th class="manage-column"><?php _e('Comment', 'nsevent'); ?></th>
				<th class="manage-column"><?php _e('Date Registered', 'nsevent'); ?></th>
			</tr>
		</tfoot>

		<tbody>
<?php 	$i = 1; ?>
<?php 	foreach ($dancers_housing as $dancer): ?>
			<tr class="<?php if (!($i % 2)) echo 'alternate'; ?>">
				<td class="column-title dancer-name"><a href="mailto:<?php echo rawurlencode(sprintf('%s <%s>', $dancer->name(), $dancer->email)); ?>"><?php echo esc_html($dancer->name(True)); ?></a><?php if ($dancer->is_vip()) echo ' [VIP]'; ?></td>
<?php 		foreach ($event->nights() as $night): ?>
				<td><?php echo ($dancer->housing_check_night($night)) ? '&bull;' : ''; ?></td>
<?php 		endforeach; ?>
				<td><?php echo esc_html($dancer->housing_gender()); ?></td>
<?php 		if ($_GET['request'] == 'housing-needed'): ?>
				<td><?php echo ($dancer->car)        ? '&bull;' : ''; ?></td>
				<td><?php echo ($dancer->no_pets)    ? '&bull;' : ''; ?></td>
				<td><?php echo ($dancer->no_smoking) ? '&bull;' : ''; ?></td>
<?php 		else: ?>
				<td><?php echo (int) $dancer->available; ?></td>
				<td><?php echo ($dancer->pets)    ? '&bull;' : ''; ?></td>
				<td><?php echo ($dancer->smoking) ? '&bull;' : ''; ?></td>
<?php 		endif; ?>
				<td><?php echo esc_html($dancer->comment); ?></td>
				<td style="font-family: monospace"><?php echo date('Y-m-d, h:i A', $dancer->date_registered); ?></td>
			</tr>
<?php 	endforeach; ?>
		</tbody>
	</table>
<?php else: ?>
	<p><?php _e('There are no housing entries for this event&hellip;', 'nsevent'); ?></p>
<?php endif; ?>
</div>

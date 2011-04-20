<?php

if ($_GET['request'] === 'housing-needed') {
	$dancers = $event->get_dancers_needing_housing();
	$housing_count = count($dancers);
	$housing_type = __('Housing Needed', 'nsevent');
}
elseif ($_GET['request'] === 'housing-providers') {
	$dancers = $event->get_dancers_providing_housing();
	$housing_count = count($dancers);
	$housing_type = __('Housing Providers', 'nsevent');
}
else {
	throw new Exception(__('Cheatin&#8217; uh?'));
}

?>

<div class="wrap" id="nsevent">
	<h2><?php echo $event->get_request_link('index-event', sprintf(__('Reports for %s', 'nsevent'), $event->get_name())); ?></h2>

	<h3>
		<?php echo $housing_type; if ($housing_count) printf(' (%d)', $housing_count); echo "\n"; ?>
<?php if ($dancers): ?>
		<a href="<?php printf('%s/%s/reports/housing-csv.php?event_id=%d&amp;request=%s', WP_PLUGIN_URL, basename(dirname(dirname(__FILE__))), $event->get_id(), $_GET['request']); ?>" class="button add-new-h3"><?php _e('Download Housing Info', 'nsevent'); ?></a>
<?php endif; ?>
	</h3>

<?php if ($dancers): ?>
	<table class="widefat page fixed report">
		<thead>
			<tr>
				<th class="manage-column column-title" width="20%"><div><?php _e('Name', 'nsevent'); ?></div></th>
<?php 	foreach ($event->get_housing_nights() as $night): ?>
				<th class="manage-column"><div><?php echo esc_html($night); ?></div></th>
<?php 	endforeach; ?>
				<th class="manage-column" width="10%"><div><?php _e('Gender', 'nsevent'); ?></div></th>
<?php 	if ($_GET['request'] == 'housing-needed'): ?>
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
<?php 	foreach ($event->get_housing_nights() as $night): ?>
				<th class="manage-column"><?php echo esc_html($night); ?></th>
<?php 	endforeach; ?>
				<th class="manage-column"><?php _e('Gender', 'nsevent'); ?></th>
<?php 	if ($_GET['request'] == 'housing-needed'): ?>
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
<?php 	foreach ($dancers as $dancer): ?>
			<tr class="<?php if (!($i % 2)) echo 'alternate'; ?>">
				<td class="column-title dancer-name"><a href="mailto:<?php echo rawurlencode(sprintf('%s <%s>', $dancer->get_name(), $dancer->get_email())); ?>"><?php echo esc_html($dancer->get_name_last_first()); ?></a><?php if ($dancer->is_vip()) echo ' [VIP]'; ?></td>
<?php 		foreach ($event->get_housing_nights() as $night): ?>
				<td><?php echo ($dancer->get_housing_for_night_by_index($night)) ? '&bull;' : ''; ?></td>
<?php 		endforeach; ?>
				<td><?php echo esc_html($dancer->get_housing_gender()); ?></td>
<?php 		if ($_GET['request'] == 'housing-needed'): ?>
				<td><?php echo ($dancer->no_pets)    ? '&bull;' : ''; ?></td>
				<td><?php echo ($dancer->no_smoking) ? '&bull;' : ''; ?></td>
<?php 		else: ?>
				<td><?php echo (int) $dancer->available; ?></td>
				<td><?php echo ($dancer->pets)    ? '&bull;' : ''; ?></td>
				<td><?php echo ($dancer->smoking) ? '&bull;' : ''; ?></td>
<?php 		endif; ?>
				<td><?php echo esc_html($dancer->comment); ?></td>
				<td style="font-family: monospace"><?php echo $dancer->get_date_registered('Y-m-d, h:i A'); ?></td>
			</tr>
<?php 	endforeach; ?>
		</tbody>
	</table>
<?php else: ?>
	<p><?php _e('There are no housing entries for this event&hellip;', 'nsevent'); ?></p>
<?php endif; ?>
</div>

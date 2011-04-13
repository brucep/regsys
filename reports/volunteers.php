<?php $volunteers = $event->get_dancers_where(array(':status' => 1)); ?>

<div class="wrap" id="nsevent">
	<h2><?php echo $event->get_request_link('index-event', sprintf(__('Reports for %s', 'nsevent'), $event->get_name())); ?></h2>

	<h3>
		<?php _e('Volunteers', 'nsevent'); if (count($volunteers)) printf(' (%d)', count($volunteers)); echo "\n"; ?>
<?php if ($volunteers): ?>
		<a href="<?php printf('%s/%s/reports/email-csv.php?event_id=%d&amp;request=volunteers', WP_PLUGIN_URL, basename(dirname(dirname(__FILE__))), $event->get_id()); ?>" class="button add-new-h3"><?php _e('Download Email Addresses', 'nsevent'); ?></a>
<?php endif; ?>
	</h3>

<?php if ($volunteers): ?>
	<table class="widefat page fixed report">
		<thead>
			<tr>
				<th class="manage-column column-title"><div><?php _e('Name', 'nsevent'); ?></div></th>
				<th class="manage-column"><div><?php _e('Phone Number', 'nsevent'); ?></div></th>
			</tr>
		</thead>

		<tfoot>
			<tr>
				<th class="manage-column column-title"><div><?php _e('Name', 'nsevent'); ?></div></th>
				<th class="manage-column"><div><?php _e('Phone Number', 'nsevent'); ?></div></th>
			</tr>
		</tfoot>

		<tbody>
<?php 	foreach($volunteers as $dancer): ?>
			<tr class="vcard">
				<td class="dancer-name fn"><?php echo esc_html($dancer->get_name_last_first()); ?></td>
				<td class="tel"><?php echo esc_html($dancer->get_volunteer_phone()); ?></td>
			</tr>
<?php 	endforeach; ?>
		</tbody>
	</table>
<?php else: ?>
	<p><?php _e('There are no volunteers for this event&hellip;', 'nsevent'); ?></p>
<?php endif; ?>
</div>

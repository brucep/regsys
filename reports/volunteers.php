<?php

$volunteers = $event->volunteers();

$number_volunteers = $this->database->query('SELECT COUNT(id) FROM %1$s_dancers WHERE event_id = :event_id and status = 1', array(':event_id' => $event->id))->fetchColumn();

?>

<div class="wrap" id="nsevent">
	<h2><?php $event->request_link('index-event', sprintf(__('Reports for %s', 'nsevent'), $event->name)); ?></h2>

	<h3>
		<?php _e('Volunteers', 'nsevent'); if ($number_volunteers) printf(' (%d)', $number_volunteers); echo "\n"; ?>
		<a href="<?php printf('%s/%s/reports/email-csv.php?event_id=%d&amp;request=volunteers', WP_PLUGIN_URL, basename(dirname(dirname(__FILE__))), $event->id); ?>" class="button add-new-h3"><?php _e('Download Email Addresses', 'nsevent'); ?></a>
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
				<td class="dancer-name fn"><?php echo esc_html($dancer->name(True)); ?></td>
				<td class="tel"><?php echo esc_html($dancer->note); ?></td>
			</tr>
<?php 	endforeach; ?>
		</tbody>
	</table>
<?php else: ?>
	<p><?php _e('There are no volunteers for this event&hellip;', 'nsevent'); ?></p>
<?php endif; ?>
</div>

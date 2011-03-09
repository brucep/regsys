<?php

require dirname(dirname(__FILE__)).'/includes/form-input.php';

if (!isset($_GET['vip-only'])) {
	$dancers = NSEvent_Dancer::find_all();
}
else {
	$dancers = NSEvent_Dancer::find_by('status', 2);
}

$database = self::get_database_connection();
$item_ids = array('package' => null, 'competition' => null, 'shirt' => null);
foreach ($item_ids as $key => &$value)
	$value = $database->query('SELECT id FROM %1$s_items WHERE event_id = :event_id AND type = :type', array(':event_id' => $event->id, ':type' => $key))->fetchAll(PDO::FETCH_COLUMN, 0);
unset($key, $value);

if (!empty($_POST)) {
	foreach ($dancers as $dancer)
	{
		if ((!isset($_GET['vip-only']) and $dancer->is_vip())
			or (!isset($_POST['amount_owed'][$dancer->id])))
			continue;
		
		$dancer->update_payment_confirmation(
			(int) isset($_POST['payment_confirmed'][$dancer->id]),
			(int) $_POST['amount_owed'][$dancer->id]);
	}
}

?>

<div class="wrap" id="nsevent"><div id="reg-list">
	<h2><?php $event->request_link('index-event', sprintf(__('Reports for %s', 'nsevent'), $event->name)); ?></h2>

	<form action="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=nsevent&amp;event_id=<?php echo $event->id; ?>&amp;request=reg-list<?php if (isset($_GET['vip-only'])) echo '&amp;vip-only'; ?>" method="post">
	<input type="submit" value="<?php _e('Save Payment Info', 'nsevent'); ?>" class="no-print" style="float: right; margin: 0 0 1em;" />
	<h3><?php _e('Registration List', 'nsevent'); echo "\n"; ?></h3>

	<table class="widefat page fixed report" id="reg-list-table">
		<thead>
			<tr>
				<th class="manage-column column-title" width="20%"><div><?php _e('Name', 'nsevent'); ?></div></th>
				<th class="manage-column"><div><?php _e('Package', 'nsevent'); ?></div></th>
				<th class="manage-column"><div><?php _e('Competitions', 'nsevent'); ?></div></th>
				<th class="manage-column"><div><?php _e('Shirts', 'nsevent'); ?></div></th>
				<th class="manage-column" width="12%"><div><?php _e('Level', 'nsevent'); ?></div></th>
				<th class="manage-column total-cost" width="6%"><div><?php _e('Total', 'nsevent'); ?></div></th>
				<th class="manage-column paid" width="6%"><div><?php _e('Paid?', 'nsevent'); ?></div></th>
				<th class="manage-column owed" width="9%"><div><?php _e('Owed', 'nsevent'); ?></div></th>
				<th class="manage-column no-print" width="8%"><div><?php _e('Method', 'nsevent'); ?></div></th>
			</tr>
		</thead>

		<tfoot>
			<tr>
				<th class="manage-column column-title"><?php _e('Name', 'nsevent'); ?></th>
				<th class="manage-column"><?php _e('Package', 'nsevent'); ?></th>
				<th class="manage-column"><?php _e('Competitions', 'nsevent'); ?></th>
				<th class="manage-column"><?php _e('Shirts', 'nsevent'); ?></th>
				<th class="manage-column"><?php _e('Level', 'nsevent'); ?></th>
				<th class="manage-column total-cost"><?php _e('Total Cost', 'nsevent'); ?></th>
				<th class="manage-column paid"><?php _e('Paid?', 'nsevent'); ?></th>
				<th class="manage-column owed"><?php _e('Owed', 'nsevent'); ?></th>
				<th class="manage-column no-print"><?php _e('Method', 'nsevent'); ?></th>
			</tr>
		</tfoot>

		<tbody>
<?php if ($dancers): $i = 1; foreach($dancers as $dancer): if (!isset($_GET['vip-only']) and $dancer->is_vip()) continue; ?>
			<tr<?php if (!($i % 2)) echo ' class="alternate"'; ?>>
				<td class="column-title dancer-name"><?php if (current_user_can('administrator')): $event->request_link('dancer', $dancer->name(True), array('dancer' => (int) $dancer->id)); else: echo esc_html($dancer->name(True)); endif; ?><?php if ($dancer->is_vip()) echo ' [VIP]'; ?></td>
<?php
 		foreach ($item_ids as $type => $ids):
			foreach ($dancer->registrations($ids) as $reg)
				$item_names[] = ($type != 'shirt') ? esc_html($reg->item()->name) : esc_html(sprintf('%s (%s)', $reg->item()->name, ucfirst($reg->item_meta)));
?>
				<td><?php if (isset($item_names)) echo implode('<br />', $item_names); ?></td>
<?php 	
			unset($item_names);
		endforeach;
?>
				<td><?php echo ($dancer->level()) ? esc_html($dancer->level()) : '&mdash;'; ?></td>
				<td class="total-cost"><?php printf('$%d', $dancer->total_cost()); ?></td>
				<td class="paid"><?php NSEvent_FormInput::checkbox(sprintf('payment_confirmed[%d]', $dancer->id), array('value' => 1, 'checked' => (bool) $dancer->payment_confirmed)); ?></td>
				<td class="owed"><?php NSEvent_FormInput::text(sprintf('amount_owed[%d]', $dancer->id), array('value' => (int) $dancer->amount_owed, 'size' => 3)); ?></td>
				<td class="no-print"><?php echo $dancer->payment_method; ?></td>
			</tr>

<?php $i++; endforeach; else: ?>
			<tr><td colspan="3"><?php _e('There are no registered dancers for this event&hellip;', 'nsevent'); ?></td></tr>
<?php endif; ?>
		</tbody>
	</table>
	</form>
</div></div>

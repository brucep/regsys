<?php

require dirname(dirname(__FILE__)).'/includes/form-input.php';

if (!isset($_GET['vip-only'])) {
	$dancers = $event->get_dancers();
}
else {
	$dancers = $event->get_dancers_where(array(':status' => 2));
}

$database = self::get_database_connection();
$item_ids = array('package' => null, 'competition' => null, 'shirt' => null);
foreach ($item_ids as $key => &$value) {
	$value = $database->query('SELECT id FROM %1$s_items WHERE event_id = :event_id AND type = :type', array(':event_id' => $event->get_id(), ':type' => $key))->fetchAll(PDO::FETCH_COLUMN, 0);
}
unset($key, $value);

if (!empty($_POST)) {
	foreach ($dancers as $dancer) {
		if ((!isset($_GET['vip-only']) and $dancer->is_vip()) or (!isset($_POST['payment_owed'][$dancer->get_id()]))) {
			continue;
		}
		
		$dancer->update_payment_confirmation(
			(int) isset($_POST['payment_confirmed'][$dancer->get_id()]),
			(int) $_POST['payment_owed'][$dancer->get_id()]);
	}
}

?>

<div class="wrap" id="nsevent"><div id="reg-list">
	<h2><?php echo $event->get_request_link('index-event', sprintf(__('Reports for %s', 'nsevent'), $event->get_name())); ?></h2>

	<form action="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=nsevent&amp;event_id=<?php echo $event->get_id(); ?>&amp;request=reg-list<?php if (isset($_GET['vip-only'])) echo '&amp;vip-only'; ?>" method="post">
<?php if ($dancers): ?>
		<input type="submit" value="<?php _e('Save Payment Info', 'nsevent'); ?>" class="no-print" style="float: right; margin: 0 0 1em;" />
<?php endif; ?>
		<h3><?php echo !isset($_GET['vip-only'])? _e('Registration List', 'nsevent') : _e('VIP Payment Confirmation', 'nsevent'); echo "\n"; ?></h3>

<?php if ($dancers): ?>
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
<?php 	$i = 1; ?>
<?php 	foreach ($dancers as $dancer): if (!isset($_GET['vip-only']) and $dancer->is_vip()) continue; ?>
			<tr<?php if (!($i % 2)) echo ' class="alternate"'; ?>>
				<td class="column-title dancer-name"><?php if (current_user_can('administrator')): echo $event->get_request_link('dancer', $dancer->get_name_last_first(), array('dancer' => (int) $dancer->get_id())); else: echo esc_html($dancer->get_name_last_first()); endif; ?><?php if ($dancer->is_vip()) echo ' [VIP]'; ?></td>
<?php
 			foreach ($item_ids as $type => $ids):
				foreach ($dancer->get_registered_items($ids) as $item)
					$item_names[] = ($type != 'shirt') ? esc_html($item->get_name()) : esc_html(sprintf('%s (%s)', $item->get_name(), ucfirst($item->get_registered_meta())));
?>
				<td><?php if (isset($item_names)) echo implode('<br />', $item_names); ?></td>
<?php 	
				unset($item_names);
			endforeach;
?>
				<td><?php echo esc_html($event->get_level_for_index($dancer->get_level(), '&mdash;')); ?></td>
				<td class="total-cost"><?php printf('$%d', $dancer->get_price_total()); ?></td>
				<td class="paid"><?php NSEvent_FormInput::checkbox(sprintf('payment_confirmed[%d]', $dancer->get_id()), array('value' => 1, 'checked' => (bool) $dancer->get_payment_confirmed())); ?></td>
				<td class="owed"><?php NSEvent_FormInput::text(sprintf('payment_owed[%d]', $dancer->get_id()), array('value' => (int) $dancer->get_payment_owed(), 'size' => 3)); ?></td>
				<td class="no-print"><?php echo $dancer->get_payment_method(); ?></td>
			</tr>
<?php 		$i++; ?>
<?php 	endforeach; ?>
		</tbody>
	</table>
<?php else: ?>
<?php 	if (!isset($_GET['vip-only'])): ?>
		<p><?php _e('There are no registered dancers for this event&hellip;', 'nsevent'); ?></p>
<?php 	else: ?>
		<p><?php _e('There are no registered VIPs for this event&hellip;', 'nsevent'); ?></p>
<?php 	endif; ?>
<?php endif; ?>
	</form>
</div>
</div>

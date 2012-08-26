<?php

function regsys_report_numbers($event)
{
	$database = RegistrationSystem::get_database_connection();
	
	# Dancers
	$lists['Dancers']['Total']   = sprintf('%d [%d Mail; %d PayPal]', $event->count_dancers(), $event->count_dancers(array(':payment_method' => 'Mail')), $event->count_dancers(array(':payment_method' => 'PayPal')));
	$lists['Dancers']['Leads']   = $event->count_dancers(array(':position' => 1));
	$lists['Dancers']['Follows'] = $event->count_dancers(array(':position' => 2));
	$lists['Dancers']['Ratio']   = @round($lists['Dancers']['Follows'] / $lists['Dancers']['Leads'], 2);
	
	# Levels
	if ($event->has_levels()) {
		foreach ($event->levels() as $level) {
			$lists['Levels (All Dancers)'][$level->label] = $event->count_dancers(array(':level_id' => $level->level_id));
			
			$lists['Levels (Dancers in Classes)'][$level->label] = sprintf('%d leads, %d follows',
				$database->query('SELECT COUNT(dancer_id) FROM %1$s_registrations LEFT JOIN %1$s_dancers USING(dancer_id) LEFT JOIN %1$s_items USING(item_id) WHERE %1$s_registrations.`event_id` = ? AND %1$s_dancers.`level_id` = ? AND %1$s_dancers.`position` = ? AND %1$s_items.`meta` = "count_for_classes"', array($event->id(), $level->level_id, 1))->fetchColumn(),
				$database->query('SELECT COUNT(dancer_id) FROM %1$s_registrations LEFT JOIN %1$s_dancers USING(dancer_id) LEFT JOIN %1$s_items USING(item_id) WHERE %1$s_registrations.`event_id` = ? AND %1$s_dancers.`level_id` = ? AND %1$s_dancers.`position` = ? AND %1$s_items.`meta` = "count_for_classes"', array($event->id(), $level->level_id, 2))->fetchColumn());
		}
		
		$lists['Levels (All Dancers)'] = array_filter($lists['Levels (All Dancers)']);
	}
	
	# Packages and Competitions
	$tiered_packages    = $database->query('SELECT * FROM %1$s_items WHERE item_id IN     (SELECT DISTINCT item_id FROM %1$s_item_prices WHERE event_id = ?)', array($event->id()))->fetchAll(PDO::FETCH_CLASS, 'RegistrationSystem_Model_Item');
	$packages_and_comps = $database->query('SELECT * FROM %1$s_items WHERE item_id NOT IN (SELECT DISTINCT item_id FROM %1$s_item_prices WHERE event_id = :event_id) AND event_id = :event_id AND type != "shirt"', array(':event_id' => $event->id()))->fetchAll(PDO::FETCH_CLASS, 'RegistrationSystem_Model_Item');
	
	# Shirts
	$shirts = $event->items_where(array(':type' => 'shirt'));
	$sizes = array();
	foreach ($shirts as &$item) {
		$sizes = array_merge($sizes, $item->sizes());
	}
	$sizes = array_unique($sizes);
	
	
	echo RegistrationSystem::render_template('reports/numbers.html', array(
		'event'  => $event,
		'lists'  => $lists,
		'shirts' => $shirts,
		'sizes'  => $sizes,
		'tiered_packages'    => $tiered_packages,
		'packages_and_comps' => $packages_and_comps));
}

<?php

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
			$database->fetchColumn('SELECT COUNT(dancer_id) FROM regsys_registrations AS r LEFT JOIN regsys_dancers AS d USING(dancer_id) LEFT JOIN regsys_items AS i USING(item_id) WHERE r.event_id = ? AND d.level_id = ? AND d.position = ? AND i.meta = "count_for_classes"', array($event->id(), $level->level_id, 1)),
			$database->fetchColumn('SELECT COUNT(dancer_id) FROM regsys_registrations AS r LEFT JOIN regsys_dancers AS d USING(dancer_id) LEFT JOIN regsys_items AS i USING(item_id) WHERE r.event_id = ? AND d.level_id = ? AND d.position = ? AND i.meta = "count_for_classes"', array($event->id(), $level->level_id, 2)));
	}
	
	$lists['Levels (All Dancers)'] = array_filter($lists['Levels (All Dancers)']);
}

# Packages and Competitions
$tiered_packages    = $database->fetchAll('SELECT * FROM regsys_items WHERE item_id IN     (SELECT DISTINCT item_id FROM regsys_item_prices WHERE event_id = ?)', array($event->id()), 'RegistrationSystem_Model_Item');
$packages_and_comps = $database->fetchAll('SELECT * FROM regsys_items WHERE item_id NOT IN (SELECT DISTINCT item_id FROM regsys_item_prices WHERE event_id = :event_id) AND event_id = :event_id AND type != "shirt"', array(':event_id' => $event->id()), 'RegistrationSystem_Model_Item');

# Shirts
$shirts = self::$database->fetchAll('SELECT * FROM regsys_items WHERE event_id = ? AND type = ? ORDER BY item_id ASC', array($event->id(), 'shirt'), 'RegistrationSystem_Model_Item');
$sizes = array();
foreach ($shirts as &$item) {
	$sizes = array_merge($sizes, $item->sizes());
}
$sizes = array_unique($sizes);


echo self::render_template('report-numbers.html', array(
	'event'  => $event,
	'lists'  => $lists,
	'shirts' => $shirts,
	'sizes'  => $sizes,
	'tiered_packages'    => $tiered_packages,
	'packages_and_comps' => $packages_and_comps));

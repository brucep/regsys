<?php

function regsys_report_numbers($event)
{
	$database = RegistrationSystem::get_database_connection();
	
	# Dancers
	$lists['Dancers']['Total']   = $event->count_dancers();
	$lists['Dancers']['Leads']   = $event->count_dancers(array(':position' => 1));
	$lists['Dancers']['Follows'] = $event->count_dancers(array(':position' => 2));
	$lists['Dancers']['Ratio']   = @round($lists['Dancers']['Follows'] / $lists['Dancers']['Leads'], 2);
	
	if ($event->has_discounts()) {
		foreach ($event->discounts() as $d) {
			$lists['Discounts'][$d->discount_code] = $event->count_discounts_used($d->discount_code);
			
			if ($d->discount_limit) {
				$lists['Discounts'][$d->discount_code] .= ' of ' . $d->discount_limit;
			}
		}
	}
	
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
	
	# Packages
	$lists['Packages'] = array();
	$packages = $event->items_where(array(':preregistration' => 1, ':type' => 'package'));
	foreach ($packages as $item) {
		$lists['Packages'][$item->name] = $database->query('SELECT COUNT(dancer_id) FROM %1$s_registrations LEFT JOIN %1$s_dancers USING(dancer_id) WHERE %1$s_registrations.`item_id` = :item_id AND %1$s_dancers.`status` != 2', array(':item_id' => $item->id()))->fetchColumn();
		
		if ($event->has_vip()) {
			$vip_count = $database->query('SELECT COUNT(dancer_id) FROM %1$s_registrations LEFT JOIN %1$s_dancers USING(dancer_id) WHERE %1$s_registrations.`item_id` = :item_id AND %1$s_dancers.`status` = 2', array(':item_id' => $item->id()))->fetchColumn();
			
			if ($vip_count) {
				$lists['Packages'][$item->name] .= sprintf(' (+%d %s)', $vip_count, _n('VIP', 'VIPs', $vip_count));
			}
		}
	}
	$lists['Packages'] = array_filter($lists['Packages']);
	
	# Shirts
	$shirts = $event->items_where(array(':preregistration' => 1, ':type' => 'shirt'));
	foreach ($shirts as $item) {
		$header_key = sprintf('%s (%d)', $item->name, $event->count_registrations_where(array(':item_id' => $item->id())));
		
		foreach (explode(',', $item->description) as $size) {
			$lists[$header_key][ucfirst($size)] = $event->count_registrations_where(array(':item_id' => $item->id(), ':item_meta' => $size));
		}
		
		$lists[$header_key] = array_filter($lists[$header_key]);
	}
	
	echo RegistrationSystem::render_template('reports/numbers.html', array(
		'event' => $event,
		'lists' => $lists));
}

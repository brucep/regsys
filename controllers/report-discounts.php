<?php

function regsys_report_discounts($event)
{
	$database = RegistrationSystem::get_database_connection();
	
	$dancers = $database->query('SELECT d.*, ed.discount_code FROM regsys_dancers AS d LEFT JOIN regsys_event_discounts AS ed USING(discount_id) WHERE d.event_id = ? AND d.discount_id IS NOT NULL ORDER BY discount_id ASC, last_name ASC, first_name ASC', array($event->id()))->fetchAll(PDO::FETCH_CLASS, 'RegistrationSystem_Model_Dancer');

	$groups = array();
	foreach ($dancers as $dancer) {
		$groups[$dancer->discount_code][] = $dancer;
	}
	
	echo RegistrationSystem::render_template('report-discounts.html', array(
		'event'  => $event,
		'groups' => $groups));
}

<?php

function regsys_report_discounts($event)
{
	$database = RegistrationSystem::get_database_connection();
	
	$dancers = $database->query('SELECT regsys_dancers.*, regsys_event_discounts.discount_code FROM regsys_dancers LEFT JOIN regsys_event_discounts USING(discount_id) WHERE regsys_dancers.event_id = ? AND regsys_dancers.discount_id IS NOT NULL ORDER BY discount_id ASC, last_name ASC, first_name ASC', array($event->id()))->fetchAll(PDO::FETCH_CLASS, 'RegistrationSystem_Model_Dancer');

	$groups = array();
	foreach ($dancers as $dancer) {
		$groups[$dancer->discount_code][] = $dancer;
	}
	
	echo RegistrationSystem::render_template('report-discounts.html', array(
		'event'  => $event,
		'groups' => $groups));
}

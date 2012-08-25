<?php

function regsys_report_discounts($event)
{
	$database = RegistrationSystem::get_database_connection();
	
	$dancers = $database->query('SELECT * FROM %s_dancers WHERE event_id = ? AND discount_id IS NOT NULL ORDER BY discount_id ASC, last_name ASC, first_name ASC', array($event->id()))->fetchAll(PDO::FETCH_CLASS, 'RegistrationSystem_Model_Dancer');
	
	echo RegistrationSystem::render_template('reports/discounts.html', array(
		'event'   => $event,
		'dancers' => $dancers));
}

<?php

function regsys_report_competitions($event)
{
	echo RegistrationSystem::render_template('report-competitions.html', array(
		'event' => $event,
		'items' => RegistrationSystem::get_database_connection()->fetchAll('SELECT * FROM regsys_items WHERE event_id = ? AND type = ? ORDER BY item_id ASC', array($event->id(), 'competition'), 'RegistrationSystem_Model_Item')));
}

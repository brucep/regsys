<?php

function regsys_report_index()
{
	$events = RegistrationSystem::get_database_connection()->fetchAll('SELECT * FROM regsys_events ORDER BY date_paypal_prereg_end DESC', array(), 'RegistrationSystem_Model_Event');
	echo RegistrationSystem::render_template('report-index.html', array('events' => $events));
}

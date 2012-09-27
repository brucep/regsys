<?php

function regsys_report_index()
{
	echo RegistrationSystem::render_template('report-index.html', array('events' => RegistrationSystem_Model_Event::get_events()));
}

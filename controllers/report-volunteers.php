<?php

function regsys_report_volunteers($event)
{
	echo RegistrationSystem::render_template('report-volunteers.html', array(
		'event'      => $event,
		'volunteers' => $event->dancers_where(array(':status' => 1))));
}

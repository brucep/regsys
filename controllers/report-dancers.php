<?php

function regsys_report_dancers($event)
{
	echo RegistrationSystem::render_template('reports/dancers.html', array(
		'event'   => $event,
		'dancers' => $event->dancers()));
}

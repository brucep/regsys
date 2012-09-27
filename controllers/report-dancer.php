<?php

function regsys_report_dancer($event, $dancer)
{
	echo RegistrationSystem::render_template('report-dancer.html', array(
		'event'  => $event,
		'dancer' => $dancer));
}

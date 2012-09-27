<?php

function regsys_report_housing_needed($event)
{
	$dancers = $event->dancers_where(array(':housing_type' => 1));
	
	echo RegistrationSystem::render_template('report-housing.html', array(
		'event'         => $event,
		'dancers'       => $dancers,
		'housing_count' => count($dancers),
		'housing_type'  => 'Housing Needed',
		'housing_href'  => 'housing_needed'));
}

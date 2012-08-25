<?php
	
function regsys_report_housing_providers($event)
{
	echo RegistrationSystem::render_template('reports/housing.html', array(
		'event'         => $event,
		'dancers'       => $event->dancers_where(array(':housing_type' => 2)),
		'housing_count' => $event->count_housing_spots_available(),
		'housing_type'  => 'Housing Providers',
		'housing_href'  => 'housing_providers'));
}

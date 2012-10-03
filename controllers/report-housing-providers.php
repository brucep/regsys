<?php
	
echo self::render_template('report-housing.html', array(
	'event'         => $event,
	'dancers'       => $event->dancers_where(array(':housing_type' => 2)),
	'housing_count' => $database->fetchColumn('SELECT SUM(housing_spots_available) FROM regsys_housing WHERE event_id = ? AND housing_type = 2', array($this->event_id)),
	'housing_type'  => 'Housing Providers',
	'housing_href'  => 'housing_providers'));

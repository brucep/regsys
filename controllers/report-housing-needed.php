<?php

$dancers = $event->dancers_where(array(':housing_type' => 1));

echo self::render_template('report-housing.html', array(
	'event'         => $event,
	'dancers'       => $dancers,
	'housing_count' => count($dancers),
	'housing_type'  => 'Housing Needed',
	'housing_href'  => 'housing_needed'));

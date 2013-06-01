<?php

if (!isset($_GET['data']) or !in_array($_GET['data'], array('competitions', 'dancers', 'housing_needed', 'housing_providers', 'volunteers'))) {
	throw new Exception('Unable to handle to data for: ' . $_GET['data']);
}

$rows = array();

if ($_GET['data'] == 'housing_needed') {
	$filename = 'Housing Needed';
	
	$rows[0] = array('Last Name', 'First Name', 'Email Address');
	$rows[0] = array_merge($rows[0], $event->housing_nights());
	$rows[0] = array_merge($rows[0], array(
		'Gender',
		'No Pets',
		'No Smoke',
		'Bedtime',
		'From',
		'Comment',
		'Date Registered'));
	
	$dancers = $event->dancers_where(array(':housing_type' => 1));
	
	foreach ($dancers as $dancer) {
		$row = array($dancer->last_name, $dancer->first_name, $dancer->email);
		
		foreach ($event->housing_nights() as $night) {
			$row[] = in_array($night, $dancer->housing_nights()) ? '•' : '';
		}
		
		$row[] = $dancer->housing_gender();
		$row[] = $dancer->housing_prefers_no_pets()  ? '•' : '';
		$row[] = $dancer->housing_prefers_no_smoke() ? '•' : '';
		$row[] = $dancer->housing_bedtime();
		$row[] = $dancer->housing_from_scene;
		$row[] = $dancer->housing_comment;
		$row[] = date('Y-m-d, h:i A', $dancer->date_registered());
		
		$rows[] = $row;
	}
}
elseif ($_GET['data'] == 'housing_providers') {
	$filename = 'Housing Providers';
	
	$rows[0] = array('Last Name', 'First Name', 'Email Address');
	$rows[0] = array_merge($rows[0], $event->housing_nights());
	$rows[0] = array_merge($rows[0], array(
		'Gender',
		'Spots',
		'Has Pets',
		'Smokes',
		'Bedtime',
		'Comment',
		'Date Registered'));
	
	$dancers = $event->dancers_where(array(':housing_type' => 2));
	
	foreach ($dancers as $dancer) {
		$row = array($dancer->last_name, $dancer->first_name, $dancer->email);
		
		foreach ($event->housing_nights() as $night) {
			$row[] = in_array($night, $dancer->housing_nights()) ? '•' : '';
		}
		
		$row[] = $dancer->housing_gender();
		$row[] = $dancer->housing_spots_available();
		$row[] = $dancer->housing_has_pets()  ? '•' : '';
		$row[] = $dancer->housing_has_smoke() ? '•' : '';
		$row[] = $dancer->housing_bedtime();
		$row[] = $dancer->housing_comment;
		$row[] = date('Y-m-d, h:i A', $dancer->date_registered());
		
		$rows[] = $row;
	}
}
else {
	if ($_GET['data'] == 'competitions') {
		$filename = 'Competitors';
		$dancers  = $database->fetchAll('SELECT DISTINCT d.dancer_id as dancer_id, last_name, first_name, email FROM regsys_registrations AS r LEFT JOIN regsys_items AS i USING(item_id) LEFT JOIN regsys_dancers AS d USING(dancer_id) WHERE r.event_id = ? AND i.type = "competition" ORDER BY last_name ASC, first_name ASC', array($event->id()), 'RegistrationSystem_Model_Dancer');
	}
	elseif ($_GET['data'] == 'dancers') {
		$filename = 'Dancers';
		$dancers  = $event->dancers();
	}
	elseif ($_GET['data'] == 'volunteers') {
		$filename = 'Volunteers';
		$dancers  = $event->dancers_where(array(':status' => 1));
	}
	
	$rows[0] = array('Last Name', 'First Name', 'Email Address');
	
	foreach ($dancers as $dancer) {
		$rows[] = array($dancer->last_name, $dancer->first_name, $dancer->email);
	}
}

$output = fopen('php://output', 'w');

if (!$output) {
	throw new Exception('Unable to open output file.');
}

$filename .= sprintf(' for %s - %s.csv', $event->name, date('Y-m-d'));

header('Content-Type: text/csv');
header(sprintf('Content-Disposition: attachment; filename="%s"', $filename));
header('Pragma: no-cache');
header('Expires: 0');

foreach ($rows as $row) {
	fputcsv($output, $row);
}

exit();

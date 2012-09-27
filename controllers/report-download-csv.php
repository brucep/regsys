<?php

function regsys_report_download_csv($event)
{
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
			$database = RegistrationSystem::get_database_connection();
			$dancers  = $database->query('SELECT DISTINCT regsys_dancers.`dancer_id` as dancer_id, last_name, first_name, email FROM regsys_registrations LEFT JOIN regsys_items USING(item_id) LEFT JOIN regsys_dancers USING(dancer_id) WHERE regsys_registrations.`event_id` = :event_id AND regsys_items.`type` = "competition" ORDER BY regsys_dancers.`last_name` ASC, regsys_dancers.`first_name` ASC', array(':event_id' => $event->id()))->fetchAll(PDO::FETCH_CLASS, 'RegistrationSystem_Model_Dancer');
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
	
	exit;
}

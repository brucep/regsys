<?php

require dirname(dirname(__FILE__)).'/prep-csv.php';

if (isset($_GET['request']) and $_GET['request'] === 'housing-needed') {
	$rows[0] = array(__('Last Name', 'nsevent'), __('First Name', 'nsevent'), __('Email Address', 'nsevent'));
	$rows[0] = array_merge($rows[0], NSEvent_CSVHelper::$event->get_housing_nights());
	$rows[0] = array_merge($rows[0], array(
		__('Gender', 'nsevent'),
		__('No Pets', 'nsevent'),
		__('No Smoke', 'nsevent'),
		__('Bedtime', 'nsevent'),
		__('From', 'nsevent'),
		__('Comment', 'nsevent'),
		__('Date Registered', 'nsevent')));
	
	$dancers = NSEvent_CSVHelper::$event->get_dancers_where(array(':housing_type' => 1));
	
	foreach ($dancers as $dancer) {
		$row = array($dancer->get_last_name(), $dancer->get_first_name(), $dancer->get_email());
		
		foreach (NSEvent_CSVHelper::$event->get_housing_nights() as $night) {
			$row[] = ($dancer->get_housing_for_night_by_index($night)) ? '•' : '';
		}
		
		$row[] = $dancer->get_housing_gender();
		$row[] = $dancer->get_housing_prefers_no_pets()  ? '•' : '';
		$row[] = $dancer->get_housing_prefers_no_smoke() ? '•' : '';
		$row[] = $dancer->get_housing_bedtime();
		$row[] = $dancer->get_housing_from_scene();
		$row[] = $dancer->get_housing_comment();
		$row[] = $dancer->get_date_registered('Y-m-d, h:i A');
		
		$rows[] = $row;
	}
	
	NSEvent_CSVHelper::download($rows, sprintf(__('Housing Needed for %s -', 'nsevent'), NSEvent_CSVHelper::$event->get_name()));
}
elseif (isset($_GET['request']) and $_GET['request'] === 'housing-providers') {
	$rows[0] = array(__('Last Name', 'nsevent'), __('First Name', 'nsevent'), __('Email Address', 'nsevent'));
	$rows[0] = array_merge($rows[0], NSEvent_CSVHelper::$event->get_housing_nights());
	$rows[0] = array_merge($rows[0], array(
		__('Gender', 'nsevent'),
		__('Spots', 'nsevent'),
		__('Has Pets', 'nsevent'),
		__('Smokes', 'nsevent'),
		__('Bedtime', 'nsevent'),
		__('Comment', 'nsevent'),
		__('Date Registered', 'nsevent')));
	
	$dancers = NSEvent_CSVHelper::$event->get_dancers_where(array(':housing_type' => 2));
	
	foreach ($dancers as $dancer) {
		$row = array($dancer->get_last_name(), $dancer->get_first_name(), $dancer->get_email());
		
		foreach (NSEvent_CSVHelper::$event->get_housing_nights() as $night) {
			$row[] = ($dancer->get_housing_for_night_by_index($night)) ? '1' : '';
		}
		
		$row[] = $dancer->get_housing_gender();
		$row[] = $dancer->get_housing_spots_available();
		$row[] = $dancer->get_housing_has_pets()  ? '•' : '';
		$row[] = $dancer->get_housing_has_smoke() ? '•' : '';
		$row[] = $dancer->get_housing_bedtime();
		$row[] = $dancer->get_housing_comment();
		$row[] = $dancer->get_date_registered('Y-m-d, h:i A');
		
		$rows[] = $row;
	}
	
	NSEvent_CSVHelper::download($rows, sprintf(__('Housing Provider for %s -', 'nsevent'), NSEvent_CSVHelper::$event->get_name()));
}

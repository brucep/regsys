<?php

require dirname(dirname(__FILE__)).'/prep-csv.php';

if (!isset($_GET['request']) or !in_array($_GET['request'], array('housing-needed', 'housing-providers', 'housing-needed-email'))) {
	throw new Exception('Bad request');
}

if ($_GET['request'] === 'housing-needed') {
	$rows[0] = array_merge(
		array(__('Last Name', 'nsevent'), __('First Name', 'nsevent')),
		NSEvent_CSVHelper::$event->get_housing_nights(),
		array(__('Gender', 'nsevent')));

	$dancers_housing = NSEvent_CSVHelper::$event->get_dancers_needing_housing();
	$housing_title = __('Housing Needed', 'nsevent');

	$rows[0] = array_merge($rows[0], array(
		__('Has Car', 'nsevent'),
		__('No Pets', 'nsevent'),
		__('No Smoking', 'nsevent'),
		__('Comment', 'nsevent')));
	
	foreach ($dancers_housing as $dancer) {
		$row = array($dancer->get_last_name(), $dancer->get_first_name());
		
		foreach (NSEvent_CSVHelper::$event->get_housing_nights() as $night) {
			$row[] = ($dancer->get_housing_for_night_by_index($night)) ? '•' : '';
		}
		
		$row[] = $dancer->get_housing_gender();
		$row[] = ($dancer->no_pets)    ? '•' : '';
		$row[] = ($dancer->no_smoking) ? '•' : '';
		$row[] = $dancer->comment;
		
		$rows[] = $row;
	}
}
elseif ($_GET['request'] === 'housing-providers') {
	$rows[0] = array_merge(
		array(__('Last Name', 'nsevent'), __('First Name', 'nsevent')),
		NSEvent_CSVHelper::$event->get_housing_nights(),
		array(__('Gender', 'nsevent')));

	$dancers_housing = NSEvent_CSVHelper::$event->get_dancers_providing_housing();
	$housing_title = __('Housing Providers', 'nsevent');
	
	$rows[0] = array_merge($rows[0], array(
		__('Available', 'nsevent'),
		__('Pets', 'nsevent'),
		__('Smoking', 'nsevent'),
		__('Comment', 'nsevent')));
	
	foreach ($dancers_housing as $dancer) {
		$row = array($dancer->get_last_name(), $dancer->get_first_name());
		
		foreach (NSEvent_CSVHelper::$event->nights() as $night) {
			$row[] = ($dancer->get_housing_for_night_by_index($night)) ? '1' : '';
		}
		
		$row[] = $dancer->get_housing_gender();
		$row[] = $dancer->available;
		$row[] = ($dancer->pets)    ? '1' : '';
		$row[] = ($dancer->smoking) ? '1' : '';
		$row[] = $dancer->comment;
		
		$rows[] = $row;
	}
}
elseif ($_GET['request'] === 'housing-needed-email') {
	$dancers_housing = NSEvent_CSVHelper::$event->get_dancers_needing_housing();
	$housing_title = __('Housing Needed Emails', 'nsevent');
	$rows[0] = array(__('Last Name', 'nsevent'), __('First Name', 'nsevent'), __('Email Address', 'nsevent'));
	
	foreach ($dancers_housing as $dancer) {
		$rows[] = array($dancer->get_last_name(), $dancer->get_first_name(), $dancer->get_email());
	}
}

NSEvent_CSVHelper::download($rows, $housing_title);

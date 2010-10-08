<?php

require dirname(dirname(__FILE__)).'/prep-csv.php';

if (!isset($_GET['request']) or !in_array($_GET['request'], array('dancers', 'competitors', 'volunteers')))
	throw new Exception('Bad request');

if ($_GET['request'] == 'dancers')
	$dancers = NSEvent_Dancer::find_all();
elseif ($_GET['request'] == 'volunteers')
	$dancers = NSEvent_Dancer::find_by('status', 1);
else
	$dancers = NSEvent_CSVHelper::$database->query('SELECT DISTINCT %1$s_dancers.`id` as id, last_name, first_name, email FROM %1$s_registrations LEFT JOIN %1$s_items ON %1$s_registrations.`item_id` = %1$s_items.`id` LEFT JOIN %1$s_dancers ON %1$s_registrations.`dancer_id` = %1$s_dancers.`id` WHERE %1$s_registrations.`event_id` = :event_id AND %1$s_items.`type` = "competition" ORDER BY %1$s_dancers.`last_name` ASC, %1$s_dancers.`first_name` ASC', array(':event_id' => NSEvent_CSVHelper::$event->id))->fetchAll(PDO::FETCH_CLASS, 'NSEvent_Dancer');

$rows[0] = array(
	__('Last Name', 'nsevent'),
	__('First Name', 'nsevent'),
	__('Email Address', 'nsevent'));

foreach ($dancers as $dancer)
	$rows[] = array($dancer->last_name, $dancer->first_name, $dancer->email);

NSEvent_CSVHelper::download($rows, ucfirst($_GET['request']));

?>
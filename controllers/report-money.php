<?php

function regsys_report_money($event)
{
	$database = RegistrationSystem::get_database_connection();
	
	$total  = $database->query('SELECT SUM(price) FROM %s_registrations WHERE event_id = ?', array($event->id()))->fetchColumn();
	$total += $database->query('SELECT SUM(paypal_fee) FROM %s_dancers  WHERE event_id = ?', array($event->id()))->fetchColumn();
	
	$groups = array('Mail' => array(), 'PayPal' => array());
	foreach ($event->dancers() as $dancer) {
		$groups[$dancer->payment_method][] = $dancer;
	}
	
	echo RegistrationSystem::render_template('reports/money.html', array(
		'event'  => $event,
		'groups' => $groups,
		'items'  => $event->items(),
		'total'  => $total));
}

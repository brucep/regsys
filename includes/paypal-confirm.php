<?php

if (empty($_POST)) {
	header('HTTP/1.1 500 Internal Server Error');
	exit();
}

require dirname(__FILE__) . '/paypal-ipn.php';
set_error_handler('RegistrationSystem_PayPal_IPN::error_handler', E_ALL);

try {
	$notification = new RegistrationSystem_PayPal_IPN($_POST);
	
	
	require dirname(__FILE__) . '/../../../../wp/wp-load.php';
	
	$options = RegistrationSystem::get_options();
	
	
	if (!isset($_GET['test'])) {
		if (!$notification->is_valid()) {
			throw new Exception('PayPal notification invalid.');
		}
		elseif ($notification->payment_status != 'Completed') {
			exit(); # The "Pending" status will spam you to death!
		}
		if (!$notification->is_test() and $notification->receiver_email != $options['paypal_business']) {
			throw new Exception('Receiver email does not match: ' . $notification->receiver_email);
		}
	}
	
	
	$database = RegistrationSystem::get_database_connection();
	RegistrationSystem_Model::set_database($database);
	
	
	$event = RegistrationSystem_Model_Event::get_event_by_id($options['current_event_id']);
	
	if (!$event) {
		throw new Exception(sprintf('Event ID not found: %d', $options['current_event_id']));
	}
	
	$dancer = $event->dancer_by_id($notification->custom);
	
	if (!$dancer) {
		throw new Exception(sprintf('Dancer ID not found: %d', $notification->custom));
	}
	
	
	$payment_owed = $dancer->payment_owed();
	unset($dancer, $event, $options);
	
	
	$unconfirmed_registrations = array();
	
	$tmp_registrations = $database->query('SELECT item_id, price FROM %s_registrations WHERE dancer_id = ? AND paypal_confirmed = 0', array($notification->custom))->fetchAll(PDO::FETCH_OBJ);
	
	foreach ($tmp_registrations as $reg) {
		$unconfirmed_registrations[$reg->item_id] = $reg->price;
	}
	
	unset($tmp_registrations, $reg);
	
	
	$output[] = 'Dancer ID ' . $notification->custom;
	
	foreach ($notification->get_all_items() as $item) {
		if (empty($item['number'])) {
			continue; # Skip PayPal fee
		}
		
		if (isset($unconfirmed_registrations[$item['number']])) {
			$payment_owed = $payment_owed - $item['mc_gross'];
			
			$database->query('UPDATE %s_registrations SET paypal_confirmed = 1 WHERE dancer_id = ? AND item_id = ?', array($notification->custom, $item['number']));
			
			$output[] = 'Confirmed item ' . $item['number'];
		}
		else {
			$output[] = '  Skipped item ' . $item['number'];
		}
	}
	
	$registrations_remaining = $database->query('SELECT COUNT(dancer_id) FROM %s_registrations WHERE dancer_id = ? AND paypal_confirmed = 0', array($notification->custom))->fetchColumn();
	
	$output[] = sprintf('$%d owed', $payment_owed);
	$output[] = sprintf('%d registration%s remaining', $registrations_remaining, $registrations_remaining == 1 ? '' : 's');
	
	$database->query('UPDATE %s_dancers SET payment_owed = ?, payment_confirmed = ? WHERE dancer_id = ?', array($payment_owed, (!$registrations_remaining and $payment_owed == 0), $notification->custom));
	
	exit(implode("\n", $output));
}
catch (Exception $e) {
	header('HTTP/1.1 500 Internal Server Error');
	
	switch (get_class($e)) {
		case 'ErrorException':
			$message = sprintf("Error: %s [%s:%d]\n\n\n%s", $e->getMessage(), $e->getFile(), $e->getLine(), print_r($_POST, true));
			break;
		
		default:
			$message = $e->getMessage();
	}
	
	@mail(@get_option('admin_email'), 'IPN Exception: ' . basename(__FILE__, '.php'), $message);
	
	exit($message);
}

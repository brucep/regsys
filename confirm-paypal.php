<?php

if (empty($_POST)) {
	header('HTTP/1.0 500 Internal Server Error');
	exit();
}

set_error_handler(function ($number, $message, $file, $line) {
	throw new \Exception(sprintf("Error: %s [%s:%d]\n\n\n%s", $message, $file, $line, print_r($_POST, true)));
	}, E_ALL);

try {
	# Load WordPress to access plugin (change path if needed)
	require __DIR__ . '/../../../wp/wp-load.php';
	
	
	$options = \RegSys::getOptions();
	
	$notification = new \RegSys\Payment\PayPal\IPN($_POST);
	
	if (!$options['registrationTesting'] and isset($_GET['test'])) {
		unset($_GET['test']);
	}
	
	if (!isset($_GET['test'])) {
		if (!$notification->isValid()) {
			throw new \Exception('PayPal notification invalid.');
		}
		elseif ($notification->payment_status != 'Completed') {
			exit(); # The "Pending" status will spam you to death!
		}
		if (!$notification->isTest() and $notification->receiver_email != $options['paypalBusiness']) {
			throw new \Exception('Receiver email does not match: ' . $notification->receiver_email);
		}
	}
	
	
	$db = \RegSys::getDatabaseConnection();
	\RegSys\Entity::setDatabase($db);
	
	$event = \RegSys\Entity\Event::eventByID($options['currentEventID']);
	
	if (!$event) {
		throw new Exception(sprintf('Event not found for ID: %d', $options['currentEventID']));
	}
	
	$dancer = $event->dancerByID($notification->custom);
	
	if (!$dancer) {
		throw new Exception(sprintf('Dancer not found for ID: %d', $notification->custom));
	}
	
	$paymentOwed = $dancer->paymentOwed();
	unset($event);
	
	
	$unconfirmedRegistrations = array();
	
	$result = $db->fetchAll('SELECT itemID, price FROM regsys__registrations WHERE dancerID = ? AND paypalConfirmed = 0', array($notification->custom));
	
	foreach ($result as $reg) {
		$unconfirmedRegistrations[$reg->itemID] = $reg->price;
	}
	
	unset($tempRegistrations, $reg);
	
	
	$output[] = 'Dancer ID ' . $notification->custom;
	$confirmedRegistrations = 0;
	
	foreach ($notification->allItems() as $item) {
		if (empty($item['number'])) {
			continue; # Skip PayPal fee
		}
		
		if (isset($unconfirmedRegistrations[$item['number']])) {
			$paymentOwed = $paymentOwed - $item['mc_gross'];
			
			$db->query('UPDATE regsys__registrations SET paypalConfirmed = 1 WHERE dancerID = ? AND itemID = ?', array($notification->custom, $item['number']));
			
			$confirmedRegistrations++;
			$output[] = 'Confirmed item ' . $item['number'];
		}
		else {
			$output[] = '  Skipped item ' . $item['number'];
		}
	}
	
	if ($confirmedRegistrations > 0) {
		$fee = $options['paypalFee'] ? $options['paypalFee'] - $notification->mc_fee : $notification->mc_fee;
		$db->query('UPDATE regsys__dancers SET paypalFee = ? WHERE dancerID = ?', array($fee + $dancer->paypalFee(), $notification->custom));
		$output[] = sprintf('$%.2f fee recorded%s', $fee, $options['paypalFee'] ? sprintf(' (%d - %.2f)', $options['paypalFee'], $notification->mc_fee) : '');
	}
	else {
		$output[] = 'No fee recorded';
	}
	
	$registrationsRemaining = $db->fetchColumn('SELECT COUNT(dancerID) FROM regsys__registrations WHERE dancerID = ? AND paypalConfirmed = 0', array($notification->custom));
	
	$output[] = sprintf('$%d owed', $paymentOwed);
	$output[] = sprintf('%d registration%s remaining', $registrationsRemaining, $registrationsRemaining == 1 ? '' : 's');
	
	$db->query('UPDATE regsys__dancers SET paymentOwed = ?, paymentConfirmed = ? WHERE dancerID = ?', array($paymentOwed, ($registrationsRemaining == 0 and $paymentOwed == 0), $notification->custom));
	
	isset($_GET['test']) ? exit(implode("\n", $output)) : exit();
}
catch (Exception $e) {
	header('HTTP/1.0 500 Internal Server Error');
	
	@mail(@get_option('admin_email'), 'IPN Exception: ' . basename(__FILE__, '.php'), $e->getMessage());
	
	exit($e->getMessage());
}

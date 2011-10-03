<?php

# Populate values for confirmation email
self::$validated_items = $dancer->get_registered_items();
self::$validated_package_id = $dancer->get_registered_package_id();

$package_cost      = 0;
$competitions      = array();
$competitions_cost = 0;
$shirts            = array();
$shirts_cost       = 0;
$total_cost        = 0;

if ($dancer->get_registered_package_id()) {
	$package_cost = $dancer->get_price_for_registered_item($dancer->get_registered_package_id());
}

foreach ($dancer->get_registered_items() as $item) {
	$total_cost += $dancer->get_price_for_registered_item($item->get_id());
	
	if ($item->get_type() == 'competition') {
		$competitions[$item->get_id()] = $item;
		$competitions_cost += $dancer->get_price_for_registered_item($item->get_id());
	}
	elseif ($item->get_type() == 'shirt') {
		$shirts[$item->get_id()] = $item;
		$shirts_cost += $dancer->get_price_for_registered_item($item->get_id());
	}
}


$confirmation_email = array(
	'to_email' => $dancer->get_email(),
	'to_name'  => $dancer->get_name(),
	'subject'  => sprintf(__('Registration for %s: %s', 'nsevent'), $event->get_name(), $dancer->get_name())
	);

# Get body of email message
ob_start();
require dirname(dirname(__FILE__)).'/registration/confirmation-email.php';
$confirmation_email['body'] = preg_replace("/^(- .+\n)\n+-/m", '$1-', ob_get_contents());
ob_end_clean();

self::send_confirmation_email($confirmation_email);

echo '<p>Confirmation email resent.</p>';

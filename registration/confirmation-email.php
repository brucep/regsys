<?php echo $dancer->get_name(), ",\n"; ?>

Thanks for registering for <?php echo $event->get_name(); ?>!  
We are very excited that you're joining us!

We have you confirmed for the following:


ABOUT YOU
---------

- First Name: <?php echo $dancer->get_first_name(), "\n"; ?>
- Last Name: <?php echo $dancer->get_last_name(), "\n"; ?>
- Mobile Phone Number: <?php echo $dancer->get_mobile_phone(), "\n"; ?>
- Position: <?php echo $dancer->get_position(), "\n"; ?>
<?php if ($event->has_levels()): ?>
- Level: <?php echo $event->get_level_for_index($dancer->get_level()), "\n"; ?>
<?php endif; ?>
<?php if ($vip): ?>
- VIP
<?php endif; ?>


<?php if ($dancer->is_volunteer()): ?>
VOLUNTEER
---------

- Thanks for volunteering!
- Your phone number: <?php echo $dancer->get_volunteer_phone(), "\n"; ?>


<?php endif; ?>
<?php if ($dancer->needs_housing()): ?>
HOUSING NEEDED
--------------

<?php 	if ($dancer->get_housing_prefers_no_smoke()): ?>
- I would prefer no smoking.
<?php 	endif; ?>
<?php 	if ($dancer->get_housing_prefers_no_pets()): ?>
- I would prefer no pets.
<?php 	endif; ?>
- I would prefer to be housed with: <?php echo $dancer->get_housing_gender(), "\n"; ?>
- I will need housing for: <?php echo $dancer->get_housing_nights($event->get_housing_nights()), "\n"; ?>
<?php 	if ($dancer->get_housing_comment()): ?>
<?php echo "\n", $dancer->get_housing_comment(), "\n"; ?>
<?php endif; ?>

<?php elseif ($dancer->is_housing_provider()): ?>
HOUSING PROVIDER
----------------

<?php echo '- ', sprintf(_n('I have room for %d person.', 'I have room for %d persons.', $dancer->get_housing_spots_available(), 'nsevent'), $dancer->get_housing_spots_available()), "\n"; ?>
<?php 	if ($dancer->get_housing_has_smoke()): ?>
- I smoke.
<?php 	endif; ?>
<?php 	if ($dancer->get_housing_has_pets()): ?>
- I have pets.
<?php 	endif; ?>
- I will house: <?php echo $dancer->get_housing_gender(), "\n"; ?>
- I will provide housing for: <?php echo $dancer->get_housing_nights($event->get_housing_nights()), "\n"; ?>
<?php 	if ($dancer->get_housing_comment()): ?>
<?php echo "\n", $dancer->get_housing_comment(), "\n"; ?>
<?php endif; ?>

<?php endif; ?>
<?php if (self::$validated_package_id): ?>
PACKAGE
-------

<?php
printf('+ $%1$d :: %2$s',
	$package_cost,
	self::$validated_items[self::$validated_package_id]->get_name());
?>


<?php endif; ?>
<?php if ($competitions): ?>
COMPETITIONS
------------

<?php
	foreach ($competitions as $item) {
 		printf('+ $%1$d :: %2$s%3$s'."\n",
			$dancer->is_vip() ? $item->get_price_for_vip() : $item->get_price_for_prereg($_POST['payment_discount']),
			$item->get_name(),
			(isset($_POST['item_meta'][$item->get_id()])) ? sprintf(' (%s)', ucfirst($_POST['item_meta'][$item->get_id()])) : '');
 	}
?>


<?php endif; ?>
<?php if ($shirts): ?>
SHIRTS
------

<?php
	foreach ($shirts as $item) {
 		printf('+ $%1$d :: %2$s%3$s'."\n",
			$dancer->is_vip() ? $item->get_price_for_vip() : $item->get_price_for_prereg($_POST['payment_discount']),
			$item->get_name(),
			(isset($_POST['item_meta'][$item->get_id()])) ? sprintf(' (%s)', ucfirst($_POST['item_meta'][$item->get_id()])) : '');
 	}
?>


<?php endif; ?>
<?php if ($dancer->get_price_total() > 0): ?>

TOTALS
------

<?php

	if (self::$validated_package_id) {
		printf('+ $%1$d :: Package'."\n", $package_cost);
	}

	if ($competitions) {
		printf('+ $%1$d :: Competitions'."\n", $competitions_cost);
	}

	if ($shirts) {
		printf('+ $%1$d :: Shirts'."\n", $shirts_cost);
	}

	if ($dancer->get_payment_method() == 'PayPal' and !empty($options['paypal_fee'])) {
		printf('+ $%1$d  :: PayPal Processing Fee'."\n", $options['paypal_fee']);
		$total_cost = $total_cost + (int) $options['paypal_fee'];
	}

	printf('+ $%1$d :: Grand Total', $total_cost);


	if ($dancer->get_payment_method() == 'Mail') {
		printf(__(<<<EOD


*YOUR REGISTRATION IS NOT COMPLETE!*

You still need to write a check to "%3\$s" for $%1\$d for mail it to:

%4\$s

Your check must be postmarked by %2\$s.

*REFUNDS ARE NOT ALLOWED AFTER %5\$s.*
EOD
			, 'nsevent'),
			$dancer->get_price_total(),
			$dancer->get_date_mail_postmark_by($options['postmark_within'], 'F jS'),
			$options['payable_to'],
			$options['mailing_address'],
			$event->get_date_refund_end('F jS'));
	}
endif;

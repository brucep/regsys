<?php echo $dancer->get_name(), ",\n"; ?>

Thanks for registering for <?php echo $event->get_name(); ?>!  
We are very excited that you're joining us!

We have you confirmed for the following:


ABOUT YOU
---------

- First Name: <?php echo $dancer->first_name, "\n"; ?>
- Last Name:  <?php echo $dancer->last_name, "\n"; ?>
- Position:   <?php echo $dancer->position(), "\n"; ?>
<?php if ($event->levels): ?>
- Level:      <?php echo $dancer->level(), "\n"; ?>
<?php endif; ?>
<?php if ($vip): ?>
- VIP
<?php endif; ?>


<?php if ($dancer->is_volunteer()): ?>
VOLUNTEER
---------

- Thanks for volunteering!
- Your phone number: <?php echo $_POST['volunteer_phone'], "\n"; ?>


<?php endif; ?>
<?php if (isset($_POST['housing_needed'])): ?>
HOUSING NEEDED
--------------

<?php 	if ($_POST['housing_needed_no_smoking']): ?>
- I would prefer no smoking.
<?php 	endif; ?>
<?php 	if ($_POST['housing_needed_no_pets']): ?>
- I would prefer no pets.
<?php 	endif; ?>
- I would prefer to be housed with: <?php echo NSEvent_Model::bit_field($_POST['housing_needed_gender'], NSEvent_Model_Dancer::$possible_housing_genders, 'string'), "\n"; ?>
- I will need housing for: <?php echo NSEvent_Model::bit_field($_POST['housing_needed_nights'], $event->nights(), 'string'), "\n"; ?>
<?php 	if (!empty($_POST['housing_needed_comment'])): ?>
<?php echo "\n", $_POST['housing_needed_comment'], "\n"; ?>
<?php endif; ?>

<?php elseif (isset($_POST['housing_provider'])): ?>
HOUSING PROVIDER
----------------

- I have room for <?php echo (int) $_POST['housing_provider_available']; ?> person(s).
<?php 	if ($_POST['housing_provider_smoking']): ?>
- I smoke.
<?php 	endif; ?>
<?php 	if ($_POST['housing_provider_pets']): ?>
- I have pets.
<?php 	endif; ?>
- I will house: <?php echo NSEvent_Model::bit_field($_POST['housing_provider_gender'], NSEvent_Model_Dancer::$possible_housing_genders, 'string'), "\n"; ?>
- I will provide housing for: <?php echo NSEvent_Model::bit_field($_POST['housing_provider_nights'], $event->nights(), 'string'), "\n"; ?>
<?php 	if (!empty($_POST['housing_provider_comment'])): ?>
<?php echo "\n", $_POST['housing_provider_comment'], "\n"; ?>
<?php endif; ?>

<?php endif; ?>
<?php if (self::$validated_package_id): ?>
PACKAGE
-------

<?php
printf('- $%1$d :: %2$s%3$s',
	$package_cost,
	self::$validated_items[self::$validated_package_id]->name,
	($event->get_early_end() and $dancer->get_date_registered() <= $event->get_early_end()) ? ' [Early Bird]' : '');
?>


<?php endif; ?>
<?php if ($competitions): ?>
COMPETITIONS
------------

<?php
	foreach ($competitions as $item):
 		printf('- $%1$d :: %2$s%3$s'."\n",
			$item->get_price_for_discount($_POST['payment_discount'], $event->is_early_bird()),
			$item->name,
			(isset($_POST['item_meta'][$item->get_id()])) ? sprintf(' (%s)', ucfirst($_POST['item_meta'][$item->get_id()])) : '');
 	endforeach;
?>


<?php endif; ?>
<?php if ($shirts): ?>
SHIRTS
------

<?php
	foreach ($shirts as $item):
 		printf('- $%1$d :: %2$s%3$s'."\n",
			$item->get_price_for_discount($_POST['payment_discount'], $event->is_early_bird()),
			$item->name,
			(isset($_POST['item_meta'][$item->get_id()])) ? sprintf(' (%s)', ucfirst($_POST['item_meta'][$item->get_id()])) : '');
 	endforeach;
?>


<?php endif; ?>
TOTALS
------

<?php

if (self::$validated_package_id)
	printf('- $%1$d :: Package'."\n", $package_cost);

if ($competitions)
	printf('- $%1$d :: Competitions'."\n", $competitions_cost);

if ($shirts)
	printf('- $%1$d :: Shirts'."\n", $shirts_cost);

if ($dancer->payment_method == 'PayPal' and !empty($options['paypal_fee']))
{
	printf('- $%1$d  :: PayPal Processing Fee'."\n", $options['paypal_fee']);
	$total_cost = $total_cost + (int) $options['paypal_fee'];
}

printf(' - $%1$d :: Grand Total', $total_cost);

?>

<?php

	if ($dancer->payment_method == 'Mail')
		printf(__(<<<EOD


*YOUR REGISTRATION IS NOT COMPLETE!*

You still need to do the following:

* Write a check to "%3\$s" for $%1\$d
* Send your check to:  
%4\$s

*REFUNDS ARE NOT ALLOWED AFTER %2\$s.*
EOD
		, 'nsevent'),
			$dancer->get_price_total(),
			date('F jS', $event->get_date_postmark_by()),
			'Naptown Stomp',
			"\tSwingIN c/o  \n\tNaptown Stomp  \n\tP.O. BOX 1051  \n\tINDIANAPOLIS IN  46206");

?>

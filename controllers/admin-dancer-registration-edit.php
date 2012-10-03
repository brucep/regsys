<?php

self::$validation = new RegistrationSystem_Form_Validation;
	
if (!empty($_POST)) {
	self::$validation->add_rule('items', 'RegistrationSystem::validate_items');
	
	if (self::$validation->validate()) {
		$additional_owed = 0;
		
		foreach (self::$validated_items as $item) {
			$price = $dancer->is_vip() ? $item->price_for_vip() : $item->price_for_prereg();
			$additional_owed += $price;
			
			$event->add_registration(array(
				'dancer_id' => $dancer->id(),
				'item_id'   => $item->id(),
				'price'     => $price,
				'item_meta' => isset($_POST['item_meta'][$item->id()]) ? $_POST['item_meta'][$item->id()] : '',
				));
		}
		
		$dancer->update_payment_confirmation(false, $dancer->payment_owed() + $additional_owed);
		
		foreach ($_POST['item_meta'] as $key => $value) {
			if (isset(self::$validated_items[$key])) {
				continue;
			}
			
			$database->query('UPDATE regsys_registrations SET item_meta = ? WHERE item_id = ? AND dancer_id = ? AND event_id = ?;', array($value, $key, $dancer->id(), $event->id()));
		}
		
		unset($_POST['items'], $_POST['item_meta']);
	}
}

echo self::render_template('admin-registration-edit.html', array(
	'event'      => $event,
	'items'      => $event->items(),
	'dancer'     => $dancer,
	'validation' => self::$validation));

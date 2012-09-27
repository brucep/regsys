<?php

function regsys_admin_dancer_registration_edit($event, $dancer)
{
	RegistrationSystem::$validation = new RegistrationSystem_Form_Validation;
	
	if (!empty($_POST)) {
		RegistrationSystem::$validation->add_rule('items', 'RegistrationSystem::validate_items');
		
		if (RegistrationSystem::$validation->validate()) {
			$database = RegistrationSystem::get_database_connection();
			
			$additional_owed = 0;
			
			foreach (RegistrationSystem::$validated_items as $item) {
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
				if (isset(RegistrationSystem::$validated_items[$key])) {
					continue;
				}
				
				$database->query('UPDATE %s_registrations SET item_meta = ? WHERE item_id = ? AND dancer_id = ? AND event_id = ?;', array($value, $key, $dancer->id(), $event->id()));
			}
			
			unset($_POST['items'], $_POST['item_meta']);
		}
	}
	
	echo RegistrationSystem::render_template('admin-registration-edit.html', array(
		'event'      => $event,
		'items'      => $event->items(),
		'dancer'     => $dancer,
		'validation' => RegistrationSystem::$validation));
}

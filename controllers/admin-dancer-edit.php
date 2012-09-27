<?php

function regsys_admin_dancer_edit($event, $dancer)
{
	$validation = new RegistrationSystem_Form_Validation;
	
	if (!empty($_POST)) {
		$validation->add_rules(array(
			'first_name'      => 'trim|required|max_length[100]|ucfirst',
			'last_name'       => 'trim|required|max_length[100]|ucfirst',
			'email'           => 'trim|valid_email|max_length[100]', # TODO: Update RegistrationSystem::validate_*
			'mobile_phone'    => 'trim|required|max_length[30]',
			'position'        => 'intval|in[1,2]',
			'payment_method'  => 'in[Mail,PayPal]',
			'date_registered' => 'required|strtotime',
			));
		
		if ($event->has_levels()) {
			$validation->add_rule('level_id', sprintf('intval|in[%s]',
				implode(',', array_keys($event->levels_keyed_by_id()))));
		}
		else {
			$_POST['level'] = 1;
		}
		
		if ($validation->validate()) {
			$database = RegistrationSystem::get_database_connection();
			
			$database->query('UPDATE %s_dancers SET first_name = ?, last_name = ?, email = ?, position = ?, level_id = ?, status = ?, date_registered = ?, payment_method = ?, mobile_phone = ? WHERE dancer_id = ?;', array(
				@$_POST['first_name'],
				@$_POST['last_name'],
				@$_POST['email'],
				@$_POST['position'],
				@$_POST['level_id'],
				@$_POST['status'],
				@$_POST['date_registered'],
				@$_POST['payment_method'],
				@$_POST['mobile_phone'],
				$dancer->id()));
			
			$dancer = $event->dancer_by_id($dancer->id());
		}
	}
	else {
		# Put values into POST so that form is pre-populated.
		$reflection = new ReflectionObject($dancer);
		
		if (version_compare(PHP_VERSION, '5.3', '>=')) {
			foreach ($reflection->getProperties() as $property) {
					$property->setAccessible(true);
					$_POST[$property->getName()] = $property->getValue($dancer);
			}
		}
		else {
			$temp = (array) $dancer;
			
			foreach ($reflection->getProperties() as $property) {
				if (isset($temp[$property->getName()])) {
					$_POST[$property->getName()] = $temp[$property->getName()];
				}
				else {
					$_POST[$property->getName()] = $temp["\0RegistrationSystem_Model_Dancer\0" . $property->getName()];
				}
			}
			
			unset($temp);
		}
	}
	
	if (isset($_POST['date_registered']) and is_numeric($_POST['date_registered'])) {
		$_POST['date_registered'] = date('Y-m-d h:i A', $_POST['date_registered']);
	}
	
	echo RegistrationSystem::render_template('admin-dancer-edit.html', array(
		'event'      => $event,
		'dancer'     => $dancer,
		'validation' => $validation));
}

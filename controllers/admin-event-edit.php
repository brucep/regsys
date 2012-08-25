<?php

function regsys_admin_event_edit($event)
{
	$validation = new RegistrationSystem_Form_Validation;
	
	if (!empty($_POST)) {
		$validation->add_rules(array(
			'name'                   => 'trim|required',
			'date_mail_prereg_end'   => 'required|strtotime',
			'date_paypal_prereg_end' => 'required|strtotime',
			'date_refund_end'        => 'if_set[date_refund_end]|strtotime',
			'has_vip'                => 'intval|in[0,1]',
			'has_volunteers'         => 'intval|in[0,1]',
			'has_housing'            => 'intval|in[0,1,2]',
			));
		
		if ($validation->validate()) {
			$database = RegistrationSystem::get_database_connection();
			
			$event = new RegistrationSystem_Model_Event($_POST);
			
			if ($_GET['request'] == 'admin_event_add') {
				$database->query('INSERT %s_events VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?);', array(
					@(string) $_POST['name'],
		 			@(int)    $_POST['date_mail_prereg_end'],
		 			@(int)    $_POST['date_paypal_prereg_end'],
		 			@(int)    $_POST['date_refund_end'],
		 			@(int)    $_POST['has_levels'],
		 			@(int)    $_POST['has_vip'],
		 			@(int)    $_POST['has_volunteers'],
		 			@(int)    $_POST['has_housing'],
		 			@(string) $_POST['housing_nights'],
					));
				
				wp_redirect(site_url('wp-admin/admin.php') . sprintf('?page=reg-sys&event_id=%d&request=admin_event_edit&added=true', $database->lastInsertID()));
				exit();
			}
			else {
				$database->query('UPDATE %s_events SET `name` = ?, date_mail_prereg_end = ?, date_paypal_prereg_end = ?, date_refund_end = ?, has_levels = ?, has_vip = ?, has_volunteers = ?, has_housing = ?, housing_nights = ? WHERE event_id = ?', array(
					$_POST['name'],
		 			$_POST['date_mail_prereg_end'],
		 			$_POST['date_paypal_prereg_end'],
		 			$_POST['date_refund_end'],
		 			@(int) $_POST['has_levels'],
		 			$_POST['has_vip'],
		 			$_POST['has_volunteers'],
		 			$_POST['has_housing'],
		 			@(string) $_POST['housing_nights'],
					$event->id(),
					));
				
				$levels = $event->levels();
				
				foreach ($_POST['edit_levels'] as $key => $value) {
					if ($value) {
						if (!isset($levels[$key])) {
							$database->query('INSERT %s_event_levels VALUES (?, ?, ?, ?);', array(
								$event->id(),
								$key,
								$value,
								isset($_POST['edit_tryouts'][$key]),
								));
						}
						elseif (isset($levels[$key])) {
							$database->query('UPDATE %s_event_levels SET label = ?, has_tryouts = ? WHERE event_id = ? AND level_id = ?', array(
								$value,
								isset($_POST['edit_tryouts'][$key]),
								$event->id(),
								$key,
								));
						}
					}
					elseif (!$value and isset($levels[$key])) {
						$database->query('DELETE FROM %s_event_levels WHERE event_id = ? AND level_id = ?', array(
							$event->id(),
							$key,
							));
					}
				}
				
				$event->unset_levels();
				unset($_POST['edit_tryouts'], $levels);
				
				$discounts = $event->discounts();
				
				foreach ($_POST['edit_discount_code'] as $key => $value) {
					if ($value) {
						if (!isset($discounts[$key])) {
							$database->query('INSERT %s_event_discounts VALUES (?, ?, ?, ?, ?, ?);', array(
								$event->id(),
								$key,
								$value,
								(int) $_POST['edit_discount_amount'][$key],
								(int) $_POST['edit_discount_limit'][$key],
								strtotime($_POST['edit_discount_expires'][$key]),
								));
						}
						elseif (isset($discounts[$key])) {
							$database->query('UPDATE %s_event_discounts SET discount_code = ?, discount_amount = ?, discount_limit = ?, discount_expires = ? WHERE event_id = ? AND discount_id = ?', array(
								$value,
								(int) $_POST['edit_discount_amount'][$key],
								(int) $_POST['edit_discount_limit'][$key],
								strtotime($_POST['edit_discount_expires'][$key]),
								$event->id(),
								$key,
								));
						}
					}
					elseif (!$value and isset($discounts[$key])) {
						$database->query('DELETE FROM %s_event_discounts WHERE event_id = ? AND discount_id = ?', array(
							$event->id(),
							$key,
							));
					}
				}
				
				$event->unset_discounts();
				unset($_POST['edit_discount_code'], $_POST['edit_discount_amount'], $_POST['edit_discount_limit'], $discounts);
			}
		}
	}
	elseif (isset($event)) {
		# Put values into POST so that form is pre-populated.
		$reflection = new ReflectionObject($event);
		
		if (version_compare(PHP_VERSION, '5.3', '>=')) {
			foreach ($reflection->getProperties() as $property) {
					$property->setAccessible(true);
					$_POST[$property->getName()] = $property->getValue($event);
			}
		}
		else {
			$temp = (array) $event;
			
			foreach ($reflection->getProperties() as $property) {
				if (isset($temp[$property->getName()])) {
					$_POST[$property->getName()] = $temp[$property->getName()];
				}
				else {
					$_POST[$property->getName()] = $temp["\0RegistrationSystem_Model_Event\0" . $property->getName()];
				}
			}
			
			unset($temp);
		}
	}
	
	# Format dates for display
	foreach ($_POST as $key => $value) {
		if (in_array($key, array('date_mail_prereg_end', 'date_paypal_prereg_end', 'date_refund_end'))) {
			if (empty($value)) {
				unset($_POST[$key]);
			}
			elseif (is_numeric($value)) {
				$_POST[$key] = date('Y-m-d h:i A', $value);
			}
		}
	}
	unset($key, $value);
	
	# Needed if there are validations errors when adding an event.
	if (isset($_GET['noheader'])) {
		require_once ABSPATH . 'wp-admin/admin-header.php';
	}
	
	echo RegistrationSystem::render_template('admin/event-edit.html', array(
		'event'      => $event,
		'validation' => $validation,
		'vip_href'   => get_permalink(get_page_by_path('register')) . '?vip'));
}

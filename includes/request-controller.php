<?php

class NSEvent_Request_Controller
{
	static public function admin_event_add()
	{
		# Separate method used to avoid loading non-existent event
		self::admin_event_edit(null);
	}
	
	static public function admin_event_delete($event)
	{
		if (isset($_POST['confirmed'])) {
			if (isset($_GET['registrations_only']) and $_GET['registrations_only'] == 'true')
			{
				$event->delete_registrations();
				wp_redirect(site_url('wp-admin/admin.php') . '?page=nsevent&request=report_index&deleted_event=' . rawurlencode($event->name()) . '&registrations_only=true');
				exit();
			}
			else {
				$event->delete();
				wp_redirect(site_url('wp-admin/admin.php') . '?page=nsevent&request=report_index&deleted_event=' . rawurlencode($event->name()));
				exit();
			}
		}
		
		# Needed if the confirmation checkbox wasn't checked.
		if (isset($_GET['noheader'])) {
			require_once ABSPATH . 'wp-admin/admin-header.php';
		}
		
		echo NSEvent::render_template('admin/event-delete.html', array('event' => $event));
	}
	
	static public function admin_event_edit($event)
	{
		$validation = new NSEvent_Form_Validation;
		
		if (!empty($_POST)) {
			$validation->add_rules(array(
				'name'                   => 'trim|required',
				'date_mail_prereg_end'   => 'required|strtotime',
				'date_paypal_prereg_end' => 'required|strtotime',
				'date_refund_end'        => 'if_set[date_refund_end]|strtotime',
				));
			
			if ($validation->validate()) {
				$event = new NSEvent_Model_Event($_POST);
				
				if ($_GET['request'] == 'admin_event_add') {
					$event->add();
					wp_redirect(site_url('wp-admin/admin.php') . sprintf('?page=nsevent&event_id=%d&request=admin_event_edit&added=true', $event->id()));
					exit();
				}
				else {
					$event->update();
				}
			}
		}
		elseif (isset($event)) {
			# Put values into POST so that form is pre-populated.
			$reflection = new ReflectionObject($event);
			
			if (version_compare(PHP_VERSION, '5.3', '>=')) {
				foreach ($reflection->getProperties(ReflectionProperty::IS_PRIVATE) as $property) {
						$property->setAccessible(true);
						$_POST[$property->getName()] = $property->getValue($event);
				}
			}
			else {
				$temp = (array) $event;
				
				foreach ($reflection->getProperties(ReflectionProperty::IS_PRIVATE) as $property) {
					$key = "\0NSEvent_Model_Event\0" . $property->getName();
					$_POST[$property->getName()] = $temp[$key];
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
		
		echo NSEvent::render_template('admin/event-edit.html', array(
			'event'      => $event,
			'validation' => $validation));
	}
	
	static public function report_competitions($event)
	{
		echo NSEvent::render_template('reports/competitions.html', array(
			'event' => $event,
			'items' => $event->items_where(array(':type' => 'competition'))));
	}
	
	static public function report_dancer($event, $dancer)
	{
		echo NSEvent::render_template('reports/dancer.html', array(
			'event'  => $event,
			'dancer' => $dancer));
	}
	
	static public function report_dancers($event)
	{
		echo NSEvent::render_template('reports/dancers.html', array(
			'event'   => $event,
			'dancers' => $event->dancers()));
	}
	
	static public function report_index()
	{
		echo NSEvent::render_template('reports/index.html', array('events' => NSEvent_Model_Event::get_events()));
	}
	
	static public function report_index_event($event)
	{
		echo NSEvent::render_template('reports/index-event.html', array('event' => $event));
	}
	
	static public function report_housing_needed($event)
	{
		$dancers = $event->dancers_where(array(':housing_type' => 1));
		
		echo NSEvent::render_template('reports/housing.html', array(
			'event'         => $event,
			'dancers'       => $dancers,
			'housing_count' => count($dancers),
			'housing_type'  => 'Housing Needed',
			'housing_href'  => 'housing_needed'));
	}
	
	static public function report_housing_providers($event)
	{
		echo NSEvent::render_template('reports/housing.html', array(
			'event'         => $event,
			'dancers'       => $event->dancers_where(array(':housing_type' => 2)),
			'housing_count' => $event->count_housing_spots_available(),
			'housing_type'  => 'Housing Providers',
			'housing_href'  => 'housing_providers'));
	}
	
	static public function report_money($event)
	{
		echo NSEvent::render_template('reports/money.html', array(
			'event'   => $event,
			'dancers' => $event->dancers(),
			'items'   => $event->items()));
	}
	
	static public function report_numbers($event)
	{
		$database = NSEvent::get_database_connection();
		
		# Dancers
		$lists['Dancers']['Total']   = $event->count_dancers();
		$lists['Dancers']['Leads']   = $event->count_dancers(array(':position' => 1));
		$lists['Dancers']['Follows'] = $event->count_dancers(array(':position' => 2));
		$lists['Dancers']['Ratio']   = @round($lists['Dancers']['Follows'] / $lists['Dancers']['Leads'], 2);
		
		if ($event->has_discount()) {
			$lists['Dancers']['Discounts'] = sprintf('%d of %d', $event->count_discounts_used(), $event->discount_limit());
			
			if ($event->has_discount_openings()) {
				$lists['Dancers']['Discounts'] .= sprintf(' (%d remaining)', $event->discount_limit() - $event->count_discounts_used());
			}
		}
		
		# Levels
		if ($event->has_levels()) {
			foreach ($event->levels() as $level) {
				$lists['Levels (All Dancers)'][$level['label']] = $event->count_dancers(array(':level_id' => $level['level_id']));
				
				$lists['Levels (Dancers in Classes)'][$level['label']] = sprintf('%d leads, %d follows',
					$database->query('SELECT COUNT(dancer_id) FROM %1$s_registrations LEFT JOIN %1$s_dancers USING(dancer_id) LEFT JOIN %1$s_items USING(item_id) WHERE %1$s_registrations.`event_id` = :event_id AND %1$s_dancers.`level_id` = :level_id AND %1$s_dancers.`position` = :position AND %1$s_items.`meta` = "count_for_classes"', array(':event_id' => $event->id(), ':level_id' => $level['level_id'], ':position' => 1))->fetchColumn(),
					$database->query('SELECT COUNT(dancer_id) FROM %1$s_registrations LEFT JOIN %1$s_dancers USING(dancer_id) LEFT JOIN %1$s_items USING(item_id) WHERE %1$s_registrations.`event_id` = :event_id AND %1$s_dancers.`level_id` = :level_id AND %1$s_dancers.`position` = :position AND %1$s_items.`meta` = "count_for_classes"', array(':event_id' => $event->id(), ':level_id' => $level['level_id'], ':position' => 2))->fetchColumn());
			}
			
			$lists['Levels (All Dancers)'] = array_filter($lists['Levels (All Dancers)']);
		}
		
		# Packages
		$lists['Packages'] = array();
		$packages = $event->items_where(array(':preregistration' => 1, ':type' => 'package'));
		foreach ($packages as $item) {
			$lists['Packages'][$item->name()] = $database->query('SELECT COUNT(dancer_id) FROM %1$s_registrations LEFT JOIN %1$s_dancers USING(dancer_id) WHERE %1$s_registrations.`item_id` = :item_id AND %1$s_dancers.`status` != 2', array(':item_id' => $item->id()))->fetchColumn();
			
			if ($event->has_vip()) {
				$vip_count = $database->query('SELECT COUNT(dancer_id) FROM %1$s_registrations LEFT JOIN %1$s_dancers USING(dancer_id) WHERE %1$s_registrations.`item_id` = :item_id AND %1$s_dancers.`status` = 2', array(':item_id' => $item->id()))->fetchColumn();
				
				if ($vip_count) {
					$lists['Packages'][$item->name()] .= sprintf(' (+%d %s)', $vip_count, _n('VIP', 'VIPs', $vip_count, 'nsevent'));
				}
			}
		}
		$lists['Packages'] = array_filter($lists['Packages']);
		
		# Shirts
		$shirts = $event->items_where(array(':preregistration' => 1, ':type' => 'shirt'));
		foreach ($shirts as $item) {
			$header_key = sprintf('%s (%d)', $item->name(), $event->count_registrations_where(array(':item_id' => $item->id())));
			
			foreach (explode(',', $item->description()) as $size) {
				$lists[$header_key][ucfirst($size)] = $event->count_registrations_where(array(':item_id' => $item->id(), ':item_meta' => $size));
			}
			
			$lists[$header_key] = array_filter($lists[$header_key]);
		}
		
		echo NSEvent::render_template('reports/numbers.html', array(
			'event' => $event,
			'lists' => $lists));
	}
	
	static public function report_packet_printout($event)
	{
		echo NSEvent::render_template('reports/packet-printout.html', array(
			'event'   => $event,
			'dancers' => $event->dancers()));
	}
	
	static public function report_reg_list($event)
	{
		if (isset($_GET['vip_only']) and $_GET['vip_only'] == 'true') {
			$dancers = $event->dancers_where(array(':status' => 2));
		}
		else {
			$dancers = $event->dancers_where(array(':status' => 2), false); # false = not equal to 2
		}
		
		if (!empty($_POST)) {
			foreach ($dancers as $dancer) {
				$dancer->update_payment_confirmation(
					(int) isset($_POST['payment_confirmed'][$dancer->id()]),
					(int) isset($_POST['payment_owed'][$dancer->id()]) ? $_POST['payment_owed'][$dancer->id()] : $dancer->payment_owed());
			}
		}
		
		echo NSEvent::render_template('reports/reg-list.html', array(
			'event' => $event,
			'dancers' => $dancers));
	}
	
	static public function report_volunteers($event)
	{
		echo NSEvent::render_template('reports/volunteers.html', array(
			'event'      => $event,
			'volunteers' => $event->dancers_where(array(':status' => 1))));
	}
}

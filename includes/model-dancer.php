<?php

class NSEvent_Model_Dancer extends NSEvent_Model
{
	private $event_id,
	        $id,
	        $first_name,
	        $last_name,
	        $email,
	        $date_registered,
	        $housing_type,
	        $housing_spots_available,
	        $housing_nights,
	        $housing_nights_array,
	        $housing_gender,
	        $housing_bedtime,
	        $housing_pets,
	        $housing_smoke,
	        $housing_from_scene,
	        $housing_comment,
	        $level,
	        $mobile_phone,
	        $note,
	        $position,
	        $payment_confirmed,
	        $payment_discount,
	        $payment_method,
	        $payment_owed,
	        $price_total,
	        $registered_items,
	    	$status;
	
	public function __construct(array $parameters = array())
	{
		foreach ($parameters as $key => $value) {
			$this->$key = $value;
		}
	}
	
	public function __toString()
	{
		return sprintf('%s %s [#%d]', $this->first_name, $this->last_name, $this->id);
	}
		
	public function add($event_id)
	{
		$this->date_registered = time();
		$this->event_id = $event_id;
		
		self::$database->query('INSERT %s_dancers VALUES (:event_id, NULL, :first_name, :last_name, :email, :position, :level, :status, :date_registered, :payment_method, :payment_discount, :payment_confirmed, :payment_owed, :mobile_phone, :note)', array(
			':event_id'          => $this->event_id,
			':first_name'        => $this->first_name,
			':last_name'         => $this->last_name,
			':email'             => $this->email,
			':position'          => $this->position,
			':level'             => $this->level,
			':status'            => $this->status,
			':date_registered'   => $this->date_registered,
			':payment_method'    => $this->payment_method,
			':payment_discount'  => $this->payment_discount,
			':payment_confirmed' => (int) $this->payment_confirmed,
			':payment_owed'      => (int) $this->payment_owed,
			':mobile_phone'      => (string) $this->mobile_phone,
			':note'              => (string) $this->note,
			));
		
		$this->id = self::$database->lastInsertID();
	}
	
	public function add_housing()
	{
		self::$database->query('INSERT %1$s_housing VALUES (:event_id, :dancer_id, :housing_type, :housing_spots_available, :housing_nights, :housing_gender, :housing_bedtime, :housing_pets, :housing_smoke, :housing_from_scene, :housing_comment)', array(
			':event_id'                => $this->event_id,
			':dancer_id'               => $this->id,
			':housing_type'            => (int) $this->housing_type,
			':housing_spots_available' => (int) $this->housing_spots_available,
			':housing_nights'          => (int) $this->housing_nights,
			':housing_gender'          => (int) $this->housing_gender,
			':housing_bedtime'         => (int) $this->housing_bedtime,
			':housing_pets'            => (int) $this->housing_pets,
			':housing_smoke'           => (int) $this->housing_smoke,
			':housing_from_scene'      => (string) $this->housing_from_scene,
			':housing_comment'         => (string) $this->housing_comment,
			));
	}
	
	public function add_housing_provider(array $parameters, $event_id)
	{
		self::$database->query('INSERT %1$s_housing_providers VALUES (:event_id, :dancer_id, :available, :smoking, :pets, :gender, :nights, :comment)', array(
			':event_id'  => $event_id,
			':dancer_id' => $this->id,
			':available' => $parameters['housing_provider_available'],
			':smoking'   => $parameters['housing_provider_smoking'],
			':pets'      => $parameters['housing_provider_pets'],
			':gender'    => $parameters['housing_provider_gender'],
			':nights'    => $parameters['housing_provider_nights'],
			':comment'   => $parameters['housing_provider_comment'],
			));
	}
	
	public function add_housing_needed(array $parameters, $event_id)
	{
		self::$database->query('INSERT %1$s_housing_needed VALUES (:event_id, :dancer_id, :no_smoking, :no_pets, :gender, :nights, :comment)', array(
			':event_id'   => $event_id,
			':dancer_id'  => $this->id,
			':no_smoking' => $parameters['housing_needed_no_smoking'],
			':no_pets'    => $parameters['housing_needed_no_pets'],
			':gender'     => $parameters['housing_needed_gender'],
			':nights'     => $parameters['housing_needed_nights'],
			':comment'    => $parameters['housing_needed_comment'],
			));
	}
	
	public function delete()
	{
	    $statement = self::$database->query('DELETE FROM %1$s_dancers WHERE event_id = :event_id AND id = :id LIMIT 1', array(':event_id' => self::$event->get_id(), ':id' => $this->id));
	    $counts['dancer'] = $statement->rowCount();
	    
	    $statement = self::$database->query('DELETE FROM %1$s_registrations WHERE event_id = :event_id AND dancer_id = :dancer_id', array(':event_id' => self::$event->get_id(), ':dancer_id' => $this->id));
	    $counts['registrations'] = $statement->rowCount();
	    
	    $statement = self::$database->query('DELETE FROM %1$s_housing_needed WHERE event_id = :event_id AND dancer_id = :dancer_id LIMIT 1', array(':event_id' => self::$event->get_id(), ':dancer_id' => $this->id));
	    $counts['housing_needed'] = $statement->rowCount();
	    
	    $statement = self::$database->query('DELETE FROM %1$s_housing_providers WHERE event_id = :event_id AND dancer_id = :dancer_id LIMIT 1', array(':event_id' => self::$event->get_id(), ':dancer_id' => $this->id));
	    $counts['housing_provider'] = $statement->rowCount();
	    
	    return $counts;
	}
	
	public function update_payment_confirmation($payment_confirmed, $payment_owed)
	{
		$this->payment_confirmed = $payment_confirmed;
		$this->payment_owed = $payment_owed;
		
		$statement = self::$database->query('UPDATE %1$s_dancers SET payment_confirmed = :payment_confirmed, payment_owed = :payment_owed WHERE event_id = :event_id AND id = :id LIMIT 1', array(':event_id' => $this->event_id, ':id' => $this->id, ':payment_confirmed' => $payment_confirmed, ':payment_owed' => $payment_owed));
		return (bool) $statement->rowCount();
	}
	
	public function get_id()
	{
		return (int) $this->id;
	}
	
	public function get_name()
	{
		return sprintf('%s %s', $this->first_name, $this->last_name);
	}
	
	public function get_name_last_first()
	{
		return sprintf('%s, %s', $this->last_name, $this->first_name);
	}
	
	public function get_first_name()
	{
		return $this->first_name;
	}
	
	public function get_last_name()
	{
		return $this->last_name;
	}
	
	public function get_date_mail_postmark_by($number_days, $format = false)
	{
		$timestamp = strtotime(sprintf('+%d days', $number_days), $this->date_registered);
		
		$day_of_week = date('N', $timestamp);
		
		if ($day_of_week == 7) {
			$timestamp = strtotime('+1 day', $timestamp);
		}
		elseif ($day_of_week == 6) {
			$timestamp = strtotime('+2 days', $timestamp);
		}
		
		return ($format === false) ? (int) $timestamp : date($format, $timestamp);
	}
	
	public function get_date_paypal_payment_by($number_days, $format = false)
	{
		$timestamp = strtotime(sprintf('+%d days', $number_days), $this->date_registered);
		
		return ($format === false) ? (int) $timestamp : date($format, $timestamp);
	}
	
	public function get_date_registered($format = false)
	{
		return ($format === false) ? (int) $this->date_registered : date($format, $this->date_registered);
	}
	
	public function get_email()
	{
		return $this->email;
	}
	
	public function get_housing_bedtime()
	{
		switch ($this->housing_bedtime) {
			case 1:
				return __('Early Bird', 'nsevent');
			
			case 2:
				return __('Night Owl', 'nsevent');
			
			default:
				return __('No Preference', 'nsevent');
		}
	}
	
	public function get_housing_comment()
	{
		return $this->housing_comment;
	}
	
	public function get_housing_for_night_by_index($night_id)
	{
		if (!isset($this->housing_nights_array)) {
			$this->housing_nights_array = array_filter(self::bit_field($this->housing_nights, NSEvent_Model_Event::$possible_housing_nights, 'booleans'));
		}
		
		return isset($this->housing_nights_array[$night_id]) ? $this->housing_nights_array[$night_id] : false;
	}
	
	public function get_housing_from_scene()
	{
		return $this->housing_from_scene;
	}
	
	public function get_housing_gender()
	{
		switch ($this->housing_gender) {
			case 1:
				return __('Boys', 'nsevent');
			
			case 2:
				return __('Girls', 'nsevent');
			
			default:
				return __('Boys, Girls', 'nsevent');
		}
	}
	
	public function get_housing_nights(array $event_nights)
	{
		return isset($this->housing_nights) ? self::bit_field($this->housing_nights, $event_nights, 'string') : false;
	}
	
	public function get_housing_spots_available()
	{
		return (int) $this->housing_spots_available;
	}
	
	public function get_housing_has_pets()
	{
		return ($this->housing_type == 2 and $this->housing_pets == 1);
	}
	
	public function get_housing_has_smoke()
	{
		return ($this->housing_type == 2 and $this->housing_smoke == 1);
	}
	
	public function get_housing_prefers_no_pets()
	{
		return ($this->housing_type == 1 and $this->housing_pets == 1);
	}
	
	public function get_housing_prefers_no_smoke()
	{
		return ($this->housing_type == 1 and $this->housing_smoke == 1);
	}
	
	public function get_housing_type()
	{
		switch ($this->housing_type) {
			case 1:
				return __('Housing Needed', 'nsevent');
			
			case 2:
				return __('Housing Provider', 'nsevent');
			
			default:
				return false;
		}
	}
	
	public function get_level()
	{
		return (int) $this->level;
	}
	
	public function get_mobile_phone()
	{
		return $this->mobile_phone;
	}
	
	public function get_payment_confirmed()
	{
		return (bool) $this->payment_confirmed;
	}
	
	public function get_payment_method()
	{
		return $this->payment_method;
	}
	
	public function get_payment_owed()
	{
		return (int) $this->payment_owed;
	}
	
	public function get_position()
	{
		switch ($this->position) {
			case 1:
				return __('Lead', 'nsevent');
			
			case 2:
				return __('Follow', 'nsevent');
			
			default:
				return false;
		}
	}
	
	public function get_price_for_registered_item($item_id)
	{
		if (!isset($this->registered_items)) {
			$this->get_registered_items();
		}
		
		return array_key_exists($item_id, $this->registered_items) ? $this->registered_items[$item_id]->get_registered_price() : false;
	}
	
	
	public function get_price_total()
	{
		if (!isset($this->price_total)) {
			$this->price_total = self::$database->query('SELECT SUM(price) FROM %1$s_registrations WHERE event_id = :event_id AND dancer_id = :dancer_id', array(':event_id' => $this->event_id, ':dancer_id' => $this->id))->fetchColumn();
		}
		
		return ($this->price_total !== false) ? (int) $this->price_total : false;
	}
	
	public function get_registered_items($item_id = false)
	{
		if (!isset($this->registered_items))
		{
			$this->registered_items = array();
			
			$registered_items = self::$database->query('SELECT %1$s_items.*, %1$s_registrations.`price` as registered_price, %1$s_registrations.`item_meta` as registered_meta FROM %1$s_registrations LEFT JOIN %1$s_items ON %1$s_registrations.`item_id` = %1$s_items.`id` WHERE %1$s_registrations.`event_id` = :event_id AND dancer_id = :dancer_id', array(':event_id' => $this->event_id, ':dancer_id' => $this->id))->fetchAll(PDO::FETCH_CLASS, 'NSEvent_Model_Item');
			
			foreach ($registered_items as $item) {
				$this->registered_items[$item->get_id()] = $item;
			}
		}
		
		if (is_array($item_id)) {
			$result = array();
			
			foreach ($this->registered_items as $item) {
				if (in_array($item->get_id(), $item_id)) {
					$result[$item->get_id()] = $item;
				}
			}
			
			return $result;
		}
		elseif ($item_id === false) {
			return $this->registered_items;
		}
		elseif (array_key_exists($item_id, $this->registered_items)) {
			return $this->registered_items[$item_id];
		}
		else {
			return array();
		}
	}
	
	public function is_housing_provider()
	{
		return ($this->housing_type == 2);
	}
	
	public function is_volunteer()
	{
		return ($this->status === '1');
	}
	
	public function is_vip()
	{
		return ($this->status === '2');
	}
	
	public function needs_housing()
	{
		return ($this->housing_type == 1);
	}
	
	public function received_discount()
	{
		return ($this->payment_discount == 1 and $this->status != 2);
	}
}

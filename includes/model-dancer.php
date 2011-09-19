<?php

class NSEvent_Model_Dancer extends NSEvent_Model
{
	private $event_id,
	        $dancer_id,
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
	        $registered_package_id,
	    	$status;
	
	public function __construct(array $parameters = array())
	{
		foreach ($parameters as $key => $value) {
			$this->$key = $value;
		}
	}
	
	public function __toString()
	{
		return sprintf('%s %s [#%d]', $this->first_name, $this->last_name, $this->dancer_id);
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
		
		$this->dancer_id = self::$database->lastInsertID();
	}
	
	public function add_housing()
	{
		self::$database->query('INSERT %1$s_housing VALUES (:event_id, :dancer_id, :housing_type, :housing_spots_available, :housing_nights, :housing_gender, :housing_bedtime, :housing_pets, :housing_smoke, :housing_from_scene, :housing_comment)', array(
			':event_id'                => $this->event_id,
			':dancer_id'               => $this->dancer_id,
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
			':dancer_id' => $this->dancer_id,
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
			':dancer_id'  => $this->dancer_id,
			':no_smoking' => $parameters['housing_needed_no_smoking'],
			':no_pets'    => $parameters['housing_needed_no_pets'],
			':gender'     => $parameters['housing_needed_gender'],
			':nights'     => $parameters['housing_needed_nights'],
			':comment'    => $parameters['housing_needed_comment'],
			));
	}
	
	public function delete()
	{
	    $statement = self::$database->query('DELETE FROM %1$s_dancers WHERE event_id = :event_id AND dancer_id = :dancer_id LIMIT 1', array(':event_id' => $this->event_id, ':dancer_id' => $this->dancer_id));
	    $counts['dancer'] = $statement->rowCount();
	    
	    $statement = self::$database->query('DELETE FROM %1$s_registrations WHERE event_id = :event_id AND dancer_id = :dancer_id', array(':event_id' => $this->event_id, ':dancer_id' => $this->dancer_id));
	    $counts['registrations'] = $statement->rowCount();
	    
	    $statement = self::$database->query('DELETE FROM %1$s_housing WHERE event_id = :event_id AND dancer_id = :dancer_id LIMIT 1', array(':event_id' => $this->event_id, ':dancer_id' => $this->dancer_id));
	    $counts['housing'] = $statement->rowCount();
	    	    
	    return $counts;
	}
	
	public function update_payment_confirmation($payment_confirmed, $payment_owed)
	{
		$this->payment_confirmed = $payment_confirmed;
		$this->payment_owed = $payment_owed;
		
		$statement = self::$database->query('UPDATE %1$s_dancers SET payment_confirmed = :payment_confirmed, payment_owed = :payment_owed WHERE event_id = :event_id AND dancer_id = :dancer_id LIMIT 1', array(':event_id' => $this->event_id, ':dancer_id' => $this->dancer_id, ':payment_confirmed' => $payment_confirmed, ':payment_owed' => $payment_owed));
		return (bool) $statement->rowCount();
	}
	
	public function id()
	{
		return (int) $this->dancer_id;
	}
	
	public function name()
	{
		return sprintf('%s %s', $this->first_name, $this->last_name);
	}
	
	public function name_last_first()
	{
		return sprintf('%s, %s', $this->last_name, $this->first_name);
	}
	
	public function first_name()
	{
		return $this->first_name;
	}
	
	public function last_name()
	{
		return $this->last_name;
	}
		
	public function date_postmark_by($format = false)
	{
		$timestamp = strtotime(sprintf('+%d days', self::$options['postmark_within']), $this->date_registered);
		
		return ($format === false) ? (int) $timestamp : date($format, $timestamp);
	}
	
	public function date_registered($format = false)
	{
		return ($format === false) ? (int) $this->date_registered : date($format, $this->date_registered);
	}
	
	public function email()
	{
		return $this->email;
	}
	
	public function housing_bedtime()
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
	
	public function housing_comment()
	{
		return $this->housing_comment;
	}
	
	public function housing_for_night_by_index($night_id)
	{
		if (!isset($this->housing_nights_array)) {
			$this->housing_nights_array = array_filter(self::bit_field($this->housing_nights, NSEvent_Model_Event::$possible_housing_nights, 'booleans'));
		}
		
		return isset($this->housing_nights_array[$night_id]) ? $this->housing_nights_array[$night_id] : false;
	}
	
	public function housing_from_scene()
	{
		return $this->housing_from_scene;
	}
	
	public function housing_gender()
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
	
	public function housing_nights(array $event_nights)
	{
		return isset($this->housing_nights) ? self::bit_field($this->housing_nights, $event_nights, 'string') : false;
	}
	
	public function housing_spots_available()
	{
		return (int) $this->housing_spots_available;
	}
	
	public function housing_has_pets()
	{
		return ($this->housing_type == 2 and $this->housing_pets == 1);
	}
	
	public function housing_has_smoke()
	{
		return ($this->housing_type == 2 and $this->housing_smoke == 1);
	}
	
	public function housing_prefers_no_pets()
	{
		return ($this->housing_type == 1 and $this->housing_pets == 1);
	}
	
	public function housing_prefers_no_smoke()
	{
		return ($this->housing_type == 1 and $this->housing_smoke == 1);
	}
	
	public function housing_type()
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
	
	public function level()
	{
		return (int) $this->level;
	}
	
	public function mailto()
	{
		return 'mailto:' . rawurlencode(sprintf('%s %s <%s>', $this->first_name, $this->last_name, $this->email));
	}
	
	public function mobile_phone()
	{
		return $this->mobile_phone;
	}
	
	public function payment_confirmed()
	{
		return (bool) $this->payment_confirmed;
	}
	
	public function payment_discount()
	{
		return (int) $this->payment_discount;
	}
	
	public function payment_method()
	{
		return $this->payment_method;
	}
	
	public function payment_owed()
	{
		return (int) $this->payment_owed;
	}
	
	public function paypal_href()
	{
		$href = sprintf('https://%1$s/cgi-bin/webscr?cmd=_cart&upload=1&no_shipping=1&business=%2$s&custom=%3$d',
			!self::$options['paypal_sandbox'] ? 'www.paypal.com' : 'www.sandbox.paypal.com',
			rawurlencode(self::$options['paypal_business']),
			$this->dancer_id);
		
		if (!empty($options['paypal_fee']) and !$dancer->is_vip()) {
			$href .= sprintf('&item_name_1=%1$s&amount_1=%2$s', 'Processing%20Fee', $options['paypal_fee']);
			$i = 2;
		}
		else {
			$i = 1;
		}
		
		foreach ($this->registered_items() as $item) {
			if ($item->registered_price() == 0) {
				continue;
			}
			
			$href .= sprintf('&item_name_%1$d=%2$s&amount_%1$d=%3$s', $i, rawurlencode($item->name()), rawurlencode($item->registered_price()));
			
			if ($item->meta() == 'size') {
				$href .= sprintf('&on0_%1$d=%2$s&os0_%1$d=%3$s', $i, $item->meta_label(), ucfirst($item->registered_meta()));
			}
			
			$i++;
		}
		
		return $href;
	}
	
	public function position()
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
	
	public function price_for_registered_item($item_id)
	{
		if (!isset($this->registered_items)) {
			$this->registered_items();
		}
		
		return array_key_exists($item_id, $this->registered_items) ? $this->registered_items[$item_id]->registered_price() : false;
	}
	
	
	public function price_total()
	{
		if (!isset($this->price_total)) {
			$this->price_total = self::$database->query('SELECT SUM(price) FROM %1$s_registrations WHERE event_id = :event_id AND dancer_id = :dancer_id', array(':event_id' => $this->event_id, ':dancer_id' => $this->dancer_id))->fetchColumn();
		}
		
		return ($this->price_total !== false) ? (int) $this->price_total : false;
	}
	
	public function registered_items($item_id = false)
	{
		if (!isset($this->registered_items))
		{
			$this->registered_items = array();
			
			$registered_items = self::$database->query('SELECT %1$s_items.*, %1$s_registrations.`price` as registered_price, %1$s_registrations.`item_meta` as registered_meta FROM %1$s_registrations LEFT JOIN %1$s_items USING(item_id) WHERE %1$s_registrations.`event_id` = :event_id AND dancer_id = :dancer_id', array(':event_id' => $this->event_id, ':dancer_id' => $this->dancer_id))->fetchAll(PDO::FETCH_CLASS, 'NSEvent_Model_Item');
			
			foreach ($registered_items as $item) {
				$this->registered_items[$item->id()] = $item;
			}
		}
		
		if (is_array($item_id)) {
			$result = array();
			
			foreach ($this->registered_items as $item) {
				if (in_array($item->id(), $item_id)) {
					$result[$item->id()] = $item;
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
	
	public function registered_package_id()
	{
		if (!isset($this->registered_package_id)) {
			$this->registered_package_id = self::$database->query('SELECT %1$s_registrations.`item_id` FROM %1$s_registrations LEFT JOIN %1$s_items USING(item_id) WHERE %1$s_registrations.`event_id` = :event_id AND dancer_id = :dancer_id AND %1$s_items.`type` = "package"', array(':event_id' => $this->event_id, ':dancer_id' => $this->dancer_id))->fetchColumn();
		}
		
		return ($this->registered_package_id !== false) ? (int) $this->registered_package_id : false;
	}
	
	public function is_housing_provider()
	{
		return ($this->housing_type == 2);
	}
	
	public function is_overdue_for_payment()
	{
		return (self::$options['postmark_within'] and !$this->payment_confirmed and  time() > strtotime(sprintf('+%d days', self::$options['postmark_within']), $this->date_registered));
	}
	
	public function is_volunteer()
	{
		return ($this->status == 1);
	}
	
	public function is_vip()
	{
		return ($this->status == 2);
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

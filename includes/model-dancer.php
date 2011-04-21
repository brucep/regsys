<?php

class NSEvent_Model_Dancer extends NSEvent_Model
{
	private $event_id,
	        $id,
	        $first_name,
	        $last_name,
	        $email,
	        $date_registered,
	        $housing_nights_array,
	        $housing_type,
	        $level,
	        $note,
	        $position,
	        $payment_confirmed,
	        $payment_discount,
	        $payment_method,
	        $payment_owed,
	        $price_total,
	        $registered_items,
	    	$status,
	        $volunteer_phone;
	
	public $available, $gender, $nights, $pets, $smoking, $no_pets, $no_smoking, $comment; # Housing
	
	public static $possible_housing_genders = array(1 => 'Boys', 2 => 'Girls');
	
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
		self::$database->query('INSERT %s_dancers VALUES (:event_id, NULL, :first_name, :last_name, :email, :position, :level, :status, :date_registered, :payment_method, :payment_discount, DEFAULT, DEFAULT, :volunteer_phone, :note)', array(
			':event_id'          => $event_id,
			':first_name'        => $this->first_name,
			':last_name'         => $this->last_name,
			':email'             => $this->email,
			':position'          => $this->position,
			':level'             => $this->level,
			':status'            => $this->status,
			':date_registered'   => time(),
			':payment_method'    => $this->payment_method,
			':payment_discount'  => $this->payment_discount,
			':volunteer_phone'   => $this->volunteer_phone,
			':note'              => (string) $this->note,
			));
		
		$this->event_id = $event_id;
		$this->id = self::$database->lastInsertID();
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
		
	public function get_date_registered($format = false)
	{
		return ($format === false) ? (int) $this->date_registered : date($format, $this->date_registered);
	}
	
	public function get_email()
	{
		return $this->email;
	}
	
	public function get_housing_gender()
	{
		return isset($this->gender) ? self::bit_field($this->gender, self::$possible_housing_genders, 'string') : false;
	}
	
	public function get_housing_for_night_by_index($night_id)
	{
		if (!isset($this->housing_nights_array)) {
			$this->housing_nights_array = array_filter(self::bit_field($this->nights, NSEvent_Model_Event::$possible_housing_nights, 'booleans'));
		}
		
		return isset($this->housing_nights_array[$night_id]) ? $this->housing_nights_array[$night_id] : false;
	}
	
	public function get_housing_nights(array $event_nights)
	{
		return isset($this->nights) ? self::bit_field($this->nights, $event_nights, 'string') : false;
	}
	
	public function get_housing_type()
	{
		return $this->housing_type;
	}
	
	public function get_level()
	{
		return (int) $this->level;
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
	
	public function get_volunteer_phone()
	{
		return $this->volunteer_phone;
	}
	
	public function is_volunteer()
	{
		return ($this->status === '1');
	}
	
	public function is_vip()
	{
		return ($this->status === '2');
	}
}

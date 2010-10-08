<?php

class NSEvent_Dancer extends NSEvent_Model
{
	public $first_name, $last_name, $email, $date_registered, $position, $housing_nights_array;
	private $level, $status, $registrations, $total_cost;
	public static $possible_housing_genders = array(1 => 'Boys', 2 => 'Girls');
	
	public function __construct(array $parameters = array())
	{
		foreach($parameters as $key => $value)
			$this->$key = $value;
	}
	
	public static function find_all()
	{
		$statement = self::$database->query('SELECT * FROM %s_dancers WHERE event_id = :event_id ORDER BY last_name ASC, first_name ASC, date_registered ASC', array(':event_id' => self::$event->id));
		return $statement->fetchAll(PDO::FETCH_CLASS, 'NSEvent_Dancer');
	}
	
	public static function find_by($field, $value)
	{
		$statement = self::$database->query('SELECT * FROM %s_dancers WHERE event_id = :event_id AND `'.$field.'` = :value ORDER BY last_name ASC, first_name ASC, date_registered ASC', array(':event_id' => self::$event->id, ':value' => $value));
		return $statement->fetchAll(PDO::FETCH_CLASS, 'NSEvent_Dancer');
	}
	
	public static function find($id)
	{
		$statement = self::$database->query('SELECT * FROM %s_dancers WHERE event_id = :event_id AND id = :id', array(':event_id' => self::$event->id, ':id' => $id));
		return $statement->fetchObject('NSEvent_Dancer');
	}
	
	public static function count($field = Null, $value = Null)
	{
		if ($field == Null)
			$statement = self::$database->query('SELECT COUNT(id) FROM %s_dancers WHERE event_id = :event_id', array(':event_id' => self::$event->id));
		else
			$statement = self::$database->query('SELECT COUNT(id) FROM %s_dancers WHERE event_id = :event_id AND `'.$field.'` = :value', array(':event_id' => self::$event->id, ':value' => $value));
		
		$result = $statement->fetchColumn();
		return ($result !== False) ? (int) $result : False;
	}
	
	/*public static function is_already_registered($first_name, $last_name, $email)
	{
		$statement = self::$database->query('SELECT COUNT(id) FROM %s_dancers WHERE event_id = :event_id AND first_name = :first_name AND last_name = :last_name AND email = :email LIMIT 1', array(':event_id' => self::$event->id));
		return (bool) $statement->fetchColumn();
	}*/
	
	public static function add(array $parameters)
	{ // TODO: VIPs who don't owe anything should be marked as paid
		self::$database->query('INSERT %s_dancers VALUES (:event_id, NULL, :first_name, :last_name, :email, :position, :level, :status, :date_registered, :payment_method, :payment_discount, DEFAULT, DEFAULT, :note)', array(
			':event_id'          => self::$event->id,
			':first_name'        => $parameters['first_name'],
			':last_name'         => $parameters['last_name'],
			':email'             => $parameters['email'],
			':position'          => $parameters['position'],
			':level'             => !isset($parameters['level']) ? '1' : $parameters['level'],
			':status'            => $parameters['status'],
			':date_registered'   => time(),
			':payment_method'    => $parameters['payment_method'],
			':payment_discount'  => $parameters['discount'],
			':note'              => !isset($parameters['note']) ? '' : $parameters['note'],
			));
		
		return self::find(self::$database->lastInsertID());
	}
	
	public static function count_housing_available()
	{
		return self::$database->query('SELECT SUM(available) FROM %1$s_housing_providers WHERE event_id = :event_id', array(':event_id' => self::$event->id))->fetchColumn();
	}
	
	public static function count_housing_needed()
	{
		return self::$database->query('SELECT COUNT(dancer_id) FROM %1$s_housing_needed WHERE event_id = :event_id', array(':event_id' => self::$event->id))->fetchColumn();
	}
	
	public static function get_housing_providers()
	{
		$statement = self::$database->query('SELECT * FROM %1$s_housing_providers LEFT JOIN %1$s_dancers ON id = dancer_id WHERE %1$s_housing_providers.`event_id` = :event_id ORDER BY last_name ASC, first_name ASC', array(':event_id' => self::$event->id));
		return $statement->fetchAll(PDO::FETCH_CLASS, 'NSEvent_Dancer');
	}
	
	public static function get_housing_needed()
	{
		$statement = self::$database->query('SELECT * FROM %1$s_housing_needed LEFT JOIN %1$s_dancers ON id = dancer_id WHERE %1$s_housing_needed.`event_id` = :event_id ORDER BY last_name ASC, first_name ASC', array(':event_id' => self::$event->id));
		return $statement->fetchAll(PDO::FETCH_CLASS, 'NSEvent_Dancer');
	}
	
	public function add_housing_provider(array $parameters)
	{
		self::$database->query('INSERT %1$s_housing_providers VALUES (:event_id, :dancer_id, :available, :smoking, :pets, :gender, :nights, :comment)', array(
			':event_id'  => self::$event->id,
			':dancer_id' => $this->id,
			':available' => $parameters['housing_provider_available'],
			':smoking'   => $parameters['housing_provider_smoking'],
			':pets'      => $parameters['housing_provider_pets'],
			':gender'    => $parameters['housing_provider_gender'],
			':nights'    => $parameters['housing_provider_nights'],
			':comment'   => $parameters['housing_provider_comment'],
			));
	}
	
	public function add_housing_needed(array $parameters)
	{
		self::$database->query('INSERT %1$s_housing_needed VALUES (:event_id, :dancer_id, :car, :no_smoking, :no_pets, :gender, :nights, :comment)', array(
			':event_id'   => self::$event->id,
			':dancer_id'  => $this->id,
			':car'        => $parameters['housing_needed_car'],
			':no_smoking' => $parameters['housing_needed_no_smoking'],
			':no_pets'    => $parameters['housing_needed_no_pets'],
			':gender'     => $parameters['housing_needed_gender'],
			':nights'     => $parameters['housing_needed_nights'],
			':comment'    => $parameters['housing_needed_comment'],
			));
	}
	
	public function delete()
	{
	    $statement = self::$database->query('DELETE FROM %1$s_dancers WHERE event_id = :event_id AND id = :id LIMIT 1', array(':event_id' => self::$event->id, ':id' => $this->id));
	    $counts['dancer'] = $statement->rowCount();
	    
	    $statement = self::$database->query('DELETE FROM %1$s_registrations WHERE event_id = :event_id AND dancer_id = :dancer_id', array(':event_id' => self::$event->id, ':dancer_id' => $this->id));
	    $counts['registrations'] = $statement->rowCount();
	    
	    $statement = self::$database->query('DELETE FROM %1$s_housing_needed WHERE event_id = :event_id AND dancer_id = :dancer_id LIMIT 1', array(':event_id' => self::$event->id, ':dancer_id' => $this->id));
	    $counts['housing_needed'] = $statement->rowCount();
	    
	    $statement = self::$database->query('DELETE FROM %1$s_housing_providers WHERE event_id = :event_id AND dancer_id = :dancer_id LIMIT 1', array(':event_id' => self::$event->id, ':dancer_id' => $this->id));
	    $counts['housing_provider'] = $statement->rowCount();
	    
	    return $counts;
	}
	
	public function update_payment_confirmation($payment_confirmed, $amount_owed)
	{
		$this->payment_confirmed = $payment_confirmed;
		$this->amount_owed = $amount_owed;
		
		$statement = self::$database->query('UPDATE %1$s_dancers SET payment_confirmed = :payment_confirmed, amount_owed = :amount_owed WHERE event_id = :event_id AND id = :id LIMIT 1', array(':event_id' => self::$event->id, ':id' => $this->id, ':payment_confirmed' => $payment_confirmed, ':amount_owed' => $amount_owed));
		return (bool) $statement->rowCount();
	}
	
	public function populate_housing_info()
	{
		$statement = self::$database->query('SELECT car, no_smoking, no_pets, gender, nights, comment FROM %1$s_housing_needed WHERE event_id = :event_id AND dancer_id = :dancer_id', array(':event_id' => self::$event->id, ':dancer_id' => $this->id));
		$housing_info = $statement->fetch(PDO::FETCH_ASSOC);
		
		if ($housing_info)
			$housing_info['housing_type'] = __('Housing Needed', 'nsevent');
		else
		{
			$statement = self::$database->query('SELECT available, smoking, pets, gender, nights, comment FROM %1$s_housing_providers WHERE event_id = :event_id AND dancer_id = :dancer_id', array(':event_id' => self::$event->id, ':dancer_id' => $this->id));
			$housing_info = $statement->fetch(PDO::FETCH_ASSOC);
			
			if ($housing_info)
				$housing_info['housing_type'] = __('Housing Provider', 'nsevent');
		}
		
		if ($housing_info)
		{
			foreach ($housing_info as $key => $value)
				$this->$key = $value;

			return True;
		}
		else
			return False;
		
	}
	
	public function name($last_name_first = False)
	{
		if ($last_name_first !== True)
			return $this->first_name.' '.$this->last_name;
		else
			return $this->last_name.', '.$this->first_name;
	}
	
	public function position()
	{
		switch ($this->position)
		{
			case 1:
				return "Lead";
			case 2:
				return "Follow";
			default:
				return False;
		}
	}
	
	public function level()
	{
		return self::$event->levels($this->level);
	}
	
	public function is_volunteer()
	{
		return ($this->status == 1);
	}
	
	public function is_vip()
	{
		return ($this->status == 2);
	}
	
	public function registrations($item_id = False)
	{
		if (!isset($this->registrations))
		{
			$registrations = NSEvent_Registration::find_by('dancer_id', $this->id);
			if (!$registrations)
				return array();
			
			foreach($registrations as $reg)
				$this->registrations[$reg->item_id] = $reg;
		}
		
		if (is_array($item_id) and !empty($item_id))
		{
			$result = array();
			
			foreach ($this->registrations as $reg)
				if (in_array($reg->item_id, $item_id))
					$result[$reg->item_id] = $reg;
			
			return $result;
		}
		elseif (empty($item_id))
			return $this->registrations;
		elseif (isset($this->registrations[$item_id]))
			return $this->registrations[$item_id];
		else
			return False;
	}
	
	public function cost_for_registered_item($item_id)
	{
		if (!isset($this->registrations))
			$this->registrations();
		
		return isset($this->registrations[$item_id]) ? $this->registrations[$item_id]->price : False;
	}
	
	public function total_cost()
	{
		if (!isset($this->total_cost))
			$this->total_cost = self::$database->query('SELECT SUM(price) FROM %1$s_registrations WHERE event_id = :event_id AND dancer_id = :dancer_id', array(':event_id' => self::$event->id, ':dancer_id' => $this->id))->fetchColumn();
		
		return (int) $this->total_cost;
	}
	
	public function total_cost_after_paypal()
	{
		if (!isset($this->total_cost))
			$this->total_cost();
	}
	
	public function housing_gender()
	{
		if (isset($this->gender))
			return self::bit_field($this->gender, self::$possible_housing_genders, 'string');
		else
			return False;
	}
	
	public function housing_nights()
	{
		if (isset($this->nights))
			return self::bit_field($this->nights, self::$event->nights(), 'string');
		else
			return False;
	}
	
	public function housing_check_night($night_id)
	{
		if (!isset($this->housing_nights_array))
			$this->housing_nights_array = array_filter(self::bit_field($this->nights, NSEvent_Event::$possible_nights, 'booleans'));
		
		return $this->housing_nights_array[$night_id];
	}
}

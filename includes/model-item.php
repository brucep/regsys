<?php

class NSEvent_Item extends NSEvent_Model
{
	protected $registered_dancers, $openings;
	
	public static function find_all()
	{
		$statement = self::$database->query('SELECT * FROM %1$s_items WHERE event_id = :event_id', array(':event_id' => self::$event->id));
		return $statement->fetchAll(PDO::FETCH_CLASS, 'NSEvent_Item');
	}
	
	public static function find_by(array $where)
	{
		if (empty($where))
			return False;
		
		foreach($where as $field => $value)
			$query[] = sprintf(' `%1$s` = :%1$s', substr($field, 1));
		
		$query = implode(' AND', $query);
		$where[':event_id'] = self::$event->id;
		
		$statement = self::$database->query('SELECT * FROM %1$s_items WHERE event_id = :event_id AND'.$query, $where);
		return $statement->fetchAll(PDO::FETCH_CLASS, 'NSEvent_Item');
	}
	
	public static function find($id)
	{
		$statement = self::$database->query('SELECT * FROM %1$s_items WHERE event_id = :event_id AND id = :id', array(':event_id' => self::$event->id, ':id' => $id));
		return $statement->fetchObject('NSEvent_Item');
	}
	
	public function openings($position = False)
	{
		if (!isset($this->openings))
			if (!$this->limit_total and !$this->limit_per_position)
				$this->openings = True;
			else
			{
				if ($this->limit_total)
				{
					$limit = $this->limit_total;
					$number_dancers = NSEvent_Registration::count_for_item($this->id);
				}
				else if ($position === False)
				{
					$limit = $this->limit_per_position * 2;
					return $limit - NSEvent_Registration::count_for_item($this->id);
				}
				else
				{
					$limit = $this->limit_per_position;
					$number_dancers = NSEvent_Registration::count_for_item($this->id, $position);
				}
			
				if ($number_dancers !== False)
					if (($limit - $number_dancers) > 0)
						$openings = $limit - $number_dancers;
					else
						$openings = 0;
				else
					# If there was an error communicating with the database, assume there are no more openings.
					$openings = False;
				
				if ($position === False)
					$this->openings = $openings;
				else
					return $openings;
			}
		
		return $this->openings;
	}
	
	public function get_price_for_discount($discount, $early = False)
	{
		if ($discount === 'vip') {
			$price = $this->price_vip;
		}
		elseif ($early) {
			if ($discount === 0) {
				$price = $this->price_early;
			}
			elseif ($discount === 1) {
				$price = $this->price_early_discount1;
			}
			elseif ($discount === 2) {
				$price = $this->price_early_discount2;
			}
		}
		else {
			if ($discount === 0) {
				$price = $this->price_prereg;
			}
			elseif ($discount === 1) {
				$price = $this->price_prereg_discount1;
			}
			elseif ($discount === 2) {
				$price = $this->price_prereg_discount2;
			}
		}
		
		if (isset($price)) {
		 	return (int) $price;
		}
		else {
		 	throw new Exception(__('Unable to get the price for the specified discount.', 'nsevent'));
		}
	}
	
	public function meta_label()
	{
		switch ($this->has_meta)
		{
			case 'position':
				return __('Position', 'nsevent');
			
			case 'partner_name':
				return __('Partner', 'nsevent');
				
			case 'team_members':
				return __('Team Members', 'nsevent');
			
			case 'size':
				return __('Size', 'nsevent');
			
			default:
				return False;
		}
	}
	
	public function total_money_from_registrations()
	{
		return self::$database->query('SELECT SUM(price) FROM %1$s_registrations WHERE %1$s_registrations.`event_id` = :event_id AND item_id = :item_id', array(':event_id' => self::$event->id, ':item_id' => $this->id))->fetchColumn();
	}
	
	public function registered_dancers()
	{
		if (!isset($this->registered_dancers))
		{
			$order_by = ($this->has_meta != 'position') ? '' : 'item_meta DESC, ';
			$order_by .= 'last_name ASC, first_name ASC';
			
			$statement = self::$database->query('SELECT %1$s_dancers.*, %1$s_registrations.`item_meta` FROM %1$s_registrations LEFT JOIN %1$s_dancers ON id = dancer_id WHERE %1$s_registrations.`event_id` = :event_id AND item_id = :item_id ORDER BY '.$order_by, array(':event_id' => self::$event->id, ':item_id' => $this->id));
			$this->registered_dancers = $statement->fetchAll(PDO::FETCH_CLASS, 'NSEvent_Dancer');
		}
		
		return $this->registered_dancers;
	}
}

<?php

class NSEvent_Model_Item extends NSEvent_Model
{
	private $event_id,
	        $id,
	        $name,
	        $date_expires,
	        $description,
	        $limit_per_position,
	        $limit_total,
	        $meta,
	        $openings,
	        $preregistration,
	        $price_prereg,
	        $price_door,
	        $price_discount,
	        $price_scaled,
	        $price_tier,
	        $price_vip,
	        $registered_dancers,
	        $registered_meta,
	        $registered_price,
	        $type;
		
	public function __toString()
	{
		return sprintf('%s [#%d]', $this->name, $this->id);
	}
	
	public function get_id()
	{
		return (int) $this->id;
	}
	
	public function get_name()
	{
		return $this->name;
	}
	
	public function get_description()
	{
		return $this->description;
	}
	
	public function get_date_expires($format = false)
	{
		return ($format === false) ? (int) $this->date_expires : date($format, $this->date_expires);
	}
	
	public function get_limit_per_position()
	{
		return (int) $this->limit_per_position;
	}
	
	public function get_limit_total()
	{
		return (int) $this->limit_total;
	}
	
	public function get_meta()
	{
		return $this->meta;
	}
	
	public function get_meta_label()
	{
		switch ($this->meta) {
			case 'position':
				return __('Position', 'nsevent');
			
			case 'partner_name':
				return __('Partner', 'nsevent');
				
			case 'team_members':
				return __('Team Members', 'nsevent');
			
			case 'size':
				return __('Size', 'nsevent');
			
			default:
				return false;
		}
	}
	
	public function get_price_at_door()
	{
		return (int) $this->price_door;
	}
	
	public function get_price_for_prereg($discount = false)
	{
		if ($this->type != 'package') {
			$price = $this->price_prereg;
		}
		else {
			if (!isset($this->price_scaled)) {
				$number_dancers = self::$database->query('SELECT COUNT(dancer_id) FROM %1$s_registrations JOIN %1$s_items ON %1$s_registrations.`item_id` = %1$s_items.`id` JOIN %1$s_dancers ON %1$s_registrations.`dancer_id` = %1$s_dancers.`id` WHERE %1$s_registrations.`event_id` = :event_id AND %1$s_items.`id` = :item_id AND %1$s_dancers.`status` != 2', array(':event_id' => $this->event_id, ':item_id' => $this->id))->fetchColumn();
				
				$this->price_scaled = self::$database->query('SELECT scale_price FROM %1$s_item_prices WHERE event_id = :event_id AND item_id = :item_id AND :number_dancers <= scale_count ORDER BY scale_count ASC LIMIT 1', array(':event_id' => $this->event_id, ':item_id' => $this->id, ':number_dancers' => $number_dancers))->fetchColumn();
			}
			
			$price = !empty($this->price_scaled) ? $this->price_scaled : $this->price_prereg;
		}
		
		if ($discount) {
			$price = $price - $this->price_discount;
		}
		
		return (int) $price;
	}
	
	public function get_price_for_vip()
	{
		return (int) $this->price_vip;
	}
	
	public function get_price_tier()
	{
		if ($this->type != 'package') {
			return false;
		}
		else {
			if (!isset($this->price_tier)) {
				$number_dancers = self::$database->query('SELECT COUNT(dancer_id) FROM %1$s_registrations JOIN %1$s_items ON %1$s_registrations.`item_id` = %1$s_items.`id` JOIN %1$s_dancers ON %1$s_registrations.`dancer_id` = %1$s_dancers.`id` WHERE %1$s_registrations.`event_id` = :event_id AND %1$s_items.`id` = :item_id AND %1$s_dancers.`status` != 2', array(':event_id' => $this->event_id, ':item_id' => $this->id))->fetchColumn();
				
				$this->price_tier = (int) self::$database->query('SELECT scale_count FROM %1$s_item_prices WHERE event_id = :event_id AND item_id = :item_id AND :number_dancers <= scale_count ORDER BY scale_count ASC LIMIT 1', array(':event_id' => $this->event_id, ':item_id' => $this->id, ':number_dancers' => $number_dancers))->fetchColumn();
			}
			
			return $this->price_tier;
		}
	}
	
	public function get_registered_dancers()
	{
		if (!isset($this->registered_dancers)) {
			$order_by = ($this->meta != 'position') ? '' : 'item_meta DESC, ';
			$order_by .= 'last_name ASC, first_name ASC';
			
			$this->registered_dancers = self::$database->query('SELECT %1$s_dancers.*, %1$s_registrations.`item_meta` FROM %1$s_registrations LEFT JOIN %1$s_dancers ON id = dancer_id WHERE %1$s_registrations.`event_id` = :event_id AND item_id = :item_id ORDER BY '.$order_by, array(':event_id' => $this->event_id, ':item_id' => $this->id))->fetchAll(PDO::FETCH_CLASS, 'NSEvent_Model_Dancer');
		}
		
		return $this->registered_dancers;
	}
	
	public function get_registered_meta()
	{
		return $this->registered_meta;
	}
	
	public function get_registered_price()
	{
		return $this->registered_price;
	}
	
	public function get_total_money_from_registrations()
	{
		return self::$database->query('SELECT SUM(price) FROM %1$s_registrations WHERE %1$s_registrations.`event_id` = :event_id AND item_id = :item_id', array(':event_id' => $this->event_id, ':item_id' => $this->id))->fetchColumn();
	}
	
	public function get_type()
	{
		return $this->type;
	}
	
	public function is_expired()
	{
		return (!empty($this->date_expires) and time() > $this->date_expires);
	}
	
	public function count_openings($position = false)
	{
		if (!isset($this->openings)) {
			if (!$this->limit_total and !$this->limit_per_position) {
				$this->openings = true;
			}
			else {
				if ($this->limit_total) {
					$limit = $this->limit_total;
					$number_dancers = $this->count_registrations_where(array(':item_id' => $this->id));
				}
				elseif ($position === false) {
					$limit = $this->limit_per_position * 2;
					return $limit - $this->count_registrations_where(array(':item_id' => $this->id));
				}
				else {
					$limit = $this->limit_per_position;
					$number_dancers = $this->count_registrations_where(array(':item_id' => $this->id, ':position' => $position), 'dancers');
				}
			
				if ($number_dancers !== false) {
					$openings = (($limit - $number_dancers) > 0) ? $limit - $number_dancers : 0;
				}
				else
				{
					# If there was an error communicating with the database, assume there are no more openings.
					$openings = false;
				}
				
				if ($position === false) {
					$this->openings = $openings;
				}
				else {
					return $openings;
				}
			}
		}
		
		return $this->openings;
	}
	
	private function count_registrations_where(array $where = array(), $join_table = false)
	{
		$where[':item_id'] = $this->id;
		$query = array('%1$s_registrations.`event_id` = :event_id');
		
		foreach ($where as $field => $value) {
			$query[] = sprintf(' `%1$s` = :%1$s', substr($field, 1));
		}
		
		$query = ' WHERE '.implode(' AND', $query);
		$where[':event_id'] = $this->event_id;
		
		switch ($join_table) {
			case 'items':
				$query = ' JOIN %1$s_items ON %1$s_items.`id` = %1$s_registrations.`item_id`'.$query;
				break;
			
			case 'dancers':
				$query = ' JOIN %1$s_dancers ON %1$s_dancers.`id` = %1$s_registrations.`dancer_id`'.$query;
				break;
		}
		
		$result = self::$database->query('SELECT COUNT(*) FROM %1$s_registrations'.$query, $where)->fetchColumn();
		return ($result !== false) ? (int) $result : false;
	}
}

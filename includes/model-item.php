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
	        $note,
	        $openings,
	        $preregistration,
	        $price_early,
	        $price_early_discount1,
	        $price_early_discount2,
	        $price_prereg,
	        $price_prereg_discount1,
	        $price_prereg_discount2,
	        $price_door,
	        $price_door_discount1,
	        $price_door_discount2,
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
	
	public function get_price_for_discount($discount = false, $early = false)
	{
		if ($discount === 'vip') {
			$price = $this->price_vip;
		}
		elseif ($early === true) {
			if ($discount === 1) {
				$price = $this->price_early_discount1;
			}
			elseif ($discount === 2) {
				$price = $this->price_early_discount2;
			}
			else {
				$price = $this->price_early;
			}
		}
		elseif ($early === 'door') {
			if ($discount === 1) {
				$price = $this->price_door_discount1;
			}
			elseif ($discount === 2) {
				$price = $this->price_door_discount2;
			}
			else {
				$price = $this->price_door;
			}
		}
		else {
			if ($discount === 1) {
				$price = $this->price_prereg_discount1;
			}
			elseif ($discount === 2) {
				$price = $this->price_prereg_discount2;
			}
			else {
				$price = $this->price_prereg;
			}
		}
		
		if (isset($price)) {
		 	return (int) $price;
		}
		else {
		 	throw new Exception(__('Unable to get the price for the specified discount.', 'nsevent'));
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
		return ($this->date_expires and $this->date_expires <= time());
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
		$where = array_merge(array(':event_id' => $this->event_id, ':item_id' => $this->id), $where);
		$query = array();
		
		foreach ($where as $field => $value) {
			$query[] = sprintf(' `%1$s` = :%1$s', substr($field, 1));
		}
		
		$query = ' WHERE '.implode(' AND', $query);
		
		switch ($join_table) {
			case 'items':
				$query = 'JOIN %1$s_items ON %1$s_items.`id` = %1$s_registrations.`item_id`'.$query;
				break;
			
			case 'dancers':
				$query = 'JOIN %1$s_dancers ON %1$s_dancers.`id` = %1$s_registrations.`dancer_id`'.$query;
				break;
		}
		
		$result = self::$database->query('SELECT COUNT(event_id) FROM %1$s_registrations '.$query, $where)->fetchColumn();
		return ($result !== false) ? (int) $result : false;
	}
}

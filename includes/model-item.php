<?php

class RegistrationSystem_Model_Item extends RegistrationSystem_Model
{
	public  $name,
	        $description,
	        $meta,
	        $registered_meta,
	        $registered_price,
	        $type;
	
	private $event_id,
	        $item_id,
	        $count_registrations,
	        $count_registrations_by_position,
	        $date_expires,
	        $limit_per_position,
	        $limit_total,
	        $openings,
	        $preregistration,
	        $price_prereg,
	        $price_door,
	        $price_scaled,
	        $price_tier,
	        $price_vip,
	        $registered_dancers;
		
	public function __toString()
	{
		return sprintf('%s [#%d]', $this->name, $this->item_id);
	}
	
	public function id()
	{
		return (int) $this->item_id;
	}
	
	public function date_expires()
	{
		return (int) $this->date_expires;
	}
	
	public function limit_per_position()
	{
		return (int) $this->limit_per_position;
	}
	
	public function limit_total()
	{
		return (int) $this->limit_total;
	}
	
	public function meta_label()
	{
		switch ($this->meta) {
			case 'position':
				return 'Position';
			
			case 'partner_name':
				return 'Partner';
				
			case 'team_members':
				return 'Team Members';
			
			case 'size':
				return 'Size';
			
			default:
				return false;
		}
	}
	
	public function price_at_door()
	{
		return (int) $this->price_door;
	}
	
	public function price_for_prereg($discount_amount = false)
	{
		if ($this->type != 'package') {
			$price = $this->price_prereg;
		}
		else {
			if (!isset($this->price_scaled)) {
				$number_dancers = self::$database->query('SELECT COUNT(dancer_id) FROM %1$s_registrations JOIN %1$s_items USING(item_id) JOIN %1$s_dancers USING(dancer_id) WHERE %1$s_registrations.`event_id` = :event_id AND %1$s_items.`item_id` = :item_id AND %1$s_dancers.`status` != 2', array(':event_id' => $this->event_id, ':item_id' => $this->item_id))->fetchColumn();
				
				$this->price_scaled = self::$database->query('SELECT scale_price FROM %1$s_item_prices WHERE event_id = :event_id AND item_id = :item_id AND :number_dancers <= scale_count ORDER BY scale_count ASC LIMIT 1', array(':event_id' => $this->event_id, ':item_id' => $this->item_id, ':number_dancers' => $number_dancers))->fetchColumn();
			}
			
			$price = !empty($this->price_scaled) ? $this->price_scaled : $this->price_prereg;
		}
		
		if ($discount_amount) {
			$price = $price - $discount_amount;
		}
		
		return (int) $price;
	}
	
	public function price_for_vip()
	{
		return (int) $this->price_vip;
	}
	
	public function price_tier()
	{
		if ($this->type != 'package') {
			return false;
		}
		else {
			if (!isset($this->price_tier)) {
				$number_dancers = self::$database->query('SELECT COUNT(dancer_id) FROM %1$s_registrations JOIN %1$s_items USING(item_id) JOIN %1$s_dancers USING(dancer_id) WHERE %1$s_registrations.`event_id` = :event_id AND %1$s_items.`item_id` = :item_id AND %1$s_dancers.`status` != 2', array(':event_id' => $this->event_id, ':item_id' => $this->item_id))->fetchColumn();
				
				$this->price_tier = (int) self::$database->query('SELECT scale_count FROM %1$s_item_prices WHERE event_id = :event_id AND item_id = :item_id AND :number_dancers < scale_count ORDER BY scale_count ASC LIMIT 1', array(':event_id' => $this->event_id, ':item_id' => $this->item_id, ':number_dancers' => $number_dancers))->fetchColumn();
			}
			
			return $this->price_tier;
		}
	}
	
	public function registered_dancers()
	{
		if (!isset($this->registered_dancers)) {
			$order_by = ($this->meta != 'position') ? '' : 'item_meta DESC, ';
			$order_by .= 'last_name ASC, first_name ASC';
			
			$this->registered_dancers = self::$database->query('SELECT %1$s_dancers.*, %1$s_registrations.`item_meta` FROM %1$s_registrations LEFT JOIN %1$s_dancers USING(dancer_id) WHERE %1$s_registrations.`event_id` = :event_id AND item_id = :item_id ORDER BY '.$order_by, array(':event_id' => $this->event_id, ':item_id' => $this->item_id))->fetchAll(PDO::FETCH_CLASS, 'RegistrationSystem_Model_Dancer');
		}
		
		return $this->registered_dancers;
	}
	
	public function total_money_from_registrations()
	{
		return self::$database->query('SELECT SUM(price) FROM %1$s_registrations WHERE %1$s_registrations.`event_id` = :event_id AND item_id = :item_id', array(':event_id' => $this->event_id, ':item_id' => $this->item_id))->fetchColumn();
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
					$number_dancers = $this->count_registrations_where(array(':item_id' => $this->item_id));
				}
				elseif ($position === false) {
					$limit = $this->limit_per_position * 2;
					return $limit - $this->count_registrations_where(array(':item_id' => $this->item_id));
				}
				else {
					$limit = $this->limit_per_position;
					$number_dancers = $this->count_registrations_where(array(':item_id' => $this->item_id, ':position' => $position), 'dancers');
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
	
	public function openings_by_position()
	{
		$leads   = $this->count_openings('lead');
		$follows = $this->count_openings('follow');
		
		return sprintf('%1$d %3$s, %2$d %4$s',
			$leads,
			$follows,
			_n('lead',   'leads',   $leads),
			_n('follow', 'follows', $follows));
	}
	
	public function count_registrations()
	{
		if (!isset($this->count_registrations)) {
			$this->count_registrations = (int) self::$database->query('SELECT COUNT(*) FROM %1$s_registrations WHERE item_id = :item_id', array(':item_id' => $this->item_id))->fetchColumn();
		}
		
		return $this->count_registrations;
	}
	
	public function count_registrations_by_position()
	{
		if (!isset($this->count_registrations_by_position)) {
			$this->count_registrations_by_position = array();
			
			foreach (array('leads' => 1, 'follows' => 2) as $key => $value) {
				$result = (int) self::$database->query('SELECT COUNT(*) FROM %1$s_registrations JOIN %1$s_dancers USING(dancer_id) WHERE item_id = :item_id AND position = :position', array(':item_id' => $this->item_id, ':position' => $value))->fetchColumn();
				
				$this->count_registrations_by_position[$key] = $result;
			}
		}
		
		return $this->count_registrations_by_position;
	}
	
	public function registrations_by_position()
	{
		$result = $this->count_registrations_by_position();
		
		return sprintf('%1$d %3$s, %2$d %4$s',
			$result['leads'],
			$result['follows'],
			_n('lead',   'leads',   $result['leads']),
			_n('follow', 'follows', $result['follows']));
	}
	
	private function count_registrations_where(array $where = array(), $join_table = false)
	{
		$where[':item_id'] = $this->item_id;
		$query = array('%1$s_registrations.`event_id` = :event_id');
		
		foreach ($where as $field => $value) {
			$query[] = sprintf(' `%1$s` = :%1$s', substr($field, 1));
		}
		
		$query = ' WHERE '.implode(' AND', $query);
		$where[':event_id'] = $this->event_id;
		
		switch ($join_table) {
			case 'items':
				$query = ' JOIN %1$s_items USING(item_id)'.$query;
				break;
			
			case 'dancers':
				$query = ' JOIN %1$s_dancers USING(dancer_id)'.$query;
				break;
		}
		
		$result = self::$database->query('SELECT COUNT(*) FROM %1$s_registrations'.$query, $where)->fetchColumn();
		return ($result !== false) ? (int) $result : false;
	}
}

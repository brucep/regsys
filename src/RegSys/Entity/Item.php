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
	        $count_registrations_for_vips,
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
			return (int) $this->price_prereg;
		}
		else {
			if (!isset($this->price_scaled)) {
				$number_dancers = self::$database->fetchColumn('SELECT COUNT(dancer_id) FROM regsys_registrations AS r JOIN regsys_items AS i USING(item_id) JOIN regsys_dancers AS d USING(dancer_id) WHERE r.event_id = ? AND i.item_id = ? AND d.status != 2', array($this->event_id, $this->item_id));
				
				$this->price_scaled = self::$database->fetchColumn('SELECT scale_price FROM regsys_item_prices WHERE event_id = ? AND item_id = ? AND ? <= scale_count ORDER BY scale_count ASC LIMIT 1', array($this->event_id, $this->item_id, $number_dancers));
			}
			
			$price = !empty($this->price_scaled) ? $this->price_scaled : $this->price_prereg;
			
			if ($discount_amount !== false) {
				if ($discount_amount < 0) {
					$price = $price - $discount_amount * -1; # Negative numbers for amount off
				}
				else {
					$price = $discount_amount; # Zero or positive number for fixed price
				}
			}
			
			return (int) $price;
		}
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
				$number_dancers = self::$database->fetchColumn('SELECT COUNT(dancer_id) FROM regsys_registrations AS r JOIN regsys_items AS i USING(item_id) JOIN regsys_dancers AS d USING(dancer_id) WHERE r.event_id = ? AND i.item_id = ? AND d.status != 2', array($this->event_id, $this->item_id));
				
				$this->price_tier = (int) self::$database->fetchColumn('SELECT scale_count FROM regsys_item_prices WHERE event_id = ? AND item_id = ? AND ? < scale_count ORDER BY scale_count ASC LIMIT 1', array($this->event_id, $this->item_id, $number_dancers));
			}
			
			return $this->price_tier;
		}
	}
	
	public function registered_dancers()
	{
		if (!isset($this->registered_dancers)) {
			$order_by = ($this->meta != 'position') ? '' : 'item_meta DESC, ';
			$order_by .= 'last_name ASC, first_name ASC';
			
			$this->registered_dancers = self::$database->fetchAll('SELECT d.*, r.item_meta FROM regsys_registrations AS r LEFT JOIN regsys_dancers AS d USING(dancer_id) WHERE r.event_id = ? AND item_id = ? ORDER BY ' . $order_by, array($this->event_id, $this->item_id), 'RegistrationSystem_Model_Dancer');
		}
		
		return $this->registered_dancers;
	}
	
	public function total_money_from_registrations($payment_method)
	{
		return self::$database->fetchColumn('SELECT SUM(price) FROM regsys_registrations AS r LEFT JOIN regsys_dancers USING (dancer_id) WHERE r.event_id = ? AND payment_method = ? AND item_id = ?', array($this->event_id, $payment_method, $this->item_id));
	}
	
	public function is_expired()
	{
		return (!empty($this->date_expires) and time() > $this->date_expires);
	}
	
	public function sizes()
	{
		return $this->type == 'shirt' ? explode(',', 'None,' . $this->description) : null;
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
			$this->count_registrations = (int) self::$database->fetchColumn('SELECT COUNT(dancer_id) FROM regsys_registrations WHERE item_id = ?', array($this->item_id));
		}
		
		return $this->count_registrations;
	}
	
	public function count_registrations_for_vips() {
		if (!isset($this->count_registrations_for_vips)) {
			$this->count_registrations_for_vips = (int) self::$database->fetchColumn('SELECT COUNT(dancer_id) FROM regsys_registrations LEFT JOIN regsys_dancers USING(dancer_id) WHERE item_id = ? AND status = 2', array($this->item_id));
		}
		
		return $this->count_registrations_for_vips;
	}
	
	public function count_registrations_by_position()
	{
		if (!isset($this->count_registrations_by_position)) {
			$this->count_registrations_by_position = array();
			
			foreach (array('leads' => 1, 'follows' => 2) as $key => $value) {
				$result = (int) self::$database->fetchColumn('SELECT COUNT(dancer_id) FROM regsys_registrations JOIN regsys_dancers USING(dancer_id) WHERE item_id = ? AND position = ?', array($this->item_id, $value));
				
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
	
	public function registration_price_numbers() {
		$event = RegistrationSystem_Model_Event::get_event_by_id($this->event_id);
		$result = array();
		
		if ($this->type == 'package' and self::$database->fetchColumn('SELECT item_id FROM regsys_item_prices WHERE item_id = ?', array($this->item_id))) {
			$registrations = self::$database->fetchAll('SELECT price, payment_method FROM regsys_registrations LEFT JOIN regsys_dancers USING (dancer_id) WHERE price > 0 AND item_id = ? ORDER BY price ASC', array($this->item_id));
			
			foreach ($registrations as $reg) {
				if (!isset($result[$reg->price])) {
					$result[$reg->price] = array_combine(array_merge(array('Total'), $event->payment_methods()), array(0, 0, 0));
				}
				
				$result[$reg->price]['Total']++;
				$result[$reg->price][$reg->payment_method]++;
			}
			
			$result['Total']['Total'] = sprintf('%d%s', $this->count_registrations() - $this->count_registrations_for_vips(), $this->count_registrations_for_vips() ? sprintf(' (+%d VIPs)', $this->count_registrations_for_vips()) : '');
			
			foreach ($event->payment_methods() as $payment_method) {
				$result['Total'][$payment_method] = $this->count_registrations_by_payment_method($payment_method);
			}
			
			return $result;
		}
		else {
			return null;
		}
	}
	
	public function count_registrations_by_payment_method($payment_method)
	{
		return self::$database->fetchColumn('SELECT COUNT(dancer_id) from regsys_registrations LEFT JOIN regsys_dancers USING (dancer_id) WHERE price > 0 AND item_id = ? AND payment_method = ?', array($this->item_id, $payment_method));
	}
	
	public function count_registrations_by_size($size)
	{
		return self::$database->fetchColumn('SELECT COUNT(dancer_id) from regsys_registrations WHERE item_id = ? AND item_meta = ?', array($this->item_id, $size));
	}
	
	private function count_registrations_where(array $where = array(), $join_dancers_table = false)
	{
		$where[':item_id'] = $this->item_id;
		$query = array('r.event_id = :event_id');
		
		foreach ($where as $field => $value) {
			$query[] = sprintf(' `%1$s` = :%1$s', substr($field, 1));
		}
		
		$query = ' WHERE ' . implode(' AND', $query);
		$where[':event_id'] = $this->event_id;
		
		if ($join_dancers_table) {
			$query = ' JOIN regsys_dancers USING(dancer_id)' . $query;
		}
		
		$result = self::$database->fetchColumn('SELECT COUNT(dancer_id) FROM regsys_registrations AS r' . $query, $where);
		return ($result !== false) ? (int) $result : false;
	}
}

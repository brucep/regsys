<?php

class NSEvent_Model_Event extends NSEvent_Model
{
	private $id,
	        $name,
	        $date_early_end,
	        $date_prereg_end,
	    	$date_refund_end,
	    	$date_payment_by,
	        $discount1,
	        $discount2,
	        $discount_label,
	        $discount_note,
	        $has_housing,
	        $has_vip,
	        $has_volunteers,
	        $housing_nights,
	        $is_early_bird,
	        $levels,
	        $shirt_description;
	
	public static $possible_housing_nights = array(
	        1  => 'Friday',
	        2  => 'Saturday',
	        4  => 'Sunday',
	        8  => 'Monday',
	        16 => 'Tuesday',
	        32 => 'Wednesday',
	        64 => 'Thursday');
	
	public function __construct()
	{
		if (is_string($this->levels)) {
			$this->levels = unserialize($this->levels);
		}
		
		$this->is_early_bird = ($this->date_early_end and time() < $this->date_early_end);
	}
	
	public function __toString()
	{
		return sprintf('%s [#%d]', $this->name, $this->id);
	}
	
	static public function get_events()
	{
		return self::$database->query('SELECT * FROM %1$s_events ORDER BY date_prereg_end DESC')->fetchAll(PDO::FETCH_CLASS, 'NSEvent_Model_Event');
	}
	
	static public function get_event_by_id($event_id)
	{
		return self::$database->query('SELECT * FROM %1$s_events WHERE id = :id', array(':id' => $event_id))->fetchObject('NSEvent_Model_Event');
	}
		
	public function get_items()
	{
		return self::$database->query('SELECT * FROM %1$s_items WHERE event_id = :event_id', array(':event_id' => $this->id))->fetchAll(PDO::FETCH_CLASS, 'NSEvent_Model_Item');
	}
	
	public function get_items_where(array $where)
	{
		$query = array('`event_id` = :event_id');
		
		foreach ($where as $field => $value) {
			$query[] = sprintf(' `%1$s` = :%1$s', substr($field, 1));
		}
		
		$query = implode(' AND', $query);
		$where[':event_id'] = $this->id;
		
		return self::$database->query('SELECT * FROM %1$s_items WHERE '.$query, $where)->fetchAll(PDO::FETCH_CLASS, 'NSEvent_Model_Item');
	}
	
	public function get_item_by_id($item_id)
	{
		return self::$database->query('SELECT * FROM %1$s_items WHERE event_id = :event_id AND id = :id', array(':event_id' => $this->id, ':id' => $item_id))->fetchObject('NSEvent_Model_Item');
	}
		
	public function get_dancers()
	{
		return self::$database->query('SELECT * FROM %1$s_dancers LEFT JOIN %1$s_housing ON %1$s_housing.`dancer_id` = %1$s_dancers.`id` WHERE %1$s_dancers.`event_id` = :event_id ORDER BY last_name ASC, first_name ASC, date_registered ASC', array(':event_id' => $this->id))->fetchAll(PDO::FETCH_CLASS, 'NSEvent_Model_Dancer');
	}
		
	public function get_dancers_where(array $where)
	{
		$query = array('%1$s_dancers.`event_id` = :event_id');
		
		foreach ($where as $field => $value) {
			$query[] = sprintf(' `%1$s` = :%1$s', substr($field, 1));
		}
		
		$query = implode(' AND', $query);
		$where[':event_id'] = $this->id;
		
		return self::$database->query('SELECT * FROM %1$s_dancers LEFT JOIN %1$s_housing ON %1$s_housing.`dancer_id` = %1$s_dancers.`id` WHERE '.$query.' ORDER BY last_name ASC, first_name ASC, date_registered ASC', $where)->fetchAll(PDO::FETCH_CLASS, 'NSEvent_Model_Dancer');
	}
	
	public function get_dancer_ids()
	{
		return self::$database->query('SELECT id FROM %1$s_dancers WHERE event_id = :event_id ORDER BY last_name ASC, first_name ASC, date_registered ASC', array(':event_id' => $this->id))->fetchAll(PDO::FETCH_COLUMN, 0);
	}
		
	public function get_dancer_by_id($dancer_id)
	{
		return self::$database->query('SELECT * FROM %1$s_dancers LEFT JOIN %1$s_housing ON %1$s_housing.`dancer_id` = %1$s_dancers.`id` WHERE %1$s_dancers.`event_id` = :event_id AND id = :id', array(':event_id' => $this->id, ':id' => $dancer_id))->fetchObject('NSEvent_Model_Dancer');
	}
	
	public function get_volunteers()
	{
		return ($this->has_volunteers()) ? self::$database->query('SELECT * FROM %1$s_dancers WHERE event_id = :event_id AND status = 1 ORDER BY last_name ASC, first_name ASC', array(':event_id' => $this->id))->fetchAll(PDO::FETCH_CLASS, 'NSEvent_Model_Dancer') : array();
	}
	
	public function count_dancers(array $where = array())
	{
		$query = array('`event_id` = :event_id');
		
		foreach ($where as $field => $value) {
			$query[] = sprintf(' `%1$s` = :%1$s', substr($field, 1));
		}
		
		$query = implode(' AND', $query);
		$where[':event_id'] = $this->id;
		
		$result = self::$database->query('SELECT COUNT(id) FROM %1$s_dancers WHERE '.$query, $where)->fetchColumn();
		return ($result !== false) ? (int) $result : false;
	}
	
	public function count_housing_spots_available()
	{
		$result = self::$database->query('SELECT SUM(housing_spots_available) FROM %1$s_housing WHERE event_id = :event_id AND housing_type = 2', array(':event_id' => $this->id))->fetchColumn();
		return ($result !== false) ? (int) $result : false;
	}
	
	public function count_registrations_where(array $where, $join_table = false)
	{
		$where = array_merge(array(':event_id' => $this->id), $where);
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
	
	public function add_registration($parameters)
	{
		$statement = self::$database->query('INSERT %1$s_registrations VALUES (:event_id, :dancer_id, :item_id, :price, :item_meta)', array(
			':event_id'  => $this->id,
			':dancer_id' => $parameters['dancer_id'],
			':item_id'   => $parameters['item_id'],
			':price'     => $parameters['price'],
			':item_meta' => $parameters['item_meta'],
			));
		
		return $statement->rowCount();
	}
	
	public function get_id()
	{
		return (int) $this->id;
	}
	
	public function get_name()
	{
		return $this->name;
	}
	
	public function get_date_early_end($format = false)
	{
		return ($format === false) ? (int) $this->date_early_end : date($format, $this->date_early_end);
	}
	
	public function get_date_prereg_end($format = false)
	{
		return ($format === false) ? (int) $this->date_prereg_end : date($format, $this->date_prereg_end);
	}
	
	public function get_date_postmark_by($format = false)
	{
		if (!empty($this->date_postmark_by)) {
			$timestamp = $this->date_postmark_by;
		}
		elseif ($this->is_early_bird()) {
			$timestamp = $this->date_early_end;
		}
		else {
			$timestamp = $this->date_prereg_end;
		}
		
		$day_of_week = date('N', $timestamp);
		
		if ($day_of_week == 7) {
			$timestamp = strtotime('+1 day', $timestamp);
		}
		elseif ($day_of_week == 6) {
			$timestamp = strtotime('+2 days', $timestamp);
		}
		
		return ($format === false) ? (int) $timestamp : date($format, $timestamp);
	}
	
	public function get_date_refund_end($format = false)
	{
		$timestamp = !empty($this->date_refund_end) ? $this->date_refund_end : $this->date_prereg_end;
		
		$day_of_week = date('N', $timestamp);
		
		if ($day_of_week == 7) {
			$timestamp = strtotime('+1 day', $timestamp);
		}
		elseif ($day_of_week == 6) {
			$timestamp = strtotime('+2 days', $timestamp);
		}
		
		return ($format === false) ? (int) $timestamp : date($format, $timestamp);
	}
	
	public function get_discount_label()
	{
		return $this->discount_label;
	}
	
	public function get_discount_name($key)
	{
		$key = 'discount'.$key;
		return isset($this->$key) ? $this->$key : false;
	}
	
	public function get_discount_note()
	{
		return $this->discount_note;
	}
	
	public function get_housing_nights()
	{
		return $this->has_housing ? self::bit_field($this->housing_nights, self::$possible_housing_nights) : array();
	}
	
	public function get_levels()
	{
		return $this->levels;
	}
	
	public function get_level_for_index($index, $default = false)
	{
		return isset($this->levels[$index]) ? $this->levels[$index] : $default;
	}
	
	public function get_request_href($request, array $parameters = array())
	{
		$href = sprintf('%s/wp-admin/admin.php?page=nsevent&amp;event_id=%d&amp;request=%s',
			get_bloginfo('wpurl'),
			$this->id,
			rawurlencode($request));
		
		foreach ($parameters as $key => $value) {
			$href .= sprintf('&amp;%s=%s', rawurlencode($key), rawurlencode($value));
		}
		
		return $href;
	}
	
	public function get_request_link($request, $label, array $parameters = array(), $class = '', $format = '')
	{
		return sprintf('<a href="%1$s%4$s"%3$s>%2$s</a>',
			$this->get_request_href($request, $parameters),
			esc_html($label),
			empty($class)  ? '' : sprintf(' class="%s"', esc_attr($class)),
			empty($format) ? '' : sprintf('&amp;format=%s', rawurlencode($format)));
	}
	
	public function get_shirt_description()
	{
		return $this->shirt_description;
	}
	
	public function get_total_money_from_registrations()
	{
		return self::$database->query('SELECT SUM(price) FROM %1$s_registrations WHERE %1$s_registrations.`event_id` = :event_id', array(':event_id' => $this->id))->fetchColumn();
	}
	
	public function has_discount($key = false)
	{
		if ($key === false) {
			return ($this->discount1 or $this->discount2);
		}
		else {
			$key = 'discount'.$key;
			return (isset($this->$key) and $this->$key === true);
		}
	}

	public function has_housing()
	{
		return (bool) $this->has_housing;
	}
	
	public function has_levels()
	{
		return (!empty($this->levels));
	}
	
	public function has_vip()
	{
		return (bool) $this->has_vip;
	}
	
	public function has_volunteers()
	{
		return (bool) $this->has_volunteers;
	}
	
	public function is_early_bird()
	{
		return $this->is_early_bird;
	}
}

<?php

class NSEvent_Event extends NSEvent_Model
{
	public $name,
	       $early_end,
	       $prereg_end,
	       $discount1,
	       $discount2,
	       $discount_label,
	       $discount_note,
	       $has_vip,
	       $has_volunteers,
	       $has_housing,
	       $nights,
	       $levels;
	
	public static $possible_nights = array(
		1  => 'Friday',
		2  => 'Saturday',
		4  => 'Sunday',
		8  => 'Monday',
		16 => 'Tuesday',
		32 => 'Wednesday',
		64 => 'Thursday',
		);
	
	public static function find_all()
	{
		$statement = self::$database->query('SELECT * FROM %s_events ORDER BY prereg_end DESC');
		return $statement->fetchAll(PDO::FETCH_CLASS, 'NSEvent_Event');
	}
	
	//public static function find_by($field, $value)
	//{
	//	$statement = self::$database->query('SELECT * FROM %s_events WHERE `'.$field.'` = :value ORDER BY last_name ASC, first_name ASC', array(':value' => $value));
	//	return $statement->fetchAll(PDO::FETCH_CLASS, 'NSEvent_Event');
	//}
	
	public static function find($id)
	{
		$statement = self::$database->query('SELECT * FROM %s_events WHERE id = :id', array(':id' => $id));
		return $statement->fetchObject('NSEvent_Event');
	}
	
	public function levels($key = False)
	{
		if (is_string($this->levels))
			$this->levels = unserialize($this->levels);
		
		if ($key === False)
			return $this->levels;
		elseif (!isset($this->levels))
			return False;
		elseif (isset($this->levels[$key]))
			return $this->levels[$key];
		else
			return $key;
	}
	
	public function nights()
	{
		return $this->bit_field($this->nights, self::$possible_nights);
	}
	
	public function postmark_by($early = False)
	{
		$timestamp = $early ? $this->early_end : $this->prereg_end;
		$day_of_week = date('N', $timestamp);
		
		if ($day_of_week == 7)
			return strtotime('+1 day', $timestamp);
		elseif ($day_of_week == 6)
			return strtotime('+2 days', $timestamp);
		else
			return $timestamp;
	}
	
	public function refund_end()
	{
		$timestamp = (!self::$event->refund_end) ? (int) self::$event->prereg_end : (int) self::$event->refund_end;
		$day_of_week = date('N', $timestamp);
		
		if ($day_of_week == 7)
			return strtotime('+1 day', $timestamp);
		elseif ($day_of_week == 6)
			return strtotime('+2 days', $timestamp);
		else
			return $timestamp;
	}
	
	public function has_discounts()
	{
		return (bool) $this->discount1;
	}
	
	public function total_money_from_registrations()
	{
		return self::$database->query('SELECT SUM(price) FROM %1$s_registrations WHERE %1$s_registrations.`event_id` = :event_id', array(':event_id' => self::$event->id))->fetchColumn();
	}
	
	public function volunteers()
	{
		if (!$this->has_volunteers)
			return False;
		else
			return self::$database->query('SELECT * FROM %1$s_dancers WHERE event_id = :event_id AND status = 1 ORDER BY last_name ASC, first_name ASC', array(':event_id' => self::$event->id))->fetchAll(PDO::FETCH_CLASS, 'NSEvent_Dancer');
	}
	
	public function request_href($request, array $parameters = array())
	{
		$href = sprintf('%s/wp-admin/admin.php?page=nsevent&amp;event_id=%d&amp;request=%s',
			get_bloginfo('wpurl'),
			$this->id,
			rawurlencode($request));
		
		foreach ($parameters as $key => $value)
		{
			$href .= sprintf('&amp%s=%s', rawurlencode($key), rawurlencode($value));
		}
		
		return $href;
	}
	
	public function request_link($request, $label, array $parameters = array(), $class = '', $format = '')
	{
		printf('<a href="%1$s%4$s"%3$s>%2$s</a>',
			$this->request_href($request, $parameters),
			esc_html($label),
			empty($class)  ? '' : sprintf(' class="%s"', esc_attr($class)),
			empty($format) ? '' : sprintf('&amp;format=%s', rawurlencode($format)));
	}
}

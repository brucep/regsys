<?php

class RegistrationSystem_Model_Event extends RegistrationSystem_Model
{
	public  $name;
	
	private $event_id,
	        $date_mail_prereg_end,
	        $date_paypal_prereg_end,
	    	$date_refund_end,
	        $discounts,
	        $has_housing,
	        $has_levels,
	        $has_vip,
	        $has_volunteers,
	        $housing_nights,
	        $levels,
	        $levels_keyed_by_id;
	
	public function __construct(array $parameters = array())
	{
		foreach ($parameters as $key => $value) {
			$this->$key = $value;
		}
	}
	
	public function __toString()
	{
		return sprintf('%s [#%d]', $this->name, $this->event_id);
	}
	
	static public function get_events()
	{
		return self::$database->query('SELECT * FROM %1$s_events ORDER BY date_paypal_prereg_end DESC')->fetchAll(PDO::FETCH_CLASS, 'RegistrationSystem_Model_Event');
	}
	
	static public function get_event_by_id($event_id)
	{
		return self::$database->query('SELECT * FROM %1$s_events WHERE event_id = :event_id', array(':event_id' => $event_id))->fetchObject('RegistrationSystem_Model_Event');
	}
	
	public function items()
	{
		return self::$database->query('SELECT * FROM %1$s_items WHERE event_id = :event_id', array(':event_id' => $this->event_id))->fetchAll(PDO::FETCH_CLASS, 'RegistrationSystem_Model_Item');
	}
	
	public function items_where(array $where, $exclude_expired = false)
	{
		$query = array('`event_id` = :event_id');
		
		foreach ($where as $field => $value) {
			$query[] = sprintf(' `%1$s` = :%1$s', substr($field, 1));
		}
		
		$query = implode(' AND', $query);
		$where[':event_id'] = $this->event_id;
		
		if ($exclude_expired) {
			$query .= ' AND date_expires <= :date_expires';
			$where[':date_expires'] = time();
		}
		
		return self::$database->query('SELECT * FROM %1$s_items WHERE '.$query, $where)->fetchAll(PDO::FETCH_CLASS, 'RegistrationSystem_Model_Item');
	}
	
	public function item_by_id($item_id)
	{
		return self::$database->query('SELECT * FROM %1$s_items WHERE event_id = :event_id AND item_id = :item_id', array(':event_id' => $this->event_id, ':item_id' => $item_id))->fetchObject('RegistrationSystem_Model_Item');
	}
	
	public function dancers()
	{
		return self::$database->query('SELECT *, %1$s_event_levels.`label` as level, %1$s_dancers.`event_id` as event_id FROM %1$s_dancers LEFT JOIN %1$s_event_levels USING(level_id, event_id) LEFT JOIN %1$s_housing USING(dancer_id) WHERE %1$s_dancers.`event_id` = :event_id ORDER BY last_name ASC, first_name ASC, date_registered ASC', array(':event_id' => $this->event_id))->fetchAll(PDO::FETCH_CLASS, 'RegistrationSystem_Model_Dancer');
	}
	
	public function dancers_where(array $where, $equal = true)
	{
		$query = array('%1$s_dancers.`event_id` = :event_id');
		
		foreach ($where as $field => $value) {
			$query[] = sprintf(' `%1$s` %2$s :%1$s',
				substr($field, 1),
				$equal ? '=' : '!=');
		}
		
		$query = implode(' AND', $query);
		$where[':event_id'] = $this->event_id;
		
		return self::$database->query('SELECT *, %1$s_event_levels.`label` as level, %1$s_dancers.`event_id` as event_id FROM %1$s_dancers LEFT JOIN %1$s_event_levels USING(level_id, event_id) LEFT JOIN %1$s_housing USING(dancer_id) WHERE '.$query.' ORDER BY last_name ASC, first_name ASC, date_registered ASC', $where)->fetchAll(PDO::FETCH_CLASS, 'RegistrationSystem_Model_Dancer');
	}
	
	public function dancer_by_id($dancer_id)
	{
		return self::$database->query('SELECT *, %1$s_event_levels.`label` as level, %1$s_dancers.`event_id` as event_id FROM %1$s_dancers LEFT JOIN %1$s_event_levels USING(level_id, event_id) LEFT JOIN %1$s_housing USING(dancer_id) WHERE %1$s_dancers.`event_id` = :event_id AND %1$s_dancers.`dancer_id` = :dancer_id', array(':event_id' => $this->event_id, ':dancer_id' => $dancer_id))->fetchObject('RegistrationSystem_Model_Dancer');
	}
	
	public function volunteers()
	{
		return ($this->has_volunteers()) ? self::$database->query('SELECT * FROM %1$s_dancers WHERE event_id = :event_id AND status = 1 ORDER BY last_name ASC, first_name ASC', array(':event_id' => $this->event_id))->fetchAll(PDO::FETCH_CLASS, 'RegistrationSystem_Model_Dancer') : array();
	}
	
	public function discounts()
	{
		if (!isset($this->discounts)) {
			$this->discounts = array();
			$discounts = self::$database->query('SELECT * FROM %1$s_event_discounts WHERE event_id = ? ORDER BY discount_code ASC', array($this->event_id))->fetchAll(PDO::FETCH_OBJ);
			
			foreach ($discounts as $d) {
				$this->discounts[$d->discount_id] = $d;
			}
		}
		
		return $this->discounts;
	}
	
	public function unset_discounts()
	{
		# Used after editing discounts with the Edit Event form.
		unset($this->discounts);
	}
	
	public function discount_by_code($code)
	{
		return self::$database->query('SELECT * FROM %1$s_event_discounts WHERE event_id = ? AND discount_code = ?', array($this->event_id, $code))->fetchObject();
	}
	
	public function count_dancers(array $where = array())
	{
		$query = array('`event_id` = :event_id');
		
		foreach ($where as $field => $value) {
			$query[] = sprintf(' `%1$s` = :%1$s', substr($field, 1));
		}
		
		$query = implode(' AND', $query);
		$where[':event_id'] = $this->event_id;
		
		$result = self::$database->query('SELECT COUNT(dancer_id) FROM %1$s_dancers WHERE '.$query, $where)->fetchColumn();
		return ($result !== false) ? (int) $result : false;
	}
	
	public function count_discounts_used($code)
	{
		$result = self::$database->query('SELECT COUNT(dancer_id) FROM %1$s_dancers JOIN %1$s_event_discounts USING(discount_id) WHERE %1$s_dancers.`event_id` = ? AND %1$s_event_discounts.`discount_code` = ?', array($this->event_id, $code))->fetchColumn();
		return ($result !== false) ? (int) $result : false;
	}
	
	public function count_housing_spots_available()
	{
		$result = self::$database->query('SELECT SUM(housing_spots_available) FROM %1$s_housing WHERE event_id = :event_id AND housing_type = 2', array(':event_id' => $this->event_id))->fetchColumn();
		return ($result !== false) ? (int) $result : false;
	}
	
	public function count_registrations_where(array $where, array $join_tables = array())
	{
		$where_query = array('%1$s_registrations.`event_id` = :event_id');
		
		foreach ($where as $field => $value) {
			$where_query[] = sprintf(' `%1$s` = :%1$s', substr($field, 1));
		}
		
		$where_query = ' WHERE '.implode(' AND', $where_query);
		
		$where[':event_id'] = $this->event_id;
		
		$join_query = '';
		
		if (in_array('items', $join_tables)) {
			$join_query .= ' JOIN %1$s_items USING(item_id)';
		}
		
		if (in_array('dancers', $join_tables)) {
			$join_query .= ' JOIN %1$s_dancers USING(dancer_id)';
		}
		
		$result = self::$database->query('SELECT COUNT(*) FROM %1$s_registrations'.$join_query.$where_query, $where)->fetchColumn();
		return ($result !== false) ? (int) $result : false;
	}
	
	public function add_registration($parameters)
	{
		$statement = self::$database->query('INSERT %1$s_registrations VALUES (:event_id, :dancer_id, :item_id, :price, DEFAULT, :item_meta)', array(
			':event_id'  => $this->event_id,
			':dancer_id' => $parameters['dancer_id'],
			':item_id'   => $parameters['item_id'],
			':price'     => $parameters['price'],
			':item_meta' => $parameters['item_meta'],
			));
		
		return $statement->rowCount();
	}
	
	public function id()
	{
		return (int) $this->event_id;
	}
	
	public function date_mail_prereg_end()
	{
		return (int) $this->date_mail_prereg_end;
	}
	
	public function date_paypal_prereg_end()
	{
		return (int) $this->date_paypal_prereg_end;
	}
	
	public function date_refund_end()
	{
		return (int) $this->date_refund_end ? $this->date_refund_end : $this->date_paypal_prereg_end;
	}
	
	public function housing_nights()
	{
		if (is_string($this->housing_nights)) {
			$this->housing_nights = explode(',', $this->housing_nights);
		}
		
		return $this->housing_nights;
	}
	
	public function levels()
	{
		if (!isset($this->levels)) {
			$this->levels = array();
			$levels = self::$database->query('SELECT level_id, label, has_tryouts FROM %1$s_event_levels WHERE event_id = :event_id', array(':event_id' => $this->event_id))->fetchAll(PDO::FETCH_OBJ);
			
			foreach ($levels as $level) {
				$this->levels[$level->level_id] = $level;
			}
		}
		
		return $this->levels;
	}
	
	public function levels_keyed_by_id()
	{
		if (!isset($this->levels_keyed_by_id)) {
			$this->levels_keyed_by_id = array();
			
			foreach ($this->levels() as $level) {
				$this->levels_keyed_by_id[$level->level_id] = $level->label;
			}
		}
		
		return $this->levels_keyed_by_id;
	}
	
	public function unset_levels()
	{
		# Used after editing levels with the Edit Event form.
		unset($this->levels);
	}
	
	public function total_money_from_registrations()
	{
		return self::$database->query('SELECT SUM(price) FROM %1$s_registrations WHERE %1$s_registrations.`event_id` = :event_id', array(':event_id' => $this->event_id))->fetchColumn();
	}
	
	public function has_discounts()
	{
		return (bool) $this->discounts();
	}
	
	public function has_discount_openings($code)
	{
		$limit = self::$database->query('SELECT discount_limit FROM %1$s_event_discounts WHERE event_id = ? AND discount_code = ?', array($this->event_id, $code))->fetchColumn();
		return ($limit > 0) ? (bool) ($limit - $this->count_discounts_used($code)) : true;
	}
	
	public function has_housing_support()
	{
		return ($this->has_housing == 2 or $this->has_housing == 1);
	}
	
	public function has_housing_registrations()
	{
		return ($this->has_housing == 2);
	}
	
	public function has_levels()
	{
		return (bool) $this->has_levels;
	}
	
	public function has_vip()
	{
		return (bool) $this->has_vip;
	}
	
	public function has_volunteers()
	{
		return (bool) $this->has_volunteers;
	}
}

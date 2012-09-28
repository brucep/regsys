<?php

class RegistrationSystem_Model_Event extends RegistrationSystem_Model
{
	public  $name,
	        $visualization,
	        $visualization_color;
	
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
		return self::$database->query('SELECT * FROM regsys_events ORDER BY date_paypal_prereg_end DESC')->fetchAll(PDO::FETCH_CLASS, 'RegistrationSystem_Model_Event');
	}
	
	static public function get_event_by_id($event_id)
	{
		return self::$database->query('SELECT * FROM regsys_events WHERE event_id = ?', array($event_id))->fetchObject('RegistrationSystem_Model_Event');
	}
	
	public function items()
	{
		return self::$database->query('SELECT * FROM regsys_items WHERE event_id = ? ORDER BY item_id ASC', array($this->event_id))->fetchAll(PDO::FETCH_CLASS, 'RegistrationSystem_Model_Item');
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
			$query .= ' AND (date_expires = 0 OR date_expires > :current_time)';
			$where[':current_time'] = time();
		}
		
		return self::$database->query('SELECT * FROM regsys_items WHERE ' . $query . ' ORDER BY item_id ASC', $where)->fetchAll(PDO::FETCH_CLASS, 'RegistrationSystem_Model_Item');
	}
	
	public function item_by_id($item_id)
	{
		return self::$database->query('SELECT * FROM regsys_items WHERE event_id = ? AND item_id = ?', array($this->event_id, $item_id))->fetchObject('RegistrationSystem_Model_Item');
	}
	
	public function dancers()
	{
		return self::$database->query('SELECT *, el.label AS level, d.event_id AS event_id FROM regsys_dancers AS d LEFT JOIN regsys_event_levels AS el USING(level_id, event_id) LEFT JOIN regsys_housing USING(dancer_id) WHERE d.event_id = ? ORDER BY last_name ASC, first_name ASC, date_registered ASC', array($this->event_id))->fetchAll(PDO::FETCH_CLASS, 'RegistrationSystem_Model_Dancer');
	}
	
	public function dancers_where(array $where, $equal = true)
	{
		$query = array('d.event_id = :event_id');
		
		foreach ($where as $field => $value) {
			$query[] = sprintf(' `%1$s` %2$s :%1$s',
				substr($field, 1),
				$equal ? '=' : '!=');
		}
		
		$query = implode(' AND', $query);
		$where[':event_id'] = $this->event_id;
		
		return self::$database->query('SELECT *, el.label AS level, d.event_id AS event_id FROM regsys_dancers AS d LEFT JOIN regsys_event_levels as el USING(level_id, event_id) LEFT JOIN regsys_housing USING(dancer_id) WHERE ' . $query . ' ORDER BY last_name ASC, first_name ASC, date_registered ASC', $where)->fetchAll(PDO::FETCH_CLASS, 'RegistrationSystem_Model_Dancer');
	}
	
	public function dancer_by_id($dancer_id)
	{
		return self::$database->query('SELECT *, el.label AS level, d.event_id AS event_id FROM regsys_dancers AS d LEFT JOIN regsys_event_levels AS el USING(level_id, event_id) LEFT JOIN regsys_housing USING(dancer_id) WHERE d.event_id = ? AND d.dancer_id = ?', array($this->event_id, $dancer_id))->fetchObject('RegistrationSystem_Model_Dancer');
	}
	
	public function volunteers()
	{
		return ($this->has_volunteers()) ? self::$database->query('SELECT * FROM regsys_dancers WHERE event_id = ? AND status = 1 ORDER BY last_name ASC, first_name ASC', array($this->event_id))->fetchAll(PDO::FETCH_CLASS, 'RegistrationSystem_Model_Dancer') : array();
	}
	
	public function discounts()
	{
		if (!isset($this->discounts)) {
			$this->discounts = array();
			$discounts = self::$database->query('SELECT * FROM regsys_event_discounts WHERE event_id = ? ORDER BY discount_code ASC', array($this->event_id))->fetchAll(PDO::FETCH_OBJ);
			
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
		return self::$database->query('SELECT * FROM regsys_event_discounts WHERE event_id = ? AND discount_code = ?', array($this->event_id, $code))->fetchObject();
	}
	
	public function count_dancers(array $where = array())
	{
		$query = array('event_id = :event_id');
		
		foreach ($where as $field => $value) {
			$query[] = sprintf(' `%1$s` = :%1$s', substr($field, 1));
		}
		
		$query = implode(' AND', $query);
		$where[':event_id'] = $this->event_id;
		
		$result = self::$database->query('SELECT COUNT(dancer_id) FROM regsys_dancers WHERE '.$query, $where)->fetchColumn();
		return ($result !== false) ? (int) $result : false;
	}
	
	public function count_discounts_used($code, $payment_method = null)
	{
		if ($payment_method == null) {
			$result = self::$database->query('SELECT COUNT(dancer_id) FROM regsys_dancers AS d JOIN regsys_event_discounts USING(discount_id) WHERE d.event_id = ? AND discount_code = ?', array($this->event_id, $code))->fetchColumn();
		}
		else {
			$result = self::$database->query('SELECT COUNT(dancer_id) FROM regsys_dancers AS d JOIN regsys_event_discounts USING(discount_id) WHERE d.event_id = ? AND discount_code = ? AND payment_method = ?', array($this->event_id, $code, $payment_method))->fetchColumn();
		}
		
		return ($result !== false) ? (int) $result : false;
	}
	
	public function count_housing_spots_available()
	{
		$result = self::$database->query('SELECT SUM(housing_spots_available) FROM regsys_housing WHERE event_id = ? AND housing_type = 2', array($this->event_id))->fetchColumn();
		return ($result !== false) ? (int) $result : false;
	}
	
	public function add_registration($parameters)
	{
		$statement = self::$database->query('INSERT regsys_registrations VALUES (?, ?, ?, ?, DEFAULT, ?)', array(
			$this->event_id,
			$parameters['dancer_id'],
			$parameters['item_id'],
			$parameters['price'],
			$parameters['item_meta'],
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
			$levels = self::$database->query('SELECT level_id, label, has_tryouts FROM regsys_event_levels WHERE event_id = ?', array($this->event_id))->fetchAll(PDO::FETCH_OBJ);
			
			foreach ($levels as $level) {
				$this->levels[$level->level_id] = $level;
			}
		}
		
		return $this->levels;
	}
	
	public function levels_for_registration_form()
	{
		$levels = array();
		
		foreach ($this->levels() as $level) {
			$levels[] = array('value' => $level->level_id, 'label' => !$level->has_tryouts ? esc_html($level->label) : esc_html($level->label) . ' <em>(Tryouts required)</em>');
		}
		
		return $levels;
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
	
	public function total_money_from_registrations($payment_method)
	{
		return self::$database->query('SELECT SUM(price) FROM regsys_registrations AS r LEFT JOIN regsys_dancers USING (dancer_id) WHERE r.event_id = ? AND payment_method = ?', array($this->event_id, $payment_method))->fetchColumn();
	}
	
	public function has_discounts()
	{
		return (bool) $this->discounts();
	}
	
	public function has_discount_expired($code)
	{
		$expires = self::$database->query('SELECT discount_expires FROM regsys_event_discounts WHERE event_id = ? AND discount_code = ?', array($this->event_id, $code))->fetchColumn();
		return $expires ? $expires <= time() : false;
	}
	
	public function has_discount_openings($code)
	{
		$limit = self::$database->query('SELECT discount_limit FROM regsys_event_discounts WHERE event_id = ? AND discount_code = ?', array($this->event_id, $code))->fetchColumn();
		
		if ($limit !== false) {
			return ($limit > 0) ? (bool) ($limit - $this->count_discounts_used($code)) : true;
		}
		else {
			return false;
		}
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
	
	public function payment_methods()
	{
		return array('Mail', 'PayPal');
	}
}

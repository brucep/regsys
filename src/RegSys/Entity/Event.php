<?php

namespace RegSys\Entity;

class Event extends \RegSys\Entity
{
	protected $eventID,
	          $dateMail,
	          $datePayPal,
	          $dateRefund,
	          $discounts,
	          $hasHousing = 2,
	          $hasLevels,
	          $hasVolunteers,
	          $housingNights = 'Friday,Saturday,Sunday',
	          $levels,
	          $name,
	          $visualization,
	          $visualizationColor,
	          $volunteerDescription;
	
	public function __construct(array $parameters = array())
	{
		foreach ($parameters as $key => $value) {
			$this->$key = $value;
		}
	}
	
	public function __call($name, $arguments)
	{
		return isset($this->$name) ? $this->$name : null;
	}
	
	public function __toString()
	{
		return sprintf('%s [#%d]', $this->name, $this->eventID);
	}
	
	static public function eventByID($eventID)
	{
		return self::$db->fetchObject('SELECT * FROM regsys__events WHERE eventID = ?', array($eventID), '\RegSys\Entity\Event');
	}
		
	public function countDancers(array $where = array())
	{
		$query = array('eventID = :eventID');
		
		foreach ($where as $field => $value) {
			$query[] = sprintf(' `%1$s` = :%1$s', substr($field, 1));
		}
		
		$query = implode(' AND', $query);
		$where[':eventID'] = $this->eventID;
		
		$result = self::$db->fetchColumn('SELECT COUNT(dancerID) FROM regsys__dancers WHERE '.$query, $where);
		return ($result !== false) ? (int) $result : false;
	}
	
	public function countDiscountsUsed($code, $paymentMethod = null)
	{
		if ($paymentMethod == null) {
			$result = self::$db->fetchColumn('SELECT COUNT(dancerID) FROM regsys__dancers AS d JOIN regsys__event_discounts USING(discountID) WHERE d.eventID = ? AND discountCode = ?', array($this->eventID, $code));
		}
		else {
			$result = self::$db->fetchColumn('SELECT COUNT(dancerID) FROM regsys__dancers AS d JOIN regsys__event_discounts USING(discountID) WHERE d.eventID = ? AND discountCode = ? AND paymentMethod = ?', array($this->eventID, $code, $payment_method));
		}
		
		return ($result !== false) ? (int) $result : false;
	}
	
	public function dancers()
	{
		return self::$db->fetchAll('SELECT *, el.levelLabel AS level, d.eventID AS eventID FROM regsys__dancers AS d LEFT JOIN regsys__event_levels AS el USING(levelID, eventID) LEFT JOIN regsys__housing USING(dancerID) WHERE d.eventID = ? ORDER BY lastName ASC, firstName ASC, dateRegistered ASC', array($this->eventID), '\RegSys\Entity\Dancer');
	}
	
	public function dancersWhere(array $where, $equal = true)
	{
		$query = array('d.eventID = :eventID');
		
		foreach ($where as $field => $value) {
			$query[] = sprintf(' `%1$s` %2$s :%1$s',
				substr($field, 1),
				$equal ? '=' : '!=');
		}
		
		$query = implode(' AND', $query);
		$where[':eventID'] = $this->eventID;
		
		return self::$db->fetchAll('SELECT *, el.levelLabel AS level, d.eventID AS eventID FROM regsys__dancers AS d LEFT JOIN regsys__event_levels as el USING(levelID, eventID) LEFT JOIN regsys__housing USING(dancerID) WHERE ' . $query . ' ORDER BY lastName ASC, firstName ASC, dateRegistered ASC', $where, '\RegSys\Entity\Dancer');
	}
	
	public function dancerByID($dancerID)
	{
		return self::$db->fetchObject('SELECT *, el.levelLabel AS level, d.eventID AS eventID FROM regsys__dancers AS d LEFT JOIN regsys__event_levels AS el USING(levelID, eventID) LEFT JOIN regsys__housing USING(dancerID) WHERE d.eventID = ? AND d.dancerID = ?', array($this->eventID, $dancerID), '\RegSys\Entity\Dancer');
	}
	
	public function discounts()
	{
		if (!isset($this->discounts)) {
			$this->discounts = array();
			$result = self::$db->fetchAll('SELECT discountCode, discountAmount, discountLimit, discountExpires FROM regsys__event_discounts WHERE eventID = ? ORDER BY discountCode ASC', array($this->eventID));
			
			foreach ($result as $d) {
				$this->discounts[$d->discountCode] = $d;
			}
		}
		
		return $this->discounts;
	}
	
	public function discountByCode($code)
	{
		return self::$db->fetchObject('SELECT * FROM regsys__event_discounts WHERE eventID = ? AND discountCode = ?', array($this->eventID, $code));
	}
	
	public function hasDiscounts()
	{
		return (bool) $this->discounts();
	}
	
	public function hasDiscountExpired($code)
	{
		$expires = self::$db->fetchColumn('SELECT discountExpires FROM regsys__event_discounts WHERE eventID = ? AND discountCode = ?', array($this->eventID, $code));
		return $expires ? $expires <= time() : false;
	}
	
	public function hasDiscountOpenings($code)
	{
		$limit = self::$db->fetchColumn('SELECT discountLimit FROM regsys__event_discounts WHERE eventID = ? AND discountCode = ?', array($this->eventID, $code));
		
		if ($limit !== false) {
			return ($limit > 0) ? (bool) ($limit - $this->countDiscountsUsed($code)) : true;
		}
		else {
			return false;
		}
	}
	
	public function hasHousingRegistrations()
	{
		return ($this->hasHousing == '2');
	}
	
	public function hasHousingSupport()
	{
		return ($this->hasHousing == '2' or $this->hasHousing == '1');
	}
		
	public function hasLevels()
	{
		return (bool) $this->hasLevels;
	}
		
	public function hasVolunteers()
	{
		return (bool) $this->hasVolunteers;
	}
		
	public function id()
	{
		return (int) $this->eventID;
	}
	
	public function items()
	{
		return self::$db->fetchAll('SELECT * FROM regsys__items WHERE eventID = ? ORDER BY itemID ASC', array($this->eventID), '\RegSys\Entity\Item');
	}
	
	public function itemByID($itemID)
	{
		return self::$db->fetchObject('SELECT * FROM regsys__items WHERE eventID = ? AND itemID = ?', array($this->eventID, $itemID), '\RegSys\Entity\Item');
	}
	
	public function itemsForRegistrationByType($type)
	{
		return self::$db->fetchAll('SELECT * FROM regsys__items WHERE eventID = ? AND (dateExpires IS NULL OR dateExpires > ?) AND type = ? ORDER BY itemID ASC', array($this->eventID, time(), $type), '\RegSys\Entity\Item');
	}
	
	public function levels()
	{
		if (!isset($this->levels)) {
			$this->levels = array();
			$levels = self::$db->fetchAll('SELECT levelID, levelLabel, hasTryouts FROM regsys__event_levels WHERE eventID = ?', array($this->eventID));
			
			foreach ($levels as $level) {
				$this->levels[$level->levelID] = $level;
			}
		}
		
		return $this->levels;
	}
	
	public function levelsForRegistrationForm()
	{
		$levels = array();
		
		foreach ($this->levels() as $level) {
			$levels[] = array('value' => $level->levelID, 'label' => !$level->hasTryouts ? htmlspecialchars($level->levelLabel, ENT_NOQUOTES, 'UTF-8') : htmlspecialchars($level->levelLabel, ENT_NOQUOTES, 'UTF-8') . ' <em>(Tryouts required)</em>');
		}
		
		return $levels;
	}
		
	public function paymentMethods()
	{
		return array('Mail', 'PayPal');
	}
	
	public function totalMoneyFromRegistrations($paymentMethod)
	{
		return self::$db->fetchColumn('SELECT SUM(price) FROM regsys__registrations AS r LEFT JOIN regsys__dancers USING (dancerID) WHERE r.eventID = ? AND paymentMethod = ?', array($this->eventID, $paymentMethod));
	}
		
	public function validate()
	{
		$validationErrors = array();
		
		foreach (array('name' => 'Name', 'dateMail' => 'Mail date', 'datePayPal' => 'PayPal date') as $field => $label) {
			$this->$field = trim($this->$field);
			if (empty($this->$field)) {
				$validationErrors[$field] = $label . ' is a required field.';
			}
		}
		
		$this->dateMail = strtotime($this->dateMail);
		$this->datePayPal = strtotime($this->datePayPal);
		
		return $validationErrors;
	}
}

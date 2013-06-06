<?php

namespace RegSys\Entity;

class Item extends \RegSys\Entity
{
	protected $itemID,
	          $eventID,
	          $name,
	          $countRegistrations,
	          $countRegistrationsForVIPs,
	          $countRegistrationsByPosition,
	          $dateExpires,
	          $description,
	          $limitPerPosition,
	          $limitTotal,
	          $meta,
	          $openings,
	          $pricePrereg,
	          $priceDoor,
	          $priceForTier,
	          $priceTier,
	          $priceTiers,
	          $registeredDancers,
	          $registeredPrice,
	          $registeredMeta,
	          $type;
	
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
		return sprintf('%s [#%d]', $this->name, $this->itemID);
	}
	
	public function addRegistration($dancerID)
	{
		$result = self::$db->query('INSERT regsys__registrations VALUES (?, ?, ?, ?, DEFAULT, ?)', array(
			$this->eventID,
			$dancerID,
			$this->itemID,
			$this->registeredPrice,
			$this->registeredMeta,
			));
		
		return $result->rowCount();
	}
	
	public function countOpenings($position = false)
	{
		if (!isset($this->openings)) {
			if (!$this->limitTotal and !$this->limitPerPosition) {
				$this->openings = true;
			}
			else {
				if ($this->limitTotal) {
					$limit = $this->limitTotal;
					$numberDancers = $this->countRegistrationsWhere(array(':itemID' => $this->itemID));
				}
				elseif ($position === false) {
					$limit = $this->limitPerPosition * 2;
					return $limit - $this->countRegistrationsWhere(array(':itemID' => $this->itemID));
				}
				else {
					$limit = $this->limitPerPosition;
					$numberDancers = $this->countRegistrationsWhere(array(':itemID' => $this->itemID, ':position' => $position), 'dancers');
				}
				
				if ($numberDancers !== false) {
					$openings = (($limit - $numberDancers) > 0) ? $limit - $numberDancers : 0;
				}
				else {
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
		
	public function countRegistrations()
	{
		if (!isset($this->countRegistrations)) {
			$this->countRegistrations = (int) self::$db->fetchColumn('SELECT COUNT(dancerID) FROM regsys__registrations WHERE itemID = ?', array($this->itemID));
		}
		
		return $this->countRegistrations;
	}
	
	public function countRegistrationsByPaymentMethod($payment_method)
	{
		return self::$db->fetchColumn('SELECT COUNT(dancerID) from regsys__registrations LEFT JOIN regsys__dancers USING (dancerID) WHERE price > 0 AND itemID = ? AND paymentMethod = ?', array($this->itemID, $payment_method));
	}
	
	public function countRegistrationsByPosition()
	{
		if (!isset($this->countRegistrationsByPosition)) {
			$this->countRegistrationsByPosition = array();
			
			foreach (array('leads' => 1, 'follows' => 2) as $key => $value) {
				$result = (int) self::$db->fetchColumn('SELECT COUNT(dancerID) FROM regsys__registrations JOIN regsys__dancers USING(dancerID) WHERE itemID = ? AND position = ?', array($this->itemID, $value));
				
				$this->countRegistrationsByPosition[$key] = $result;
			}
		}
		
		return $this->countRegistrationsByPosition;
	}
	
	public function countRegistrationsBySize($size)
	{
		return self::$db->fetchColumn('SELECT COUNT(dancerID) from regsys__registrations WHERE itemID = ? AND itemMeta = ?', array($this->itemID, $size));
	}
	
	private function countRegistrationsWhere(array $where = array(), $join_dancers_table = false)
	{
		$where[':itemID'] = $this->itemID;
		$query = array('r.eventID = :eventID');
		
		foreach ($where as $field => $value) {
			$query[] = sprintf(' `%1$s` = :%1$s', substr($field, 1));
		}
		
		$query = ' WHERE ' . implode(' AND', $query);
		$where[':eventID'] = $this->eventID;
		
		if ($join_dancers_table) {
			$query = ' JOIN regsys__dancers USING(dancerID)' . $query;
		}
		
		$result = self::$db->fetchColumn('SELECT COUNT(dancerID) FROM regsys__registrations AS r' . $query, $where);
		return ($result !== false) ? (int) $result : false;
	}
	
	public function id()
	{
		return (int) $this->itemID;
	}
	
	public function isExpired()
	{
		return (!empty($this->dateExpires) and time() > $this->dateExpires);
	}
	
	public function pricePreregPackage($discountAmount = null)
	{
		if ($this->type == 'package') {
			if (!isset($this->priceForTier)) {
				$numberDancers = self::$db->fetchColumn('SELECT COUNT(dancerID) FROM regsys__registrations AS r JOIN regsys__items AS i USING(itemID) JOIN regsys__dancers AS d USING(dancerID) WHERE r.eventID = ? AND i.itemID = ? AND d.volunteer != 2', array($this->eventID, $this->itemID));
				
				$this->priceForTier = self::$db->fetchColumn('SELECT tierPrice FROM regsys__item_prices WHERE eventID = ? AND itemID = ? AND ? <= tierCount ORDER BY tierCount ASC LIMIT 1', array($this->eventID, $this->itemID, $numberDancers));
			}
			
			$price = !empty($this->priceForTier) ? $this->priceForTier : $this->pricePrereg;
			
			if ($discountAmount !== null) {
				if ($discountAmount < 0) {
					$price = $price - $discountAmount * -1; # Negative numbers for amount off
				}
				else {
					$price = $discountAmount; # Zero or positive number for fixed price
				}
			}
			
			return (int) $price;
		}
		else {
			return null;
		}
	}
	
	public function priceTier()
	{
		if ($this->type != 'package') {
			return false;
		}
		else {
			if (!isset($this->priceTier)) {
				$numberDancers = self::$db->fetchColumn('SELECT COUNT(dancerID) FROM regsys__registrations AS r JOIN regsys__items AS i USING(itemID) JOIN regsys__dancers AS d USING(dancerID) WHERE r.eventID = ? AND i.itemID = ? AND d.volunteer != 2', array($this->eventID, $this->itemID));
				
				$this->priceTier = (int) self::$db->fetchColumn('SELECT tierCount FROM regsys__item_prices WHERE eventID = ? AND itemID = ? AND ? < tierCount ORDER BY tierCount ASC LIMIT 1', array($this->eventID, $this->itemID, $numberDancers));
			}
			
			return $this->priceTier;
		}
	}
	
	public function priceTiers()
	{
		if ($this->type != 'package') {
			return false;
		}
		elseif (!isset($this->priceTiers)) {
			$result = self::$db->fetchAll('SELECT tierCount as `count`, tierPrice as price FROM regsys__item_prices WHERE itemID = ? ORDER BY tierCount ASC', array($this->itemID));
			$this->priceTiers = array();
			
			foreach ($result as $tier) {
				$this->priceTiers[$tier->count] = $tier;
			}
		}
		return $this->priceTiers;
	}
	
	public function registeredDancers()
	{
		if (!isset($this->registeredDancers)) {
			$orderBy = ($this->meta != 'Position') ? '' : 'itemMeta DESC, ';
			$orderBy .= 'lastName ASC, firstName ASC';
			
			$this->registeredDancers = self::$db->fetchAll('SELECT d.*, itemMeta FROM regsys__registrations AS r LEFT JOIN regsys__dancers AS d USING(dancerID) WHERE r.eventID = ? AND itemID = ? ORDER BY ' . $orderBy, array($this->eventID, $this->itemID), '\RegSys\Entity\Dancer');
		}
		
		return $this->registeredDancers;
	}
	
	public function sizes()
	{
		return $this->type == 'shirt' ? explode(',', 'None,' . $this->description) : null;
	}
	
	public function totalMoneyFromRegistrations($paymentMethod)
	{
		return self::$db->fetchColumn('SELECT SUM(price) FROM regsys__registrations AS r LEFT JOIN regsys__dancers USING (dancerID) WHERE r.eventID = ? AND paymentMethod = ? AND itemID = ?', array($this->eventID, $paymentMethod, $this->itemID));
	}
	
	public function registrationPriceNumbers() {
		$event = \RegSys\Entity\Event::eventByID($this->eventID);
		$result = array();
		
		if ($this->type == 'package' and self::$db->fetchColumn('SELECT itemID FROM regsys__item_prices WHERE itemID = ?', array($this->itemID))) {
			$registrations = self::$db->fetchAll('SELECT price, paymentMethod FROM regsys__registrations LEFT JOIN regsys__dancers USING (dancerID) WHERE price > 0 AND itemID = ? ORDER BY price ASC', array($this->itemID));
			
			foreach ($registrations as $reg) {
				if (!isset($result[$reg->price])) {
					$result[$reg->price] = array_combine(array_merge(array('Total'), $event->paymentMethods()), array(0, 0, 0));
				}
				
				$result[$reg->price]['Total']++;
				$result[$reg->price][$reg->paymentMethod]++;
			}
			
			$result['Total']['Total'] = $this->countRegistrations();
			
			foreach ($event->paymentMethods() as $paymentMethod) {
				$result['Total'][$paymentMethod] = $this->countRegistrationsByPaymentMethod($paymentMmethod);
			}
			
			return $result;
		}
		else {
			return null;
		}
	}
	
	# Used by \RegSys\Entity\Dancer#validateItems
	public function setRegisteredPrice($price)
	{
		$this->registeredPrice = $price;
	}
	
	# Used by \RegSys\Entity\Dancer#validateItems
	public function setRegisteredMeta($meta)
	{
		$this->registeredMeta = $meta;
	}
	
	public function validate()
	{
		$validationErrors = array();
		
		$this->name = trim($this->name);
		$this->dateExpires = !empty($this->dateExpires) ? strtotime($this->dateExpires) : null;
		
		foreach (array('pricePrereg', 'priceDoor', 'limitPerPosition', 'limitTotal') as $field) {
			$this->$field = (int) $this->$field;
		}
		
		foreach (array('name' => 'Name', 'type' => 'Type', 'pricePrereg' => 'Price for Preregistration') as $field => $label) {
			if (empty($this->$field)) {
				$validationErrors[$field] = $label . ' is a required field.';
			}
		}
		
		if (!in_array($this->type, array('package', 'competition', 'shirt'))) {
			$validationErrors['type'] = 'Type has an invalid value.';
		}
		
		if ($this->type == 'package' and !in_array($this->meta, array('', 'Count for Classes')) or
			$this->type == 'competition' and !in_array($this->meta, array('', 'Position', 'Partner', 'Team Members', 'CrossoverJJ')) or
			$this->type == 'shirt') {
			$this->meta = null;
		}
		
		return $validationErrors;
	}
}

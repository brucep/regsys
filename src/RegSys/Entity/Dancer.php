<?php

namespace RegSys\Entity;

class Dancer extends \RegSys\Entity
{
	protected $dancerID,
	          $eventID,
	          $firstName,
	          $lastName,
  	          $email,
	          $confirmEmail,
	          $dateRegistered,
	          $discountCode,
	          $housingBedtime,
	          $housingComment,
	          $housingFromScene,
	          $housingGender,
	          $housingNights,
	          $housingPets,
	          $housingSmoke,
	          $housingSpotsAvailable,
	          $housingType,
	          $level,
	          $levelID,
	          $note,
	          $paymentConfirmed = 0,
	          $paymentMethod,
	          $paymentOwed = 0,
	          $paypalFee,
	          $phone,
	          $position,
	          $priceTotal,
	          $registeredItems,
	    	  $volunteer;
	
	public function __construct(array $parameters = array())
	{
		$reflection = new \ReflectionObject($this);
		$properties = $reflection->getProperties(\ReflectionProperty::IS_PROTECTED);
		
		# Don't include extra data from POST during registration form
		foreach ($properties as $property) {
			if (isset($parameters[$property->getName()])) {
				$name = $property->getName();
				$this->$name = $parameters[$name];
			}
		}
	}
	
	public function __call($name, $arguments)
	{
		return isset($this->$name) ? $this->$name : null;
	}
	
	public function __toString()
	{
		return sprintf('%s %s [#%d]', $this->firstName, $this->lastName, $this->dancerID);
	}
		
	public function add($eventID)
	{
		$this->eventID = $eventID;
		$this->dateRegistered = time();
		
		self::$db->query('INSERT regsys__dancers VALUES (?, NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, DEFAULT, ?, ?)', array(
			$this->eventID,
			$this->firstName,
			$this->lastName,
			$this->email,
			$this->position,
			$this->levelID,
			$this->volunteer,
			$this->dateRegistered,
			$this->discountCode,
			$this->paymentMethod,
			$this->paymentConfirmed,
			$this->paymentOwed,
			$this->phone,
			$this->note,
			));
		
		$this->dancerID = self::$db->lastInsertID();
	}
	
	public function addHousing()
	{
		self::$db->query('INSERT regsys__housing VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', array(
			$this->eventID,
			$this->dancerID,
			$this->housingType,
			$this->housingSpotsAvailable,
			$this->housingNights,
			$this->housingGender,
			$this->housingBedtime,
			$this->housingPets,
			$this->housingSmoke,
			$this->housingFromScene,
			$this->housingComment,
			));
	}
	
	public function datePostmarkBy()
	{
		return strtotime(sprintf('+%d days', self::$options['postmarkWithin']), $this->dateRegistered);
	}
	
	public function hasPets()
	{
		return ($this->housingType == '1' and $this->housingPets == '1');
	}
	
	public function hasSmoke()
	{
		return ($this->housingType == '1' and $this->housingSmoke == '1');
	}
	
	public function housingBedtimeLabel()
	{
		switch ($this->housingBedtime) {
			case 1:
				return 'Early Bird';
			
			case 2:
				return 'Night Owl';
			
			case 0:
				return 'No Preference';
			
			default:
				return null;
		}
	}
	
	public function housingGenderLabel()
	{
		switch ($this->housingGender) {
			case 1:
				return 'Boys';
			
			case 2:
				return 'Girls';
			
			case 3:
				return 'Boys, Girls';
			
			default:
				return null;
		}
	}
	
	public function housingTypeLabel()
	{
		if ($this->isHousingProvider()) {
			return 'Housing Provider';
		}
		elseif ($this->needsHousing()) {
			return 'Housing Needed';
		}
		else {
			return null;
		}
	}
	
	public function id()
	{
		return (int) $this->dancerID;
	}
	
	public function isHousingProvider()
	{
		return ($this->housingType == '1');
	}
	
	public function isOverdue()
	{
		return (self::$options['postmarkWithin'] and !$this->paymentConfirmed and  time() > $this->datePostmarkBy());
	}
	
	public function mailtoHref()
	{
		return 'mailto:' . rawurlencode(sprintf('%s %s <%s>', $this->firstName, $this->lastName, $this->email));
	}
	
	public function name()
	{
		return sprintf('%s %s', $this->firstName, $this->lastName);
	}
	
	public function nameReversed()
	{
		return sprintf('%s, %s', $this->lastName, $this->firstName);
	}
	
	public function needsHousing()
	{
		return ($this->housingType == '0');
	}
	
	public function paypalHref($notifyUrl)
	{
		$href = sprintf('https://%1$s/cgi-bin/webscr?cmd=_cart&upload=1&no_shipping=1&business=%2$s&notify_url=%3$s&custom=%4$d',
			self::$options['paypalSandbox'] ? 'www.sandbox.paypal.com' : 'www.paypal.com',
			rawurlencode(self::$options['paypalBusiness']),
			$notifyUrl,
			$this->dancerID);
		
		if (!empty(self::$options['paypalFee'])) {
			$href .= sprintf('&item_name_1=%1$s&amount_1=%2$d', 'Processing%20Fee', self::$options['paypalFee']);
			$i = 2;
		}
		else {
			$i = 1;
		}
		
		foreach ($this->registeredItems() as $item) {
			if ($item->registeredPrice() == 0) {
				continue;
			}
			
			$href .= sprintf('&item_number_%1$d=%2$d&item_name_%1$d=%3$s&amount_%1$d=%4$s', $i,rawurlencode($item->id()), rawurlencode($item->name()), rawurlencode($item->registeredPrice()));
			
			if ($item->meta() == 'size') {
				$href .= sprintf('&on0_%1$d=%2$s&os0_%1$d=%3$s', $i, $item->metaLabel(), $item->registeredMeta());
			}
			
			$i++;
		}
		
		return $href;
	}
	
	public function positionLabel()
	{
		return ($this->position == '1') ? 'Follow' : 'Lead';
	}
	
	public function prefersNoPets()
	{
		return ($this->housingType == '0' and $this->housingPets == '1');
	}
	
	public function prefersNoSmoke()
	{
		return ($this->housingType == '0' and $this->housingSmoke == '1');
	}
		
	public function priceTotal()
	{
		if (!isset($this->priceTotal)) {
			$this->priceTotal = self::$db->fetchColumn('SELECT SUM(price) FROM regsys__registrations WHERE eventID = ? AND dancerID = ?', array($this->eventID, $this->dancerID));
		}
		
		return $this->priceTotal;
	}
	
	public function registeredItems($itemID = null)
	{
		if (!isset($this->registeredItems))
		{
			$this->registeredItems = array();
			
			$result = self::$db->fetchAll('SELECT i.*, r.price AS registeredPrice, r.itemMeta AS registeredMeta FROM regsys__registrations AS r LEFT JOIN regsys__items as i USING(itemID) WHERE r.eventID = ? AND dancerID = ?', array($this->eventID, $this->dancerID), '\RegSys\Entity\Item');
			
			foreach ($result as $item) {
				$this->registeredItems[$item->id()] = $item;
			}
		}
		
		if ($itemID === null) {
			return $this->registeredItems;
		}
		elseif (isset($this->registeredItems[$itemID])) {
			return $this->registeredItems[$itemID];
		}
		else {
			false;
		}
	}
		
	public function sendConfirmationEmail(\RegSys\Entity\Event $event, \Twig_Environment $twig, $notifyUrl)
	{
		$body = $twig->loadTemplate('FormEmail.txt')->render(array(
			'event' => $event,
			'dancer' => $this,
			'notifyUrl' => $notifyUrl));
		
		if (self::$options['emailTransport'] == 'smtp') {
			$transport = \Swift_SmtpTransport::newInstance(self::$options['emailHost']);
			
			if (!empty(self::$options['emailPort'])) {
				$transport->setPort(self::$options['emailPort']);
			}
			
			if (!empty(self::$options['emailUsername'])) {
				$transport->setUsername(self::$options['emailUsername']);
			}
			
			if (!empty(self::$options['emailPassword'])) {
				$transport->setPassword(self::$options['emailPassword']);
			}
			
			if (in_array(self::$options['emailEncryption'], array('ssl', 'tsl'))) {
				$transport->setEncryption(self::$options['emailEncryption']);
			}
		}
		else {
			$transport = \Swift_MailTransport::newInstance();
		}
		
		$message = \Swift_Message::newInstance()
			->setSubject(sprintf('Registration for %s: %s', $event->name(), $this->name()))
			->setFrom(self::$options['emailFrom'])
			->addTo($this->email(), $this->name())
			->setBody($body);
		
		if (!empty(self::$options['emailReplyTo'])) {
			$message->setReplyTo(self::$options['emailReplyTo']);
		}
		
		if (!empty(self::$options['emailBcc'])) {
			$message->setBcc(self::$options['emailBcc']);
		}
		
		return (bool) \Swift_Mailer::newInstance($transport)->send($message);
	}
	
	public function updatePaymentConfirmation($paymentConfirmed, $paymentOwed)
	{
		$this->paymentConfirmed = $paymentConfirmed;
		$this->paymentOwed = $paymentOwed;
		
		return (bool) self::$db->query('UPDATE regsys__dancers SET paymentConfirmed = ?, paymentOwed = ? WHERE eventID = ? AND dancerID = ? LIMIT 1', array($paymentConfirmed, $paymentOwed, $this->eventID, $this->dancerID))->rowCount();
	}
	
	public function validate(\RegSys\Entity\Event $event, $isAdminForm = false)
	{
		$validationErrors = array();
		
		# Check presence and length of basic fields
		foreach(array('firstName' => 'First Name', 'lastName' => 'Last Name', 'email' => 'Email', 'phone' => 'Mobile Phone') as $field => $label) {
			$this->$field = trim($this->$field);
			if (empty($this->$field)) {
				$validationErrors[$field] = sprintf('%s is a required field.', $label);
			}
		}
		
		# Sane case on names
		$this->firstName = ucfirst(stripslashes($this->firstName));
		$this->lastName = ucfirst(stripslashes($this->lastName));
		
		# Email Address
		if (!$isAdminForm and !self::$options['registrationTesting']) {
			if ($this->confirmEmail != $this->email) {
				$validationErrors['email'] = 'Your email addresses do not match.';
			}
			elseif (self::$db->fetchObject('SELECT dancerID FROM regsys__dancers WHERE eventID = ? AND email =? AND firstName = ? AND lastName = ?', array($this->eventID, $this->email, $this->firstName, $this->lastName))) {
				$validationErrors['email'] =  sprintf('Someone has already registered with this information. If you have already registered and need to change your information, then please reply to your confirmation email. For any other concerns, email <a href="mailto:%1$s">%1$s</a>.', self::$options['emailFrom']);
			}
		}
		
		# Standardize phone number format (for common US number layouts, if possible)
		preg_match('/^(?:\(?([0-9]{3})\)?)?[- \.]?([0-9]{3})[- \.]?([0-9]{4})/', $this->phone, $matches);
		unset($matches[0]);
		if (!empty($matches)) {
			$this->phone = implode('-', array_filter($matches));
		}
		
		# Position
		if (!in_array($this->position, array('0', '1'))) {
			$validationErrors['position'] = 'Position is a required field.';
		}
		
		# Volunteer
		$this->volunteer = intval($event->hasVolunteers() and $this->volunteer == '1');
		
		# Payment Method
		if (!in_array($this->paymentMethod, $event->paymentMethods()) or (!$isAdminForm and time() > $event->{'date' . $this->paymentMethod}())) {
			# Null, invalid value, or value is past the related date (e.g., 'Mail' after `dateMail`) 
			$validationErrors['paymentMethod'] = 'Payment Method is a required field.';
		}
		
		# Level
		if ($event->hasLevels()) {
			if (in_array($this->levelID, array_keys($event->levels()))) {
				# Used for confirmation page
				$levels = $event->levels();
				$this->level = $levels[$this->levelID]->levelLabel;
			}
			else {
				$validationErrors['levelID'] = 'Level is a required field.';
			}
		}
		else {
			$this->levelID = 1;
		}
		
		# Discount Code
		if (!empty($this->discountCode) and $event->hasDiscounts()) {
			if (!$event->hasDiscountOpenings($this->discountCode) or $event->hasDiscountExpired($this->discountCode)) {
				$this->discountCode = null;
				$validationErrors['discountCode'] = sprintf('"%s" is not an acceptable discount code.', htmlspecialchars($this->discountCode, ENT_NOQUOTES, 'UTF-8'));
			}
		}
		else {
			$this->discountCode = null;
		}
		
		if ($isAdminForm) {
			$this->dateRegistered = strtotime($this->dateRegistered);
		}
		
		return $validationErrors;
	}
	
	public function validateHousing(\RegSys\Entity\Event $event, $isAdminForm = false)
	{
		$validationErrors = array();
		
		if (!$event->hasHousingRegistrations() and $this->needsHousing() and !$isAdminForm) {
			# Housing requests disabled after form was loaded.
			$this->housingType = null;
			$validationErrors['housingTypeNeeded'] = 'Sorry, housing requests have been disabled.';
		}
		elseif ($this->housingType === null or !$event->hasHousingSupport()) {
			# Housing not requested or event doesn't have housing
			$this->housingType = null;
		}
		elseif (($this->isHousingProvider() and $event->hasHousingSupport()) or ($this->needsHousing() and $event->hasHousingRegistrations())) {
			$this->housingComment = stripslashes(trim($this->housingComment));
			
			$this->housingSmoke = (int) $this->housingSmoke;
			$this->housingPets = (int) $this->housingPets;
			
			$key = $this->needsHousing() ? 'housingNeeded' : 'housingProvider';
			
			if (empty($this->housingNights)) {
				$validationErrors[$key . '[housingNights]'] = 'Housing Nights is a required field.';
			}
			else {
				if (is_array($this->housingNights)) {
					$this->housingNights = implode(',', $this->housingNights);
				}
			}
			
			if (!in_array($this->housingGender,  array(1, 2, 3))) {
				$validationErrors[$key . '[housingGender]'] = 'Housing Gender is a required field.';
			}
			
			if (!in_array($this->housingBedtime, array(0, 1, 2))) {
				$validationErrors[$key . '[housingBedtime]'] = 'Housing Bedtime is a required field.';
			}
			
			if ($this->isHousingProvider() and $this->housingSpotsAvailable <= 0) {
				$validationErrors['housingProvider[housingSpotsAvailable]'] = 'You must specify the number of spots available.';
			}
			
			if ($this->needsHousing()) {
				$this->housingFromScene = ucwords(trim($this->housingFromScene));
				
				if (empty($this->housingFromScene)) {
					$validationErrors['housingNeeded[housingFromScene]'] = "This is a required field.";
				}
			}
		}
		
		return $validationErrors;
	}
	
	public function validateItems(\RegSys\Entity\Event $event, array $items, array $meta = array(), $isAdminForm = false)
	{
		$validationErrors = array();
		
		if (!is_array($this->registeredItems)) {
			$this->registeredItems = array();
		}
				
		foreach ($items as $id => $value) {
			$item = $event->itemByID($id);
			
			if (!$item) {
				continue;
			}
			
			if ($item->type() == 'competition' and $item->meta()) {
				if ($item->meta() == 'Position') {
					# If position wasn't specified specifically for item, use dancer's position
					if (!isset($meta[$item->id()]) or !in_array($meta[$item->id()], array('Lead', 'Follow'))) {
						if ($this->position != null) {
							$meta[$item->id()] = !$this->position ? 'Lead' : 'Follow';
						}
						else {
							$validationErrors['item' . $item->id()] = sprintf('Can\'t determine position for %s.', $item->name());
							continue;
						}
					}
				}
				elseif ($item->meta() == 'CrossoverJJ') {
					if (isset($meta[$item->id()])) {
						# Pass array for position/level on registration form
						if (is_array($meta[$item->id()])) {
							# If position wasn't specified specifically for item, use dancer's position
							if (!isset($meta[$item->id()]['position']) or !in_array($meta[$item->id()]['position'], array('Lead', 'Follow'))) {
								if ($this->position != null) {
									$meta[$item->id()]['position'] = !$this->position ? 'Lead' : 'Follow';
								}
								else {
									$validationErrors['item' . $item->id()] = sprintf('Can\'t determine position for %s.', $item->name());
									continue;
								}
							}
							
							if (!isset($meta[$item->id()]['level'])) {
								$validationErrors['item' . $item->id()] = sprintf('Level must be specified for %s.', $item->name());
								continue;
							}
							
							$meta[$item->id()] = $meta[$item->id()]['position'] . '/' . $meta[$item->id()]['level'];
							
						}
						# Accept string value from confirmation page
					}
					else {
						$validationErrors['item' . $item->id()] = sprintf('Position and level must be specified for %s.', $item->name());
					}
				}
				elseif ($item->meta() == 'Partner') {
					if (isset($meta[$item->id()])) {
						$meta[$item->id()] = ucwords(trim($meta[$item->id()]));
					}
					
					if (empty($meta[$item->id()])) {
						$validationErrors['item' . $item->id()] = sprintf('Your partner\'s name must be specified for %s.', $item->name());
						continue;
					}
					// TODO: Check if partner has already registered for this item
				}
				elseif ($item->meta() == 'Team Members') {
					if (isset($meta[$item->id()])) {
						$meta[$item->id()] = trim($meta[$item->id()]);
					}
					
					if (empty($meta[$item->id()])) {
						$validationErrors['item' . $item->id()] = sprintf('Team members must be specified for %s.', $item->name());
						continue;
					}
					else {
						# Standarize formatting
						$meta[$item->id()] = ucwords(preg_replace(array("/[\r\n]+/", "/\n+/", "/\r+/", '/,([^ ])/', '/, , /'), ', $1', $meta[$item->id()]));
						
						if (strlen($meta[$item->id()]) > 65535) {
							$validationErrors['item' . $item->id()] = sprintf('Team members list for %s is too long.', $item->name());
							continue;
						}
					}
				}
			}
			elseif ($item->type() == 'shirt') {
				if ($value == 'None' or !in_array($value, explode(',', $item->description()))) {
					continue;
				}
				
				$meta[$item->id()] = $value; # Populate meta for the confirmation and PayPal page
			}
			
			# Check openings again, in case they have filled since the form was first displayed to the user
			if (!$isAdminForm and (($item->meta() != 'position' and !$item->countOpenings()) or ($item->meta() == 'position' and !$item->countOpenings($meta[$item->id()])))) {
				$validationErrors['item' . $item->id()] = sprintf('There are no longer any openings for %s.', $item->name());
				continue;
			}
			
			# Item is good
			$item->setRegisteredPrice($item->pricePrereg());
			
			if (isset($meta[$item->id()])) {
				$item->setRegisteredMeta($meta[$item->id()]);
			}
			
			# Used for confirmation/accepted page
			if (!$isAdminForm) {
				$this->priceTotal += $item->registeredPrice();
			}
			
			$this->registeredItems[$item->id()] = $item;
		}
		
		# Used for confirmation/accepted page
		if (!$isAdminForm) {
			$this->paymentOwed = $this->priceTotal;
			
			if ($this->priceTotal === 0) {
				$this->paymentMethod = 'Mail';
				$this->paymentConfirmed = '1';
			}
		}
		
		return $validationErrors;
	}
	
	public function validatePackage(\RegSys\Entity\Event $event, $itemID, $tier = null)
	{
		if ($itemID == '0') {
			return array(); # "N/A"
		}
		
		$validationErrors = array();
		
		if (!is_array($this->registeredItems)) {
			$this->registeredItems = array();
		}
		
		$item = $event->itemByID($itemID);
		
		if (!$item) {
			return array(); # Ignore invalid value
		}
		
		if ($tier != null and $tier != $item->priceTier()) {
			$validationErrors['package'] = 'The price has changed on this package. Review the price before continuing with your registration.';
		}
		
		# Item is good
		$item->setRegisteredPrice($item->pricePreregPackage(!empty($this->discountCode) ? $event->discountByCode($this->discountCode)->discountAmount : null));
		$this->registeredItems[] = $item;
		$this->priceTotal += $item->registeredPrice();
		
		return $validationErrors;
	}
}

<?php

namespace RegSys\Controller\BackEndController;

class AdminEvent extends \RegSys\Controller\BackEndController
{
	public function getContext()
	{
		$editing = $this->event instanceof \RegSys\Entity\Event;		
		
		if (!empty($_POST)) {
			$this->event = new \RegSys\Entity\Event($_POST);
			$validationErrors = $this->event->validate();
			
			if (!$validationErrors) {
				if (!$editing) {
					$this->db->query('INSERT regsys__events VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, DEFAULT, ?);', array(
						(string) $this->event->name(),
			 			(int)    $this->event->dateMail(),
			 			(int)    $this->event->datePayPal(),
			 			(int)    $this->event->dateRefund(),
			 			(int)    $this->event->hasLevels(),
			 			(int)    $this->event->hasVolunteers(),
			 			(int)    $this->event->hasHousing(),
			 			(string) $this->event->housingNights(),
			 			(int)    $this->event->visualization(),
			 			(string) $this->event->volunteerDescription(),
						));
					
					$this->event = \RegSys\Entity\Event::eventByID($this->db->lastInsertID());
					$editing = true;
				}
				else {
					$this->db->query('UPDATE regsys__events SET `name` = ?, dateMail = ?, datePayPal = ?, dateRefund = ?, hasLevels = ?, hasVolunteers = ?, hasHousing = ?, housingNights = ?, visualization = ?, volunteerDescription = ? WHERE eventID = ?', array(
						(string) $this->event->name(),
			 			(int)    $this->event->dateMail(),
			 			(int)    $this->event->datePayPal(),
			 			(int)    $this->event->dateRefund(),
			 			(int)    $this->event->hasLevels(),
			 			(int)    $this->event->hasVolunteers(),
			 			(int)    $this->event->hasHousing(),
			 			(string) $this->event->housingNights(),
			 			(int)    $this->event->visualization(),
			 			(string) $this->event->volunteerDescription(),
						$this->event->id(),
						));
				}
				
				$levels = $this->event->levels();
				foreach ($_POST['editLevels'] as $id => $label) {
					if ($label) {
						if (!isset($levels[$id])) {
							$this->db->query('INSERT regsys__event_levels VALUES (?, ?, ?, ?);', array(
								$this->event->id(),
								$id,
								$label,
								isset($_POST['editTryouts'][$id]),
								));
						}
						elseif (isset($levels[$id])) {
							$this->db->query('UPDATE regsys__event_levels SET levelLabel = ?, hasTryouts = ? WHERE eventID = ? AND levelID = ?', array(
								$label,
								isset($_POST['editTryouts'][$id]),
								$this->event->id(),
								$id,
								));
						}
					}
					elseif (!$label and isset($levels[$id])) {
						$this->db->query('DELETE FROM regsys__event_levels WHERE eventID = ? AND levelID = ?', array(
							$this->event->id(),
							$id,
							));
					}
				}
				unset($_POST['editTryouts'], $levels);
								
				$discounts = $this->event->discounts();
				foreach ($_POST['editDiscountCode'] as $key => $code) {
					if (isset($discounts[$code]) and isset($_POST['editDiscountDelete'][$code])) {
						$this->db->query('DELETE FROM regsys__event_discounts WHERE eventID = ? AND discountCode = ?', array(
							$this->event->id(),
							$code,
							));
					}
					elseif ($code) {
						$amount  = isset($_POST['editDiscountAmount'][$key])  ? (int) $_POST['editDiscountAmount'][$key] : 0;
						$limit   = isset($_POST['editDiscountLimit'][$key])   ? (int) $_POST['editDiscountLimit'][$key] : 0;
						$expires = isset($_POST['editDiscountExpires'][$key]) ? strtotime($_POST['editDiscountExpires'][$key]) : 0;
						
						if (!isset($discounts[$code])) {
							$this->db->query('INSERT regsys__event_discounts VALUES (?, ?, ?, ?, ?);', array(
								$this->event->id(),
								$code,
								$amount,
								$limit,
								$expires,
								));
						}
						elseif (isset($discounts[$code])) {
							$this->db->query('UPDATE regsys__event_discounts SET discountCode = ?, discountAmount = ?, discountLimit = ?, discountExpires = ? WHERE eventID = ? AND discountCode = ?', array(
								$code,
								$amount,
								$limit,
								$expires,
								$this->event->id(),
								$code,
								));
						}
					}
				}
				unset($_POST['editDiscountCode'], $_POST['editDiscountAmount'], $_POST['editDiscountLimit'], $_POST['editDiscountDelete'], $discounts, $key, $code, $amount, $limit, $expires);
				
				# Reload event
				$this->event = \RegSys\Entity\Event::eventByID($this->event->id());
			}
			else {
				$this->viewHelper->setErrors($validationErrors);
			}
		}
		elseif ($this->event === null) {
			# Add New Event
			$this->event = new \RegSys\Entity\Event();
		}
		
		$this->viewHelper->setThing($this->event);
				
		return array('editing' => $editing);
	}
}

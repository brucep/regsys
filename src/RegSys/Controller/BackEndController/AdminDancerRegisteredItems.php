<?php

namespace RegSys\Controller\BackEndController;

class AdminDancerRegisteredItems extends \RegSys\Controller\BackEndController
{
	public function getContext()
	{
		$dancer = $this->getRequestedDancer();
		
		if (!empty($_POST)) {
			$paymentOwed = $dancer->paymentOwed();
			
			if (isset($_POST['itemsAdd'])) {
				$validationErrors = $dancer->validateItems(
					$this->event,
					$_POST['itemsAdd'],
					isset($_POST['itemMeta']) ? $_POST['itemMeta'] : array(),
					true);
				
				if (!$validationErrors) {
					foreach ($_POST['itemsAdd'] as $itemID => $temp) {
						$item = $dancer->registeredItems($itemID); # Added during validation
						
						$item->addRegistration($dancer->id());
						
						$paymentOwed = $paymentOwed + $item->registeredPrice();
						
						unset($_POST['itemsAdd'][$itemID], $_POST['itemMeta'][$itemID]);
					}
				}
				else {
					$this->viewHelper->setErrors($validationErrors);
				}
			}
			
			if (isset($_POST['itemsDelete'])) {
				foreach ($_POST['itemsDelete'] as $itemID => $temp) {
					if ($dancer->registeredItems($itemID)) {
						$paymentOwed = $paymentOwed - $dancer->registeredItems($itemID)->registeredPrice();
						
						$this->db->query('DELETE FROM regsys__registrations WHERE dancerID = ? AND itemID = ?', array($dancer->id(), $itemID));
					}
					
					unset($_POST['itemsDelete'][$itemID], $_POST['itemMeta'][$itemID]);
				}
			}
			
			if ($dancer->paymentOwed() != $paymentOwed) {
				$this->db->query('UPDATE regsys__dancers SET paymentOwed = ?, paymentConfirmed = ? WHERE dancerID = ?', array($paymentOwed, $paymentOwed <= 0, $dancer->id()));
			}
			
			if (isset($_POST['itemMeta'])) {
				# Reload dancer to reset registeredItems
				$dancer = $this->getRequestedDancer();
				
				foreach ($_POST['itemMeta'] as $itemID => $meta) {
					if ($dancer->registeredItems($itemID) and $dancer->registeredItems($itemID)->registeredMeta() != $meta) {
						$this->db->query('UPDATE regsys__registrations SET itemMeta = ? WHERE dancerID = ? AND itemID = ?', array($meta, $dancer->id(), $itemID));
					}
				}
			}
			
			# Reload dancer to reset registeredItems
			$dancer = $this->getRequestedDancer();
		}
		
		return array('dancer' => $dancer);
	}
}

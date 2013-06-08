<?php

namespace RegSys\Controller\BackEndController;

class AdminDancerRegisteredItems extends \RegSys\Controller\BackEndController
{
	public function getContext()
	{
		$dancer = $this->getRequestedDancer();
		
		if (!empty($_POST)) {
			$validationErrors = array();
			$paymentOwed = $dancer->paymentOwed();
			
			if (isset($_POST['itemsAdd'])) {
				$validationErrors = $dancer->validateItems(
					$this->event,
					$_POST['itemsAdd'],
					isset($_POST['itemMeta']) ? $_POST['itemMeta'] : array(),
					true);
				
				if (!$validationErrors) {
					foreach ($_POST['itemsAdd'] as $itemID => $temp) {
						# New items are already added to `registeredItems` during validation
						$item = $dancer->registeredItems($itemID);
						
						if ($item instanceof \RegSys\Entity\Item) {
							$item->addRegistration($dancer->id());
							
							$paymentOwed = $paymentOwed + $item->registeredPrice();
						}
						
						unset($_POST['itemsAdd'][$itemID], $_POST['itemMeta'][$itemID]);
					}
				}
			}
			
			if (isset($_POST['itemsDelete'])) {
				foreach ($_POST['itemsDelete'] as $itemID => $temp) {
					$item = $dancer->registeredItems($itemID);
					
					if ($item instanceof \RegSys\Entity\Item) {
						$paymentOwed = $paymentOwed - $item->registeredPrice();
						
						$this->db->query('DELETE FROM regsys__registrations WHERE dancerID = ? AND itemID = ?', array($dancer->id(), $item->id()));
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
				
				foreach ($_POST['itemMeta'] as $itemID => $newMeta) {
					 $item = $dancer->registeredItems($itemID);
					
					if ($item instanceof \RegSys\Entity\Item) {
						if ($item->meta() == 'CrossoverJJ') {
							if (isset($newMeta['position']) and isset($newMeta['level'])) {
								$newMeta = $newMeta['position'] . '/' . $newMeta['level'];
							}
							else {
								$validationErrors['item' . $item->id()] = sprintf('Position and level must be specified for %s.', $item->name());
								continue;
							}
						}
						
						if ($item->registeredMeta() != $newMeta) {
							$this->db->query('UPDATE regsys__registrations SET itemMeta = ? WHERE dancerID = ? AND itemID = ?', array($newMeta, $dancer->id(), $item->id()));
						}
					}
					
					unset($_POST['itemMeta'][$itemID]);
				}
			}
			
			$this->viewHelper->setErrors($validationErrors);
			
			# Reload dancer to reset registeredItems
			$dancer = $this->getRequestedDancer();
		}
		
		return array('dancer' => $dancer);
	}
}

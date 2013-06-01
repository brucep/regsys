<?php

namespace RegSys\Controller\BackEndController;

class AdminDancer extends \RegSys\Controller\BackEndController
{
	public function getContext()
	{
		if (isset($_GET['new'])) {
			$dancer = new \RegSys\Entity\Dancer();
			$editing = false;
		}
		else {
			$dancer = $this->getRequestedDancer();
			$editing = true;
		}
		
		if (!empty($_POST)) {
			$dancer = new \RegSys\Entity\Dancer($_POST);
			$validationErrors = $dancer->validate($this->event, true);
			
			if (!$validationErrors) {
				if (!$editing) {
					$dancer->add($this->event->id());
					$editing = true;
				}
				else {
					$this->db->query('UPDATE regsys__dancers SET firstName = ?, lastName = ?, email = ?, position = ?, levelID = ?, volunteer = ?, dateRegistered = ?, paymentMethod = ?, phone = ? WHERE dancerID = ?;', array(
						$dancer->firstName(),
						$dancer->lastName(),
						$dancer->email(),
						$dancer->position(),
						$dancer->levelID(),
						$dancer->volunteer(),
						$dancer->dateRegistered(),
						$dancer->paymentMethod(),
						$dancer->phone(),
						$dancer->id()));
				}
			}
			else {
				$this->viewHelper->setErrors($validationErrors);
			}
		}
		
		$this->viewHelper->setThing($dancer);
		
		return array('dancer' => $dancer, 'editing' => $editing);
	}
}

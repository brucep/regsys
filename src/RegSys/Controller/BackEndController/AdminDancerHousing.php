<?php

namespace RegSys\Controller\BackEndController;

class AdminDancerHousing extends \RegSys\Controller\BackEndController
{
	public function getContext()
	{
		if (!$this->event->hasHousingSupport()) {
			throw new \Exception('This event does not have housing support.');
		}
		
		$dancer = $this->getRequestedDancer();
		$editing = ($dancer->needsHousing() or $dancer->isHousingProvider());
		
		if (!empty($_POST)) {
			$_POST['eventID'] = $this->event->id();
			$_POST['dancerID'] = (int) $_GET['dancerID'];
			
			$dancer = new \RegSys\Entity\Dancer($_POST);
			$validationErrors = $dancer->validateHousing($this->event, true);
			
			if (!$validationErrors) {
				if (!$editing) {
					$dancer->addHousing();
					$editing = true;
				}
				else {
					$this->db->query('UPDATE regsys__housing SET housingType = ?, housingSpotsAvailable = ?, housingNights = ?, housingGender = ?, housingBedtime = ?, housingPets = ?, housingSmoke = ?, housingFromScene = ?, housingComment = ? WHERE dancerID = ?;', array(
						$dancer->housingType(),
						$dancer->housingSpotsAvailable(),
						$dancer->housingNights(),
						$dancer->housingGender(),
						$dancer->housingBedtime(),
						$dancer->housingPets(),
						$dancer->housingSmoke(),
						$dancer->housingFromScene(),
						$dancer->housingComment(),
						$dancer->id()));
				}
				
				# Reload dancer
				$dancer = $this->getRequestedDancer();
			}
			else {
				$this->viewHelper->setErrors($validationErrors);
			}
		}
		
		$this->viewHelper->setThing($dancer);
		
		return array('dancer' => $dancer, 'editing' => $editing);
	}
}

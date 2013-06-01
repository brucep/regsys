<?php

namespace RegSys\Controller\BackEndController;

class ReportRegistrationList extends \RegSys\Controller\BackEndController
{	
	public function getContext()
	{
		$dancers = $this->event->dancers();
		
		if (!empty($_POST)) {
			foreach ($dancers as $dancer) {
				$dancer->updatePaymentConfirmation(
					(int) isset($_POST['paymentConfirmed'][$dancer->id()]),
					(int) isset($_POST['paymentOwed'][$dancer->id()]) ? $_POST['paymentOwed'][$dancer->id()] : $dancer->paymentOwed());
			}
		}
		
		return array('dancers' => $dancers);
	}
}

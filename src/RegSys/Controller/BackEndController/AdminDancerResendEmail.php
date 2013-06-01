<?php

namespace RegSys\Controller\BackEndController;

class AdminDancerResendEmail extends \RegSys\Controller\BackEndController
{
	public function getContext()
	{
		$dancer = $this->getRequestedDancer();
		$dancer->sendConfirmationEmail($this->event, $this->loadTwig(), $this->container['notifyUrl']);
		
		return sprintf('%s%s&dancerID=%d&sentConfirmationEmail=true', $this->requestHref, 'ReportDancer', rawurlencode($dancer->id()));
	}
}

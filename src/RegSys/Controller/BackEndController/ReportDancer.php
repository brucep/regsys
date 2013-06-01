<?php

namespace RegSys\Controller\BackEndController;

class ReportDancer extends \RegSys\Controller\BackEndController
{	
	public function getContext()
	{
		return array('dancer' => $this->getRequestedDancer(), 'notifyUrl' => $this->container['notifyUrl']);
	}
}

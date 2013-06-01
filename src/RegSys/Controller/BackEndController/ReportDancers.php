<?php

namespace RegSys\Controller\BackEndController;

class ReportDancers extends \RegSys\Controller\BackEndController
{	
	public function getContext()
	{
		return array('dancers' => $this->event->dancers());
	}
}

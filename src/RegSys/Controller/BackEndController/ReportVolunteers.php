<?php

namespace RegSys\Controller\BackEndController;

class ReportVolunteers extends \RegSys\Controller\BackEndController
{	
	public function getContext()
	{
		return array('volunteers' => $this->event->dancersWhere(array(':volunteer' => 1)));
	}
}

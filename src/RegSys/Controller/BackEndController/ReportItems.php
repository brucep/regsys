<?php

namespace RegSys\Controller\BackEndController;

class ReportItems extends \RegSys\Controller\BackEndController
{	
	public function getContext()
	{
		return array('items' => $this->event->items());
	}
}

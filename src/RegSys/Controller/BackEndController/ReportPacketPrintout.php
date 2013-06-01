<?php

namespace RegSys\Controller\BackEndController;

class ReportPacketPrintout extends \RegSys\Controller\BackEndController
{	
	public function getContext()
	{
		return array('dancers' => $this->event->dancers());
	}
}

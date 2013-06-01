<?php

namespace RegSys\Controller\BackEndController;

class ReportIndex extends \RegSys\Controller\BackEndController
{	
	public function getContext()
	{
		return array('events' => $this->db->fetchAll('SELECT * FROM regsys__events ORDER BY datePayPal DESC', array(), '\RegSys\Entity\Event'));
	}
}

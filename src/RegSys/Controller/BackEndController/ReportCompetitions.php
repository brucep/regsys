<?php

namespace RegSys\Controller\BackEndController;

class ReportCompetitions extends \RegSys\Controller\BackEndController
{	
	public function getContext()
	{
		return array('items' => $this->db->fetchAll('SELECT * FROM regsys__items WHERE eventID = ? AND type = ? ORDER BY itemID ASC', array($this->event->id(), 'competition'), '\RegSys\Entity\Item'));
	}
}

<?php

namespace RegSys\Controller\BackEndController;

class AdminDancerHousingDelete extends \RegSys\Controller\BackEndController
{
	public function getContext()
	{
		$dancer = $this->getRequestedDancer();
		
		if (isset($_POST['confirmed'])) {
			$this->db->query('DELETE FROM regsys__housing WHERE eventID = ? AND dancerID = ?', array($this->event->id(), $dancer->id()));
			
			return sprintf('%s%s&dancerID=%d&deleted=%s', $this->requestHref, 'ReportDancer', $dancer->id(), rawurlencode('housing information'));
		}
		
		return array('dancer' => $dancer);
	}
}

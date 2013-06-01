<?php

namespace RegSys\Controller\BackEndController;

class AdminDancerDelete extends \RegSys\Controller\BackEndController
{
	public function getContext()
	{
		$dancer = $this->getRequestedDancer();
		
		if (isset($_POST['confirmed'])) {
			$this->db->query('DELETE FROM regsys__registrations WHERE eventID = ? AND dancerID = ?', array($this->event->id(), $dancer->id()));
			$this->db->query('DELETE FROM regsys__housing       WHERE eventID = ? AND dancerID = ?', array($this->event->id(), $dancer->id()));
			$this->db->query('DELETE FROM regsys__dancers       WHERE eventID = ? AND dancerID = ?', array($this->event->id(), $dancer->id()));
			
			return sprintf('%s%s&deleted=%s', $this->requestHref, 'ReportDancers', rawurlencode($dancer->name()));
		}
		
		return array('dancer' => $dancer);
	}
}

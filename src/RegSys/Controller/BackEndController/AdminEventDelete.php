<?php

namespace RegSys\Controller\BackEndController;

class AdminEventDelete extends \RegSys\Controller\BackEndController
{
	public function getContext()
	{
		if (isset($_POST['confirmed'])) {
			$this->requestHref = preg_replace('/&eventID=\d+/', '', $this->requestHref);
			
			$this->db->query('DELETE FROM regsys__registrations WHERE eventID = ?;', array($this->event->id()));
			$this->db->query('DELETE FROM regsys__housing       WHERE eventID = ?;', array($this->event->id()));
			$this->db->query('DELETE FROM regsys__dancers       WHERE eventID = ?;', array($this->event->id()));
			
			if (isset($_GET['registrationsOnly'])) {
				return sprintf('%s%s&deleted=%s&registrationsOnly', $this->requestHref, 'ReportIndex', rawurlencode($this->event->name()));
			}
			else {
				$this->db->query('DELETE FROM regsys__item_prices     WHERE eventID = ?;', array($this->event->id()));
				$this->db->query('DELETE FROM regsys__items           WHERE eventID = ?;', array($this->event->id()));
				$this->db->query('DELETE FROM regsys__event_discounts WHERE eventID = ?;', array($this->event->id()));
				$this->db->query('DELETE FROM regsys__event_levels    WHERE eventID = ?;', array($this->event->id()));
				$this->db->query('DELETE FROM regsys__events          WHERE eventID = ?;', array($this->event->id()));
				
				return sprintf('%s%s&deleted=%s', $this->requestHref, 'ReportIndex', rawurlencode($this->event->name()));
			}
		}
		
		return array();
	}
}

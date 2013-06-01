<?php

namespace RegSys\Controller\BackEndController;

class ReportMoney extends \RegSys\Controller\BackEndController
{	
	public function getContext()
	{
		$total = $this->db->fetchColumn('SELECT SUM(price) FROM regsys__registrations WHERE eventID = ?', array($this->event->id()));
		$total += $this->db->fetchColumn('SELECT SUM(paypalFee) FROM regsys__dancers  WHERE eventID = ?', array($this->event->id()));
		
		$groups = array('Mail' => array(), 'PayPal' => array());
		
		foreach ($this->event->dancers() as $dancer) {
			$groups[$dancer->paymentMethod()][] = $dancer;
		}
		
		return array('groups' => $groups, 'total'  => $total);
	}
}

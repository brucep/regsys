<?php

namespace RegSys\Controller\BackEndController;

class AdminItemDelete extends \RegSys\Controller\BackEndController
{
	public function getContext()
	{
		$item = $this->getRequestedItem();
		
		if (isset($_POST['confirmed'])) {
			$this->db->query('DELETE FROM regsys__registrations WHERE eventID = ? AND itemID = ?', array($this->event->id(), $item->id()));
			$this->db->query('DELETE FROM regsys__items         WHERE eventID = ? AND itemID = ?', array($this->event->id(), $item->id()));
			
			return sprintf('%s%s&deleted=%s', $this->requestHref, 'ReportItems', rawurlencode($item->name()));
		}
		
		return array('item' => $item);
	}
}

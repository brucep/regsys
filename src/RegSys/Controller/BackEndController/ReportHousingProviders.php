<?php

namespace RegSys\Controller\BackEndController;

class ReportHousingProviders extends \RegSys\Controller\BackEndController
{	
	public function getContext()
	{
		return array(
			'dancers'      => $this->event->dancersWhere(array(':housingType' => 1)),
			'housingCount' => $this->db->fetchColumn('SELECT SUM(housingSpotsAvailable) FROM regsys__housing WHERE eventID = ? AND housingType = 1', array($this->event->id())),
			'housingType'  => 'Housing Providers',
			'housingHref'  => 'housingProviders',
			);
	}
	
	public function render(array $context)
	{
		return parent::render($context, 'ReportHousing.html');
	}
}

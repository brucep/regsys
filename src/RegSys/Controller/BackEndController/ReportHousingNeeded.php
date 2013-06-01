<?php

namespace RegSys\Controller\BackEndController;

class ReportHousingNeeded extends \RegSys\Controller\BackEndController
{	
	public function getContext()
	{
		$dancers = $this->event->dancersWhere(array(':housingType' => 0));
		
		return array(
			'dancers'      => $dancers,
			'housingCount' => count($dancers),
			'housingType'  => 'Housing Needed',
			'housingHref'  => 'housingNeeded',			
			);
	}
	
	public function render(array $context)
	{
		return parent::render($context, 'ReportHousing.html');
	}
}

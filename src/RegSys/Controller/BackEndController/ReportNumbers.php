<?php

namespace RegSys\Controller\BackEndController;

class ReportNumbers extends \RegSys\Controller\BackEndController
{	
	public function getContext()
	{
		# Dancers
		$lists['Dancers']['Total']   = sprintf('%d [%d Mail; %d PayPal]', $this->event->countDancers(), $this->event->countDancers(array(':paymentMethod' => 'Mail')), $this->event->countDancers(array(':paymentMethod' => 'PayPal')));
		$lists['Dancers']['Leads']   = $this->event->countDancers(array(':position' => 0));
		$lists['Dancers']['Follows'] = $this->event->countDancers(array(':position' => 1));
		$lists['Dancers']['Ratio']   = @round($lists['Dancers']['Follows'] / $lists['Dancers']['Leads'], 2);
		
		# Levels
		if ($this->event->hasLevels()) {
			foreach ($this->event->levels() as $level) {
				$lists['Levels (Dancers in Classes)'][$level->levelLabel] = sprintf('%d leads, %d follows',
					$this->db->fetchColumn('SELECT COUNT(dancerID) FROM regsys__registrations AS r LEFT JOIN regsys__dancers AS d USING(dancerID) LEFT JOIN regsys__items AS i USING(itemID) WHERE r.eventID = ? AND d.levelID = ? AND d.position = ? AND i.meta = "Count for Classes"', array($this->event->id(), $level->levelID, 0)),
					$this->db->fetchColumn('SELECT COUNT(dancerID) FROM regsys__registrations AS r LEFT JOIN regsys__dancers AS d USING(dancerID) LEFT JOIN regsys__items AS i USING(itemID) WHERE r.eventID = ? AND d.levelID = ? AND d.position = ? AND i.meta = "Count for Classes"', array($this->event->id(), $level->levelID, 1)));
			}
		}
		
		# Packages and Competitions
		$tieredPackages   = $this->db->fetchAll('SELECT * FROM regsys__items WHERE itemID IN     (SELECT DISTINCT itemID FROM regsys__item_prices WHERE eventID = ?)', array($this->event->id()), '\RegSys\Entity\Item');
		$packagesAndComps = $this->db->fetchAll('SELECT * FROM regsys__items WHERE itemID NOT IN (SELECT DISTINCT itemID FROM regsys__item_prices WHERE eventID = :eventID) AND eventID = :eventID AND type != "shirt"', array(':eventID' => $this->event->id()), '\RegSys\Entity\Item');
		
		# Shirts
		$shirts = $this->db->fetchAll('SELECT * FROM regsys__items WHERE eventID = ? AND type = "shirt" ORDER BY itemID ASC', array($this->event->id()), '\RegSys\Entity\Item');
		$sizes = array();
		foreach ($shirts as $item) {
			$sizes = array_merge($sizes, $item->sizes());
		}
		$sizes = array_unique($sizes);
		
		return array(
			'lists'  => $lists,
			'shirts' => $shirts,
			'sizes'  => $sizes,
			'tieredPackages' => $tieredPackages,
			'packagesAndComps' => $packagesAndComps,
			);
	}
}

<?php

namespace RegSys\Controller\BackEndController;

class AdminItem extends \RegSys\Controller\BackEndController
{
	public function getContext()
	{
		if (isset($_GET['new'])) {
			$item = new \RegSys\Entity\Item();
			$editing = false;
		}
		else {
			$item = $this->getRequestedItem();
			$editing = true;
		}
		
		if (!empty($_POST)) {
			$item = new \RegSys\Entity\Item($_POST);
			$validationErrors = $item->validate();
			
			if (!$validationErrors) {
				if (!$editing) {
					$this->db->query('INSERT regsys__items VALUES (?, NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?);', array(
						$this->event->id(),
						$item->name(),
						$item->type(),
						$item->pricePrereg(),
						$item->priceDoor(),
						$item->limitTotal(),
						$item->limitPerPosition(),
						$item->dateExpires(),
						$item->meta(),
						$item->description(),
						));
					
					$item = $this->event->itemByID($this->db->lastInsertID());
					$editing = true;
				}
				else {
					$this->db->query('UPDATE regsys__items SET name = ?, type = ?, pricePrereg = ?, priceDoor = ?, limitTotal = ?, limitPerPosition = ?, dateExpires = ?, meta = ?, description = ? WHERE itemID = ?', array(
						$item->name(),
						$item->type(),
						$item->pricePrereg(),
						$item->priceDoor(),
						$item->limitTotal(),
						$item->limitPerPosition(),
						$item->dateExpires(),
						$item->meta(),
						$item->description(),
						$item->id(),
						));
				}
				
				if ($item->type() == 'package') {
					$tiers = $item->priceTiers();
					
					foreach ($_POST['priceTiersCount'] as $key => $count) {
						if (isset($tiers[$count]) and isset($_POST['priceTiersDelete'][$count])) {
							$this->db->query('DELETE FROM regsys__item_prices WHERE itemID = ? AND tierCount = ?', array(
								$item->id(),
								$count,
								));
						}
						elseif ($count) {
							$price = isset($_POST['priceTiersPrice'][$key]) ? (int) $_POST['priceTiersPrice'][$key] : 0;
							
							if (!isset($tiers[$count])) {
								$this->db->query('INSERT regsys__item_prices VALUES (?, ?, ?, ?);', array(
									$this->event->id(),
									$item->id(),
									$count,
									$price,
									));
							}
							elseif (isset($tiers[$count])) {
								$this->db->query('UPDATE regsys__item_prices SET tierCount = ?, tierPrice = ? WHERE itemID = ? AND tierCount = ?', array(
									$count,
									$price,
									$item->id(),
									$count,
									));
							}
						}
					}
					
					unset($_POST['priceTiersCount'], $_POST['priceTiersPrice'], $_POST['priceTiersDelete'], $tiers);
				}
				
				# Reload item
				$item = $this->event->itemByID($item->id());
			}
			else {
				$this->viewHelper->setErrors($validationErrors);
			}
		}
		
		$this->viewHelper->setThing($item);
		
		return array('item' => $item, 'editing' => $editing);
	}
}

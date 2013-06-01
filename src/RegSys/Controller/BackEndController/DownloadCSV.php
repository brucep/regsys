<?php

namespace RegSys\Controller\BackEndController;

class DownloadCSV extends \RegSys\Controller\BackEndController
{	
	public function getContext()
	{
		if (!isset($_GET['data']) or !in_array($_GET['data'], array('competitions', 'dancers', 'housingNeeded', 'housingProviders', 'volunteers'))) {
			throw new \Exception('Unable to handle to data for: ' . $_GET['data']);
		}
		
		$rows = array();
		
		if ($_GET['data'] == 'housingNeeded') {
			$filename = 'Housing Needed';
			
			$rows[0] = array('Last Name', 'First Name', 'Email Address');
			$rows[0] = array_merge($rows[0], explode(',', $this->event->housingNights()));
			$rows[0] = array_merge($rows[0], array(
				'Gender',
				'No Pets',
				'No Smoke',
				'Bedtime',
				'From',
				'Comment',
				'Date Registered'));
			
			$dancers = $this->event->dancersWhere(array(':housingType' => 0));
			
			foreach ($dancers as $dancer) {
				$row = array($dancer->lastName(), $dancer->firstName(), $dancer->email());
				
				foreach (explode(',', $this->event->housingNights()) as $night) {
					$row[] = in_array($night, explode(',', $dancer->housingNights())) ? '•' : '';
				}
				
				$row[] = $dancer->housingGender();
				$row[] = $dancer->prefersNoPets()  ? '•' : '';
				$row[] = $dancer->prefersNoSmoke() ? '•' : '';
				$row[] = $dancer->housingBedtime();
				$row[] = $dancer->housingFromScene();
				$row[] = $dancer->housingComment();
				$row[] = date('Y-m-d, h:i A', $dancer->dateRegistered());
				
				$rows[] = $row;
			}
		}
		elseif ($_GET['data'] == 'housing_providers') {
			$filename = 'Housing Providers';
			
			$rows[0] = array('Last Name', 'First Name', 'Email Address');
			$rows[0] = array_merge($rows[0], explode(',', $this->event->housingNights()));
			$rows[0] = array_merge($rows[0], array(
				'Gender',
				'Spots',
				'Has Pets',
				'Smokes',
				'Bedtime',
				'Comment',
				'Date Registered'));
			
			$dancers = $this->event->dancers_where(array(':housingType' => 1));
			
			foreach ($dancers as $dancer) {
				$row = array($dancer->lastName(), $dancer->firstName(), $dancer->email());
				
				foreach (explode(',', $this->event->housingNights()) as $night) {
					$row[] = in_array($night, explode(',', $dancer->housingNights())) ? '•' : '';
				}

				$row[] = $dancer->housingGender();
				$row[] = $dancer->housingSpotsAvailable();
				$row[] = $dancer->hasPets()  ? '•' : '';
				$row[] = $dancer->hasSmoke() ? '•' : '';
				$row[] = $dancer->housingBedtime();
				$row[] = $dancer->housingComment();
				$row[] = date('Y-m-d, h:i A', $dancer->dateRegistered());
				
				$rows[] = $row;
			}
		}
		else {
			if ($_GET['data'] == 'competitions') {
				$filename = 'Competitors';
				$dancers  = $this->db->fetchAll('SELECT DISTINCT * FROM regsys__registrations AS r LEFT JOIN regsys__items AS i USING(itemID) LEFT JOIN regsys__dancers USING(dancerID) WHERE r.eventID = ? AND i.type = "competition" ORDER BY lastName ASC, firstName ASC', array($this->event->id()), '\RegSys\Entity\Dancer');
			}
			elseif ($_GET['data'] == 'dancers') {
				$filename = 'Dancers';
				$dancers  = $this->event->dancers();
			}
			elseif ($_GET['data'] == 'volunteers') {
				$filename = 'Volunteers';
				$dancers  = $this->event->dancersWhere(array(':volunteer' => 1));
			}
			
			$rows[0] = array('Last Name', 'First Name', 'Email Address');
			
			foreach ($dancers as $dancer) {
				$rows[] = array($dancer->lastName(), $dancer->firstName(), $dancer->email());
			}
		}
		
		$output = fopen('php://output', 'w');
		
		if (!$output) {
			throw new \Exception('Unable to open output file.');
		}
		
		$filename .= sprintf(' for %s - %s.csv', $this->event->name(), date('Y-m-d'));
		
		header('Content-Type: text/csv');
		header(sprintf('Content-Disposition: attachment; filename="%s"', $filename));
		header('Pragma: no-cache');
		header('Expires: 0');
		
		foreach ($rows as $row) {
			fputcsv($output, $row);
		}
		
		exit();
	}
}

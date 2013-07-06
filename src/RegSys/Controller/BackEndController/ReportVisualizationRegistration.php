<?php

namespace RegSys\Controller\BackEndController;

class ReportVisualizationRegistration extends \RegSys\Controller\BackEndController
{	
	public function getContext()
	{
		$data = array(0 => array('x'));
		$colors = array();
		
		# Order ASC so line for newer events overlap lines for earlier events
		$events = $this->db->fetchAll('SELECT * FROM regsys__events WHERE visualization = 1 ORDER BY name ASC', array(), '\RegSys\Entity\Event');
		
		# An event without dancers will cause data errors in Google's JavaScript
		$events = array_filter($events, function ($event) { return $event->countDancers(); });
		
		foreach ($events as $event) {
			$data[0][] = $event->name();
			$colors[] = $event->visualizationColor();
		}
		
		$dates = $this->db->fetchObject('SELECT MIN(DISTINCT FROM_UNIXTIME(dateRegistered, "%m-%d")) AS start, MAX(DISTINCT FROM_UNIXTIME(dateRegistered, "%m-%d")) AS end FROM regsys__dancers AS d JOIN regsys__events AS e USING(eventID) WHERE dateRegistered > 0 AND visualization = 1');
		
		$dates = $this->createDateRange('2012-' . $dates->start, '2012-' . $dates->end);
		
		$i = 1;
		foreach ($dates as $date) {
			$data[$i] = array($date);
			
			foreach ($events as $event) {
				if ($event->id() == $this->options['currentEventID'] and strtotime(date('Y-') . $date) > time()) {
					$data[$i][] = null;
				}
				elseif ($date > date('m-d', $event->datePayPal())) {
					$data[$i][] = null;
				}
				else {
					$count = (int) $this->db->fetchColumn('SELECT COUNT(dancerID) FROM regsys__dancers WHERE FROM_UNIXTIME(dateRegistered, "%m-%d") <= ? AND eventID = ?', array($date, $event->id()));
					$data[$i][] = $count > 0 ? $count : null;
				}
			}
			
			$i++;
		}
		
		return array('registrationData' => $data, 'colors' => $colors);
	}
	
	protected function createDateRange($start, $end)
	{
		// Modified from http://boonedocks.net/mike/archives/137-Creating-a-Date-Range-Array-with-PHP.html
		
		$range = array();
		$start = strtotime($start);	
		$end   = strtotime($end);
		
		if ($start > $end) {
			return $this->createDateRangeArray($end, $start);
		}
		
		while($start <= $end) {
			$range[] = date('m-d', $start);
			$start = strtotime('+ 1 day', $start);
		}
		
		return $range;
	}
}

<?php

$events = $database->fetchAll('SELECT event_id, name, date_paypal_prereg_end FROM regsys_events WHERE visualization = 1 ORDER BY name ASC');
$data   = array(0 => array('x'));

foreach ($events as $event) {
	$data[0][] = $event->name;
}

// Modified from http://boonedocks.net/mike/archives/137-Creating-a-Date-Range-Array-with-PHP.html
function createDateRange($start, $end) {
	$range = array();
	$start = strtotime($start);	
	$end   = strtotime($end);
	
	if ($start > $end) {
		return createDateRangeArray($end, $start);
	}
	
	while($start <= $end) {
		$range[] = date('m-d', $start);
		$start = strtotime('+ 1 day', $start);
	}
	
	return $range;
}

$dates = $database->fetchObject('SELECT MIN(DISTINCT FROM_UNIXTIME(date_registered, "%%m-%%d")) AS start, MAX(DISTINCT FROM_UNIXTIME(date_registered, "%%m-%%d")) AS end FROM regsys_dancers WHERE date_registered > 1 AND event_id IN (SELECT event_id FROM regsys_events WHERE visualization = 1)');
$dates = createDateRange('2012-' . $dates->start, '2012-' . $dates->end);

$i = 1;
foreach ($dates as $date) {
	$data[$i] = array($date);
	
	foreach ($events as $event) {
		if ($event->event_id == $options['current_event_id'] and strtotime(date('Y-') . $date) > time()) {
			$data[$i][] = null;
		}
		elseif ($date > date('m-d', $event->date_paypal_prereg_end)) {
			$data[$i][] = null;
		}
		else {
			$count = (int) $database->fetchColumn('SELECT COUNT(dancer_id) FROM regsys_dancers WHERE FROM_UNIXTIME(date_registered, "%%m-%%d") <= ? AND event_id = ?', array($date, $event->event_id));
			$data[$i][] = $count > 0 ? $count : null;
		}
	}
	
	$i++;
}

$colors = $database->query('SELECT visualization_color FROM regsys_events WHERE visualization = 1 ORDER BY name ASC')->fetchAll(PDO::FETCH_COLUMN);

echo self::render_template('report-index-visualization.html', array('registration_data' => $data, 'colors' => $colors));

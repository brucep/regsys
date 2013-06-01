<?php

echo self::render_template('report-competitions.html', array(
	'event' => $event,
	'items' => $database->fetchAll('SELECT * FROM regsys_items WHERE event_id = ? AND type = ? ORDER BY item_id ASC', array($event->id(), 'competition'), 'RegistrationSystem_Model_Item')));

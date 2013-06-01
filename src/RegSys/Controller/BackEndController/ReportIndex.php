<?php

$events = $database->fetchAll('SELECT * FROM regsys_events ORDER BY date_paypal_prereg_end DESC', array(), 'RegistrationSystem_Model_Event');
echo self::render_template('report-index.html', array('events' => $events));

<?php

echo self::render_template('report-packet-printout.html', array(
	'event'   => $event,
	'dancers' => $event->dancers()));

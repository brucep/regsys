<?php

echo self::render_template('report-dancers.html', array(
	'event'   => $event,
	'dancers' => $event->dancers()));

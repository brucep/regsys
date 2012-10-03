<?php

echo self::render_template('report-volunteers.html', array(
	'event'      => $event,
	'volunteers' => $event->dancers_where(array(':status' => 1))));

<?php

function regsys_report_index_event($event)
{
	echo RegistrationSystem::render_template('report-index-event.html', array('event' => $event));
}

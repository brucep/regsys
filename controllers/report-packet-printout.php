<?php

function regsys_report_packet_printout($event)
{
	echo RegistrationSystem::render_template('report-packet-printout.html', array(
		'event'   => $event,
		'dancers' => $event->dancers()));
}

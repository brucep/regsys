<?php

function regsys_report_competitions($event)
{
	echo RegistrationSystem::render_template('reports/competitions.html', array(
		'event' => $event,
		'items' => $event->items_where(array(':type' => 'competition'))));
}

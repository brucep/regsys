<?php

function regsys_admin_event_add()
{
	@include dirname(__FILE__) . '/admin-event-edit.php';
	
	# Separate method used to avoid loading non-existent event
	regsys_admin_event_edit(null);
}

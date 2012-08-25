<?php

function regsys_report_reg_list($event)
{
	if (isset($_GET['vip_only']) and $_GET['vip_only'] == 'true') {
		$dancers = $event->dancers_where(array(':status' => 2));
	}
	else {
		$dancers = $event->dancers_where(array(':status' => 2), false); # false = not equal to 2
	}
	
	if (!empty($_POST)) {
		foreach ($dancers as $dancer) {
			$dancer->update_payment_confirmation(
				(int) isset($_POST['payment_confirmed'][$dancer->id()]),
				(int) isset($_POST['payment_owed'][$dancer->id()]) ? $_POST['payment_owed'][$dancer->id()] : $dancer->payment_owed());
		}
	}
	
	echo RegistrationSystem::render_template('reports/reg-list.html', array(
		'event' => $event,
		'dancers' => $dancers));
}

<?php

try {
	if (!$dancer->send_confirmation_email()) {
		throw new Exception('Email could not be sent to ' . $dancer->email());
	}
	
	wp_redirect(site_url('wp-admin/admin.php') . sprintf('?page=reg-sys&request=report_dancer&event_id=%d&dancer_id=%d&confirmation_email=true', $event->id(), $dancer->id()));
	exit();
}
catch (Exception $e) {
	if (isset($_GET['noheader'])) {
		require_once ABSPATH . 'wp-admin/admin-header.php';
	}
	
	echo $e->getMessage();
}

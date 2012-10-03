<?php

if (isset($_POST['confirmed'])) {
	$database->query('DELETE FROM regsys_registrations WHERE event_id = ? AND dancer_id = ?',         array($event->id(), $dancer->id()));
	$database->query('DELETE FROM regsys_housing       WHERE event_id = ? AND dancer_id = ? LIMIT 1', array($event->id(), $dancer->id()));
	$database->query('DELETE FROM regsys_dancers       WHERE event_id = ? AND dancer_id = ? LIMIT 1', array($event->id(), $dancer->id()));
	
	wp_redirect(site_url('wp-admin/admin.php') . sprintf('?page=reg-sys&request=report_index_event&event_id=%d&deleted_dancer=%s', $event->id(), rawurlencode($dancer->name())));
	exit();
}

# Needed if the confirmation checkbox wasn't checked.
if (isset($_GET['noheader'])) {
	require_once ABSPATH . 'wp-admin/admin-header.php';
}

echo self::render_template('admin-dancer-delete.html', array(
	'event'  => $event,
	'dancer' => $dancer));

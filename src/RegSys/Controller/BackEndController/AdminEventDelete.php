<?php

if (isset($_POST['confirmed'])) {
	$database->query('DELETE FROM regsys_registrations WHERE event_id = ?;', array($event->id()));
	$database->query('DELETE FROM regsys_housing       WHERE event_id = ?;', array($event->id()));
	$database->query('DELETE FROM regsys_dancers       WHERE event_id = ?;', array($event->id()));
	
	if (isset($_GET['registrations_only']) and $_GET['registrations_only'] == 'true') {
		wp_redirect(site_url('wp-admin/admin.php') . '?page=reg-sys&request=report_index&deleted_event=' . rawurlencode($event->name) . '&registrations_only=true');
		exit();
	}
	else {
		$database->query('DELETE FROM regsys_item_prices     WHERE event_id = ?;', array($event->id()));
		$database->query('DELETE FROM regsys_items           WHERE event_id = ?;', array($event->id()));
		$database->query('DELETE FROM regsys_event_discounts WHERE event_id = ?;', array($event->id()));
		$database->query('DELETE FROM regsys_event_levels    WHERE event_id = ?;', array($event->id()));
		$database->query('DELETE FROM regsys_events          WHERE event_id = ?;', array($event->id()));
		
		wp_redirect(site_url('wp-admin/admin.php') . '?page=reg-sys&request=report_index&deleted_event=' . rawurlencode($event->name));
		exit();
	}
}

# Needed if the confirmation checkbox wasn't checked.
if (isset($_GET['noheader'])) {
	require_once ABSPATH . 'wp-admin/admin-header.php';
}

echo self::render_template('admin-event-delete.html', array('event' => $event));

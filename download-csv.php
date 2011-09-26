<?php

# Load WordPress to get database settings.
# A separate file is required so that headers aren't loaded.
require dirname(dirname(dirname(dirname(__FILE__)))) . '/wp/wp-load.php';

class NSEvent_Download_CSV
{
	static private $database, $event;
	
	static public function handle_request()
	{
		try {
			if (!current_user_can('edit_pages')) {
				throw new Exception(__('Cheatin&#8217; uh?'));
			}
			
			self::$database = NSEvent::get_database_connection();
			NSEvent_Model::set_database(self::$database);
			
			if (isset($_GET['event_id'])) {
				if (!self::$event = NSEvent_Model_Event::get_event_by_id($_GET['event_id'])) {
					throw new Exception(sprintf('Event ID not found: %d', $_GET['event_id']));
				}
			}
			else {
				throw new Exception('Event ID not specified.');
			}
			
			if (!isset($_GET['request']) or !in_array($_GET['request'], array('competitions', 'dancers', 'housing_needed', 'housing_providers', 'volunteers'))) {
				throw new Exception('Unable to handle page request: ' . $_GET['request']);
			}
			
			list($rows, $filename) = self::$_GET['request']();
			
			$output = fopen('php://output', 'w');
			
			if (!$output) {
				throw new Exception('Unable to open output file.');
			}
			
			$filename .= sprintf(' for %s - %s.csv', self::$event->name(), date('Y-m-d'));
			
			header('Content-Type: text/csv');
			header(sprintf('Content-Disposition: attachment; filename="%s"', $filename));
			header('Pragma: no-cache');
			header('Expires: 0');
			
			foreach ($rows as $row) {
				fputcsv($output, $row);
			}
			
			exit;
		}
		catch (Exception $e) {
			@header('HTTP/1.1 404 Not Found');
			exit(esc_html($e->getMessage()));
		}
	}
	
	static private function competitions()
	{
		$dancers = self::$database->query('SELECT DISTINCT %1$s_dancers.`dancer_id` as dancer_id, last_name, first_name, email FROM %1$s_registrations LEFT JOIN %1$s_items USING(item_id) LEFT JOIN %1$s_dancers USING(dancer_id) WHERE %1$s_registrations.`event_id` = :event_id AND %1$s_items.`type` = "competition" ORDER BY %1$s_dancers.`last_name` ASC, %1$s_dancers.`first_name` ASC', array(':event_id' => self::$event->id()))->fetchAll(PDO::FETCH_CLASS, 'NSEvent_Model_Dancer');
		return array(self::email_for_dancers($dancers), 'Competitors');
	}
	
	static private function email_for_dancers(array $dancers)
	{
		$rows[0] = array('Last Name', 'First Name', 'Email Address');
		
		foreach ($dancers as $dancer) {
			$rows[] = array($dancer->last_name(), $dancer->first_name(), $dancer->email());
		}
		
		return $rows;
	}
	
	static private function dancers()
	{
		return array(self::email_for_dancers(self::$event->dancers()), 'Dancers');
	}
	
	static private function housing_needed()
	{
		$rows[0] = array('Last Name', 'First Name', 'Email Address');
		$rows[0] = array_merge($rows[0], self::$event->housing_nights());
		$rows[0] = array_merge($rows[0], array(
			'Gender',
			'No Pets',
			'No Smoke',
			'Bedtime',
			'From',
			'Comment',
			'Date Registered'));
		
		$dancers = self::$event->dancers_where(array(':housing_type' => 1));
		
		foreach ($dancers as $dancer) {
			$row = array($dancer->last_name(), $dancer->first_name(), $dancer->email());
			
			foreach (self::$event->housing_nights() as $night) {
				$row[] = in_array($night, $dancer->housing_nights()) ? '•' : '';
			}
			
			$row[] = $dancer->housing_gender();
			$row[] = $dancer->housing_prefers_no_pets()  ? '•' : '';
			$row[] = $dancer->housing_prefers_no_smoke() ? '•' : '';
			$row[] = $dancer->housing_bedtime();
			$row[] = $dancer->housing_from_scene();
			$row[] = $dancer->housing_comment();
			$row[] = $dancer->date_registered('Y-m-d, h:i A');
			
			$rows[] = $row;
		}
		
		return array($rows, 'Housing Needed');
	}
	
	static private function housing_providers()
	{
		$rows[0] = array('Last Name', 'First Name', 'Email Address');
		$rows[0] = array_merge($rows[0], self::$event->housing_nights());
		$rows[0] = array_merge($rows[0], array(
			'Gender',
			'Spots',
			'Has Pets',
			'Smokes',
			'Bedtime',
			'Comment',
			'Date Registered'));
		
		$dancers = self::$event->dancers_where(array(':housing_type' => 2));

		foreach ($dancers as $dancer) {
			$row = array($dancer->last_name(), $dancer->first_name(), $dancer->email());

			foreach (self::$event->housing_nights() as $night) {
				$row[] = in_array($night, $dancer->housing_nights()) ? '•' : '';
			}
			
			$row[] = $dancer->housing_gender();
			$row[] = $dancer->housing_spots_available();
			$row[] = $dancer->housing_has_pets()  ? '•' : '';
			$row[] = $dancer->housing_has_smoke() ? '•' : '';
			$row[] = $dancer->housing_bedtime();
			$row[] = $dancer->housing_comment();
			$row[] = $dancer->date_registered('Y-m-d, h:i A');
			
			$rows[] = $row;
		}
		
		return array($rows, 'Housing Providers');
	}
	
	static private function volunteers()
	{
		return array(self::email_for_dancers(self::$event->dancers_where(array(':status' => 1))), 'Volunteers');
	}
}

NSEvent_Download_CSV::handle_request();

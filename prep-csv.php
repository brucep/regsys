<?php

# Load WordPress to get database settings.
# A separate file is required so that headers aren't loaded. 
require dirname(dirname(dirname(dirname(__FILE__)))).'/wp/wp-load.php';

class NSEvent_CSVHelper
{
	static public $database, $event;
	
	static public function download(array $rows, $filename = '')
	{
		$output = fopen('php://output', 'w');
		if (!$output)
			throw new Exception('Unable to open output file.');
		
		// if ($filename != '' and substr($filename, strlen($filename) - 1) != ' ')
		// 	$filename .= ' '; # Add space after the prefix
		// $filename .= date('Y-m-d').'.csv';
		$filename .= sprintf(' %s.csv', date('Y-m-d'));
		
		header('Content-Type: text/csv');
		header(sprintf('Content-Disposition: attachment; filename="%s"', $filename));
		header('Pragma: no-cache');
		header('Expires: 0');
		
		foreach($rows as $row)
			fputcsv($output, $row);
		
		exit;
	}
	
	static public function connect_database()
	{
		global $wpdb;
		
		require dirname(__FILE__).'/includes/database.php';
		self::$database = NSEvent_Database::get_instance();
		self::$database->connect();
		self::$database->prefix = $wpdb->prefix.'nsevent';
	}
	
	static public function load_event()
	{
		if (isset($_GET['event_id']))
		{
			if (!self::$event = NSEvent_Event::find($_GET['event_id']))
				throw new Exception(sprintf('Event ID not found: %d', $_GET['event_id']));
			NSEvent_Model::$event = self::$event;
		}
		else
			throw new Exception('Event ID not specified.');
	}
}

NSEvent_CSVHelper::connect_database();
NSEvent_CSVHelper::load_event();

?>
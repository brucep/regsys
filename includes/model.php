<?php

abstract class NSEvent_Model
{
	static protected $database, $options;
	
	static public function set_database($database)
	{
		self::$database = $database;
	}
	
	static public function set_options($options)
	{
		self::$options = $options;
	}
		
	static protected function bit_field($input, array $fields, $return = '')
	{
		$result = array();
		$result_booleans = array();
		krsort($fields);
		
		foreach ($fields as $key => $value) {
			// When not using validated data, check if input is too large
			if ($input >= $key) {
				$result[$key] = $value;
				$result_booleans[$value] = true;
				$input -= $key;
			}
			else {
				$result_booleans[$value] = false;
			}
		}
		
		ksort($result);
		ksort($result_booleans);
		
		if ($return == 'string') {
			return ucwords(implode(', ', array_keys(array_filter($result_booleans))));
		}
		elseif ($return == 'booleans') {
			return $result_booleans;
		}
		else {
			return $result;
		}
	}
}

<?php

abstract class NSEvent_Model
{
	static public $database, $event;
	
	public function bit_field($input, array $fields, $return = '')
	{
		$result = array();
		$result_booleans = array();
		krsort($fields);
		
		foreach ($fields as $key => $value)
			// When not using validated data, check if input is too large
			if ($input >= $key)
			{
				$result[$key] = $value;
				$result_booleans[$value] = TRUE;
				$input -= $key;
			}
			else
				$result_booleans[$value] = FALSE;
		
		ksort($result);
		ksort($result_booleans);
		
		if ($return == 'string')
			return ucwords(implode(', ', array_keys(array_filter($result_booleans))));
		elseif ($return == 'booleans')
			return $result_booleans;
		else 
			return $result;
	}
}

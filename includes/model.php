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
}

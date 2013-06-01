<?php

namespace RegSys;

abstract class Entity
{
	static protected $db, $options;
	
	static public function setDatabase(\RegSys\Database $db)
	{
		self::$db = $db;
	}
	
	static public function setOptions(array $options)
	{
		self::$options = $options;
	}
}

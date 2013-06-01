<?php

namespace RegSys;

class ViewHelper
{
	protected $thing, $validationErrors = array();
	
	public function getError($key = null, $prefix = '', $suffix = '')
	{
		if (!isset($this->validationErrors[$key])) {
			return null;
		}
		else {
			return $prefix . htmlspecialchars($this->validationErrors[$key], ENT_NOQUOTES, 'UTF-8') . $suffix;
		}
	}
	
	public function hasErrors()
	{
		return !empty($this->validationErrors);
	}
	
	public function SetErrors(array $validationErrors)
	{
		$this->validationErrors = $validationErrors;
	}
	
	public function setThing($thing)
	{
		$this->thing = $thing;
	}
	
	public function getThingValue($key)
	{
		if (is_object($this->thing)) {
			# Dancer, Event, and Item Entities
			return $this->thing->$key();
		}
		elseif (is_array($this->thing)) {
			# Options and Registration Form
			if (strpos($key, '[') >= 1) {
				$key = explode('[', $key, 2);
				$parent_key = array_shift($key);
				
				$key = explode(']', current($key), 2);
				$child_key = array_shift($key);
				
				if (isset($this->thing[$parent_key]) and isset($this->thing[$parent_key][$child_key])) {
					return $this->thing[$parent_key][$child_key];
				}
				else {
					return null;
				}
			}
			else {
				return isset($this->thing[$key]) ? $this->thing[$key] : null;
			}
		}
	}
}

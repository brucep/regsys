<?php

class NSEvent_FormValidation
{
	static protected $rules = array(),
	          $errors = array(),
	          $validated = array(),
	          $error_messages = array(),
	          $error_delimiter_prefix = '<div class="nsevent-validation-error">',
	          $error_delimiter_suffix = '</div>';
	
	static public function set_error_messages(array $error_messages = array())
	{
		if (empty(self::$error_messages)) {
			self::$error_messages = array(
				'required'    => __('%s is a required field.', 'nsevent'),
				'in'          => __('%s does not have an acceptable value.', 'nsevent'),
				'max_length'  => __('%s is too long.', 'nsevent'),
				'valid_email' => __('%s is not a valid email address.', 'nsevent'),
			);
		}
		
		if (!empty($error_messages)) {
			self::$error_messages = array_merge(self::$error_messages, $error_messages);
		}
	}
	
	static public function validate()
	{
		$did_validate = true;
		
		if (empty(self::$rules)) {
			throw new NSEvent_FormValidation_Exception(__('No rules to validate.', 'nsevent'));
		}
		
		foreach (self::$rules as $key => $conditions) {
			if (isset(self::$validated[$key])) {
				continue;
			}
			
			self::$validated[$key] = true;
			
			foreach (explode('|', $conditions) as $condition) {
				# Get parameter for rule (i.e., "rule|rule[parameter]|rule")
				if (strpos($condition, '[') >= 1) {
					$condition = explode('[', $condition, 2);
					$callable = array_shift($condition);
					
					$condition = explode(']', current($condition), 2);
					$parameter = array_shift($condition);
					
					# Get condition name for error messages later.
					$condition = $callable;
					if ($parameter === '') {
						$parameter = null;
					}
				}
				else {
					$callable = $condition;
					$parameter = null;
				}
				
				if ($condition === 'if_set' or $condition === 'if_not_set') {
					if ($parameter == null) {
						throw new NSEvent_FormValidation_Exception(sprintf('%s rule must have a valid parameter.', $condition));
					}
					elseif (($condition === 'if_set' and !empty($_POST[$parameter])) or ($condition === 'if_not_set' and empty($_POST[$parameter]))) {
						continue;
					}
					else {
						break;
					}
				}
				elseif ($condition === 'if_key_value' or $condition === 'if_not_key_value') {
					if ($parameter == null or strpos($parameter, ',') === false) {
						throw new NSEvent_FormValidation_Exception(sprintf('%s rule must have a valid parameter.', $condition));
					}
					
					list($key, $value) = explode(',', $parameter, 2);
					
					if (($condition === 'if_key_value' and isset($_POST[$key]) and $_POST[$key] == $value) or ($condition === 'if_not_key_value' and isset($_POST[$key]) and $_POST[$key] != $value)) {
						continue;
					}
					else {
						break;
					}
				}
				
				if (is_callable(__CLASS__.'::_'.$callable)) {
					$callable = __CLASS__.'::_'.$callable;
				}
				elseif (!is_callable($callable)) {
					throw new NSEvent_FormValidation_Exception(sprintf('`%s` is not callable for rule `%s`.', $callable, $key));
				}
				
				if ($parameter == null) {
					// if (in_array($callable, array( __CLASS__.'::_required') )))
					if ($callable === __CLASS__.'::_required') {
						$result = call_user_func($callable, $key);
					}
					else {
						$result = call_user_func($callable, self::get_post_value($key));
					}
				}
				else {
					$result = call_user_func($callable, self::get_post_value($key), $parameter, $key);
				}
				
				if ($result === false) {
					unset(self::$validated[$key]);
					$did_validate = false;
					
					if (isset(self::$error_messages[$condition])) {
						self::$errors[$key] = sprintf(self::$error_messages[$condition], htmlspecialchars(ucwords(str_replace('_', ' ', $key)), ENT_QUOTES, 'UTF-8'));
					}
					elseif (!isset(self::$errors[$key])) {
						self::$errors[$key] = sprintf(__('%s has an invalid value.', 'nsevent'), htmlspecialchars(ucwords(str_replace('_', ' ', $key)), ENT_QUOTES, 'UTF-8'));
					}
					break;
				}
				elseif ($result !== true) {
					self::set_post_value($key, $result);
				}
			}
		}
		
		return $did_validate;
	}
	
	static public function add_rules(array $rules)
	{
		foreach ($rules as $name => $value) {
			self::$rules[$name] = $value;
		}
	}
	
	static public function add_rule($name, $value)
	{
		self::$rules[$name] = $value;
	}
	
	static public function set_error_delimiters($prefix, $suffix)
	{
		self::$error_delimiter_prefix = $prefix;
		self::$error_delimiter_suffix = $suffix;
	}
	
	static public function get_errors($prefix = null, $suffix = null, $escape = false)
	{
		if (empty(self::$errors)) {
			return false;
		}
		
		$output = array();
		
		foreach (self::$errors as $error) {
			$output[] = sprintf('%s%s%s',
				!is_string($prefix) ? self::$error_delimiter_prefix : $prefix,
				$escape !== true ? $error : htmlspecialchars($error, ENT_QUOTES, 'UTF-8'),
				!is_string($suffix) ? self::$error_delimiter_suffix : $suffix);
		}
		
		return implode("\n", $output);
	}
	
	static public function set_error($rule, $string)
	{
		self::$errors[$rule] = $string;
	}
	
	static public function get_error($rule, $prefix = null, $suffix = null, $escape = false)
	{
		if (isset(self::$errors[$rule])) {
			return sprintf('%s%s%s',
				!is_string($prefix) ? self::$error_delimiter_prefix : $prefix,
				$escape !== true ? self::$errors[$rule] : htmlspecialchars(self::$errors[$rule], ENT_QUOTES, 'UTF-8'),
				!is_string($suffix) ? self::$error_delimiter_suffix : $suffix);
		}
		else {
			return '';
		}
	}
	
	static public function reset()
	{
		self::$rules = array();
		self::$groups = array();
		self::$errors = array();
		self::$validated = array();
	}
	
	static protected function get_post_value($key)
	{
		if (strpos($key, '[') >= 1) {
			$key = explode('[', $key, 2);
			$parent_key = array_shift($key);
			
			$key = explode(']', current($key), 2);
			$child_key = array_shift($key);
			
			if (isset($_POST[$parent_key]) and isset($_POST[$parent_key][$child_key])) {
				return $_POST[$parent_key][$child_key];
			}
			else {
				return false;
			}
		}
		else {
			return isset($_POST[$key]) ? $_POST[$key] : false;
		}
	}
	
	static protected function set_post_value($key, $value)
	{
		if (strpos($key, '[') >= 1) {
			$key = explode('[', $key, 2);
			$parent_key = array_shift($key);
			
			$key = explode(']', current($key), 2);
			$child_key = array_shift($key);
			
			$_POST[$parent_key][$child_key] = $value;
		}
		else {
			$_POST[$key] = $value;
		}
	}
	
	#
	# Validation conditions
	#
	
	static protected function _required($key)
	{
		return !empty($_POST[$key]);
	}
	
	static protected function _greater_than($number, $minimum)
	{
		return ($number > $minimum);
	}
	
	static protected function _in($needle, $haystack, $key)
	{
		return in_array($needle, explode(',', $haystack));
	}
	
	static protected function _max_length($string, $max_length)
	{
		return (strlen($string) <= $max_length);
	}
	
	static protected function _valid_email($string)
	{
		return (bool) preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $string);
	}
}

class NSEvent_FormValidation_Exception extends Exception {}

<?php

class NSEvent_Form_Validation
{
	protected $rules = array(),
	          $errors = array(),
	          $validated = array(),
	          $error_messages = array(),
	          $error_delimiter_prefix = '<div class="error"><p><strong>',
	          $error_delimiter_suffix = '</strong></p></div>';
	
	public function __construct(array $error_messages = array())
	{
		$this->error_messages = array(
			'required'    => '%s is a required field.',
			'in'          => '%s does not have an acceptable value.',
			'max_length'  => '%s is too long.',
			'valid_email' => '%s is not a valid email address.',
			);
		
		$this->error_messages = array_merge($this->error_messages, $error_messages);
	}
	
	public function validate()
	{
		$did_validate = true;
		
		if (empty($this->rules)) {
			throw new NSEvent_FormValidation_Exception('No rules to validate.');
		}
		
		foreach ($this->rules as $key => $conditions) {
			if (isset($this->validated[$key])) {
				continue;
			}
			
			$this->validated[$key] = true;
			
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
				
				if (is_callable(array($this, "_$callable"))) {
					$callable = array($this, "_$callable");
				}
				elseif (!is_callable($callable)) {
					throw new NSEvent_FormValidation_Exception(sprintf('`%s` is not callable for rule `%s`.', $callable, $key));
				}
				
				if ($parameter == null) {
					$result = call_user_func($callable, self::get_post_value($key));
				}
				else {
					$result = call_user_func($callable, self::get_post_value($key), $parameter, $key);
				}
				
				if ($result === false) {
					unset($this->validated[$key]);
					$did_validate = false;
					
					if (strpos($key, '[') >= 1) {
						$displayable_key = explode('[', $key, 2);
						array_shift($displayable_key);
						$displayable_key = explode(']', current($displayable_key), 2);
						$displayable_key = array_shift($displayable_key);
					}
					else {
						$displayable_key = $key;
					}
					
					$displayable_key = htmlspecialchars(ucwords(str_replace('_', ' ', $displayable_key)), ENT_QUOTES, 'UTF-8');
					
					if (isset($this->error_messages[$condition])) {
						$this->errors[$key] = sprintf($this->error_messages[$condition], $displayable_key);
					}
					elseif (!isset($this->errors[$key])) {
						$this->errors[$key] = sprintf('%s has an invalid value.', $displayable_key);
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
	
	public function add_rules(array $rules)
	{
		foreach ($rules as $name => $value) {
			$this->rules[$name] = $value;
		}
	}
	
	public function add_rule($name, $value)
	{
		$this->rules[$name] = $value;
	}
	
	public function set_error_delimiters($prefix, $suffix)
	{
		$this->error_delimiter_prefix = $prefix;
		$this->error_delimiter_suffix = $suffix;
	}
	
	public function get_errors($prefix = null, $suffix = null, $escape = false)
	{
		if (empty($this->errors)) {
			return false;
		}
		
		$output = array();
		
		foreach ($this->errors as $error) {
			$output[] = sprintf('%s%s%s',
				!is_string($prefix) ? $this->error_delimiter_prefix : $prefix,
				$escape !== true ? $error : htmlspecialchars($error, ENT_QUOTES, 'UTF-8'),
				!is_string($suffix) ? $this->error_delimiter_suffix : $suffix);
		}
		
		return implode("\n", $output);
	}
	
	public function set_error($rule, $string)
	{
		$this->errors[$rule] = $string;
	}
	
	public function get_error($rule, $prefix = null, $suffix = null, $escape = false)
	{
		if (isset($this->errors[$rule])) {
			return sprintf('%s%s%s',
				!is_string($prefix) ? $this->error_delimiter_prefix : $prefix,
				$escape !== true ? $this->errors[$rule] : htmlspecialchars($this->errors[$rule], ENT_QUOTES, 'UTF-8'),
				!is_string($suffix) ? $this->error_delimiter_suffix : $suffix);
		}
		else {
			return '';
		}
	}
	
	public function reset()
	{
		$this->rules = array();
		$this->groups = array();
		$this->errors = array();
		$this->validated = array();
	}
	
	protected function get_post_value($key)
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
	
	protected function set_post_value($key, $value)
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
	
	protected function _required($string)
	{
		return !empty($string);
	}
	
	protected function _greater_than($number, $minimum)
	{
		return ($number > $minimum);
	}
	
	protected function _in($needle, $haystack, $key)
	{
		return in_array($needle, explode(',', $haystack));
	}
	
	protected function _max_length($string, $max_length)
	{
		return (strlen($string) <= $max_length);
	}
	
	protected function _valid_email($string)
	{
		return (bool) preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $string);
	}
}

class NSEvent_Form_Validation_Exception extends Exception {}

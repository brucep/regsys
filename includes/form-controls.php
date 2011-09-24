<?php

class NSEvent_Form_Controls
{
	public function checkbox($name, array $args = array())
	{
		if (!isset($args['value'])) {
			$args['value'] = '1';
		}
		
		if (!isset($args['checked'])) {
			$args['checked'] = false;
		}
		
		if (!isset($args['id']))
		{
		 	if (strpos($name, '[') !== false) {
				$args['id'] = '';
			}
			else {
				$args['id'] = $name;
			}
		}

		if (isset($args['id']) and $args['id'] !== '') {
			$args['id'] = sprintf(' id="%s"', htmlspecialchars($args['id'], ENT_QUOTES, 'UTF-8'));
		}
		
		printf('%6$s<input type="checkbox"%4$s value="%3$s" name="%1$s" %2$s%5$s/>%7$s',
			htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
			$args['id'],
			htmlspecialchars($args['value'], ENT_QUOTES, 'UTF-8'),
			self::_set_radio_or_checkbox($name, $args['value'], $args['checked']),
			(isset($args['disabled']) and $args['disabled']) ? ' disabled="disabled"' : '',
			!isset($args['label']) ? '' : '<label>',
			!isset($args['label']) ? '' : sprintf('&nbsp;%s</label>', $args['label']));
	}
	
	public function hidden($name, array $args = array())
	{
		if (!isset($args['value'])) {
			$args['value'] = self::_set_value($name, 1);
		}
		
		if (isset($args['suffix'])) {
			$name .= '_'.$args['suffix'];
		}
		
		printf('<input type="hidden" name="%1$s" value="%2$s" />',
			htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
			htmlspecialchars($args['value'], ENT_QUOTES, 'UTF-8'));
	}
	
	public function number($name, array $args = array(), $print = true)
	{
		if (!isset($args['value'])) {
			$args['value'] = '';
		}
		
		if (!isset($args['default_value'])) {
			$args['default_value'] = '';
		}
		
		$result = sprintf('<input type="number" name="%1$s" value="%2$s"%3$s%4$s%5$s%6$s%7$s />',
			htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
			htmlspecialchars((!isset($args['value']) ? self::_set_value($name, $args['default_value']) : $value), ENT_QUOTES, 'UTF-8'),
			!isset($args['id'])    ? '' : sprintf(' id="%s"',    htmlspecialchars($args['id'],    ENT_QUOTES, 'UTF-8')),
			!isset($args['class']) ? '' : sprintf(' class="%s"', htmlspecialchars($args['class'], ENT_QUOTES, 'UTF-8')),
			!isset($args['size'])  ? '' : sprintf(' size="%s"',  (int) $args['size']),
			!isset($args['min'])   ? '' : sprintf(' min="%s"',   (int) $args['min']),
			!isset($args['max'])   ? '' : sprintf(' max="%s"',   (int) $args['max']),
			!isset($args['step'])  ? '' : sprintf(' step="%s"',  (int) $args['step']));
		
		return ($print) ? print $result : $result;
	}
	
	public function radio($name, array $args = array())
	{
		if (!isset($args['value'])) {
			$args['value'] = '';
		}
		
		$args['default'] = (isset($args['default']) and $args['default'] === true);
		
		printf('%5$s<input type="radio" name="%1$s" value="%2$s"%3$s%4$s%7$s />&nbsp;%6$s',
			htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
			htmlspecialchars($args['value'], ENT_QUOTES, 'UTF-8'),
			!isset($args['id']) ? '' : sprintf(' id="%s"', htmlspecialchars($args['id'], ENT_QUOTES, 'UTF-8')),
			self::_set_radio_or_checkbox($name, $args['value'], $args['default']),
			!isset($args['label']) ? '' : '<label>',
			!isset($args['label']) ? '' : sprintf('%s</label>', $args['label']),
			(isset($args['disabled']) and $args['disabled']) ? ' disabled="disabled"' : '');
	}
	
	// public function select($name, array $options, array $args = array())
	// {
	// 	if (!isset($args['indent'])) {
	// 		$args['indent'] = '';
	// 	}
	// 	else {
	// 		$args['indent'] = htmlspecialchars($args['indent'], ENT_QUOTES, 'UTF-8');
	// 	}
	// 	
	// 	$result = sprintf('%2$s<select name="%1$s"%3$s%4$s>'."\n",
	// 		htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
	// 		$args['indent'],
	// 		!isset($args['id'])    ? '' : sprintf(' id="%s"', htmlspecialchars($args['id'], ENT_QUOTES, 'UTF-8')),
	// 		!isset($args['class']) ? '' : sprintf(' class="%s"', htmlspecialchars($args['class'], ENT_QUOTES, 'UTF-8'));
	// 	
	// 	foreach ($options as $key => $value) {
	// 		
	// 	}
	// 	
	// 	echo $result, "\n", $args['indent'], '</select>'."\n";
	// }
	
	public function text($name, array $args = array(), $echo = true)
	{
		if (isset($args['label'])) {
			if ($args['label'] === true) {
				$args['label'] = htmlspecialchars(ucwords(str_replace('_', ' ', $name)), ENT_QUOTES, 'UTF-8');
			}
			
			$args['label'] = sprintf('<label for="%2$s">%1$s</label>',
				$args['label'],
				htmlspecialchars($name, ENT_QUOTES, 'UTF-8'));
		}
		
		if (!isset($args['default_value'])) {
			$args['default_value'] = '';
		}
		
		$result = sprintf('%3$s<input type="%7$s" value="%2$s" name="%1$s" id="%1$s"%4$s%5$s%6$s%8$s />',
			htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
			htmlspecialchars((!isset($args['value']) ? self::_set_value($name, $args['default_value']) : $args['value']), ENT_QUOTES, 'UTF-8'),
			!isset($args['label'])       ? '' : $args['label'],
			!isset($args['size'])        ? '' : sprintf(' size="%s"', (int) $args['size']),
			!isset($args['maxlength'])   ? '' : sprintf(' maxlength="%s"', (int) $args['maxlength']),
			!isset($args['class'])       ? '' : sprintf(' class="%s"', $args['class']),
			!isset($args['type'])        ? 'text' : $args['type'],
			!isset($args['placeholder']) ? '' : sprintf(' placeholder="%s"', htmlspecialchars($args['placeholder'], ENT_QUOTES, 'UTF-8')));
		
		if ($echo) {
			echo $result;
		}
		else {
			return $result;
		}
	}
	
	public function textarea($name, array $args = array())
	{
		if (!isset($args['default'])) {
			$args['default'] = '';
		}
		
		printf('<textarea name="%1$s" id="%1$s" cols="%3$s" rows="%4$s"%5$s%6$s>%2$s</textarea>',
			htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
			!isset($args['value'])       ? self::_set_value($name, $args['default']) : htmlspecialchars($args['value'], ENT_NOQUOTES, 'UTF-8'),
			!isset($args['cols'])        ? '40' : (int) $args['cols'],
			!isset($args['rows'])        ? '6'  : (int) $args['rows'],
			!isset($args['class'])       ? '' : sprintf(' class="%s"', $args['class']),
			!isset($args['placeholder']) ? '' : sprintf(' placeholder="%s"', htmlspecialchars($args['placeholder'], ENT_QUOTES, 'UTF-8')));
	}
	
	public function _set_select($field, $value = '', $default = false)
	{
		if (self::_set_radio_or_checkbox($field, $value, $default)) {
			return ' selected="selected"';
		}
		else {
			return '';
		}
	}
	
	private function _set_radio_or_checkbox($field, $value = '', $default = false)
	{
		if (strpos($field, '[') >= 1) {
			$field_key = explode('[', $field, 2);
			$field = array_shift($field_key);
			
			$field_key = explode(']', current($field_key), 2);
			$field_key = array_shift($field_key);
			
			if (!isset($_POST[$field]) or !isset($_POST[$field][$field_key])) {
				return ($default === true) ? ' checked="checked"' : '';
			}
			elseif ($_POST[$field][$field_key] != $value) {
				return '';
			}
			else {
				return ' checked="checked"';
			}
		}
		else
		{
			if (!isset($_POST[$field])) {
		 		return ($default === true) ? ' checked="checked"' : '';
			}
			elseif (($field === '' or $value === '') or ($_POST[$field] != $value)) {
				return '';
			}
			else {
				return ' checked="checked"';
			}
		}
	}

	private function _set_value($field, $default = '')
	{
		if (strpos($field, '[') >= 1)
		{
			$field_key = explode('[', $field, 2);
			$field = array_shift($field_key);
			
			$field_key = explode(']', current($field_key), 2);
			$field_key = array_shift($field_key);
			
			return (isset($_POST[$field]) and isset($_POST[$field][$field_key])) ? $_POST[$field][$field_key] : $default;
		}
		else {
			return isset($_POST[$field]) ? $_POST[$field] : $default;
		}
	}
}

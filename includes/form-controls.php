<?php

class NSEvent_Form_Controls
{
	private $array_name;
	
	public function input_checkbox($key, array $parameters = array(), $type = 'checkbox')
	{
		$parameters = array_merge(array(
			'checked' => null,
			'default' => false,
			'value'   => 1,
			), $parameters);
		
		if ($parameters['checked'] == null) {
			if ((is_null($this->array_name) and !isset($_POST[$key])) or
			    !is_null($this->array_name) and !isset($_POST[$this->array_name][$key])) {
				$parameters['checked'] = ($default === true);
			}
			else {
				if (is_null($this->array_name)) {
					$parameters['checked'] = ($parameters['value'] == $_POST[$key]);
				}
				else {
					$parameters['checked'] = ($parameters['value'] == $_POST[$this->array_name][$key]);
				}
			}
		}
		
		return sprintf('<input type="%1$s"%4$s value="%3$s" name="%2$s"%5$s>',
			($type == 'radio') ? 'radio' : 'checkbox',
			$this->name($key),
			is_null($parameters['value']) ? esc_attr($this->post_value($key, '1')) : esc_attr($parameters['value']),
			$parameters['checked'] ? ' checked="checked"' : '',
			isset($parameters['attributes']) ? $this->attributes($attributes) : '');
	}
	
	public function input_hidden($key, $value = null)
	{
		return sprintf('<input type="hidden" name="%1$s" value="%2$s">',
			$this->name($key),
			is_null($value) ? esc_attr($this->post_value($key, '1')) : esc_attr($value));
	}
	
	public function input_radio($key, array $parameters = array())
	{
		return $this->input_checkbox($key, $parameters, 'radio');
	}
	
	public function input_select($key, array $options, array $parameters = array())
	{
		$parameters = array_merge(array(
			'default_option' => null,
			'indent' => '',
			), $parameters);
		
		$result = sprintf('<select name="%1$s"%2$s>',
			$this->name($key),
			isset($parameters['attributes']) ? $this->attributes($attributes) : '');
		
		foreach ($options as $opt_value => $opt_label) {
			$result .= sprintf("\n" . $parameters['indent'] . "\t" . '<option value="%2$s"%3$s>%1$s</option>',
				esc_html($opt_label),
				esc_attr($opt_value),
				($this->post_value($key, $parameters['default_option']) == $opt_value) ? ' selected="selected"' : '');
		}
		
		return $result . "\n" . $parameters['indent'] . '</select>';
	}
	
	public function input_text($key, $value = null, array $attributes = array())
	{
		$attributes = array_merge(array('type' => 'text'), $attributes);
		
		$output = sprintf('<input value="%2$s" name="%1$s" id="%1$s"%3$s>',
			$this->name($key),
			is_null($value) ? esc_attr($this->post_value($key, '')) : esc_attr($value),
			$this->attributes($attributes));
		
		return $output;
	}
	
	public function input_textarea($key, $value = null, array $attributes = array())
	{
		$attributes = array_merge(array('cols' => '40', 'rows' => '6'), $attributes);
		
		return sprintf('<textarea name="%1$s"%3$s>%2$s</textarea>',
			$this->name($key),
			is_null($value) ? esc_html($this->post_value($key, '')) : esc_html($value),
			$this->attributes($attributes));
	}
	
	public function message($text, $class = 'updated')
	{
		return sprintf('<div class="%s"><p><strong>%s</strong></p></div>',
			esc_attr($class),
			esc_html($text));
	}
	
	public function set_array_name($array_name)
	{
		$this->array_name = $array_name;
	}
	
	public function row($label, $input)
	{
		return sprintf("<tr valign=\"top\">\n\t\t\t\t<th scope=\"row\">%s</th>\n\t\t\t\t<td>%s\n\t\t\t\t</td>\n\t\t\t</tr>\n",
			esc_html($label),
			$input);
	}
	
	public function row_radio($label, $key, array $options)
	{
		$input = '';
		
		foreach ($options as $option) {
			$input .= sprintf("\n\t\t\t\t\t" . '<label class="radio">%s %s</label>', $this->input_radio($key, $option), $option['label']);
		}
		
		return $this->row($label, $input);
	}
	
	public function row_select($label, $key, array $options, array $parameters = array())
	{
		return $this->row($label, "\n\t\t\t\t\t" . $this->input_select($key, $options, array_merge(array('indent' => "\t\t\t\t\t"), $parameters)));
	}
	
	public function row_text($label, $key, $value = null, array $attributes = array())
	{
		return $this->row($label, "\n\t\t\t\t\t" . $this->input_text($key, $value, $attributes));
	}
	
	private function attributes(array $attributes)
	{
		if (!empty($attributes)) {
			$result = '';
			
			foreach ($attributes as $key => $value) {
				$result .= sprintf(' %s="%s"', esc_attr($key), esc_attr($value));
			}
			
			return $result;
		}
		else {
			return '';
		}
	}
	
	private function name($key)
	{
		if (is_null($this->array_name)) {
			return esc_attr($key);
		}
		else {
			return esc_attr(sprintf('%s[%s]', $this->array_name, $key));
		}
	}
	
	private function post_value($key, $default_value = '')
	{
		if (!is_null($this->array_name)) {
			return isset($_POST[$this->array_name][$key]) ? $_POST[$this->array_name][$key] : $default_value;
		}
		else {
			return isset($_POST[$key]) ? $_POST[$key] : $default_value;
		}
	}
}

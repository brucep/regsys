<?php

class RegistrationSystem_PayPal_IPN
{
	private $data = array();
	
	public function __construct(array $data)
	{
		$this->data = $data;
	}
	
	public function is_test()
	{
		return ($this->test_ipn == '1');
	}
	
	public function is_valid()
	{
		if (empty($this->data)) {
			throw new Exception('No data provided.');
		}
		
		$request = 'cmd=_notify-validate';
		
		foreach ($this->data as $key => $value) {
			if (get_magic_quotes_gpc()) {
				$key   = stripslashes($key);
				$value = stripslashes($value);
			}
			
			$request .= sprintf('&%s=%s', rawurlencode($key), rawurlencode($value));
		}
		
		$host = $this->is_test() ? 'ssl://www.sandbox.paypal.com' : 'ssl://www.paypal.com';
		$fp = fsockopen($host, 443, $errno, $errstr, 30);
		
		if (!$fp) {
			throw new Exception(sprintf('Unable to establish connection with PayPal: [%s] %s', $errno, $errstr));
		}
		else {
			$header = 'POST /cgi-bin/webscr HTTP/1.0' . "\r\n" .
			          'Content-Type: application/x-www-form-urlencoded' . "\r\n" .
			          'Content-Length: ' . strlen($request) . "\r\n\r\n";
			
			fwrite($fp, $header . $request);
			
			while (!feof($fp)) {
				$response = fgets($fp);
			}
			
			fclose ($fp);
			
			return ($response === 'VERIFIED');
		}
	}
	
	public function get_all_items(array $default_options = array())
	{
		$items = array();
		$index = 0;
		
		while (++$index) {
			if (isset($this->{'item_name' . $index})) {
				$items[$index] = array(
					'name'     => $this->{'item_name'   . $index},
					'number'   => $this->{'item_number' . $index},
					'mc_gross' => $this->{'mc_gross'    . $index},
					'options'  => array());
					
				$option_index = 0;
				while (++$option_index) {
					if (isset($this->{'option_name' . $option_index . '_' . $index}) and isset($this->{'option_selection' . $option_index . '_' . $index})) {
						$items[$index]['options'][$this->{'option_name' . $option_index . '_' . $index}] = $this->{'option_selection' . $option_index . '_' . $index};
					}
					else {
						break;
					}
				}
				
				$item['options'] = array_merge($default_options, $item['options']);
			}
			else {
				break;
			}
		}
		
		return $items;
	}
	
	public function get_single_item(array $default_options = array())
	{
		$item = array(
			'name'     => $this->item_name,
			'number'   => $this->item_number,
			'mc_gross' => $this->mc_gross,
			'options'  => array());
		
		$option_index = 0;
		while (++$option_index) {
			if (isset($this->{'option_name' . $option_index}) and isset($this->{'option_selection' . $option_index})) {
				$item['options'][$this->{'option_name' . $option_index}] = $this->{'option_selection' . $option_index};
			}
			else {
				break;
			}
		}
		
		$item['options'] = array_merge($default_options, $item['options']);
		
		return $item;
	}
		
	public function __get($name)
	{
		return array_key_exists($name, $this->data) ? $this->data[$name] : null;
	}
	
	public function __isset($name)
	{
		return isset($this->data[$name]);
	}
	
	static public function error_handler($errno, $errstr, $errfile, $errline)
	{
		throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
	}
}

<?php

namespace RegSys\Payment\PayPal;

class IPN
{
	private $data = array();
	
	public function __construct(array $data)
	{
		$this->data = $data;
	}
	
	public function isTest()
	{
		return ($this->test_ipn == '1');
	}
	
	public function isValid()
	{
		if (empty($this->data)) {
			throw new \Exception('No data provided.');
		}
		
		$request = 'cmd=_notify-validate';
		
		foreach ($this->data as $key => $value) {
			if (get_magic_quotes_gpc()) {
				$key   = stripslashes($key);
				$value = stripslashes($value);
			}
			
			$request .= sprintf('&%s=%s', rawurlencode($key), rawurlencode($value));
		}
		
		$host = $this->isTest() ? 'ssl://www.sandbox.paypal.com' : 'ssl://www.paypal.com';
		$fp = fsockopen($host, 443, $errno, $errstr, 30);
		
		if (!$fp) {
			throw new \Exception(sprintf('Unable to establish connection with PayPal: [%s] %s', $errno, $errstr));
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
	
	public function allItems(array $defaultOptions = array())
	{
		$items = array();
		$index = 0;
		
		while (++$index) {
			if (isset($this->{'item_name' . $index})) {
				$items[$index] = array(
					'name'     => $this->{'item_name'   . $index},
					'number'   => $this->{'item_number' . $index},
					'mc_gross' => $this->{'mc_gross_'   . $index},
					'options'  => array());
				
				$optionIndex = 0;
				while (++$optionIndex) {
					if (isset($this->{'option_name' . $optionIndex . '_' . $index}) and isset($this->{'option_selection' . $optionIndex . '_' . $index})) {
						$items[$index]['options'][$this->{'option_name' . $optionIndex . '_' . $index}] = $this->{'option_selection' . $optionIndex . '_' . $index};
					}
					else {
						break;
					}
				}
				
				$items[$index]['options'] = array_merge($defaultOptions, $items[$index]['options']);
			}
			else {
				break;
			}
		}
		
		return $items;
	}
	
	public function singleItem(array $defaultOptions = array())
	{
		$item = array(
			'name'     => $this->item_name,
			'number'   => $this->item_number,
			'mc_gross' => $this->mc_gross,
			'options'  => array());
		
		$optionIndex = 0;
		while (++$optionIndex) {
			if (isset($this->{'option_name' . $optionIndex}) and isset($this->{'option_selection' . $optionIndex})) {
				$item['options'][$this->{'option_name' . $optionIndex}] = $this->{'option_selection' . $optionIndex};
			}
			else {
				break;
			}
		}
		
		$item['options'] = array_merge($defaultOptions, $item['options']);
		
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
}

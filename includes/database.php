<?php

class RegistrationSystem_Database
{
	private $pdo, $prefix = '';
	
	public function __construct(array $settings)
	{
		try {
			$this->pdo = @new PDO(
				sprintf('%s:host=%s;%sdbname=%s',
					'mysql',
					$settings['host'],
					!empty($settings['port']) ? sprintf('port=%d;', $settings['port']) : '',
					$settings['name']),
				$settings['user'],
				$settings['password'],
				array(
					PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
					PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC));
			
			$this->pdo->exec('SET NAMES "utf8";');
		}
		catch (PDOException $e) {
			$message = preg_replace('/^[A-Z]+\[[A-Z0-9]+\]:? \[[0-9]+\] (.*?)/', '$1', $e->getMessage());
			exit('<pre>' . $message . '</pre>');
		}
	}
	
	public function query($query, array $params = array())
	{
		try {
			$statement = $this->pdo->prepare($query);
			
			if (!($statement->execute($params))) {
				throw PDOException();
			}
		}
		catch (PDOException $e) {
			$message = preg_replace("/^[A-Z]+\[[A-Z0-9]+\]:?(.+ [0-9]+)? (.+)$/", '$2', $e->getMessage());
			
			if (defined('WP_DEBUG')) {
				$message .= "</p>\n\n<pre>$query\n\n" . print_r($params, true) . "</pre>\n\n";
			}
			
			throw new Exception($message);
		}
		
		return $statement;
	}
	
	public function lastInsertID($name = '')
	{
		return $this->pdo->lastInsertID($name);
	}
}

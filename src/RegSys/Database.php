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
					PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ));
			
			$this->pdo->exec('SET NAMES "utf8";');
		}
		catch (PDOException $e) {
			$message = preg_replace('/^[A-Z]+\[[A-Z0-9]+\]:? \[[0-9]+\] (.*?)/', '$1', $e->getMessage());
			exit('<pre>' . $message . '</pre>');
		}
	}
	
	public function fetchAll($query, array $params = array(), $class = null)
	{
		if (class_exists($class)) {
			return $this->query($query, $params)->fetchAll(PDO::FETCH_CLASS, $class);
		}
		else {
			return $this->query($query, $params)->fetchAll(PDO::FETCH_OBJ);
		}
	}
	
	public function fetchColumn($query, array $params = array())
	{
		return $this->query($query, $params)->fetchColumn();
	}
	
	public function fetchObject($query, array $params = array(), $class = null)
	{
		if ($class) {
			return $this->query($query, $params)->fetchObject($class);
		}
		else {
			return $this->query($query, $params)->fetchObject();
		}
	}
	
	public function lastInsertID($name = '')
	{
		return $this->pdo->lastInsertID($name);
	}
	
	public function query($query, array $params = array())
	{
		try {
			$statement = $this->pdo->prepare($query);
			
			if (!($statement->execute($params))) {
				throw PDOException();
			}
			
			return $statement;
		}
		catch (PDOException $e) {
			$message = preg_replace("/^[A-Z]+\[[A-Z0-9]+\]:?(.+ [0-9]+)? (.+)$/", '$2', $e->getMessage());
			
			if (defined('WP_DEBUG')) {
				$message .= "</p>\n\n<pre>$query\n\n" . print_r($params, true) . "</pre>\n\n";
			}
			
			throw new Exception($message);
		}
	}
}

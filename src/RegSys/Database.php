<?php

namespace RegSys;

class Database
{
	protected $pdo, $debug;
	
	public function __construct(\Pimple $container)
	{
		try {
			$this->debug = (bool) $container['debug'];
			
			$this->pdo = @new \PDO(
				sprintf('%s:host=%s;%sdbname=%s',
					'mysql',
					$container['host'],
					!empty($container['port']) ? sprintf('port=%d;', $container['port']) : '',
					$container['name']),
				$container['user'],
				$container['pass']);
			
			$this->pdo->exec('SET NAMES "utf8";');
		}
		catch (\PDOException $e) {
			$message = preg_replace('/^[A-Z]+\[[A-Z0-9]+\]:? \[[0-9]+\] (.*?)/', '$1', $e->getMessage());
			exit('<pre>' . $message . '</pre>');
		}
		catch (\Exception $e) {
			throw $e;
		}
	}
	
	public function beginTransaction()
	{
		return $this->pdo->beginTransaction();
	}
	
	public function commit()
	{
		return $this->pdo->commit();
	}
	
	public function fetchAll($query, array $params = array(), $class = null)
	{
		if (class_exists($class)) {
			return $this->query($query, $params)->fetchAll(\PDO::FETCH_CLASS, $class);
		}
		else {
			return $this->query($query, $params)->fetchAll(\PDO::FETCH_OBJ);
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
	
	public function lastInsertID()
	{
		return $this->pdo->lastInsertID();
	}
	
	public function query($query, array $params = array())
	{
		try {
			$statement = $this->pdo->prepare($query);
			
			if (!($statement->execute($params))) {
				$errorInfo = $statement->errorInfo();
				throw new \PDOException($errorInfo[2]);
			}
			
			return $statement;
		}
		catch (\PDOException $e) {
			$message = trim(preg_replace("/^[A-Z]+\[[A-Z0-9]+\]:?(.+ [0-9]+)? (.+)$/", '$2', $e->getMessage()));
			
			if ($this->debug) {
				ob_start(); 
				debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS); 
				$backtrace = ob_get_contents(); 
				ob_end_clean(); 
				
				$message .= sprintf("\n\n<pre>%s\n\n%s</pre>\n\n<pre>%s</pre>\n\n", $query, print_r($params, true), $backtrace);
			}
			
			throw new \Exception($message);
		}
		catch (\Exception $e) {
			throw $e;
		}
	}
	
	public function rollBack()
	{
		return $this->pdo->rollBack();
	}
}

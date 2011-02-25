<?php

class NSEvent_Database
{
	public $prefix = '';
	protected $pdo, $connected;
	protected function __construct() {}
	
	#
	# Function: connect
	# Creates a database connection.
	#
	public function connect()
	{
		if ($this->connected)
			return True;
		
		
		#
		# Establish connection via PDO
		#
		try
		{
			$this->pdo = new PDO(
				sprintf('%s:host=%s;%sdbname=%s',
					'mysql',
					DB_HOST,
					!defined('DB_HOST_PORT') ? '' : 'port={'.DB_HOST_PORT.'};',
					DB_NAME),
				DB_USER,
				DB_PASSWORD,
				array(
					PDO::ATTR_PERSISTENT => True,
					PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
					PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC));
			
			$this->pdo->query('SET NAMES "utf8";');
			
			return $this->connected = True;
		}
		catch (PDOException $e)
		{
			$message = preg_replace('/[A-Z]+\[[0-9]+\]: .+ [0-9]+ (.*?)/', '$1', $e->getMessage());
			throw new BP_RequestException($message, 500);
		}
	}

	#
	# Function: query
	# Executes a SQL query.
	#
	public function query($query, array $params = array(), $use_prefix = True)
	{
		try
		{
			if ($use_prefix)
				$query = sprintf($query, $this->prefix);
			
			$statement = $this->pdo->prepare($query);
			
			if (!($statement->execute($params)))
				throw PDOException();
		}
		catch (PDOException $e)
		{
			$message = preg_replace("/[A-Z]+\[[0-9]+\]: .+ [0-9]+ (.*?)/", "\\1", $e->getMessage());
			
			if (defined('WP_DEBUG'))
				$message .= "</p>\n\n<pre>$query\n\n".print_r($params, True)."</pre>\n\n";
			
			throw new Exception($message);
		}
		
		return $statement;
	}

	#
	# Function: lastInsertID
	# Returns the ID of the last inserted row.
	#
	public function lastInsertID($name = '')
	{
		return $this->pdo->lastInsertID($name);
	}

	#
	# Function: quote
	# Quotes a string for use in a query.
	#
	public function quote($string)
	{
		return $this->pdo->quote($string);
	}
	
	static public function &get_instance()
	{
		static $instance;
		if (!isset($instance))
			$instance = new self;
		return $instance;
	}
}

NSEvent_Model::$database = NSEvent_Database::get_instance();
require dirname(__FILE__).'/model.php';
require dirname(__FILE__).'/model-event.php';
require dirname(__FILE__).'/model-item.php';
require dirname(__FILE__).'/model-dancer.php';
require dirname(__FILE__).'/model-registration.php';

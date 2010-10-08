<?php

class NSEvent_Registration extends NSEvent_Model
{
	private $item, $dancer;
	
	public static function find_all()
	{
		$statement = self::$database->query('SELECT * FROM %1$s_registrations WHERE event_id = :event_id', array(':event_id' => self::$event->id));
		return $statement->fetchAll(PDO::FETCH_CLASS, 'NSEvent_Registration');
	}
	
	public static function find_by($field, $value)
	{
		$statement = self::$database->query('SELECT * FROM %1$s_registrations WHERE event_id = :event_id AND `'.$field.'` = :value', array(':event_id' => self::$event->id, ':value' => $value));
		return $statement->fetchAll(PDO::FETCH_CLASS, 'NSEvent_Registration');
	}
		
	public static function find($dancer_id, $item_id)
	{
		$statement = self::$database->query('SELECT * FROM %1$s_registrations WHERE event_id = :event_id AND dancer_id = :dancer_id AND item_id = :item_id', array(':event_id' => self::$event->id, ':dancer_id' => $dancer_id, ':item_id' => $item_id));
		return $statement->fetchObject('NSEvent_Registration');
	}
	
	public static function count_for_item($item_id, $item_meta = False)
	{
		$parameters = array(':event_id' => self::$event->id, ':item_id' => $item_id);
		$where = '';
		
		if ($item_meta !== False)
		{
			$parameters[':item_meta'] = $item_meta;
			$where = ' AND item_meta = :item_meta';
		}
		
		$statement = self::$database->query('SELECT COUNT(item_id) FROM %1$s_registrations WHERE event_id = :event_id AND item_id = :item_id'.$where, $parameters);
		$result = $statement->fetchColumn();
		return ($result !== False) ? (int) $result : False;
	}
	
	public static function add(array $parameters)
	{
		self::$database->query('INSERT %1$s_registrations VALUES (:event_id, :dancer_id, :item_id, :price, :item_meta)', array(
			':event_id'       => self::$event->id,
			':dancer_id'      => $parameters['dancer_id'],
			':item_id'        => $parameters['item_id'],
			':price'          => $parameters['price'],
			':item_meta'      => (isset($parameters['item_meta']) ? $parameters['item_meta'] : ''),
			));
	}
	
	public function item()
	{
		if (!isset($this->item))
			$this->item = NSEvent_Item::find($this->item_id);
		
		return $this->item;
	}
	
	public function dancer()
	{
		if (!isset($this->dancer))
			$this->dancer = NSEvent_Dancer::find($this->dancer_id);
		
		return $this->dancer;
	}
}

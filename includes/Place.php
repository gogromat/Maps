<?php
require_once(dirname(__FILE__)."/DbObject.php");
//require_once(dirname(__FILE__)."/RoomType.php");

class Place extends DbObject
{
	public static $tableName = "places";
	public $exists = false;

	/** [[Column=place_id, DataType=int, Description=Id, ReadOnly=true]]*/
	public $place_id;

	/** [[Column=place_name, DataType=varchar, Description=Place Name, MaxLength=60, Required=true]]*/
	public $place_name;//e = 'Library';

	// foreign key fields (will be populated upon request)
/*
	public $roomType;
	public function getRoomType()
	{
		$this->roomType = new RoomType($this->room_type_id);
	}

	// returns: Library[, Rm 383] (PC Classroom)
	public function getFullName()
	{
		$name = '';
		if ($this->location != '')		$name .= $this->location;
		if ($this->room_number != '')	$name .= ", Rm ".$this->room_number;
		if ($this->room_name != '')		$name .= " (".$this->room_name.")";
		return trim($name);
	}
	
	// returns: [383 - ]PC Classroom
	public function getName()
	{
		$name = '';
		if ($this->room_number != '')	$name .= $this->room_number." - ";
		if ($this->room_name != '')		$name .= $this->room_name;
		return trim($name);
	}
	// array of locations with their corresponding of floors
	public static $locations = array('Library'=>array(0,1,2,3,4), 'Library Cafe'=>array(1), 'WEB'=>array(1));

	// array of floor names (to be used with $locations)
	public static $floors = array("Lower Level", "First Floor", "Second Floor", "Third Floor", "Fourth Floor");

	// array of reservable states
	public static $reservables = array("", "internal", "public");
*/
	public function fetchData($id)
	{
		//list($arg1, $arg2) = $args;
		$query="SELECT * FROM ".self::$tableName." WHERE id=".$id;

		$row = $this->db->getRow($query);
		
		if (count($row) > 0)
			$this->setPropertyValues($row);

		if ($this->room_id > 0)
			$this->exists = true;
	}
	
	
	public function getPlaces($args=array())
	{
		global $db, $status;

		$query = "SELECT * 
			FROM ".self::$tableName." 
			WHERE 1=1 
				".(is_array(@$args['place_ids']) ? " AND place_id IN (".implode(',',$args['place_ids']).") " : "")."
				".(@$args['place_id'] != '' ? " AND place_id={$args['place_id']} " : "")."
				".(@$args['place_name'] != '' ? " AND place_name='{$args['place_name']}' " : "")."
			ORDER BY place_name";

		$results = $db->prepare($query);
        $results->execute();

		//if (PEAR::isError($results)) {
		//	handle_error($status, "Invalid place specified (".__LINE__.")", false);
		//}

		$items = array();
		foreach ($results as $r) {
			$obj = new Place();
			$obj->setPropertyValues($r);
			$obj->exists = true;
			array_push($items, $obj);
		}
		return $items;
	}

	public function getCount()
	{
		global $db;
		
		$query = "SELECT count(*) FROM ".self::$tableName;
		//return $db->getOne($query);
        $row = $db->query($query)->fetch();
        return $row[0];
	}
	
	public function save($delete=0)
	{
		// create query
		$query = ($this->exists ? $this->updateString() : $this->insertString());

		// prepare query
		$stmt = $this->db->prepare($query);

		// execute statement
		$result =& $this->db->execute($stmt);
		
		/*if (PEAR::isError($result)) {
			throw new Exception($result->getMessage());
		}*/
		
		return $result;
	}
	
	public function delete($method='hard')
	{
		if ($method == 'soft')
			return $this->save(1);
			
		// create query
		$query="DELETE FROM ".self::$tableName." WHERE id=".$this->id;

		// prepare query
		$stmt = $this->db->prepare($query);

		// execute statement
		$result =& $this->db->execute($stmt);
		
		return $result;
	}
	
	public function insertString()
	{
		$insertstr = "INSERT INTO ".self::$tableName." (";
		
		$insertarr = array();
		$valuesarr = array();
		$insertindex = 0;
		
		$rf = new ReflectionClass($this);
		$props = $rf->getProperties();
		foreach($props as $prop)
		{
			$column = AttributeReader::PropertyAttributes($this,$prop->getName())->Column;
			$primarykey = AttributeReader::PropertyAttributes($this,$prop->getName())->ReadOnly;
			
			if ($column != "" && !$primarykey)
			{
				$insertarr[$insertindex++]=$column;
				$valuesarr[$insertindex]=$this->setValue($this, $prop);
			}
		}
		$insertstr.=join(',',$insertarr).") VALUES (".join(',',$valuesarr).")";
		return $insertstr;
	}
	
	public function updateString()
	{
		$updatestr = "UPDATE ".self::$tableName." SET ";
		
		$updatearr = array();
		$updateindex = 0;
		
		$rf = new ReflectionClass($this);
		$props = $rf->getProperties();
		foreach($props as $prop)
		{
			$column = AttributeReader::PropertyAttributes($this,$prop->getName())->Column;
			$primarykey = AttributeReader::PropertyAttributes($this,$prop->getName())->ReadOnly;
			
			if ($column != "" && !$primarykey)
			{
				$updatearr[$updateindex++]=$column."=".$this->setValue($this, $prop);
			}
		}
		$updatestr.=join(',',$updatearr)." WHERE id=".$this->id;
		return $updatestr;
	}
}

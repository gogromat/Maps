<?php
require_once(dirname(__FILE__)."/DbObject.php");
require_once(dirname(__FILE__)."/Marker.php");
require_once(dirname(__FILE__)."/Place.php");

class MarkerPlace extends DbObject
{
	public static $tableName = "marker_places";
	public $exists = false;

	//Column=unit_room_id, DataType=int, Description=Unit Room, ReadOnly=true
	//public $unit_room_id;

	/** [[Column=marker_id, DataType=int, Description=Marker, Required=true, ReadOnly=true]]*/
	public $marker_id;

	/** [[Column=place_id, DataType=int, Description=Place, Required=true, ReadOnly=true]]*/
	public $place_id;

	// foreign key fields (will be populated upon request)
	
	public $marker;
	public function getMarker()
	{
		$this->marker = new Marker($this->marker_id);
	}
	
	public $place;
	public function getPlace()
	{
		$this->place = new Place($this->place_id);
	}
	

	public function fetchData($id1,$id2)
	{
		//list($arg1, $arg2) = $args;
		$query="SELECT * FROM ".self::$tableName." WHERE marker_id=".$id1
                                            ." AND place_id=".$id2;

		$row = $this->db->getRow($query);
		
		if (count($row) > 0)
			$this->setPropertyValues($row);

		if ($this->unit_room_id > 0)
			$this->exists = true;
	}
	
	
	public function getMarkerPlaces($args=array())
	{
		global $db, $status;
		
		$query = "SELECT ".(@$args['in_places'] == true ? " DISTINCT place_id" : "*")."
			FROM ".self::$tableName." ru
                ".(@$args['in_places'] == true ? "" : "
				INNER JOIN markers m ON ru.marker_id = m.marker_id
				INNER JOIN places p  ON ru.place_id  = p.place_id")."
			WHERE 1=1 
				".(@$args['unit_room_id'] != '' ? " AND unit_room_id={$args['unit_room_id']} " : "")."
				".(@$args['place_id'] != '' ? " AND p.place_id={$args['place_id']} " : "")."
				".(@$args['marker_id'] != '' ? " AND m.marker_id={$args['marker_id']} " : "")."
			ORDER BY place_id";

		//$results = $db->getAll($query);
        //echo $query;
        $results = $db->prepare($query);
        $results->execute();

		//if (PEAR::isError($results)) {
		//	handle_error($status, $results, false);
		//}

		$items = array();
		foreach ($results as $r) {
			$obj = new MarkerPlace();
			$obj->setPropertyValues($r);
			$obj->exists = true;

			$obj->marker = new Marker();
			$obj->marker->setPropertyValues($r);
			$obj->marker->exists = true;

            $obj->place = new Place();
			$obj->place->setPropertyValues($r);
			$obj->place->exists = true;

			array_push($items, $obj);
		}
		return $items;
	}
	/*
	//gets info from RoomUnits, Room Types, Rooms
	public function getRooms($args=array())
	{
		global $db, $status;
		
		$query = "SELECT *
			FROM ".self::$tableName." ru
			INNER JOIN rooms r ON ru.room_id = r.room_id 
			INNER JOIN room_types rt ON r.room_type_id = rt.room_type_id
			WHERE 1=1 
				".(@$args['unit_room_id'] != '' ? " AND ru.unit_room_id={$args['unit_room_id']} " : "")."
				".(@$args['unit_id'] != '' ? " AND ru.unit_id={$args['unit_id']} " : "")."
				".(@$args['room_id'] != '' ? " AND ru.room_id={$args['room_id']} " : "")."
				AND r.deleted=0 
			ORDER BY r.location, rt.room_type_name, r.room_number";
		$results = $db->getAll($query);

		if (PEAR::isError($results)) {
			handle_error($status, $results, false);
		}

		$items = array();
		foreach ($results as $r) {
			$obj = new Room();
			$obj->setPropertyValues($r);
			$obj->exists = true;

			$obj->roomType = new RoomType();
			$obj->roomType->setPropertyValues($r);
			$obj->roomType->exists = true;

			array_push($items, $obj);
		}
		return $items;
	}*/

	public function getCount()
	{
		global $db;
		$query = "SELECT count(*) FROM ".self::$tableName;
		return $db->getOne($query);
	}
    /*
	public function getUnitRoomCount($args=array())
	{
		global $db;
		
		$query = "SELECT count(*) FROM ".self::$tableName."
		GROUP BY unit_id HAVING unit_id =".$args['unit_id'];
		return $db->getOne($query);
	}*/
	
	public function save($delete=0)
	{
		//global $login_info;
		
		//$user = $login_info->get_user_info();
		
		//$this->modified_by = (isset($user['first_name']) ? $user['first_name'].' '.$user['last_name'] : $user['username']);
		//$this->last_modified = date("Y-m-d G:i:s");/**/
		
		//$this->deleted = ($delete == 1 || $delete === true ? '1' : '0');
		
		// create query
		$query = ($this->exists ? $this->updateString() : $this->insertString());

		// prepare query
		//$stmt = $this->db->prepare($query);

        //print_r($query.'<br />');

		// execute statement
		//$result =& $this->db->execute($stmt);
		$result =& $this->db->exec($query);


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
		$query="DELETE FROM ".self::$tableName." WHERE unit_room_id=".$this->unit_room_id;

		// prepare query
		$stmt = $this->db->prepare($query);

		// execute statement
		$result =& $this->db->execute($stmt);
		
		/*if (PEAR::isError($result)) {
			throw new Exception($result->getMessage());
		}*/
		
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
			//$primarykey = AttributeReader::PropertyAttributes($this,$prop->getName())->ReadOnly;
			
			if ($column != "")// && !$primarykey)
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
		$updatestr.=join(',',$updatearr)." WHERE unit_room_id=".$this->unit_room_id;
		return $updatestr;
	}
}

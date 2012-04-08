<?php
require_once(dirname(__FILE__)."/DbObject.php");
//require_once(dirname(__FILE__)."/RoomType.php");

class BarcodeType extends DbObject
{
	public static $tableName = "barcode_types";
	public $exists = false;

	/** [[Column=barcode_type_id, DataType=int, Description=Barcode Type Id, ReadOnly=true]]*/
	public $barcode_type_id;

	/** [[Column=barcode_type_name, DataType=varchar, Description=Barcode Type Name, MaxLength=50, Required=true]]*/
	public $barcode_type_name;//e = 'Library';

	// foreign key fields (will be populated upon request)

	public function fetchData($id)
	{
		//list($arg1, $arg2) = $args;
		$query="SELECT * FROM ".self::$tableName." WHERE barcode_type_id=".$id;

		$row = $this->db->getRow($query);
		
		if (count($row) > 0)
			$this->setPropertyValues($row);

		if ($this->room_id > 0)
			$this->exists = true;
	}
	
	
	public function getBarcodeTypes($args=array())
	{
		global $db, $status;

		$query = "SELECT * 
			FROM ".self::$tableName." 
			WHERE 1=1 
				".(is_array(@$args['barcode_type_ids']) ? " AND barcode_type_id IN (".implode(',',$args['barcode_type_ids']).") " : "")."
				".(@$args['barcode_type_id'] != '' ? " AND barcode_type_id={$args['barcode_type_id']} " : "")."
				".(@$args['barcode_type_name'] != '' ? " AND barcode_type_name='{$args['barcode_type_name']}' " : "")."
			ORDER BY barcode_type_name";

		$results = $db->prepare($query);
        $results->execute();

		//if (PEAR::isError($results)) {
		//	handle_error($status, "Invalid place specified (".__LINE__.")", false);
		//}

		$items = array();
		foreach ($results as $r) {
			$obj = new BarcodeType();
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
		$query="DELETE FROM ".self::$tableName." WHERE barcode_type_id=".$this->barcode_type_id;

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
		$updatestr.=join(',',$updatearr)." WHERE barcode_type_id=".$this->barcode_type_id;
		return $updatestr;
	}
}
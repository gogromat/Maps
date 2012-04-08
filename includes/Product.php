<?php
require_once(dirname(__FILE__)."/DbObject.php");
//require_once(dirname(__FILE__)."/RoomType.php");

class Product extends DbObject
{
	public static $tableName = "products";
	public $exists = false;

	/** [[Column=product_id, DataType=int, Description=Product Id, ReadOnly=true]]*/
	public $product_id;

	/** [[Column=product_name, DataType=varchar, Description=Product Name, MaxLength=70, Required=true]]*/
	public $product_name;

	/** [[Column=product_size, DataType=varchar, Description=Product Size, MaxLength=50]]*/
	public $product_size;

	/** [[Column=image, DataType=varchar, Description=Image, MaxLength=80]]*/
	public $image;

	/** [[Column=barcode, DataType=int, Description=Barcode]]*/
	public $barcode;

	/** [[Column=barcode_type, DataType=int, Description=Barcode Type]]*/
	public $barcode_type;

	// foreign key fields (will be populated upon request)

	public function fetchData($id)
	{
		//list($arg1, $arg2) = $args;
		$query="SELECT * FROM ".self::$tableName." WHERE product_id=".$id;

		$row = $this->db->getRow($query);
		
		if (count($row) > 0)
			$this->setPropertyValues($row);

		if ($this->product_id > 0)
			$this->exists = true;
	}
	
	
	public function getProducts($args=array())
	{
		global $db, $status;

		$query = "SELECT *
			FROM ".self::$tableName."
			WHERE 1=1
				".(is_array(@$args['ids']) ? " AND product_id IN (".implode(',',$args['ids']).") " : "")."
				".(@$args['id'] != '' ? " AND product_id={$args['id']} " : "")."
				".(@$args['product_name'] != '' ? " AND product_name LIKE '%{$args['product_name']}%' " : "")."
				".(@$args['name'] != '' ? " AND product_name LIKE '%{$args['name']}%' " : "")."
				".(@$args['image'] != '' ? " AND image ='{$args['image']}' " : "")."
				".(@$args['barcode'] != '' ? " AND barcode= {$args['barcode']} " : "")."
				".(@$args['barcode_type'] != '' ? " AND barcode_type {$args['barcode_type']} " : "")."
			ORDER BY ".(@$args['order_by'] != '' ? $args['order_by'] : " product_name ").
            (@$args['limit'] != '' ? " LIMIT {$args['limit']} " : "0");

        //echo $query;
		$results = $db->prepare($query);
        $results->execute();

        /*
            if (PEAR::isError($results)) {
            handle_error($status, "Invalid room specified (".__LINE__.")", false);
            }
        */
		$items = array();
		foreach ($results as $r) {
			$obj = new Product();
			$obj->setPropertyValues($r);
			$obj->exists = true;
			array_push($items, $obj);
		}
		return $items;
	}
/*
	//Fetch Pictures
	public static function getPictures($args=array())
	{
		global $db, $status;

		if (@$args['location'] == "" || @$args['floor'] == "")
			return array();

		$filesUnformatted = glob(dirname(__FILE__)."/../../images/pictures/{$args['location']}/{$args['floor']}/*.gif");

		$files = array();
		foreach($filesUnformatted as $f){
			$files[] = basename($f);
		}

		$query = "SELECT DISTINCT picture 
			FROM ".self::$tableName." 
			WHERE 1=1 
				AND picture <> '' 
				AND picture is not null
				".(@$args['location'] != '' ? " AND location='{$args['location']}' " : "")."
				".(@$args['floor'] != '' ? " AND floor={$args['floor']} " : "")."
				".(@$args['picture'] != '' ? " AND picture not in ('".implode("','",$files)."') " : "")."
			ORDER BY picture";
		$results = $db->getAll($query);

		if (PEAR::isError($results)) {
			handle_error($status, $results, false);
		}

		$items = array();
		foreach ($results as $r) {
			array_push($items, $r['picture']);
		}
	
		if(@$args['picture'] == ''){
			$normal = array_diff($files,$items);
			$normal2 = array_values($normal);
			return $normal2;
		}
		array_push($items,@$args['picture']);
		return $items;
	}
*/
	//SEARCH
	public function search($args=array(), $oper)
	{
		global $db, $status;

		$oper = (in_array(strtolower($oper), array('and','or')) ? $oper : 'or');

		if (empty($args))
			return array();

        //INNER JOIN room_types rt ON r.room_type_id=rt.room_type_id
		$query = "SELECT *
			FROM ".self::$tableName." r
			WHERE 1=1
				AND ( ";
		
		$keywords = array();
		foreach($args as $r)
			$keywords[] = " (room_number + ' ' + room_name + ' ' + room_type_name LIKE '%".$db->escapeSimple($r)."%') ";

		$query .= implode($oper, $keywords);
		
		$query .= " )  
		   		  ".(@$args['is_public'] == '' ? " AND is_public = 1 " : "")."
			ORDER BY room_number, room_name ";

		$results = $db->getAll($query);

		if (PEAR::isError($results)) {
			handle_error($status, "Invalid room/room type specified (".__LINE__.")", false);
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
		//global $login_info;
		//$user = $login_info->get_user_info();
		//$this->modified_by = (isset($user['first_name']) ? $user['first_name'].' '.$user['last_name'] : $user['username']);
		//$this->last_modified = date("Y-m-d G:i:s");/**/
		//$this->deleted = ($delete == 1 || $delete === true ? '1' : '0');
		
		// create query
		$query = ($this->exists ? $this->updateString() : $this->insertString());
        $result = $this->db->prepare($query);
        $result->execute();

        //echo ($query);
		// prepare query
		//$stmt = $this->db->prepare($query);

		// execute statement
		//$result =& $stmt->execute();

        //$sth = $this->db->prepare($query);

        //$result =& $this->db->execute($query);
        //echo $query;
        //$result =& $this->db->exec($query);

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
		$query="DELETE FROM ".self::$tableName." WHERE product_id=".$this->product_id;


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
            // Object Property (name of table, field, errorStack, db itself)
            //echo $prop."<br />";
			$column = AttributeReader::PropertyAttributes($this,$prop->getName())->Column;
			$primarykey = AttributeReader::PropertyAttributes($this,$prop->getName())->ReadOnly;
			// Database column name
            //echo $column."<br />";
			if ($column != "" && !$primarykey)
			{
                // Database Fields
				$insertarr[$insertindex++]=$column;
				// Values for the fields
                //echo '[___'.$this.':'.$prop.'___]<br/><br/>';
                $valuesarr[$insertindex]=$this->setValue($this, $prop);
			}
		}
        //print_r ($valuesarr);
        //echo "<br />";
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
		$updatestr.=join(',',$updatearr)." WHERE product_id=".$this->product_id;
		return $updatestr;
	}
}

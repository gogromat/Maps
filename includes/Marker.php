<?php
require_once(dirname(__FILE__)."/DbObject.php");
//require_once(dirname(__FILE__)."/RoomType.php");
require_once("MarkerPlace.php");

class Marker extends DbObject
{
	public static $tableName = "markers";
	public $exists = false;

	/** [[Column=id, DataType=int, Description=Id, ReadOnly=true]]*/
	public $id;

	/** [[Column=name, DataType=varchar, Description=Name, MaxLength=60, Required=true]]*/
	public $name;//e = 'Library';

	/** [[Column=address, DataType=varchar, Description=Address, MaxLength=80, Required=true]]*/
	public $address;

	/** [[Column=lat, DataType=decimal, Description=Latitude]]*/
	public $lat;

	/** [[Column=lng, DataType=decimal, Description=Longitude]]*/
	public $lng;

    /** [[Column=rating, DataType=float, Description=Rating]] */
    public $rating;

    /** [[Column=reference, DataType=varchar, Description=Reference]] */
    public $reference;

    /** [[Column=google_id, DataType=varchar, Description=Google ID]] */
    public $google_id;

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

    public $distance;                // Not a DB, calculated field!


	public function fetchData($id)
	{
		//list($arg1, $arg2) = $args;
		$query="SELECT * FROM ".self::$tableName." WHERE id=".$id;

		$row = $this->db->getRow($query);
		
		if (count($row) > 0)
			$this->setPropertyValues($row);

		if ($this->id > 0)
			$this->exists = true;
	}
	
	
	public function getMarkers($args=array())
	{
		global $db, $status;

        // 3956 = miles
        // 6371 = kilometers     SELECT id?
		$query = "SELECT ".(@$args['radius'] != '' ?
                "* , ( ".(@$args['kilometers'] == true ? "6371" :"3959").
                " * acos ( cos ( radians(".(@$args['center_lat'] != '' ? @$args['center_lat'] : "40").") ) * cos ( radians( lat ) ) * cos ( radians ( lng ) - radians (".
                (@$args['center_lng'] != '' ? @$args['center_lng'] : "-73").") ) + sin( radians(".
                (@$args['center_lat'] != '' ? @$args['center_lat'] : "40").") ) * sin( radians( lat ) ) ) ) AS distance " : "*")."
			FROM ".self::$tableName." as m
			    ".(@$args['with_marker_places'] == true ? " INNER JOIN ".StoreProduct::$tableName." as sp ON m.id = sp.store_id " : "")."
			WHERE 1=1
			    ".((@$args['bottom_left_lat'] != '' && @$args['top_right_lat'] ) ? " AND lat BETWEEN {$args['bottom_left_lat']} AND {$args['top_right_lat']}" : "")."
			    ".((@$args['bottom_left_lng'] != '' && @$args['top_right_lng'] ) ? " AND lng BETWEEN {$args['bottom_left_lng']} AND {$args['top_right_lng']}" : "")."
				".((is_array(@$args['ids']) && !empty($args['ids'])) ? " AND id IN (".implode(',',$args['ids']).") " : "")."
				".(@$args['id'] != '' ? " AND id={$args['id']} " : "")."
				".(@$args['name'] != '' ? " AND name='{$args['name']}' " : "")."
				".(@$args['address'] != '' ? " AND address LIKE '%{$args['address']}%' " : "")."
				".(@$args['lat'] != '' ? " AND lat='{$args['lat']}' " : "")."
				".(@$args['lng'] != '' ? " AND lng='{$args['lng']}' " : "")."
				".((is_array(@$args['with_type_ids']) && !empty($args['with_type_ids'])) ?
                        " AND id IN (SELECT marker_id FROM ".MarkerPlace::$tableName." WHERE place_id IN (".implode(',',$args['with_type_ids']).") )": "" )."

                ".((@$args['with_marker_places'] == true && @$args['product_id'] !='')  ? " AND sp.product_id={$args['product_id']} " : "")."

            ".(@$args['radius'] != '' ? " HAVING distance < {$args['radius']} " : "")."
			ORDER BY ".(@$args['radius'] != '' ? "distance":"name").
            (@$args['limit'] != '' ? " LIMIT 0, {$args['limit']} " : "");

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
			$obj = new Marker();
			$obj->setPropertyValues($r);
			$obj->exists = true;
			array_push($items, $obj);
		}
		return $items;
	}
/*
	public function getRoomsByType($args=array())
	{
		global $db, $status;

		$query = "SELECT * 
			FROM ".self::$tableName." r
				INNER JOIN room_types rt 
					ON r.room_type_id=rt.room_type_id
			WHERE 1=1 
				and (floormap <> '' OR map_object <> '')
				and (floormap is not null OR map_object is not null)
				AND deleted=(".(@$args['deleted'] == '1' ? "1" : "0").") 
				".(@$args['location'] != '' ? " AND location='{$args['location']}' " : "")."
				".(@$args['floor'] != '' ? " AND floor={$args['floor']} " : "")."
				".(@$args['floormap_exists'] == true ? " AND floormap <> '' " : "")."
				".(@$args['is_public'] != '' ? " AND is_public = ".(int)$args['is_public']." " : "")."
				".(@$args['room_like'] != '' ? " AND room_number +'|'+ room_type_name +'|'+ room_name +'|'+ description LIKE '%{$args['room_like']}%'" : "")."
			ORDER BY priority, room_type_name, location, floor, room_number, room_name";
		$results = $db->getAll($query);

		if (PEAR::isError($results)) {
			handle_error($status, "Invalid room specified (".__LINE__.")", false);
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

	public static function formatFloormaps($arr, $floormap){
		
		if ($floormap != "")
			$floormap .= ".png";
		
		foreach($arr as $val)
		{
			$temp = basename($val);

			if($temp != $floormap && !is_numeric($temp[0]) && !preg_match("/^library_/i",$temp)){
				// && !preg_match("/^b_/i", $temp) && !preg_match("/^sp_/i", $temp) && !preg_match("/^st_/i", $temp) && !preg_match("/^fire_stairs/i", $temp) && !preg_match("/^el_/i", $temp)
				$matches = array();
				if(preg_match("/(.*)\.png/i", $temp, $matches)){
					$temp = $matches[1]; 
				}
				$matches = array();
				//if(preg_match("/(.*)_[0-9]$/i", $temp, $matches)){
				//	$temp = $matches[1]; 
				//}
				$result[] = $temp;
			}
		}
		if(!empty($result))
		$result = array_unique($result);
		return $result;
	}


	//Fetch Floormaps
	public static function getFloormaps($args=array())
	{
		global $db, $status;

		if (@$args['location'] == "" || !in_array(@$args['floor'], array_keys(Room::$floors)))
			return array();

		//selects floormaps that are ONLY in files
		$filesUnformatted = glob(dirname(__FILE__)."/../../images/floormaps/{$args['location']}/{$args['floor']}/*.png");

		if(!empty($filesUnformatted)){
			$files = Room::formatFloormaps($filesUnformatted, @$args['floormap']);
		}
		else $files = array();

		//selects floormaps that are ONLY in database
		//if floormap parameter is passed, we also skip floormaps
		//that are already assigned to a room
		$query = "SELECT floormap 
			FROM ".self::$tableName." 
			WHERE 1=1 
				and floormap <> '' 
				and floormap is not null
				".(@$args['location'] != '' ? " AND location='{$args['location']}' " : "")."
				".(@$args['floor'] != '' ? " AND floor={$args['floor']} " : "")."
				".(@$args['floormap'] != '' ? " AND floormap not in ('{$args['floormap']}') " : "")."
				AND deleted=0
			ORDER BY floormap";

		$results = $db->getAll($query);

		if (PEAR::isError($results)) {
			handle_error($status, $results, false);
		}


		$items = array();
		foreach ($results as $r) {
			array_push($items, $r['floormap']);
		}

		$normal = array_values(array_diff($files,$items));
		if(@$args['floormap'] != ''){
			array_push($normal, $args['floormap']);
		}

		return $normal;
	}

	//Fetch Map Objects  
	//All description in getFloormaps()
	public static function getMapObjects($args=array())
	{
		global $db, $status;

		if (@$args['location'] == "" || !in_array(@$args['floor'], array_keys(Room::$floors)))
			return array();

		$filesUnformatted = glob(dirname(__FILE__)."/../../images/floormaps/{$args['location']}/{$args['floor']}/*.png");

		if(!empty($filesUnformatted)){
			$files = Room::formatFloormaps($filesUnformatted, @$args['map_object']);
		}
		else $files = array();

		$query = "SELECT map_object 
			FROM ".self::$tableName." 
			WHERE 1=1 
				and map_object <> '' 
				and map_object is not null
				".(@$args['location'] != '' ? " AND location='{$args['location']}' " : "")."
				".(@$args['floor'] != '' ? " AND floor={$args['floor']} " : "")."
				".(@$args['map_object'] != '' ? " AND map_object not in ('{$args['map_object']}') " : "")."
				AND deleted=0
			ORDER BY map_object";

		$results = $db->getAll($query);

		if (PEAR::isError($results)) {
			handle_error($status, $results, false);
		}

		$items = array();
		foreach ($results as $r) {
			array_push($items, $r['map_object']);
		}

		$normal = array_values(array_diff($files,$items));
		if(@$args['map_object'] != ''){
			array_push($normal, $args['map_object']);
		}

		return $normal;
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

		// prepare query
		//$stmt = $this->db->prepare($query);

		// execute statement
		//$result =& $this->db->execute($stmt);

        //$sth = $this->db->prepare($query);

        //$result =& $this->db->execute($query);
        //echo $query;
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
		$query="DELETE FROM ".self::$tableName." WHERE id=".$this->id;

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
		$updatestr.=join(',',$updatearr)." WHERE id=".$this->id;
		return $updatestr;
	}
}

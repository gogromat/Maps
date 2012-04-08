<?php
require_once(dirname(__FILE__)."/DbObject.php");
//require_once(dirname(__FILE__)."/RoomType.php");

class StoreProduct extends DbObject
{
	public static $tableName = "store_products";
	public $exists = false;

	/** [[Column=store_id, DataType=int, Description=Store Id]]*/
	public $store_id;

    /** [[Column=product_id, DataType=int, Description=Product Id]]*/
	public $product_id;

	/** [[Column=price, DataType=double, Description=Price, Required=true]]*/
	public $price;

	/** [[Column=quantity, DataType=int, Description=Quantity]]*/
	public $quantity;

	/** [[Column=image, DataType=varchar, Description=Image, MaxLength=80]]*/
	public $image;

	// foreign key fields (will be populated upon request)

	public function fetchData($store_id,$product_id)
	{
		//list($arg1, $arg2) = $args;
		$query="SELECT * FROM ".self::$tableName." WHERE product_id=".$this->product_id." AND store_id=".$this->store_id;

		$row = $this->db->getRow($query);
		
		if (count($row) > 0)
			$this->setPropertyValues($row);

		if ($this->product_id > 0)
			$this->exists = true;
	}

	public function getStoreProducts($args=array())
	{
		global $db, $status;

		$query = "SELECT *
			FROM ".self::$tableName."
			WHERE 1=1
				".(is_array(@$args['product_ids']) ? " AND product_id IN (".implode(',',$args['product_ids']).") " : "")."
				".(@$args['product_id'] != '' ? " AND product_id={$args['product_id']} " : "")."

				".(is_array(@$args['store_ids']) ? " AND store_id IN (".implode(',',$args['store_ids']).") " : "")."
				".(@$args['store_id'] != '' ? " AND store_id={$args['store_id']} " : "")."

				".(@$args['price'] != '' ? " AND price={$args['price']} " : "")."
				".(@$args['quantity'] != '' ? " AND quantity ={$args['name']} " : "")."
				".(@$args['image'] != '' ? " AND image ='{$args['image']}' " : "")."
	        ORDER BY ".(@$args['order_by'] != '' ? $args['order_by'] : " store_id, product_id ").
            (@$args['limit'] != '' ? " LIMIT {$args['limit']} " : "");

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
		$query="DELETE FROM ".self::$tableName." WHERE product_id=".$this->product_id." AND store_id=".$this->store_id;

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

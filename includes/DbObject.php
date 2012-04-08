<?php
require_once 'database.php';
require_once 'AttributeReader.php';

class DbObject
{
	public $errorStack = array();
	public $db;

	/*Properties Column=ComputerID, DataType=int/varchar/float/text/datetime/bit, Description=Title, ReadOnly=true, Required=true, MaxLength=100]]*/

	public function __construct($id=null)
	{
		global $db;
		$this->db = $db;

		if (func_num_args() > 1) {
			if (func_get_arg(0) != null)
				$this->fetchMyData(func_get_args());
		}
		else if ($id != null) {
			// fetch data if $id is present
			$this->fetchData($id);
		}
	}

	/** setPropertyValues() does not interact with database, only with form data */ 
	public function setPropertyValues($values)
	{
		if ($values != null && is_array($values))
		{//print_r($values);
			$rf = new ReflectionClass($this);
			$props = $rf->getProperties();
			foreach($props as $prop)
			{
				$column = AttributeReader::PropertyAttributes($this,$prop->getName())->Column;
				$datatype = AttributeReader::PropertyAttributes($this,$prop->getName())->DataType;
				$required = AttributeReader::PropertyAttributes($this,$prop->getName())->Required;

				if ($column != null && $column != "" && array_key_exists($column,$values))//isset($values[$column])
				{
					//if($prop->getName()=='salvage_date')var_dump(__FILE__,__LINE__,$values[$column],$datatype,$required,isset($values[$column]),"<br>");
					
					if ($datatype == "datetime")
					{//echo $values[$column].", ";
						//$trace=debug_backtrace();if(count($trace)>0)print_r($trace);//,$t['file'],$trace['line']);
						//if (date("Y-m-d", strtotime($values[$column])) != "1969-12-31")
						if (trim($values[$column]) != "")
							$prop->setValue($this, date("Y-m-d G:i:s", strtotime(preg_replace("/:[0-9]{3}/"," ",$values[$column])))); // remove :000 at the end of dates
							//$prop->setValue($this, date("Y-m-d", strtotime($values[$column])));
						else
							$prop->setValue($this, ""); // set to empty (and eventually to NULL) if no date is provided
					}
					else {
						//if($prop->getName()=='salvage_date')echo(var_dump($values[$column],$datatype,$required));
						$prop->setValue($this,trim($values[$column]));
					}
				} else {
					//if($prop->getName()=='salvage_date')var_dump($prop->getValue($obj),$datatype,$required,"<br>");
					//$prop->setValue($this,trim($values[$column]));
				}
			}
		}
		else {
			//throw new Exception("Values passed are not valid in array format");
		}
	}

	public function setValue($obj, $prop)
	{
        //echo '__|__'.$prop.'__|__';
		$datatype = AttributeReader::PropertyAttributes($obj,$prop->getName())->DataType;
		$required = AttributeReader::PropertyAttributes($obj,$prop->getName())->Required;
		// ReflectionProperty::getValue()
        $data = $prop->getValue($obj);
        
        if ($prop->getName()=='priority') {
            //var_dump(__LINE__,$prop->getValue($obj),$datatype,$required,"<br>");
            //die($this->sqlEscapeValue($datatype, $data, $required));
        }
        //echo '?'.$datatype .'XX'. $data .'XX'.$required .'?';
        //echo '=======>'.$data.'<=====';
        return $this->sqlEscapeValue($datatype, $data, $required);
	}

	function sqlEscapeValue($datatype, $value, $required=false)
	{
		$datatype = strtolower($datatype);

		if ($required === null)
			$required = false;
//var_dump(__FILE__,__LINE__,$value,$datatype,$required,"<br>");
		if ($value == null || trim($value) === '')
		{
			if ($datatype == "int" || $datatype == "float")
				$value = ($required ? 0 : "NULL");
			else if ($datatype == "bit")
				$value = ($required ? 0 : "NULL");
			else
				$value = "NULL";
		}
		else if ($datatype == 'bit')
		{
			$value = ($value == "on" || $value == '1' || $value != '0') ? "1" : ($required || $value === '0' ?"0":"NULL");
		}
		else /*if (($datatype == 'datetime') ||
					($datatype == 'uniqueidentifier') ||
					($datatype == 'datetime') ||
					($datatype == 'text') ||
					($datatype == 'ntext') ||
					($datatype == 'varchar') ||
					($datatype == 'nvarchar') ||
					($datatype == 'char'))*/
		{
			// NOTE: make sure to escape &, ?, and ! for prepare/execute to work properly
            //$value = "'" . $this->db->query(preg_replace("/([&?!])/",'\\\\'.'$1',$value)) . "'";//str_replace("'", "''",
            $value = "'" . preg_replace("/([&?!])/",'\\\\'.'$1',$value). "'";
		}
        //echo '====='.$value.'=====';
		return $value;
	}
	
	public function insertString($tableName)
	{
		$insertstr = "INSERT INTO {$tableName} (";
		
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
				$insertarr[$insertindex++] = $column;
				$valuesarr[$insertindex] = $this->setValue($this, $prop);
			}
		}
		$insertstr .= join(',',$insertarr).") VALUES (".join(',',$valuesarr).")";
		//echo $insertstr;
		return $insertstr;
	}
	
	public function updateString($tableName, $primaryKey)
	{
		$updatestr = "UPDATE {$tableName} SET ";
		
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
		$updatestr .= join(',',$updatearr)." WHERE {$primaryKey}=".$this->$primaryKey;
		
		return $updatestr;
	}

	public function getLastInsertedId()
	{
		$query = "SELECT @@IDENTITY as id";

		//execute query
        //$id = $this->db->getOne($query);
        //$id = $this->db->query($query);
		//if (PEAR::isError($id)) {
		//	return null;
		//}
        $row = $this->db->query($query)->fetch();
        return $row[0];
        //return $id;
	}
	
	//******************************************************************//
	public function getAll($sql, $style = PDO::FETCH_ASSOC) {

		global $db;
		//$result = null;
		
		try {
			//$handle = self::connect();
			//$stmt = $handle->prepare($sql);
			//$stmt->execute();
			//self::execute($sql);
			//print_r($this);
			$stmt = $db->prepare($sql);
			$stmt->execute();
			$result = $stmt->fetchAll($style);
		}
		catch(PDOException $e) {
			//self::close();
			trigger_error($e->getMessage(), E_USER_ERROR);
			return handle_error($status, $this->results);
		}
		return $result;
	}
	
	public function getRow($sql, $style = PDO::FETCH_ASSOC) {
		$result = null;
		
		try {
			$handler = self::connect();
			$stmt = $handler->prepare($sql);
			$stmt->execute();
			$result = $stmt->fetch($style);
		}
		catch(PDOException $e) {
			self::close();
			trigger_error($e->getMessage(), E_USER_ERROR);
		}
		
		return $result;
	}
	//******************************************************************//


	/**
	 * Returns object in JSON notation
	 * @return JSON object
	 */
	public function toJSON()
	{
		$columns = array();
		
		$rf = new ReflectionClass($this);
		$props = $rf->getProperties();
		foreach ($props as $prop)
		{
			$column = AttributeReader::PropertyAttributes($this,$prop->getName())->Column;
			$columns[$column] = $prop->getValue($this);
		}

		return json_encode($columns);
	}
	
	/**
	 * Returns object in array notation
	 * @return array
	 */
	public function toArray()
	{
		$columns = array();
		
		$rf = new ReflectionClass($this);
		$props = $rf->getProperties();
		foreach ($props as $prop)
		{
			$column = AttributeReader::PropertyAttributes($this,$prop->getName())->Column;
			if ($column != "")
				$columns[$column] = $prop->getValue($this);
		}

		return $columns;
	}

	// Public Methods
	public function validate()
	{
		$rf = new ReflectionClass($this);
		$props = $rf->getProperties();
		foreach ($props as $prop)
		{
			$required = AttributeReader::PropertyAttributes($this,$prop->getName())->Required;
			$column = AttributeReader::PropertyAttributes($this,$prop->getName())->Column;
			$datatype = AttributeReader::PropertyAttributes($this,$prop->getName())->DataType;
			$ReadOnly = AttributeReader::PropertyAttributes($this,$prop->getName())->ReadOnly;
			$Description = AttributeReader::PropertyAttributes($this,$prop->getName())->Description;

			if ($required == "true" && !$ReadOnly)
			{
				if ($prop->getValue($this) === null ||
					($datatype == "varchar" && $prop->getValue($this) == "") ||
					($datatype == "datetime" && $prop->getValue($this) == "") ||
					($datatype == "int" && $prop->getValue($this) === "") ||
					($datatype == "bit" && $prop->getValue($this) === "")||
					($datatype == "float" && $prop->getValue($this) === "")) 
				{
					array_push($this->errorStack, $Description." is required");
				}
			}
			if (!$ReadOnly)
			{
				if ($prop->getValue($this) != null &&
						(
						($datatype == "datetime" && $prop->getValue($this) == "") || 
						($datatype == "int" && !is_numeric($prop->getValue($this))) ||
						($datatype == "bit" && !preg_match("/[01]/",$prop->getValue($this)))
						)
					)
				{
					array_push($this->errorStack, $Description." is invalid");
				}
			}
		}

		return (count($this->errorStack) == 0);
	}

	public function __toString()
	{
		$objectdef = array();

		$rf = new ReflectionClass($this);
		$props = $rf->getProperties();
		foreach($props as $prop)
		{
			$column = AttributeReader::PropertyAttributes($this,$prop->getName())->Column;
			$description = AttributeReader::PropertyAttributes($this,$prop->getName())->Description;
			if ($column != null && $column != "")
			{
				array_push($objectdef, $description.": ".$prop->getValue($this));
			}
		}

		return join("<br/>", $objectdef);
	}

	public function startTrans()
	{
		//$this->db->autoCommit(false);
        $this->db->beginTransaction();
	}
	
	public function rollbackTrans()
	{
		$this->db->rollBack();
	}
	
	public function commitTrans()
	{
        $this->db->commit();
		//$this->db->commit();
		//$this->db->autoCommit(true);
        //$this->db->exec();
	}

}
?>

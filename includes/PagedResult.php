<?php
class PagedResult extends DbObject
{
	public $results_per_page = 20;
	public $total_pages = 1;
	public $current_page = 1;
	public $total_results = 0;
	public $select = '*';
	public $order = '';
	public $query = '';
	public $tables = array();
	public $conditions = array();
	public $results = array();

	public function isFirstPage()
	{
		return $this->current_page <= 1;
	}

	public function isLastPage()
	{
		return $this->current_page >= $this->total_pages;
	}

	public function OrderBy($sort_by_str)
	{
		$order = array();
		if (strpos($sort_by_str,',') >= 0) {
			//ACHTUNG
			foreach (preg_split('/,/',$sort_by_str) as $sort_by) {
				if (strlen(trim($sort_by)) > 2) {
					$order[] = preg_replace("/_a$/i"," asc", preg_replace("/_d$/i"," desc", $sort_by));
				}
			}
		}
		$this->order = implode(',',$order);
	}

	public function print_r2($v)
	{
		echo "<pre>";
		print_r($v);
		echo "</pre>";
	}

	/*
	$conditions[]=array(
		'AND'=>array(
			'campus_name' = array($campus_name,'like'),
			'note' = array($note,'like'),
			'custom1' => array("(bundle_name like '%t')",'custom'),
			)
		);
	$conditions[]=array(
		'OR'=>array(
			'address1' = array($address1,'like'),
			'address2' = array($address2,'like'),
			'city' = array($city,'='),
			'state' = array($state,'eq'),
			'zip' = array($zip,'like') )
		);
	//$this->table = array('resources'=>'vendor_id','vendors'=>'vendor_id');
	*/
	public function RunSearch($debug=false)
	{
		global $status;

		if ($this->query == '')
		{
			$query = 'SELECT '.$this->select.'
					FROM '.$this->table.' WHERE 1=1 ';
//			if($debug) print_r($this->conditions);
			foreach ($this->conditions as $index=>$cond)
			{
				foreach ($cond as $cond_op=>$fields)
				{
//					//if($debug) print_r($cond);
//					if($debug) print_r($fields);
					$cond_arr=array();
					foreach ($fields as $name=>$values)
					{
						if ($values[0] === "" || $values[0] == null) {
							//echo 'empty '.$name;
							continue;
						}

						try {
							$value = $values[0];
							$oper = $values[1];
						}catch(Exception $e){
							var_dump($values);
							die;
						}

						if ($value === "")
							continue;

						switch ($oper)
						{
							case "in":
								if (is_array($value) && count($value) > 0)
									$cond_arr[] = "(".$name." IN ('".join("','",$value)."'))";
								break;

							case "null":
								$cond_arr[] = "($name IS NULL)";
								break;

							case "not null":
								$cond_arr[] = "($name IS NOT NULL)";
								break;

							case "bool":
								$cond_arr[] = "($name=".($value=='on' || $value=='1' ? 1:0).")";
								break;

							case "eq":
								$cond_arr[] = "($name = '$value')";
								break;
							case "=":
								$cond_arr[] = "($name = $value)";
								break;

							case "gt":
								$cond_arr[] = "($name > '$value')";
								break;
							case ">":
								$cond_arr[] = "($name > $value)";
								break;

							case "gte":
								$cond_arr[] = "($name >= '$value')";
								break;
							case ">=":
								$cond_arr[] = "($name >= $value)";
								break;

							case "lt":
								$cond_arr[] = "($name < '$value')";
								break;
							case "<":
								$cond_arr[] = "($name < $value)";
								break;

							case "lte":
								$cond_arr[] = "($name <= '$value')";
								break;
							case "<=":
								$cond_arr[] = "($name <= $value)";
								break;
								
							case "date_eq":
								$cond_arr[] = "($name BETWEEN '$value' AND '$value 23:59:59')";
								break;

							case "custom":
								$cond_arr[] = "$value";
								break;

							default:

							case "like":
								$cond_arr[] = "($name LIKE '%$value%')";
								break;
						}
					}
					if (count($cond_arr) > 0)
						$query .= ' AND ('.implode(" $cond_op ",$cond_arr).')';
				}
			}
			$this->query = $query;
		}

		//$this->query .= $this->conditions['custom'];
		if($debug) echo $this->query;//." order by ".$this->order;

		$startrow = ($this->current_page-1) * $this->results_per_page+1;
		$endrow = $startrow + $this->results_per_page;

		//ACHTUNG
		$execquery = "SELECT *
						FROM (".preg_replace("/\sfrom\s/i", ", ROW_NUMBER() OVER (order by ".$this->order.") as RowNum from ", $this->query, 1).") search_query
						where RowNum >= $startrow and RowNum < $endrow";
		echo $this->query."<br/>".$execquery;

		//ACHTUNG
		$this->results = $this->getAll($execquery);

		print_var($this->results);
		//if (PEAR::isError($this->results)) {
		//	die($this->results->getUserinfo());
		//	return handle_error($status, $this->results);
		//}

		$results = $this->db->prepare("select count(*) from (".$this->query.") as _c");
		
		$this->total_results = $results->fetchColumn();
		//if (PEAR::isError($this->total_results)) {
		//	return handle_error($status, $this->total_results);
		//}
			
		$this->total_pages = ceil($this->total_results / $this->results_per_page);
	}

	public function getPager()
	{
		?>
		<input type="hidden" name="page" value="1"/>
		<input type="hidden" name="items_per_page" value="<?=$this->results_per_page;?>"/>

		<input type="submit" value="First" onclick="form.page.value='1'" <?=($this->isFirstPage() ? "disabled":"") ?> class="button btn-go_first" style="width:65px;"/>
		<input type="submit" value="Previous" onclick="form.page.value='<?=$this->current_page-1 ?>'" <?=($this->isFirstPage() ? "disabled":"") ?> class="button btn-go_prev" style="width:85px;"/>
		&nbsp;
		Page
		<select onchange="form.page.value=this[this.selectedIndex].value; form.submit();">
			<?php for ($p=1; $p <= $this->total_pages; $p++) {
				$selected=($p==$this->current_page ? "SELECTED='SELECTED'":""); ?>
				<option value="<?=$p;?>" <?=$selected;?>><?=$p;?></option>
			<?php } ?>
		</select> of <?=$this->total_pages; ?>
		&nbsp;
		<input type="submit" value="Next" onclick="form.page.value='<?=$this->current_page+1 ?>'" <?=($this->isLastPage() ? "disabled":"") ?> class="button btn-go_next" style="width:60px;"/>
		<input type="submit" value="Last" onclick="form.page.value='<?=$this->total_pages;?>'"<?=($this->isLastPage() ? "disabled":"") ?> class="button btn-go_last" style="width:60px;"/>
		&nbsp;
		Show:
		<select onchange="form.items_per_page.value=this[this.selectedIndex].value; form.submit();">
			<?php
			$all=10000;
			foreach (array(10,20,50,100,$all) as $item) {
				?><option value="<?=$item;?>" <?php if($this->results_per_page==$item) echo "SELECTED='SELECTED'";?>><?=($item==$all?'All':$item);?></option><?
			}
			?>
		</select>
		<?php
	}
}
?>

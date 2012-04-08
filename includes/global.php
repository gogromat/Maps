<?php
define("SESSION_TIMEOUT",		1800); // 30 mins
//define("COMMON_FILES_PATH",		"http://dewey.brooklyn.cuny.edu/common/");
$_server_port = '';
if ($_SERVER['SERVER_PORT'] != 80)
	$_server_port = ':'.$_SERVER['SERVER_PORT'];
define("COMMON_FILES_PATH",		"/common/");

  //http://{$_SERVER['SERVER_NAME']}{$_server_port}/common/

// Error message to echo when user has no rights to modify page contents
if (!defined("ERR_READONLY"))
	define("ERR_READONLY", "You are not permitted to make changes on this page");
if (!defined("ERR_NOACCESS"))
	define("ERR_NOACCESS", "You do not have access to this page");

/**
 * Check if an array is empty -- similar to PHP's "empty()", but for arrays
 */
function array_empty($arr)//, $ignore=array()//
{
	if ($arr == null || count($arr) == 0) 
		return true;
	foreach ($arr as $k=>$v)//array_diff_assoc($arr,$ignore)//
	{
		if(!in_array($k, array('sort_by', 'items_per_page', 'page')) && (!empty($v) || $v === '0' || $v === 0))
			return false;
	}
	return true;
}

function search_empty($arr)
{
	$diff = array_diff_key($arr, array('sort_by', 'items_per_page', 'page'));
	//var_dump($diff);
	//var_dump(array_empty($diff));
	return array_empty($diff);
}

//$fields=array('subject_title'=>'biology','sort_by'=>'title_a');
//var_dump(array_empty($fields,array('sort_by','subject_title')));


/**
 * Accepts an array of Objects and $key a string representing a specific property in the objects.
 * Returns a simple array with values on the property $key
 */
function array_from_key($key, $objArray)
{
	$arr = array();
	foreach ($objArray as $r) {
		$tmp = (is_object($r) ? $r->toArray() : (is_array($r) ? $r : array()));
		if (array_key_exists($key, $tmp))
			$arr[] = $tmp[$key];
	}
	return $arr;
}

// color version of print_r() used for PHP variables
function print_var($arg, $pre=0)
{
	if ($pre) echo '<pre>';
	echo preg_replace('/\[(.*)\] => (.*)/i', '[<span style="color:blue">$1</span>] => <span style="color:red">$2</span>', print_r($arg,1));
	if ($pre) echo '</pre>';
}


// color version of print_r() used for SQL query strings
function print_sql($query,$pre=1)
{
	if ($pre) echo '<pre>';
	echo preg_replace('/(select|from|where|order by|group by|having)/i','<span style="color:blue">$1</span>', 
							preg_replace('/(sum|count|max|min|day|month|year)/i','<span style="color:red">$1</span>', 
							preg_replace('/(\(\)\s)(inner|outer|join|and)(\(\)\s)/i','<span style="color:gray">$2</span>', $query)));
	if ($pre) echo '</pre>';
}


function debug($trace)
{
	print_r($trace);return;
	//foreach ($traces as $trace) {
		foreach ($trace as $act) {
			//foreach ($r as $act) {
				//$act=$trace[count($trace)-1];
				echo "<div>{$act['file']}:<b>{$act['line']}</b> <span style='color:gray'>{$act['function']}</span></div>";
				if (isset($act['args'])) {
					foreach ($act['args'] as $arg)
						echo "<div style='padding-left:30px;'>$arg</div>";
				}
			//}
		}
	//}
}

?>
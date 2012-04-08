<?php
require_once '../application_info.php';
$option = (isset($_REQUEST['option']) ? $_REQUEST['option']:"");

$query = (isset($_REQUEST['query']) ? $_REQUEST['query']:"");//$db->query()

if (accessible_permission("add_parameter","model")) {
	die('You do not have access to this page');
}
if (preg_match("/update|insert|delete|drop|alter|exec|call/i", $query)) {
	die('Illegal query');
}

$db->setFetchMode (DB_FETCHMODE_ORDERED);
$result = $db->fetchAll($query);
$db->setFetchMode (DB_FETCHMODE_ASSOC);
if (PEAR::isError($result))
	die("<span style='color:red'>".$result->getUserinfo()."</span>");

if (@$_GET['type'] == 'json') 
{
	$results = array();
	foreach ($result as $r) 
	{
		$results[] = $r;
	}
	echo json_encode($results);
}
else // plain text
{
	foreach ($result as $r) 
	{
		echo $r[0].' - '.$r[1].'<br/>';
	}
}
?>
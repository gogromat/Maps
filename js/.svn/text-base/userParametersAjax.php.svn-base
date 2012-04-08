<?php
require_once '../application_info.php';
require_once '../includes/UserParameter.php';

$param_id = (isset($_GET['param_id']) ? (int)$_GET['param_id']:0);
$user_id = (isset($_GET['user_id']) ? (int)$_GET['user_id']:0);

$parameter = new Parameter($param_id);
$param_values = $parameter->getValuesQueryResults();//array('not_user_id'=>$user_id)

if (@$_GET['type'] == 'json') 
{
	$results = array();
	foreach ($param_values as $r) 
	{
		$results[] = $r;
	}
	echo json_encode($results);
}
else // plain text
{
	foreach ($param_values as $r) 
	{
		echo $r[0].' - '.$r[1].'<br/>';
	}
}
?>
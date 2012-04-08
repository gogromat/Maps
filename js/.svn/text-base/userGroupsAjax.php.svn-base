<?php
require_once '../application_info.php';
require_once '../includes/UserGroup.php';

$user_id = (isset($_GET['user_id']) ? (int)$_GET['user_id']:0);

$args = array();
if ($user_id > 0) $args['not_user_id'] = $user_id;

$items = Group::getGroups($args);

if (@$_GET['type'] == 'json') 
{
	$results = array();
	foreach ($items as $r) 
	{
		$results[] = $r->toArray();
	}
	echo json_encode($results);
}
else // plain text
{
	foreach ($items as $r) 
	{
		echo $r->group_id.' - '.$r->group_name.'<br/>';
	}
}
?>
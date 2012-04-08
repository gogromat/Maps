<?php
require_once '../application_info.php';
require_once '../includes/GroupPermission.php';

$application_id = (isset($_GET['application_id']) ? (int)$_GET['application_id']:0);
$not_group_id = (isset($_GET['not_group_id']) ? (int)$_GET['not_group_id']:0);

$args = array();
if ($application_id > 0) $args['application_id'] = $application_id;
if ($not_group_id > 0) $args['not_group_id'] = $not_group_id;

$permissions = Permission::getPermissions($args);

if (@$_GET['type'] == 'json') 
{
	$results = array();
	foreach ($permissions as $r) 
	{
		$results[] = $r->toArray();
	}
	echo json_encode($results);
}
else // plain text
{
	foreach ($permissions as $r) 
	{
		echo $r->permission_id.' - '.$r->permission_name.'<br/>';
	}
}
?>
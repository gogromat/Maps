<?php
require_once '../application_info.php';
require_once '../includes/Log.php';

$args = array();
$args['entry_id'] = (int)@$_REQUEST['entry_id'];
$args['entry_date'] = $db->query(trim(@$_REQUEST['entry_date']));
$args['username'] = $db->query(trim(@$_REQUEST['username']));
$args['ip_address'] = $db->query(trim(@$_REQUEST['ip_address']));
$args['application'] = $db->query(trim(@$_REQUEST['application']));
$args['context'] = $db->query(trim(@$_REQUEST['context']));
$args['action'] = $db->query(trim(@$_REQUEST['action']));
$args['item_id'] = $db->query(trim(@$_REQUEST['item_id']));
$args['details'] = $db->query(trim(@$_REQUEST['details']));

$items = Log::getLogs($args);
if (PEAR::isError($items)) {
	die(json_encode(array('DB_Error'=>$items->getMessage())));
}

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
		echo $r->item_id.' - '.$r->details.'<br/>';
	}
}
?>
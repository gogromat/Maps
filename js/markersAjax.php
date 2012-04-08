<?php
//http://code.google.com/apis/maps/articles/phpsqlsearch.html
// Require Database files
require_once("../includes/Marker.php");
require_once("../includes/StoreProduct.php");
require_once("../includes/global.php");
// Get parameters from URL
$args['center_lat']     = (trim(@$_REQUEST['lat']));
$args['bottom_left_lat']= (trim(@$_REQUEST['bottom_left_lat']));
$args['top_right_lat']  = (trim(@$_REQUEST['top_right_lat']));

$args['center_lng']     = (trim(@$_REQUEST['lng']));
$args['bottom_left_lng']= (trim(@$_REQUEST['bottom_left_lng']));
$args['top_right_lng']  = (trim(@$_REQUEST['top_right_lng']));

$args['product_id']     = (trim(@$_REQUEST['product_id']));

$args['radius']         = (trim(@$_REQUEST['radius']));
$args['limit']          = (trim(@$_REQUEST['limit']));

// Types
$types = array();

if (isset($_REQUEST['types'])) {
    if (!is_array($_REQUEST['types']) && is_numeric($_REQUEST['types']))
        $types[] = (int)$_REQUEST['types'];
    else if (is_array($_REQUEST['types']))
        $types = $_REQUEST['types'];
}
// Get Results
$items = Marker::getMarkers(array('radius'=>$args['radius'],'limit'=>$args['limit'],
                                      'with_type_ids'=>$types,'with_marker_places'=>true,'product_id'=>$args['product_id'],
                                  'center_lat'=>$args['center_lat']          ,'center_lng'=>$args['center_lng'],
                                  'bottom_left_lat'=>$args['bottom_left_lat'],'bottom_left_lng'=>$args['bottom_left_lng'],
                                  'top_right_lat'=>$args['top_right_lat']    ,'top_right_lng'=>$args['top_right_lng']));

//if (PEAR::isError($items)) { die(json_encode(array('DB_Error'=>$items->getMessage())));}
if (@$_REQUEST['type'] == 'json')
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
    //[{"id":"121","name":"Jasper Pizza Place","address":"402 Connaught Dr, Jasper, AB","lat":"52.879086","lng":"-118.079315","distance":"8056.09876638623"},
    echo "[";
	foreach ($items as $r)
	{
		echo '{"'.$r->name.'","'.$r->address.'","'.$r->lat.'","'.$r->lng.'","'.$r->distance.'"}';
        //if () echo ",";
	}
    echo "]";
}

?>
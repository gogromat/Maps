<?php
//http://code.google.com/apis/maps/articles/phpsqlsearch.html
// Require Database files
require_once("../includes/Marker.php");
require_once("../includes/Place.php");
require_once("../includes/MarkerPlace.php");
require_once("../includes/global.php");

// Get parameters from URL
$form['lat']        = (trim(@$_REQUEST["lat"]));
$form['lng']        = (trim(@$_REQUEST["lng"]));
$form['rating']     = (trim(@$_REQUEST["rating"]));
$form['name']       = (trim(@$_REQUEST["name"]));
$form['address']    = (trim(@$_REQUEST["address"]));
$form['google_id']  = (trim(@$_REQUEST["google_id"]));
$form['phone']      = (trim(@$_REQUEST["phone"]));
$form['url']        = (trim(@$_REQUEST["url"]));
//types

$types = array();

if (isset($_GET['types'])) {
    if (!is_array($_GET['types']) && is_numeric($_GET['types']))
        $types[] = (int)$_GET['types'];
    else if (is_array($_GET['types']))
        $types = $_GET['types'];
}
else {
    //return handle_error($status, 'Type ID(s) missing');
    echo 'Type ID(s) missing';
}

//print_var($types);

// Get Markers
$results = Marker::getMarkers(array('lat'=>$form['lat'],'lng'=>$form['lng'],'name'=>$form['name']));
// Get Places (Types)
$places  = Place::getPlaces();

$place_ids = array_from_key('place_id',$places);
//var_dump($place_ids);

$marker_types = array();

foreach($types as $type) {
    if(!in_array($type,$place_ids)) {
        echo 'Error!';
    }
    else {
        //$marker_types[] = new MarkerType();
    }
}

$marker = new Marker(@$_REQUEST['id']);
$marker->setPropertyValues($form);

//print_var($marker->toArray(),1);
//die();

if (!$marker->validate()) {
    // some message here
    //marker->rollBack();
    echo "Error!";
}
else {

    try{

        //db object beginTransaction
        $marker->startTrans();

        $existed = $marker->exists;

        $result = $marker->save();

        if (!$existed) {
            $marker->id = $marker->getLastInsertedId();
        }

        // marker types
        foreach ($types as $type) {
            $marker_place = new MarkerPlace();//$marker->id,$type);
            $marker_place->marker_id = $marker->id;
            $marker_place->place_id  = $type;
            //print_var($marker_place->toArray(),1);
            $marker_types[] = $marker_place;
            $result2 = $marker_place->save();
        }

        //print_var($marker->toArray(),1);

    }
    catch (PDOException $e) {
        try {
            // db object rollBack
            $marker->rollbackTrans();
        }
        catch (Exception $e2) {
            echo $e2;
            die();
        }
    }
}

//check against

if (@$_REQUEST['type'] == 'json')
{
    $results = array();
    $results[] = $marker->toArray();
    foreach ($marker_types as $r)
    {
        $results[] = $r->toArray();
    }
    echo json_encode($results);
    //echo json_encode("status");
}
else // plain text
{
    //[{"id":"121","name":"Jasper Pizza Place","address":"402 Connaught Dr, Jasper, AB","lat":"52.879086","lng":"-118.079315","distance":"8056.09876638623"},
    echo "[";

    /*
    foreach ($items as $r)
    {
        echo '{"'.$r->name.'","'.$r->address.'","'.$r->lat.'","'.$r->lng.'","'.$r->distance.'"}';
        //if () echo ",";
    }
    */

    echo "]";
}

?>
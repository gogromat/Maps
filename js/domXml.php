<?php
//http://code.google.com/apis/maps/articles/phpsqlsearch.html
// Require Database files
require_once("../includes/Marker.php");
require_once("../includes/global.php");

// Get parameters from URL
$center_lat = $_GET["lat"];
$center_lng = $_GET["lng"];
$radius = $_GET["radius"];


// Start XML file, create parent node
$dom = new DOMDocument("1.0");
$node = $dom->createElement("markers");
$parnode = $dom->appendChild($node);

// Get Results
$results = Marker::getMarkers(array('radius'=>$radius,'center_lat'=>$center_lat,'center_lng'=>$center_lng));

header("Content-type: text/xml");

// Iterate through the rows, adding XML nodes for each
//while ($row = @mysql_fetch_assoc($result)){
//TODO: LATER TRANSFER TO CLASS
foreach ($results as $row)
{
    $node = $dom->createElement("marker");
    $newnode = $parnode->appendChild($node);
    $newnode->setAttribute("name", $row->name);
    $newnode->setAttribute("address", $row->address);
    $newnode->setAttribute("lat", $row->lat);
    $newnode->setAttribute("lng", $row->lng);
    $newnode->setAttribute("distance", $row->distance);
}

echo $dom->saveXML();
?>
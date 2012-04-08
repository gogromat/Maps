<?php
// Require Database files
require_once("../includes/Product.php");
require_once("../includes/global.php");
// Get parameters from URL
$form['product_name']    = (trim(@$_REQUEST["product_name"]));
$form['barcode']         = (trim(@$_REQUEST["barcode"]));
$form['barcode_type']    = (trim(@$_REQUEST["barcode_type"]));
$form['image']           = (trim(@$_REQUEST["image"]));
// Make new Object
$product = new Product(@$_REQUEST['id']);
$product->setPropertyValues($form);
//Validate
if (!$product->validate()) {
    echo "Error!";
}
else {
    try{
        $product->startTrans();
        $existed = $product->exists;
        $result = $product->save();
        if (!$existed) {
            $product->product_id = $product->getLastInsertedId();
        }
    }
    catch (PDOException $e) {
        try {
            $product->rollbackTrans();
        }
        catch (Exception $e2) {
            echo $e2;
            die();
        }
    }
}
if (@$_REQUEST['type'] == 'json')
{
    $results = array();
    $results[] = $product->toArray();
    echo json_encode($results);
}
else // plain text
{
    echo "[";
    /*foreach ($items as $r) {
        echo '{"'.$r->name.'","'.$r->address.'","'.$r->lat.'","'.$r->lng.'","'.$r->distance.'"}';
        //if () echo ","; }*/
    echo "]";
}
?>
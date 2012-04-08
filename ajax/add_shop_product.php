<?php
// Require Database files
require_once("../includes/global.php");
require_once("../includes/Shop.php");
require_once("../includes/Product.php");
// Get parameters from URL
$form['product_name']    = (trim(@$_REQUEST["product_name"]));
$form['barcode']         = (trim(@$_REQUEST["barcode"]));
$form['barcode_type']    = (trim(@$_REQUEST["barcode_type"]));
$form['image']           = (trim(@$_REQUEST["image"]));


// Shops
$shops = array();

if (isset($_REQUEST['shops'])) {
    if (!is_array($_REQUEST['shops']) && is_numeric($_REQUEST['shops']))
        $shops[] = (int)$_REQUEST['shops'];
    else if (is_array($_REQUEST['shops']))
        $shops = $_REQUEST['shops'];
}
else {
    echo 'Shop(s) missing';//return handle_error($status, 'Type ID(s) missing');
}

// Products
$products = array();

if (isset($_REQUEST['products'])) {
    if (!is_array($_REQUEST['products']) && is_numeric($_REQUEST['products']))
        $products[] = (int)$_REQUEST['products'];
    else if (is_array($_REQUEST['products']))
        $products = $_REQUEST['products'];
}
else {
    echo 'Product(s) missing';//return handle_error($status, 'Type ID(s) missing');
}

// Prices
$prices = array();

if (isset($_REQUEST['prices'])) {
    if (!is_array($_REQUEST['prices']) && is_numeric($_REQUEST['prices']))
        $prices[] = (int)$_REQUEST['prices'];
    else if (is_array($_REQUEST['prices']))
        $prices = $_REQUEST['prices'];
}
else {
    echo 'Price(s) missing';//return handle_error($status, 'Type ID(s) missing');
}


// check size/length
// if (){}



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
<?php
// Require Database files
require_once("../includes/global.php");
require_once("../includes/Marker.php");
require_once("../includes/StoreProduct.php");
require_once("../includes/Product.php");
// Get parameters from URL
//$form['product_name']    = (trim(@$_REQUEST["product_name"]));
//$form['barcode']         = (trim(@$_REQUEST["barcode"]));
//$form['barcode_type']    = (trim(@$_REQUEST["barcode_type"]));
//$form['image']           = (trim(@$_REQUEST["image"]));

// Stores
$stores = array();

if (isset($_REQUEST['store_id'])) {
    if (!is_array($_REQUEST['store_id']) && is_numeric($_REQUEST['store_id']))
        $stores[] = (int)$_REQUEST['store_id'];
    else if (is_array($_REQUEST['store_id']))
        $stores = $_REQUEST['store_id'];
}
else {
    echo 'Error: Store(s) missing';
    return false;//return handle_error($status, 'Type ID(s) missing');
}

// Products
$products = array();

if (isset($_REQUEST['product_id'])) {
    if (!is_array($_REQUEST['product_id']) && is_numeric($_REQUEST['product_id']))
        $products[] = (int)$_REQUEST['product_id'];
    else if (is_array($_REQUEST['product_id']))
        $products = $_REQUEST['product_id'];
}
else {
    echo 'Error: Product(s) missing';
    return false;//return handle_error($status, 'Type ID(s) missing');
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
    echo 'Error: Price(s) missing';
    return false;//return handle_error($status, 'Type ID(s) missing');
}

// check size/length
$number_of = count($prices);
$product_prices = array();

if (count($prices) != count($products)) {
    echo 'Error: Products are missing Prices';
    return false;
}
else {
    for($i=0; $i<$number_of; $i++) {
        $product_prices[$i]['product'] = $products[$i];
        $product_prices[$i]['price']   = $prices[$i];
    }
}

// Stores & Products
//print_var($stores);
$store_list = Marker::getMarkers(array('ids'=>$stores));
$product_list = Product::getProducts(array('ids'=>$products));
// Store ids & Product ids
$store_ids    = array_from_key('id',$store_list);
$product_ids = array_from_key('product_id',$product_list);

//echo "Store IDS[";
//print_var($store_ids);
//print_var($store_list);
//echo "]";
//print_var($stores);
//print_var($product_prices);
// Pulled store ids
foreach ($stores as $store) {
    // Pulled product ids
    foreach($product_prices as $product_price) {
        $store_product = new StoreProduct();
        $store_product->store_id    = $store;
        $store_product->product_id = $product_price['product'];
        $store_product->price      = $product_price['price'];
        //print_var($store_product->toArray(),1);
        if (!$store_product->validate()) {
            echo 'Error: not valid item(s)';
            return false;
        }
        else {
            try{
                $store_product->startTrans();
                $result = $store_product->save();
                //print_var($result);
                /*$existed = $store_product->exists;
                  if (!$existed) { $store_product->product_id = $product->getLastInsertedId();}*/
                $store_product->commitTrans();
            }
            catch (PDOException $e) {
                try {
                    $store_product->rollbackTrans();
                }
                catch (Exception $e2) {
                    echo $e2;
                    return false;die();
                }
            }
        }
    }
}
if (@$_REQUEST['type'] == 'json')
{
    $results = array();
    //$results[] = $product->toArray();
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
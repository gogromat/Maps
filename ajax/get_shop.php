<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Gogromat
 * Date: 1/12/12
 * Time: 9:18 AM
 * To change this template use File | Settings | File Templates.
 */
// Require Database files
require_once("../includes/Product.php");
require_once("../includes/global.php");
// Get parameters from URL
$form['name']            = (trim(@$_REQUEST["name"]));
$form['barcode']         = (trim(@$_REQUEST["barcode"]));
$form['barcode_type']    = (trim(@$_REQUEST["barcode_type"]));
$form['image']           = (trim(@$_REQUEST["image"]));
// Make DB call
$result = Product::getProducts(array('order_by'=>'product_id desc','limit'=>200));

if (@$_REQUEST['type'] == 'json')
{
    $results = array();
    //$results[] = $product->toArray();
    foreach ($result as $r)
    {
        $results[] = $r->toArray();
    }
    echo json_encode($results);
}
else // plain text
{
    //[{"id":"121","name":"Jasper Pizza Place","address":"402 Connaught Dr, Jasper, AB","lat":"52.879086","lng":"-118.079315","distance":"8056.09876638623"},
    echo "[";
    $resultarr = array();

    foreach ($result as $r)
    {
        $resultarr[] = '{"'.$r->product_name.'","'.$r->barcode.'","'.$r->image.'","'.$r->barcode_type.'"}';
    }
    $resultstr = implode(",",$resultarr);
    echo $resultstr;
    echo "]";
}
?>
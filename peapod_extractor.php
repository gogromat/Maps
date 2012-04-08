<?php
require_once('includes/Product.php');
/**
 * Created by JetBrains PhpStorm.
 * User: Gogromat
 * Date: 1/10/12
 * Time: 11:23 AM
 * To change this template use File | Settings | File Templates.
 * Welcome to Product Extractor
 */
set_time_limit(0);
function removeWhitespace($string)
{   //if (!is_string($string))return false;
    $string = preg_quote($string, '/');
    return preg_replace('/  +/', ' ', $string);
}
//$file = file_get_contents('http://products.peapod.com/');
$file = file_get_contents('file.txt');
preg_match_all('/(<([\w0-9]+)[^>]*>)(.*?)(<\/\\2>)/',$file,$results,PREG_SET_ORDER);
$html_values = array();
foreach($results as $r) { $html_values[] =  $r[3]; }
foreach($html_values as $k => $html_value) {
    $file1 = file_get_contents('http://products.peapod.com/'.$html_value.'.html');
    preg_match('/<!-- Begin Content Table -->(.*)<!-- End Content Table -->/s',$file1,$tables);
    $tables[0].='<!-- Begin Grey Line Table -->';
    preg_match('/<!-- End Grey Line Table -->(.*)<!-- Begin Grey Line Table -->/s',$tables[0],$trs);
    preg_match_all('/<TD.*>(.*)<\/TD>/',$trs[0],$tds);
    $vars = array(); $i=0;
    foreach($tds[0] as $k => $td) {
        if (trim(trim($td)) != '' || trim($td) != ' ' ) {
            if ($i == 5) {
                $i=0;
            }
            if ($i != 0)
                $vars[$i][] = removeWhitespace(strip_tags($td));
            else
                $vars[$i][] = $td;
            $i++;
        }
    }
    foreach ($vars[0] as $k => &$image) {
       preg_match('/src="(.*)"/',$image,$result);
       $image = substr($result[1],0,strlen($result[1])-11);
       file_put_contents('images/products4/'.$vars[3][$k].'.jpg',file_get_contents($image));
    }
    for ($i=0; $i<count($vars[0]); $i++) {
        $product = new Product();
        try {
            $product->startTrans();
            $product->product_name = mysql_real_escape_string(trim($vars[1][$i]));
            $product->product_size = mysql_real_escape_string(trim($vars[2][$i]));
            $product->barcode      = mysql_real_escape_string(trim($vars[3][$i]));
            $product->barcode_type = 2;
            $product->save();//print_var($product->toArray(),1);
            $product->commitTrans();
        } catch(PDOException $e){
            //echo ($e->getMessage());
        }
    }
}
?>
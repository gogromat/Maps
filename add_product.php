<?php 
require_once("includes/Marker.php");
require_once("includes/Product.php");
require_once("includes/BarcodeType.php");
require_once("includes/global.php");?>
<!DOCTYPE HTML>
<html>
<head>
    <!--Map set to 100%, not resizable by user-->
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
    <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>

    <style type="text/css">
      html { height: 100% }
      body { height: 100%; margin: 0; padding: 0 }
      /*#map_canvas { height: 100% }*/
     .info { width: 250px; }
      #scrolling_pane {
          height:     435px;
          width:      600px;
          overflow:   scroll;
          border:     dashed 1px maroon;
      }
    </style>

    <!--Sensor means using GPS locator to determine the user's location-->
    <script type="text/javascript" src="../common/scripts/jquery/jquery.js"></script>
    <script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?sensor=true">//sensor=set_to_true_or_false</script>
    <script type="text/javascript" src="http://code.google.com/apis/gears/gears_init.js"></script>

    <script type="text/javascript"
            src="http://www.google.com/jsapi?key=ABQIAAAA-O3c-Om9OcvXMOJXreXHAxQGj0PqsCtxKvarsoS-iqLdqZSKfxS27kJqGZajBjvuzOBLizi931BUow">
    </script>

    <script type="text/javascript">
        $(document).ready(function() {
            change_products();
            $('input[name=submit]').click(function() {
                //console.log('submitted...');
                var searchUrl = 'ajax/add_product.php?type=json&product_name='+$('input[name=name]').val()+
                                '&image='+$('input[name=image]').val()+'&barcode='+$('input[name=barcode]').val()+
                                '&barcode_type='+$('select[name=barcode_type]').val();
                $.getJSON(searchUrl, function(data) {
                }).success( function() { })
                  .error  ( function() { console.log('error here');})
                  .done   ( function() { console.log('done'); change_products(); });
            });
        });
        function change_products() {
            var searchUrl = 'ajax/get_product.php?type=json';
            $.getJSON(searchUrl, function(data) {
                $('#scrolling_pane').html('<table style="width:100%;">');
                $.each(data, function(key,value) {
                    // product name / barcode / image
                    var image = '';
                    var color = 'white';
                    if (key%2 == 0) { color = '#bc8f8f'; }
                    if(value.image == '') { image = value.barcode;  } else { image = value.image; }
                    $('#scrolling_pane').append('<tr>');
                        $('#scrolling_pane').append('<td style="background-color:'+color+';">'+value.product_name);
                        $('#scrolling_pane').append('</td>');
                        $('#scrolling_pane').append('<td style="background-color:'+color+';">'+value.barcode);
                        $('#scrolling_pane').append('</td>');
                        $('#scrolling_pane').append('<td style="background-color:'+color+';"><image src="images/products/'+image+'.jpg"/>');
                        $('#scrolling_pane').append('</td>');
                    $('#scrolling_pane').append('</tr>');
                });
                $('#scrolling_pane').append('</table>');
            }).success( function() { console.log('success');})
              .error  ( function() { console.log('error...');})
              .done   ( function() { console.log('done'); });
        }
    </script>
</head>
<body>
<form action="SERVER::PHP_SELF" style="height:30%">
<table>
    <tr>
        <td>
            <table valign="top" align="top" style="align:top;valign:top;">
                <tr>
                    <th>
                        Product Name
                    </th>
                    <td>
                        <input type="text" name="name" />
                    </td>
                </tr>
                <tr>
                    <th>
                        Product Image
                    </th>
                    <td>
                        <input type="text" name="image" />
                    </td>
                </tr>
                <tr>
                    <th>
                        Barcode
                    </th>
                    <td>
                        <input type="text" name="barcode" />
                    </td>
                </tr>
                <tr>
                    <th>
                        Barcode Type
                    </th>
                    <td>
                        <select name="barcode_type" accesskey="">
                            <?php foreach (BarcodeType::getBarcodeTypes() as $r) { ?>
                                <option value="<?=$r->barcode_type_id;?>"><?=$r->barcode_type_name;?></option>
                            <?php } ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>
                        <input type="button" name="submit" value="Submit"/>
                    </td>
                </tr>
            </table>
        </td>
        <td>
            <table>
                <tr>
                    <td>
                        <div id="scrolling_pane">
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</form>
</body>
</html>

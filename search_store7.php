<?php
require_once("includes/Marker.php");
require_once("includes/Place.php");
require_once("includes/MarkerPlace.php");
require_once("includes/global.php");
?>
<!DOCTYPE HTML>
<html>
<head>
    <meta   name="viewport"           content="initial-scale=1.0, user-scalable=no" />
    <meta   http-equiv="content-type" content="text/html; charset=UTF-8"  />
    <link   rel="stylesheet"          href="../common/scripts/jquery/jquery-ui/jquery-ui.css" type="text/css" />
    <link   rel="stylesheet"          href="main.css" type="text/css" />
    <script type="text/javascript"    src="../common/scripts/jquery/jquery.js"></script>
    <script type="text/javascript"    src="../common/scripts/jquery/jquery-ui/jquery-ui.js"></script>
    <script type="text/javascript"    src="http://maps.googleapis.com/maps/api/js?sensor=false"></script>
    <script type='text/javascript'    src='../common/scripts/knockoutjs/knockout-2.0.0.js'></script>
    <script type="text/javascript">
        var map;
        var marker     = '';
        var markers    = [];

        var infowindow = new google.maps.InfoWindow();
        function initialize() {
            var geocoder = new google.maps.Geocoder();
            var latlng = new google.maps.LatLng(40.58918888747179, -73.9494252204895);
            var myOptions = {
                zoom: 15,
                center: latlng,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            };
            map = new google.maps.Map($("#map_canvas").get(0), myOptions);
        }
        // Clear Markers
        google.maps.Map.prototype.clearOverlays = function() {
            if (markers) {
                for (var i = 0; i < markers.length; i++ ) {
                    markers[i].setMap(null);
                }
            }
        }
/*
        jQuery(document).ready(function() {
            /* SEARCH STORES *
            $('#search_stores').click(function(){ search_stores(); });

            /* SIDEBAR *
            $('#sidebar_control').click(function() { toggle_sidebar(); });
            function toggle_sidebar() { $('#sidebar').toggle(); }

            /* TAB CONTROL *
            var $tabs = $( "#map_control" ).tabs();
            $('#map_control').bind('tabsselect', function(event, ui)
            {
                ui.tab
                ui.panel
                ui.index
                if (ui.index == 3) {
                    $('#stores').html('<table>');
                    $('input[name=store_checkbox]:checked').each(function(key, value) {
                    $('#stores > table').append(
                        '<tr>' +
                           '<td>'+
                                '<label>' +
                                    '<input type="checkbox" name="stores" value="'+value.id+'" checked="checked" />'+
                                    value.value+
                                '</label>'+
                           '</td>' +
                        '</tr>');
                    });
                    $(this).css('width','800px');
                }
                else {
                    $(this).css('width','600px');
                }
            });
        });
*/
        jQuery(document).ready(function() {
            initialize();

            $('#products_autocomplete').keyup(function() {
                get_products(this,'product_list2');
            });

            function get_products(item, list)
            {
                var name = $(item).val();
                if (name.length > 3)
                {
                    $('input[name=products2]:checked').each(function(key,value){});

                    var searchUrl = 'ajax/get_product.php?type=json&limit=50&name='+name;
                    $.getJSON(searchUrl, function(data) {
                        // flush old products
                        //if (!$('#'+same_list).attr('checked')) {
                        $('#'+list).html('<table style="width:100%;">');
                          //products2.length = 0;
                        //}
                        $.each(data, function(key,value) {
                            //if ($('#'+same_list).attr('checked') && !jQuery.isEmptyObject(products2) && (jQuery.inArray(value.product_id,products2) > -1)) {
                            //   return true;
                            //}
                            var image = ''; var color = 'white'; var tr;
                            if (key%2 == 0) { color = '#FFF7EF'; }
                            if (value.image == '') { image = value.barcode;  } else { image = value.image; }
                            tr ='<tr>';
                                tr+='<td style="width:7%;background-color:'+color+';">'+
                                    '<input type="checkbox" name="products2" value="'+value.product_id+'" />';
                                tr+='</td>';
                                tr+='<td style="width:65%;background-color:'+color+';">'+
                                    value.product_name;
                                tr+='</td>';
                                tr+='<td style="background-color:'+color+';">'+
                                    value.barcode;
                                tr+='</td>';
                                tr+='<td style="background-color:'+color+';">' +
                                    '<image src="images/products/'+image+'.jpg"/>';
                                tr+='</td>';
                            tr+='</tr>';
                            $('#'+list+' > table').append(tr);
                            //products2.push(value.product_id);
                        });
                    }).error(function() { console.log('error here: localhost/maps/'+searchUrl);});
                }
            }

            /* TAB CONTROL */
            var $tabs = $( "#map_control" ).tabs();
            $('#map_control').bind('tabsselect', function(event, ui)
            {
                ui.tab
                ui.panel
                ui.index
                if (ui.index == 3) {
                    $('#stores').html('<table>');
                    $('input[name=store_checkbox]:checked').each(function(key, value) {
                    $('#stores > table').append(
                        '<tr>' +
                           '<td>'+
                                '<label>' +
                                    '<input type="checkbox" name="stores" value="'+value.id+'" checked="checked" />'+
                                    value.value+
                                '</label>'+
                           '</td>' +
                        '</tr>');
                    });
                    $(this).css('width','800px');
                }
                else {
                    $(this).css('width','600px');
                }
            });



            function Sidebar() {

                this.slide = function() {
                    console.log('shit');

                }

            }

            function Product() {



            }

            function AppViewModel() {

                this.sidebar = new Sidebar();



                
            }

            // Activates knockout.js
            ko.applyBindings(new AppViewModel());
        });
    </script>
</head>
<body onload="">



<!-- SIDEBAR «» -->
<div id="sidebar" data-bind="with: sidebar">
    <div id="sidebar_control" data-bind="click: slide" >
        Hey
    </div>
</div>

<!-- MAP -->
<div id="map_canvas" ></div>
<div id="map_control">
    <ul>
        <li id="control-0"><a href="#tabs-0">Find Products</a></li>
    </ul>
    <div id="tabs-0">
        <table style="">
            <tr>
                <td style="">
                    <div id="products_div">
                        <input type="text" value="" name="products_autocomplete" class="products_autocomplete2" id="products_autocomplete" />
                    </div>
                </td>
                <td>
                    <input type="button" id="add_product_input" value="..."/>
                </td>
            </tr>
        </table>
        <label style="font-style:italic;"><input type="checkbox" name="same_list2" id="same_list2" value="true"/>Keep list</label>
        <div  id="product_list2" name="product_list2" class="scrolling_pane" style="width:500px;height:400px;background-color:white !important;"></div>
    </div>
</div>





</body>
</html>
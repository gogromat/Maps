<?php require_once("includes/Marker.php");?>
<?php require_once("includes/Place.php");?>
<?php require_once("includes/MarkerPlace.php");?>
<?php require_once("includes/global.php");?>
<!DOCTYPE HTML>
<html>
<head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
    <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
    <style type="text/css">
        html {
            height          : 100%;
        }
        body {
            height          : 100%;
            margin          : 0;
            padding         : 0;
        }
        #map_canvas {
            height          : 100%;
            width           : 100%;
        }
        #map_control {
            position        : absolute;
            z-index         : 0;
            right           : 20px;
            top             : 50px;
            height          : 580px;
            width           : 550px;
        }
        .scrolling_pane {
            height          : 500px;
            width           : 200px;
            overflow        : scroll;
            border          : dashed 1px maroon;
        }
        .ui-autocomplete {
            max-height      : 500px;
            overflow-y      : auto;
            overflow-x      : hidden;
            padding-right   : 20px;
        }
        ul {
            display         : table;
        }
        li, .row {
            display:        : table-row;
        }
        .left, .right, .middle {
            display         : table-cell;
        }
        /*#map_control { margin-left: 200px; margin-top: 50px;} */
    </style>
    <!--JQuery-->
    <script type="text/javascript" src="../common/scripts/jquery/jquery.js"></script>
    <!--JQuery UI-->
    <link rel="stylesheet"         href="../common/scripts/jquery/jquery-ui/jquery-ui.css" type="text/css" />
    <script type="text/javascript" src="../common/scripts/jquery/jquery-ui/jquery-ui.js"></script>
    <!--Sensor means using GPS locator to determine the user's location-->
    <script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?sensor=true">//sensor=set_to_true_or_false</script>
    <script type="text/javascript" src="http://code.google.com/apis/gears/gears_init.js"></script>
    <script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?libraries=places&sensor=false"></script>
    <script type="text/javascript">
        var geocoder;
        var initialLocation;
        var browserSupportFlag = new Boolean();
        var map;
        var markers = [];
        var marker  = '';
        var stores  = [];
        var infowindow = '';
        var products = [];//products.push(0);
        function initialize() {
            geocoder = new google.maps.Geocoder();//New York
            var latlng = new google.maps.LatLng(40.69847032728747, -73.9514422416687);
            var myOptions = {
                zoom: 14,
                center: latlng,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            };
            map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
        }
        // search for stores
        function search()
        {
            var types = new Array();
            $.each($('.types:checked'), function(i,result) {  types[i] = result['value']; });
            var bounds = map.getBounds();
            var store_types = '';
            //console.log(types.length);
            if (types.length != 0){
                store_types = '&types[]='+types.join('&types[]=');
            }//console.log('Store Types:'+store_types);
            var searchUrl = 'js/markersAjax.php?type=json'+'&limit=100'+store_types+
                '&bottom_left_lat='+bounds.getSouthWest().lat()+'&bottom_left_lng='+bounds.getSouthWest().lng()+
                '&top_right_lat='  +bounds.getNorthEast().lat()+'&top_right_lng='  +bounds.getNorthEast().lng();

            $.getJSON(searchUrl, function(data) {
                $('#control-2').show(); $('#tabs-2').show();
                $('#select_stores').html('<table style="width:100%;">');
                $('#select_stores > table').append('<tr style="border-bottom-color: maroon;"><td></td><th>Name</th><th>Address</th></tr>');
                //markers
                $.each(data, function(key,value) {
                    if (value.lat && value.lng) {
                        //v.setIcon('markers/market.png');place = new places();
                        store = new Object();
                        store.id        = value.id;
                        store.name      = value.name;
                        store.address   = value.address;
                        store.lat       = value.lat;
                        store.lng       = value.lng;
                        store.rating    = value.rating;
                        store.reference = value.reference;
                        store.google_id = value.google_id;
                        // append each <tr> element to the scrolling pane table
                        $('#select_stores > table').append(
                            '<tr id="tr_'+store.id+'">' +
                                '<td>' +
                                    '<input type="checkbox" name="store_checkbox" id="checkbox_'+store.id+'" value="'+store.name+'" />' +
                                '</td>' +
                                '<td>' +
                                    store.name +
                                '</td>' +
                                '<td>' +
                                    store.address +
                                '</td>' +
                            '</tr>');
                        var storeLoc = new google.maps.LatLng(store.lat,store.lng);

                        var store_id = store.id;
                        $('#tr_'+store.id).click(store.id,
                            (function(store_id){
                                return function() {
                                    var checkbox = $('#checkbox_'+store_id).attr('checked');
                                    if (checkbox == 'checked') {
                                        $('#checkbox_'+store_id).removeAttr('checked');
                                        check_checkbox();
                                    }
                                    else {
                                        $('#checkbox_'+store_id).attr('checked','checked');
                                        $('#control-3').show();$('#tabs-3').show();
                                    }
                                };
                            })(store_id)
                        ).mouseover(function(){
                            $(this).css('background-color','#FFF7EF');
                        }).mouseout(function(){
                            $(this).css('background-color','white');
                        });

                        marker = new google.maps.Marker({
                            map: map,
                            position: storeLoc
                        });
                        var store_id = store.id;
                        //give id to marker//marker.metadata = {type: "point", id: 1};//marker.setValues({type: "point", id: key});

                        create_infowindow(marker, store);

                        google.maps.event.addListener(marker, 'mouseover',
                            (function(store_id) {
                                return function() {
                                    $('#tr_'+store_id).css({'color':'red'});
                                };
                            })(store_id)
                        );
                        google.maps.event.addListener(marker, 'mouseout',
                            (function(store_id){
                                return function() {
                                    $('#tr_'+store_id).css({'color':'black'});
                                };
                            })(store_id)
                        );
                        stores.push(store);
                        markers.push(marker);
                    }
                });
            }).success( function() { console.log('Success!'); })
              .error  ( function() { console.log('Error...');})
              .done   ( function() { console.log('Done!');    });
        }

        function check_checkbox() {
            if ($('input[name=store_checkbox]:checked').length == 0) {
                $('#control-3').hide();
                $('#tabs-3').hide();
            }
        }

        function create_infowindow(marker, store) {
            infowindow = new google.maps.InfoWindow();
            google.maps.event.addListener(marker, 'click',
                function() {
                    infowindow.setContent('<div>'+ store.name + '<br />'+ store.address+'</div>');
                    infowindow.open(map, this);
                }
            );
            google.maps.event.addListener(marker, 'dblclick',
                function() {
                    $('#checkbox_'+store.id).attr('checked','checked');
                }
            );
        }
        // JQuery function
        jQuery(document).ready(function()
        {
            var $tabs = $( "#map_control" ).tabs();
            //var $selected_tab = $tabs.tabs('option', 'selected');

            $('#map_control').bind('tabsselect', function(event, ui)
            {
                ui.tab   // anchor element of the selected (clicked) tab
                ui.panel // element, that contains the selected/clicked tab contents
                ui.index // zero-based index of the selected (clicked) tab
                if (ui.index == 2) {
                    //get stores
                    $('#stores').html('<table>');
                    $('input[name=store_checkbox]:checked').each(function(key, value) {
                       //console.log(value);
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

            $('#products_autocomplete').keyup(function() {
                get_products(this,$('#same_list'));
            });

            $('#products_search').keyup(function() {
                get_products(this,$('#same_list0'));
            });

            /**   TODO: modify the function in the following way:
              *   1. Add an option to display new searches on top
              *   2. Since users will think that search when 'keep list' is checked
              *      will populate the input "slower", or, right on full input, we need to getJSON based
              *      on array of old products values + new names such that
              *      2.1 getJSON will get the results, order them by new names on top and old values on the bottom
              *      2.2 and of course products will no longer be an array but an object, holding the 'checked' parameter
              *          so that when new getJSON renders, the old product values will be checked
              **/
            function get_products(item, same_list)
            {
                var name = $(item).val();
                if (name.length >= 3)
                {
                    var searchUrl = 'ajax/get_product.php?type=json&limit=50&name='+name;//request.term;
                    $.getJSON(searchUrl, function(data) {
                        // flush old products
                        if (!$(same_list).attr('checked')) {
                            $('#product_list').html('<table style="width:100%;">');
                            products.length = 0;
                        }

                        $.each(data, function(key,value) {
                            //check if product id is already in the list
                            if ($(same_list).attr('checked') && !jQuery.isEmptyObject(products) && (jQuery.inArray(value.product_id,products) > -1)) {
                                return true;
                            }
                            // product_id / product_name / barcode / image
                            var image = '';
                            var color = 'white';
                            var tr;
                            if (key%2 == 0) { color = '#FFF7EF'; }
                            if (value.image == '') { image = value.barcode;  } else { image = value.image; }
                            tr ='<tr>';
                                tr+='<td style="width:7%;background-color:'+color+';">'+
                                        '<input type="checkbox" name="products" value="'+value.product_id+'" />'+
                                        '&nbsp;$<input type="text" name="prices" value="0.00" id="prices_'+value.product_id+'"/>';
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
                            $('#product_list > table').append(tr);
                            products.push(value.product_id);
                        });
                    }).success( function() { console.log('success');})
                      .error  ( function() { console.log('error...');})
                      .done   ( function() { console.log('done'); });
                }
            }

            $('#search_stores').click(function(){
                search();
            });
            // Submit Store Product Info
            $('input[name=add_store_product]').click(function()
            {
                var store_ids      = [];
                var product_ids    = [];
                var product_prices = [];
                // Store ids
                $.each($("input[name=stores]:checked"),function(){
                    store_ids.push($(this).val().replace('checkbox_',''));
                });
                // Product ids
                $.each($("input[name=products]:checked"),function(){
                    product_ids.push($(this).val());
                });
                // Prices
                $.each($("input[name=prices]"),function(){
                    product_prices[$(this).attr('id').replace('prices_','')] = $(this).val();
                });
                // Product Price part of link
                var products = '';
                $.each(product_ids,function(key,value){
                    products +='&product_id[]='+value+'&prices[]='+product_prices[value];
                });
                var searchUrl = 'ajax/add_store_product.php?type=json&store_id[]='+store_ids.join('&store_id[]=')+products;
                //console.log(searchUrl);
                $.getJSON(searchUrl, function(data) {
                }).success( function() { console.log('success, saved');})
                  .error  ( function() { console.log('error here: localhost/maps/'+searchUrl);})
                  .done   ( function() { console.log('done'); change_products(); });
            });
        });
    </script>
</head>
<body onload="initialize();">


<div id="map_canvas" ></div>

<div id="map_control">
    <ul>
        <li id="control-0"><a href="#tabs-0">Find Products</a></li>
        <li id="control-1"><a href="#tabs-1">Find Store</a></li>
        <li id="control-2" style="display:none;"><a href="#tabs-2">Stores</a></li>
        <li id="control-3" style="display:none;"><a href="#tabs-3">Store Products</a></li>
    </ul>
    <div id="tabs-0">
        <p>Welcome! You can start your search here</p>
        <br />
        <!--
        <input style="bottom:140px;left:400px;"
               type="button" value="Find Products near here" id="search_products" />-->
        <input type="text" style="bottom:140px;left:400px;" value="Condo" name="products_search" id="products_search"/>
        <label style="font-style:italic;"><input type="checkbox" name="same_list" id="same_list0" value="true"/>Keep list</label>

        <div  id="select_products" class="scrolling_pane" style="width:500px;height:400px;background-color:white !important;"></div>
    </div>
    <div id="tabs-1">
        <p>Welcome! You can start your search here</p>
        <br />
        <input style="bottom:140px;left:400px;"
               type="button" value="Find Stores near here" id="search_stores" />

        <div  class="scrolling_pane" style="width:300px;height:100px;background-color:white !important;">
            <?php
                $in_places = MarkerPlace::getMarkerPlaces(array('in_places'=>true));
                $in_place_ids = array_from_key('place_id',$in_places);
                $odd_types =  array(7,12,15,25,32,34,37,40,53,80,84);
                foreach(Place::getPlaces() as $r) {
                    if (in_array($r->place_id,$in_place_ids) && !in_array($r->place_id,$odd_types)) {
                        ?><label style="display:inline-block;">
                            <input type="checkbox" class="types" name="types" value="<?=$r->place_id;?>" />
                            <?=$r->place_name;?>
                        </label><?php
                    }
                }
            ?>
        </div>
        <br />
        or navigate to...<br /><br />

        <input style="bottom:100px;left:400px;width:250px;"
               type="text" value="Brighton Beach" name="address" id="address" />

        <input style="bottom:140px;left:400px;"
               type="button" value="Navigate!" onclick="geoCodeAddress();" /><br /><br />
        or locate store...
        <input style="bottom:100px;left:400px;width:250px;"
               type="text" value="Brighton Beach" name="store" id="store" />

        <input style="bottom:140px;left:400px;"
               type="button" value="Locate Store!" /><br />
        <!--
        <input style="bottom:140px;left:480px;width:250px;"
               type="text" value="Milk" onclick="" />
        <input style="bottom:140px;left:600px;"
               type="button" value="Go Milk!" onclick="" />-->
    </div>
    <div id="tabs-2" style="display:none;">
        <table>
            <tr>
                <td>
                    <div id=""></div>
                </td>
                <td><!--Search Stores-->
                    <div id="select_stores" class="scrolling_pane" style="width:450px;background-color:white !important;"></div>
                </td>
            </tr>
        </table>
    </div>
    <div id="tabs-3" style="display:none;">
        <table>
            <tr>
                <td>
                </td>
                <td>
                    <input type="text" name="products_autocomplete" id="products_autocomplete"/>
                    <label style="font-style:italic;"><input type="checkbox" name="same_list" id="same_list" value="true"/>Keep list</label>
                    Clear un-checked
                    <span style="font-style:italic;"><input style="" type="button" name="add_store_product" value="Add to Store(s)" /></span>
                </td>
            </tr>
            <tr>
                <td><!--Stores-->
                    <div id="stores"    class="scrolling_pane" style="heght:250px !important;background-color:white !important;"></div>
                </td>
                <td><!--Products-->
                    <div id="product_list" name="product_list" class="scrolling_pane" style="width:500px;background-color:white !important;">
                        <span style="font-style:italic;">Type in the product name on top...</span>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>
<input type="hidden" value="" name="x_coord" id="x_coord"/>
<input type="hidden" value="" name="y_coord" id="y_coord"/>
</body>
</html>
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
    <script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?sensor=false"></script>
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

        /**
         * Search Stores
         *
         *
         **/
        function search_stores()
        {
            map.clearOverlays();

            var types = new Array();
            $.each($('.types:checked'), function(i,result) {
                types[i] = result['value'];
            });
            var bounds = map.getBounds();
            var store_types = '';
            if (types.length != 0) {
                store_types = '&types[]='+types.join('&types[]=');
            }
            var searchUrl = 'js/markersAjax.php?type=json'+'&limit=100'+store_types+
                '&bottom_left_lat='+bounds.getSouthWest().lat()+'&bottom_left_lng='+bounds.getSouthWest().lng()+
                '&top_right_lat='  +bounds.getNorthEast().lat()+'&top_right_lng='  +bounds.getNorthEast().lng();

            $.getJSON(searchUrl, function(data)
            {
                $('#control-2').show(); $('#tabs-2').show();
                $('#select_stores').html('<table style="width:100%;">');
                $('#select_stores > table').append('<tr style="border-bottom-color: maroon;"><td></td><th>Name</th><th>Address</th></tr>');

                $.each(data, function(key,value)
                {
                    if (value.lat && value.lng)
                    {
                        store           = new Object();
                        store.id        = value.id;
                        store.name      = value.name;
                        store.address   = value.address;
                        store.lat       = value.lat;
                        store.lng       = value.lng;
                        store.rating    = value.rating;
                        store.reference = value.reference;
                        store.google_id = value.google_id;

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

                        store_infowindow(marker, store);

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
            }).error  ( function() { console.log('error here: localhost/maps/'+searchUrl);});
        }

        jQuery(document).ready(function() {
            /* SEARCH STORES */
            $('#search_stores').click(function(){ search_stores(); });

            /* SIDEBAR */
            $('#sidebar_control').click(function() { toggle_sidebar(); });
            function toggle_sidebar() { $('#sidebar').toggle(); }

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
        });
    </script>
</head>
<body onload="initialize();">

<!-- SIDEBAR «» -->
<div id="sidebar" class="hidden"></div>
<div id="sidebar_control" ><p>»</p></div>

<!-- MAP -->
<div id="map_canvas" ></div>

<!-- MAP CONTROL -->
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
        <table style="">
            <tr>
                <td style="">
                    <div id="products_div">
                        <input type="text" value="" name="products_autocomplete2" class="products_autocomplete2" id="products_autocomplete2" />
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
    <div id="tabs-1">
        <p>Welcome! You can start your search here</p>
        <br />
        <input style="bottom:140px;left:400px;"
               type="button" value="Find Stores near here" id="search_stores" />

        <div  class="scrolling_pane" style="width:400px;height:90px;background-color:white !important;">
            <?php
                $in_places = MarkerPlace::getMarkerPlaces(array('in_places'=>true));
                $in_place_ids = array_from_key('place_id',$in_places);
                $odd_types =  array(7,12,15,25,32,34,37,40,53,80,84);
                foreach(Place::getPlaces() as $r) {
                    if (in_array($r->place_id,$in_place_ids) && !in_array($r->place_id,$odd_types)) {
                        ?><label style="display:inline-block;">
                            <input type="checkbox" class="types" name="types" value="<?=$r->place_id;?>" />
                            <?=ucfirst(str_replace('_',' ',$r->place_name));?>
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
    </div>
    <div id="tabs-2" style="display:none;">
        <table>
            <tr>
                <td>
                    <div></div>
                </td>
                <td>
                    <!--Search Stores-->
                    <div id="select_stores" class="scrolling_pane" style="width:450px;background-color:white !important;"></div>
                </td>
            </tr>
        </table>
    </div>
    <div id="tabs-3" style="display:none;">
        <table>
            <tr>
                <td></td>
                <td>
                    <input type="text" name="products_autocomplete" id="products_autocomplete"/>
                    <label style="font-style:italic;"><input type="checkbox" name="same_list" id="same_list" value="true"/>Keep list</label>
                    Clear un-checked
                    <span style="font-style:italic;"><input style="" type="button" name="add_store_product" value="Add to Store(s)" /></span>
                </td>
            </tr>
            <tr>
                <td>
                    <!--Stores-->
                    <div id="stores"    class="scrolling_pane" style="heght:250px !important;background-color:white !important;"></div>
                </td>
                <td>
                    <!--Products-->
                    <div id="product_list" name="product_list" class="scrolling_pane" style="width:500px;background-color:white !important;">
                        <span style="font-style:italic;">Type in the product name on top...</span>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>
</body>
</html>
<?php require_once("includes/Marker.php");?>
<?php require_once("includes/Place.php");?>
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
        /*
        function detectBrowser() {
            var useragent = navigator.userAgent;
            var mapdiv = $("#map_canvas");

            if (useragent.indexOf('iPhone') != -1 || useragent.indexOf('Android') != -1 )
            {
                mapdiv.style.width = '100%';
                mapdiv.style.height = '100%';
            }
            else {
                mapdiv.style.width = '600px';
                mapdiv.style.height = '800px';
            }
        }*/
        var shops  = [];
        var infowindow = '';
        /*
        var shops = {
            name      : '',
            address   : '',
            lat       : '',
            lng       : '',
            rating    : '',
            reference : '',
            google_id : ''
        };*/
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
        /*
        // Try W3C Geolocation (Preferred)
        if(navigator.geolocation) {
            browserSupportFlag = true;
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    initialLocation =
                        new google.maps.LatLng(position.coords.latitude,
                            position.coords.longitude);
                    map.setCenter(initialLocation);
                }, function() {
                    handleNoGeolocation(browserSupportFlag);
                });
        }
        // Try Google Gears Geolocation
        else if (google.gears) {
            browserSupportFlag = true;
            var geo = google.gears.factory.create('beta.geolocation');
            geo.getCurrentPosition(
                function(position) {
                    initialLocation =
                        new google.maps.LatLng(position.latitude,
                            position.longitude);
                    map.setCenter(initialLocation);
                }, function() {
                    handleNoGeoLocation(browserSupportFlag);
                });
        }
        // Browser doesn't support Geolocation
        else {
            browserSupportFlag = false;
            handleNoGeolocation(browserSupportFlag);
        }*/

        // search for shops
        function search()
        {
            var types = new Array();
            $.each($('.types:checked'), function(i,result) {  types[i] = result['value']; });
            /*var location_new = myMarker.getPosition();
            var request = {
                location: location_new,
                radius:   $('#radiusSelect').val(),
                types:    types
            };*/
            //service = new google.maps.places.PlacesService(map);
            //service.search(request, callback);
            var bounds = map.getBounds();
            var searchUrl = 'js/markersAjax.php?type=json'+'&limit=100'+
                '&bottom_left_lat='+bounds.getSouthWest().lat()+'&bottom_left_lng='+bounds.getSouthWest().lng()+
                '&top_right_lat='  +bounds.getNorthEast().lat()+'&top_right_lng='  +bounds.getNorthEast().lng();

            $.getJSON(searchUrl, function(data) {
                $('#control-2').show(); $('#tabs-2').show();
                $('#select_shops').html('<table style="width:100%;">');
                $('#select_shops > table').append('<tr style="border-bottom-color: maroon;"><td></td><th>Name</th><th>Address</th></tr>');
                //markers
                $.each(data, function(key,value) {
                    if (value.lat && value.lng) {
                        //v.setIcon('markers/market.png');place = new places();
                        shop = new Object();
                        shop.id        = value.id;
                        shop.name      = value.name;
                        shop.address   = value.address;
                        shop.lat       = value.lat;
                        shop.lng       = value.lng;
                        shop.rating    = value.rating;
                        shop.reference = value.reference;
                        shop.google_id = value.google_id;
                        // append each <tr> element to the scrolling pane table
                        $('#select_shops > table').append(
                            '<tr id="tr_'+shop.id+'">'+
                                '<td><input type="checkbox" name="shop_checkbox" id="checkbox_'+shop.id+'" value="'+shop.name+'"/></td>'+
                                '<td>'+shop.name+'</td>'+
                                '<td>'+shop.address+'</td>'+
                            '</tr>');
                        var shopLoc = new google.maps.LatLng(shop.lat,shop.lng);

                        /* TODO move this to function */
                        $('#checkbox_'+shop.id).click(function(){
                            if($(this).attr('checked') == 'checked') {
                                $('#control-3').show(); $('#tabs-3').show();
                            }
                        });

                        marker = new google.maps.Marker({
                            map: map,
                            position: shopLoc
                        });
                        var shop_id = shop.id;
                        //give id to marker//marker.metadata = {type: "point", id: 1};//marker.setValues({type: "point", id: key});

                        create_infowindow(marker, shop);

                        google.maps.event.addListener(marker, 'mouseover',
                            (function(shop_id) {
                                return function() {
                                    $('#tr_'+shop_id).css({'color':'red'});
                                };
                            })(shop_id)
                        );
                        google.maps.event.addListener(marker, 'mouseout',
                            (function(shop_id){
                                return function() {
                                    $('#tr_'+shop_id).css({'color':'black'});
                                };
                            })(shop_id)
                        );
                        /*
                        GEvent.addListener(newMarkers[count], 'mouseover',
                            (function(dinnerNumber){
                                return function(){
                                    document.getElementById(dinnerNumber).style.borderColor = '#000000';
                                };
                            })(dinnerNumber)
                        );*/
                         shops.push(shop);
                        markers.push(marker);
                        //create_search_shops(shop);
                    }
                });
            }).success( function() { console.log('Success!'); })
              .error  ( function() { console.log('Error...');})
              .done   ( function() { console.log('Done!');    });
        }

        function create_infowindow(marker, shop) {
            infowindow = new google.maps.InfoWindow();
            google.maps.event.addListener(marker, 'click',
                function() {
                    infowindow.setContent('<div>'+ shop.name + '<br />'+ shop.address+'</div>');
                    infowindow.open(map, this);
                }
            );
            google.maps.event.addListener(marker, 'dblclick',
                function() {
                    $('#checkbox_'+shop.id).attr('checked','checked');
                }
            );
        }
        // JQuery function
        jQuery(document).ready(function()
        {
            var $tabs = $( "#map_control" ).tabs();
            //var $selected_tab = $tabs.tabs('option', 'selected');
            $('#map_control').bind('tabsselect', function(event, ui) {
                ui.tab     // anchor element of the selected (clicked) tab
                ui.panel   // element, that contains the selected/clicked tab contents
                ui.index   // zero-based index of the selected (clicked) tab
                //console.log(ui.index);
                if (ui.index == 2) {
                    //get shops
                    $('#shops').html('<table>');
                    $('input[name=shop_checkbox]:checked').each(function(key, value) {
                       //console.log(value);
                       $('#shops > table').append('<tr><td>'+value.value+'</td></tr>');
                    });
                }
            });

            $('#control-3').click(function(){console.log('Max');});

            $('#products_autocomplete').autocomplete({
                // minimum length of search
                minLength : 3,
                // source + response
                source: function(request, response) {

                    var searchUrl = 'ajax/get_product.php?type=json&limit=50&name='+request.term;

                    $.getJSON(searchUrl, request, function(data) {
                      /* var products_array = [];  $.each(data, function(i, value) { products_array.push(value.product_name); }); response(products_array); */
                        // return a response
                        // with an object item that is mapped using JQuery map() function
                        response($.map(data, function(item) {
                            return {
                                value:  item.product_id,
                                desc:   item.product_name,
                                image:  item.barcode
                            }
                        }));
                    }).success( function() { console.log('success');})
                      .error  ( function() { console.log('error...');})
                      .done   ( function() { console.log('done'); });
                }//,
                /* delay: 200, focus: function() { // prevent value inserted on focus   return false;}, */
                /* change: function() {  $("#products_autocomplete").val("").css("top",2); }, */
                //select: function(event, ui) {
                    //create formatted friend
                    /* var friend = ui.item.value,  span = $("<span>").text(friend), a = $("<a>").addClass("remove").attr({
                            href: "javascript:", title: "Remove " + friend }).text("x").appendTo(span);
                    //add friend to friend div             span.insertBefore("#test_shit");  */
                    //$(this).val(ui.item.value);
                //}
            })  // refactor the JQuery renderItem to allow the displaying of HTML
                .data("autocomplete")._renderItem = function(ul, item) {

                    a = $("<a>").text(item.desc).attr({
                        href    : "javascript;"//,
                        //onclick : (function() { insert_product(item); })(item)
                    });
                    $(a).click(function(){
                       insert_product(item);
                    });

                    //console.log($(a));
                    //console.log($(a).html());
                    //console.log($(a).text());
                    //console.log($(a).append());

                    return $("<table style='width:350px;'></table>")
                           .data("item.autocomplete", item)
                           .append("<tr>" +
                                        "<td width='70%'><span style='font-weight: bold;'>" + $(a) + "</span></td>" +
                                        "<td><img style='max-height:100px;max-width:100px;float:right;' src='images/products/" + item.image + ".jpg'/></td>" +
                                   "</tr>")
                           .appendTo(ul);
                };


            function insert_product(item){
                console.log('got clicked!');
                $("#products > table").append(
                    "<tr>" +
                        "<td width='70%'><span style='font-weight: bold;'>" + item.desc + "</span></td>" +
                        "<td><img style='max-height:100px;max-width:100px;float:right;' src='images/products/" + item.image + ".jpg'/></td>" +
                    "</tr>");
                //remove inserted...
            }

        });
    </script>
</head>
<body onload="initialize();">


<div id="map_canvas" ></div>

<div id="map_control">
    <ul>
        <li id="control-1"><a href="#tabs-1">Find Shop</a></li>
        <li id="control-2" style="display:none;"><a href="#tabs-2">Shops</a></li>
        <li id="control-3" style="display:none;"><a href="#tabs-3">Shop Products</a></li>
    </ul>
    <div id="tabs-1">
        <p>Welcome! You can start your search here</p>
        <hr /><br />
        <input style="bottom:140px;left:400px;"
               type="button" value="Find Shops near here" onclick="search();" /> or navigate to...<br /><br />

        <input style="bottom:100px;left:400px;width:250px;"
               type="text" value="Brighton Beach" name="address" id="address" />

        <input style="bottom:140px;left:400px;"
               type="button" value="Navigate!" onclick="geoCodeAddress();" /><br /><br />
        or locate shop...
        <input style="bottom:100px;left:400px;width:250px;"
               type="text" value="Brighton Beach" name="shop" id="shop" />

        <input style="bottom:140px;left:400px;"
               type="button" value="Locate Shop!" onclick="" /><br />


        <input type="text" name="products" id="products_autocomplete"/>
        <div id="products" class="scrolling_pane" style="height:300px;"><table></table></div>

        <!--
        Test Product Autocomplete
        <input style="bottom:100px;left:400px;width:250px;"
               type="text" value="" name="product"  />
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
                <td><!--Search Shops-->
                    <div id="select_shops" class="scrolling_pane" style="width:450px;"></div>
                </td>
            </tr>
        </table>
    </div>
    <div id="tabs-3" style="display:none;">
        <table>
            <tr>
                <td><!--Shops--><br />
                    <div id="shops"    class="scrolling_pane" style="heght:300px !important;"></div>
                </td>
                <td><!--Products-->

                </td>
            </tr>
        </table>
    </div>
</div>
<input type="hidden" value="" name="x_coord" id="x_coord"/>
<input type="hidden" value="" name="y_coord" id="y_coord"/>
</body>
</html>

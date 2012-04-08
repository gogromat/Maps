<!--header('Content-Type: text/javascript; charset=UTF-8');-->
<!--header("content-type:application/x-javascript");-->
<?php require_once("includes/Marker.php");?>
<?php require_once("includes/Place.php");?>
<?php require_once("includes/MarkerPlace.php");?>
<?php require_once("includes/global.php");?>
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
    <!--<script type="text/javascript" src="../common/scripts/googleMaps/maps.js"></script>-->

    <!--<script type="text/javascript" src="http://code.google.com/apis/gears/gears_init.js"></script>-->

    <!--<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?libraries=places&sensor=false"></script>-->
    <!--<script type="text/javascript" src="../common/scrips/googleMaps/places.js"></script>-->
    <script type="text/javascript">
        var map;
        var marker     = '';
        var markers    = [];


        var other_array = [];

        var markers2   = [];
        var stores2    = [];

        function getBounds() {
            var bounds = map.getBounds();
            return '&bottom_left_lat='+bounds.getSouthWest().lat().toFixed(6)+'&bottom_left_lng='+bounds.getSouthWest().lng().toFixed(6)+
                   '&top_right_lat='  +bounds.getNorthEast().lat().toFixed(6)+'&top_right_lng='  +bounds.getNorthEast().lng().toFixed(6);
        }
        /* STORES CLASS */
        //function storeClass () {
        //var storeClass = function() {
        function storeClass() {
            // variables
            this.id            = '';
            this.name          = '';
            this.address       = '';
            this.lat           = '';
            this.lng           = '';
            this.reference     = '';
            this.rating        = '';
            this.google_id     = '';

            this.storeProducts = {};

            // methods
            this.getLat = function() {
                return this.lat.toFixed(6);
            };
            this.getLng = function() {
                return this.lng.toFixed(6);
            };
            this.getCoordinates = function() {
                return this.getLat() + ',' + this.getLng();
            };
            this.getLatLng = function() {
                return new google.maps.LatLng(this.getLat(), this.getLng());
            }
            this.setMarker = function() {
                marker = new google.maps.Marker({
                    map: map,
                    position: this.getLatLng()
                });
                markers.push(marker);
                return marker;
            }
            this.getProductsPrices = function() {
                var products_prices = {};
                $.each(this.storeProducts, function (product_id,product) {
                    products_prices[product_id] = product.price;
                });
                return products_prices;
            }
        };
        storeClass.prototype.myConstructor = function (object) {
            for (property in object) {
                if (object.hasOwnProperty(property)) {
                    this[property] = object[property];
                }
            }
        }
        /* STORE PRODUCTS CLASS*/
        function storeProductClass() {
            this.store_id     = '';
            this.product_id   = '';
            this.price        = '';
            this.quantity     = '';
            this.image        = '';
            //this.product = '';
        };
        storeProductClass.prototype.myConstructor = function (object) {
            for (property in object) {
                if (object.hasOwnProperty(property)) {
                    this[property] = object[property];
                }
            }
        }
        /* PRODUCTS CLASS */
        function productClass() {
            this.product_id   = '';
            this.product_name = '';
            this.product_size = '';
            this.image        = '';
            this.barcode      = '';
            this.barcode_type = 2;
        }
        productClass.prototype.myConstructor = function (object) {
            for (property in object) {
                if (object.hasOwnProperty(property)) {
                    this[property] = object[property];
                }
            }
        }

        // Store_ID -> Product:Price
        function getStoreProductPrices() {
            var store_product_prices = {};
            $.each(stores2, function (index, store) {
                store_product_prices[store.id] = store.getProductsPrices();
            });
            return store_product_prices;
        }

        // Product_ID -> Store:Price
        function getProductsStorePrices() {
            var products_store_prices = {};
            var product_ids = [];
            // product keys
            $.each(products3, function (key,product) {
                product_ids.push(product.product_id);
            });
            $.each(stores2, function (key, store) {
                $.each(store.storeProducts, function (key,storeProduct) {
                    if (jQuery.inArray(storeProduct.price,product_ids)) {
                        products_store_prices[storeProduct.product_id] = [];
                        var storeID = storeProduct.store_id.toString();
                        var obj = {};obj[storeProduct.store_id] = storeProduct.price;
                        products_store_prices[storeProduct.product_id].push(obj);
                    }
                });
            });
            return products_store_prices;
        }

        function cheapestStoreProductsPrices(product_id) {
            var products =  [];
            var is_in = false;
            if (typeof product_id === 'undefined') {
                $.each(stores2, function (key, store) {
                   $.each(store.storeProducts, function (key, storeProduct) {
                      $.each(products, function(key, product) {
                         if (product.product_id === storeProduct.product_id) {
                             is_in = true;
                             if (storeProduct.price < product.price) {
                                 product.price    = storeProduct.price;
                                 product.store_id = storeProduct.store_id;
                             }
                         }
                      });
                      if (is_in === false) {
                          products.push({ product_id : storeProduct.product_id, store_id : storeProduct.store_id,
                                          price: storeProduct.price, lat: store.getLat(), lng: store.getLng()   });
                      }
                      is_in = false;
                   });
                });
            }
            else {
            }
            return products;
        }

        function cheapestStoreProductPricesMarkers() {
            $.each(markers, function(key,marker) {
                $.each(temps, function(key,temp) {
                    var store_position = new google.maps.LatLng(temp.lat, temp.lng);
                    if ((marker.getPosition().lat().toFixed(6) == temp.lat) &&  (marker.getPosition().lng().toFixed(6) == temp.lng)) {
                        marker.setIcon('markers/landmark.png');
                    }
                });
            });
        }


        var stores     = [];
        var products   = [];
        var products2  = [];
        var products3  = [];
        var infowindow = new google.maps.InfoWindow();

        /* INITIALIAZE */
        function initialize() {
            var geocoder = new google.maps.Geocoder();
            var latlng = new google.maps.LatLng(40.58918888747179, -73.9494252204895);
            var myOptions = {
                zoom: 14,
                center: latlng,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            };
            map = new google.maps.Map($("#map_canvas").get(0), myOptions);
        }

        /* MAP : CLEAR OVERLAYS */
        google.maps.Map.prototype.clearOverlays = function() {
            if (markers) {
                for (var i = 0; i < markers.length; i++ ) {
                    markers[i].setMap(null);
                }
            }
        }

        /* SEARCH STORES */
        function search_stores() {
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
            })//.success( function() { console.log('Success!'); })
              .error  ( function() { console.log('error here: localhost/maps/'+searchUrl);})
              //.done   ( function() { console.log('Done!');    })
              ;
        }

        /* */
        function check_checkbox() {
            if ($('input[name=store_checkbox]:checked').length == 0) {
                $('#control-3').hide();
                $('#tabs-3').hide();
            }
        }

        /* CREATE INFOWINDOW */
        function store_infowindow(marker, store) {
            google.maps.event.addListener(marker, 'click',
                function() {
                    var product_info = '';
                    if (typeof store.product_id != "undefined" ) {
                        product_info =
                            '<tr>' +
                                '<td>'+store.product_name+'</td>' +
                                '<td>$'+store.price+'</td>' +
                                '<td><image src="images/products/'+store.barcode+'.jpg" /></td>' +
                            '</tr>';
                    }
                    infowindow.setContent(
                        '<div>' +
                            '<table>' +
                                '<tr>' +
                                    '<td>'+ store.name + '</td>' +
                                '</tr>' +
                                '<tr>' +
                                    '<td>'+ store.address+'</td>' +
                                '</tr>' +
                                product_info +
                            '</table>' +
                        '</div>');
                    infowindow.open(map, this);
                }
            );
            google.maps.event.addListener(marker, 'dblclick',
                function() {
                    $('#checkbox_'+store.id).attr('checked','checked');
                }
            );
        }

        /* ONLOAD */
        jQuery(document).ready(function()
        {
            /* SIDEBAR */
            $('#sidebar_control').click(function() { toggle_sidebar(); });
            function toggle_sidebar() { $('#sidebar').toggle(); }

            /* TAB CONTROL */
            var $tabs = $( "#map_control" ).tabs();
            $('#map_control').bind('tabsselect', function(event, ui) {
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

            /* GET PRODUCTS */
            $('#products_autocomplete').keyup(function() {
                get_products(this,'product_list','same_list');
            });

            /* GET PRODUCTS 2 */
            $('#products_autocomplete2').keyup(function() {
                get_products2(this,'product_list2','same_list2');
            });

            /* GET PRODUCTS */
            function get_products(item, list, same_list) {
                var name = $(item).val();
                if (name.length >= 3)
                {
                    var searchUrl = 'ajax/get_product.php?type=json&limit=50&name='+name;//request.term;
                    $.getJSON(searchUrl, function(data) {
                        // flush old products
                        if (!$('#'+same_list).attr('checked')) {
                            $('#'+list).html('<table style="width:100%;">');
                            products.length = 0;
                        }
                        $.each(data, function(key,value) {                                              //check if product id is already in the list
                            if ($('#'+same_list).attr('checked') && !jQuery.isEmptyObject(products) && (jQuery.inArray(value.product_id,products) > -1)) {
                                return true;
                            }
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
                            $('#'+list+' > table').append(tr);
                            products.push(value.product_id);
                        });
                    })//.success( function() { console.log('success');})
                      .error  ( function() { console.log('error here: localhost/maps/'+searchUrl);})
                      //.done   ( function() { console.log('done'); })
                      ;
                }
            }

            /* GET PRODUCTS 2 */
            function get_products2(item, list, same_list) {
                var name = $(item).val();
                if (name.length >= 3)
                {
                    $('input[name=products2]:checked').each(function(key,value){});

                    var searchUrl = 'ajax/get_product.php?type=json&limit=50&name='+name;
                    $.getJSON(searchUrl, function(data) {
                        // flush old products
                        if (!$('#'+same_list).attr('checked')) {
                            $('#'+list).html('<table style="width:100%;">');
                            products2.length = 0;
                        }
                        $.each(data, function(key,value) {
                            if ($('#'+same_list).attr('checked') && !jQuery.isEmptyObject(products2) && (jQuery.inArray(value.product_id,products2) > -1)) {
                                return true;
                            }
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
                            products2.push(value.product_id);
                        });
                    }).error  ( function() { console.log('error here: localhost/maps/'+searchUrl);});
                }
            }

            /* SEARCH STORES */
            $('#search_stores').click(function() {
                search_stores();
            });

            /* ADD STORE PRODUCT */
            $('input[name=add_store_product]').click(function() {
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
                $.getJSON(searchUrl, function(data) {
                })//.success( function() { console.log('success, saved');})
                  .error  ( function() { console.log('error here: localhost/maps/'+searchUrl);})
                  //.done   ( function() { console.log('done'); change_products(); })
                  ;
            });


            /* PRODUCT STORES */
            $('input[name=products2]').live("click",function() {

                var product_id = $($('input[name=products2]:checked')[0]).val();

                map.clearOverlays();

                var searchUrl = 'js/markersAjax2.php?type=json'+'&limit=100'+'&product_id='+product_id+
                    '&with_store_product=true'+'&with_marker_place=true'+'&with_product=true'+getBounds();

                $.getJSON(searchUrl, function(data) {
                    $.each(data, function(key,value) {
                        // STORE
                        if (typeof value.id !== 'undefined' && typeof value.product_id === 'undefined') {
                            mystore = new storeClass();
                            mystore.myConstructor(value);
                            marker = mystore.setMarker();
                            stores2.push(mystore);
                        }
                        // STORE PRODUCT
                        else if (typeof value.product_id  !== 'undefined' && typeof value.store_id !== 'undefined') {
                            mystore.storeProducts[value.product_id] = new storeProductClass();
                            mystore.storeProducts[value.product_id].myConstructor(value);
                        }
                        // PRODUCT
                        else {
                            product = new productClass();
                            product.myConstructor(value);
                            var same = false;
                            $.each(products3, function(key,value) {
                                if (value.product_id == product.product_id) {
                                    same = true; return;
                                }
                            });
                            if (same === false) {
                                products3.push(product);
                            }
                        }
                        /*
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
                        */
                    });
                })  .error  ( function() { console.log('error here: localhost/maps/'+searchUrl);})
                    .done   ( function() { cheapestStoreProductPricesMarkers();                 });
            });

            $('#add_product_input').click(function() {
                $('#products_div').append('<input type="text" value="" name="products_autocomplete2" class="products_autocomplete2" />');
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
            <?php //26
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
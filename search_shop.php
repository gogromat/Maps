<?php require_once("includes/Marker.php");?>
<?php require_once("includes/Place.php");?>
<?php require_once("includes/global.php");?>
<!DOCTYPE HTML>
<html>
<head>
    <!--Map set to 100%, not resizable by user-->
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
    <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>

    <style type="text/css">
        html {
            height:     100%;
        }
        body {
            height:     100%;
            margin:     0;
            padding:    0;
        }
        /*#map_canvas { height: 100% }*/
        .info {
            width:      250px;
        }
        #map_canvas {
            height:     450px;
            width:      700px;
            border:     1px solid #333;
            margin-top: 0.6em;
        }
        #scrolling_pane {
            height:     435px;
            width:      200px;
            overflow:   scroll;
            border:     dashed 1px maroon;
        }
    </style>

    <!--Sensor means using GPS locator to determine the user's location-->
    <script type="text/javascript" src="../common/scripts/jquery/jquery.js"></script>
    <script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?sensor=true">//sensor=set_to_true_or_false</script>
    <script type="text/javascript" src="http://code.google.com/apis/gears/gears_init.js"></script>
    <!--
    <script type="text/javascript" src="http://www.google.com/jsapi?key=ABQIAAAA-O3c-Om9OcvXMOJXreXHAxQGj0PqsCtxKvarsoS-iqLdqZSKfxS27kJqGZajBjvuzOBLizi931BUow"></script>
    -->
    <script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?libraries=places&sensor=false"></script>

    <script type="text/javascript">
        var geocoder;

        var initialLocation;

        var browserSupportFlag =  new Boolean();

        var map;

        var myMarker;

        var infowindow;

        var service;

        var request;

        var latLng;

        var address = "";

        var phone = "";

        var international_phone = '';

        var places_number;

        var all_places = {
            <?php
                $results = array();
                foreach(Place::getPlaces() as $r) {
                    $results[] = $r->place_id.':"'.$r->place_name.'"';
                }
                $result = implode(",",$results);
                echo $result;
            ?>
        };

        //.var places_array = new Array();

        var places_array_object = {
            name        : '',
            address     : '',
            phone       : '',
            reference   : '',
            google_id   : '',
            lat         : '',
            lng         : '',
            rating      : '',
            url         : ''
        };

        var places_types = {
            type_id     : '',
            place_id    : ''
        };


        function initialize()
        {
            geocoder = new google.maps.Geocoder();

            latLng  = new google.maps.LatLng(40.6984703, -73.9514422);

            var myOptions = {
              zoom: 14,
              center: latLng,
              mapTypeId: google.maps.MapTypeId.ROADMAP
            };
            
            map = new google.maps.Map($("#map_canvas").get(0), myOptions);

            myMarker = new google.maps.Marker({
                //position: location,
                position: latLng,
                map: map,
                draggable: true,
                icon: 'markers/youAreHere.png',
                title:'you are here!'
            });

            //map.addOverlay(marker);
            function changeAddress(event){
                var lat_lng  = new google.maps.LatLng(event.latLng.lat(), event.latLng.lng());
                var geocoders = new google.maps.Geocoder();
                geocoders.geocode({'latLng': lat_lng}, function(results, status) {
                    if (status == google.maps.GeocoderStatus.OK) {
                        //console.log(results[0]);
                        //console.log(results[0]['formatted_address']);
                        $('#addressInput').val(results[0]['formatted_address']);
                    }
                    else {
                        alert("Geocoder failed due to: " + status);
                    }
                });
            }

            // On marker Drag
            google.maps.event.addListener(myMarker, "dragend", function(event) {
                $('#x_coord').val(event.latLng.lat());
                $('#y_coord').val(event.latLng.lng());
                changeAddress(event);
            });
            // On map Click
            google.maps.event.addListener(map, "click", function(event) {
                myMarker.setPosition(event.latLng);
                $('#x_coord').val(event.latLng.Ra);
                $('#y_coord').val(event.latLng.Qa);
                changeAddress(event);
            });

            infowindow = new google.maps.InfoWindow();

        }

        function search2()
        {
            var types = new Array();
            $.each($('.types:checked'), function(i,result) {  types[i] = result['value']; });
            var location_new = myMarker.getPosition();
            var request = {
                location: location_new,
                radius:   $('#radiusSelect').val(),
                types:    types
            };
            service = new google.maps.places.PlacesService(map);
            service.search(request, callback);
        }

        function callback(results, status)
        {
            if (status == google.maps.places.PlacesServiceStatus.OK) {
              for (var i = 0; i < results.length; i++) {
                createMarker(results[i]);
              }
            }
            else /*if (status == google.maps.places.PlacesServiceStatus.ERROR){console.log('ERROR');}
            else if (status == google.maps.places.PlacesServiceStatus.INVALID_REQUEST){console.log('INVALID_REQUEST');}
            else if (status == google.maps.places.PlacesServiceStatus.OVER_QUERY_LIMIT){console.log('OVER_QUERY_LIMIT');}
            else if (status == google.maps.places.PlacesServiceStatus.REQUEST_DENIED){console.log('REQUEST_DENIED');}
            else if (status == google.maps.places.PlacesServiceStatus.UNKNOWN_ERROR){console.log('UNKNOWN_ERROR');}
            else if (status == google.maps.places.PlacesServiceStatus.ZERO_RESULTS){*/console.log(status);
        }

        //var alice = 'Alice';

        function sayAlice() {
          var sayAlert = function() { alert(alice); }
          // Local variable that ends up within closure
          var alice = 'Hello Alice';
          return sayAlert;
        }


        function createMarker(place)
        {



            var all_types = {
                <?php
                    $resultstr = array();
                    foreach(Place::getPlaces() as $p) {
                        $resultstr[] =  $p->place_id.': "'.$p->place_name.'"';
                    }
                    $result = implode(',',$resultstr);
                    echo $result;
                ?>
            };

            var placeLoc = place.geometry.location;

            var marker = new google.maps.Marker({
              map: map,
              position: place.geometry.location
            });

            console.log(place);

            var rating = place.rating;
            if (rating == 'undefined') {
                rating = '';
            }

            var reference = {
                reference: place.reference
            };

            var service_details = new google.maps.places.PlacesService(map);

            // asynchronous call to get Details passing it a reference
            // and a user-defined function to deal with error codes
            // and a user-defined way of outputting the result...
            var test = service_details.getDetails(reference,callback_details);

            function callback_details(places, status) {
                if (status == google.maps.places.PlacesServiceStatus.OK) {
                    //createMarker(place);
                    //places.formatted_address
                    console.log(place);
                    console.log(places);

                    var url = places.url;
                    url = url.slice(url.length-20,url.length);

                    var store_types = new Array();
                    store_types = places.types;

                    /*
                    $('#types_textarea').append("INSERT INTO MARKERS ('name','address','lat','lng','rating', 'reference','google_id','phone','url') VALUES (");
                    $('#types_textarea').append("'"+place.name +"',");                      // Name
                    $('#types_textarea').append("'"+places.formatted_address+"',");         // Address
                    $('#types_textarea').append(placeLoc.Qa + ',');                         // Lat
                    $('#types_textarea').append(placeLoc.Ra + ',');                         // Lng
                    $('#types_textarea').append(rating + ',');                              // Rating
                    $('#types_textarea').append("'"+place.reference +"',");                 // Reference
                    $('#types_textarea').append(place.id + ',');                            // Google ID
                    $('#types_textarea').append("'"+places.formatted_phone_number + "',");  // Phone #
                    $('#types_textarea').append("'"+url+"'");                               // Google CID URL
                    $('#types_textarea').append(");\n");
                    */
                    var number = $('[name=name[0]]').length;
                    console.log(number);

                    var placeName      = "<td><input type='text' name='name[]'       value='"+place.name+"'/></td>";
                    var placeAddress   = "<td><input type='text' name='address[]'    value='"+places.formatted_address+"'/></td>";
                    var placeLat       = "<td><input type='text' name='lat[]'        value='"+placeLoc.Qa+"'                   style='width:65px;overflow:hidden;'/></td>";
                    var placeLng       = "<td><input type='text' name='lng[]'        value='"+placeLoc.Ra+"'                   style='width:65px;overflow:hidden;'/></td>";
                    var placeRating    = "<td><input type='text' name='rating[]'     value='"+rating+"'                        style='width:50px;overflow:hidden;'/></td>";
                    var placeReference = "<td><input type='text' name='rating[]'     value='"+place.reference+"'               style='width:100px;overflow:hidden;'/></td>";
                    var placeID        = "<td><input type='text' name='google_id[]'  value='"+place.id+"'                      style='width:100px;overflow:hidden;'/></td>";
                    var placePhone     = "<td><input type='text' name='phone[]'      value='"+places.formatted_phone_number+"' style='width:90px;overflow:hidden;'/></td>";
                    var placeURL       = "<td><input type='text' name='url[]'        value='"+url+"' /></td>";

                    var color="white";
                    //if (number%2 != 0) {
                    //    color="white"
                    //}
                    $('#places').append('<tr style="background-color:'+color+';">'+placeName+placeAddress+placeLat+placeLng+placeRating+placeReference+placeID+placePhone+placeURL+'</tr>');

                    // console.log(store_types);
                    //$('#types_textarea').append("SELECT @identity...\n");
                    $.each(store_types,function(k,v) {
                        //console.log(k+':'+v);
                        $.each(all_places,function(key,value) {
                           //console.log(key+':'+value);
                           if (v == value) {
                               $('#places').append("<tr style='background-color:'+color+';><td colspan='9'></td><td><input type='text' name='type[]["+k+"]' value='" + key + "' style='width:40px;overflow:hidden;'/></td>");
                               //$('#types_textarea').append("INSERT INTO store_types ('store_id','type_id') VALUES (@identity,"+key+");\n");
                           }
                        });
                    });
                    var ok     = "<image scr='images/ok.png'/>";
                    var cancel = "<image scr='images/cancel.png'/>";

                    var content = place.name+'\n'
                                +places.formatted_address+'\n'
                                +places.formatted_phone_number+'\n'
                                +'<input type="button" value="'+ok+'" />'
                                +'<input type="button" value="'+cancel+'" />';

                    google.maps.event.addListener(marker, 'click', function() {
                        infowindow.setContent(content);
                        infowindow.open(map, this);
                    });

                }
                else console.log(status);
            }

        }
    </script>
</head>
<body onload="initialize();">

<table>
    <tr>
        <td>
            <div id="map_canvas"></div>
        </td>
        <td>
            <?=Marker::getCount();?>
            <div id="scrolling_pane">
            <?php
                foreach (Marker::getMarkers() as $r){
                    ?><div><?=$r->name;?></div><?php
                }
            ?>
            </div>
        </td>
    </tr>
</table>

<input type="hidden" value="" name="x_coord" id="x_coord"/>
<input type="hidden" value="" name="y_coord" id="y_coord"/>

<form action="model/search_shop_model.php" style="height:30%">
    <table>
        <tr>
            <th>
                Address:
            </th>
            <td>
                <input style="width:300px;" type="text" id="addressInput" value="Brighton Beach Ave, Brooklyn, NY, 11235"/></td>
            </td>
        </tr>
        <tr>
            <th>
                Radius:
            </th>
            <td>
                <select id="radiusSelect">
                    <option value="25">25</option>
                    <option value="100">100</option>
                    <option value="200" selected>200</option>
                    <option value="3200">3200</option>
                    <option value="10000">10000</option>
                </select>
                <b>Limit:</b>
                <input type="text" id="limit" value="20" style="width:35px;"/>
            </td>
        </tr>
        <tr>
            <th>Types:</th>
            <td>
                <div>
                    <?php
                        foreach(Place::getPlaces(array('place_ids'=>array(7,9,12,15,26,29,38,57,72,80,84,85,89))) as $r) {
                        ?><label><input class="types" type="checkbox" value="<?=$r->place_name;?>" <?=(in_array($r->place_id,array(7,26,29,38,57,89)) ? "checked='checked'":"");?>><?=$r->place_name;?></label><?php
                        }
                    ?>
                </div>
            </td>
        </tr>
        <tr>
            <th>
            </th>
            <td>
                <input type="button" id="submit" onclick="search2();"value="Search Locations"/>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <textarea style="width:600px;height:100px;"id="types_textarea"></textarea>
            </td>
        </tr>
    </table>
    <table id="places" style="border:1px dashed maroon;">
        <tr style="background-color:#ffffff;">
            <td>Name</td>
            <td>Address</td>
            <td>Lat</td>
            <td>Lng</td>
            <td>Rating</td>
            <td>Reference</td>
            <td>Google ID</td>
            <td>Phone</td>
            <td>URL</td>
            <td>Type</td>
        </tr>
    </table>
    <input type="submit" value="Submit" />
</form>

</body>
</html>

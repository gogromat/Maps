<?php require_once("includes/Marker.php");?>
<?php require_once("includes/global.php");?>
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
    </style>

    <!--Sensor means using GPS locator to determine the user's location-->
    <script type="text/javascript" src="../common/scripts/jquery/jquery.js"></script>
    <script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?sensor=true">//sensor=set_to_true_or_false</script>
    <script type="text/javascript" src="http://code.google.com/apis/gears/gears_init.js"></script>

    <script type="text/javascript"
            src="http://www.google.com/jsapi?key=ABQIAAAA-O3c-Om9OcvXMOJXreXHAxQGj0PqsCtxKvarsoS-iqLdqZSKfxS27kJqGZajBjvuzOBLizi931BUow">
    </script>

    <script type="text/javascript">
        
        var geocoder;

        var initialLocation;

        var browserSupportFlag =  new Boolean();

        var map;

        var myMarker;

        function initialize()
        {
            geocoder = new google.maps.Geocoder();

            var latlng  = new google.maps.LatLng(40.69847032728747, -73.9514422416687);
            //var chicago = new google.maps.LatLng(41.850033, -87.6500523);

            var myOptions = {
              zoom: 14,
              center: latlng,
              mapTypeId: google.maps.MapTypeId.ROADMAP
            };
            map = new google.maps.Map($("#map_canvas").get(0), myOptions);

            // test KML
            // geoXml = new google.maps.KmlLayer('http://api.flickr.com/services/feeds/geo/?g=322338@N20&lang=en-us&format=feed-georss');
            // geoXml.setMap(map);

            //var market_latLng = new google.maps.LatLng(map.center.Ra, map.center.Qa);
            //console.log(market_latLng);

            myMarker = new google.maps.Marker({
                //position: location,
                position: latlng,
                map: map,
                draggable: true,
                icon: 'markers/youAreHere.png',
                title:'you are here!'
            });

            //map.addOverlay(marker);
            
            // On marker Drag
            google.maps.event.addListener(myMarker, "dragend", function(event) {
                $('#x_coord').val(event.latLng.Ra);
                $('#y_coord').val(event.latLng.Qa);
            });
            // On map click
            google.maps.event.addListener(map, "click", function(event) {
                myMarker.setPosition(event.latLng);
                $('#x_coord').val(event.latLng.Ra);
                $('#y_coord').val(event.latLng.Qa);
            });
            /*
                When map is being dragged
                google.maps.event.addListener(map, "drag", function(event) {
                    marker.setPosition(event.latLng);
                    console.log(map.getBounds());
                    {Z.b, Z.d, aa.b, aa.d}
                    $('#x_coord').val(event.latLng.Pa);
                    $('#y_coord').val(event.latLng.Qa);
                });

                google.maps.event.addListener(map, "click", function(event) {
                    alert("You clicked the map.");
                    placeMarker(event.latLng);
                });
            */
        }

        function getCenterLatLngText() {
            return '(' + map.getCenter().lat() +', '+ map.getCenter().lng() +')';
        }

        //1.Make only 1 marker
        //2.Make it draggable
        //3.Populate coords from marker
        function placeMarker(location)
        {
            //console.log(marker);
            marker.position = location;
            marker.map = map;
            $('#x_coord').val(location.Ra);
            $('#y_coord').val(location.Qa);
            //map.setCenter(location);
        }

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
        }

        function geoCodeAddress()
        {
            var address = $("address").val();
            geocoder.geocode( {'address': address}, function (results, status)
            {
                if (status = google.maps.GeocoderStatus.OK) {
                    map.setCenter(results[0].geometry.location);

                    // Map Marker
                    var marker2 = new google.maps.Marker({
                        map: map,
                        position: results[0].geometry.location,
                        icon: 'markers/youAreHere.png'
                    });
                }
                else {
                    alert("Geocode was not successful:" + status);
                }
            });
        }

        function searchLocations()
        {
            var address = $('#addressInput').val();
            geocoder.geocode({'address': address}, function(results,status) {
                if (status = google.maps.GeocoderStatus.OK) {
                    var location = results[0].geometry.location;
                    map.setCenter(location);
                    myMarker.setPosition(location);
                    $('#x_coord').val(location.Ra);
                    $('#y_coord').val(location.Qa);
                    searchLocationsNear(results);
                }
                else {
                    alert("Geocode was not successful:" + status);
                }
            });
        }

        function searchLocationsNear(results)
        {
            //console.log("Qa/Lat:" + results[0].geometry.location.lat());
            //console.log("Ra/Lng:" + results[0].geometry.location.lng());
            var radius = $('#radiusSelect').val();
            var limit  = $('#limit').val();
            var searchUrl = 'js/markersAjax.php?type=json&lat='+results[0].geometry.location.Qa+'&lng='+results[0].geometry.location.Ra+'&radius='+radius+'&limit='+limit;
            $('#sidebar').html('');
            $.getJSON(searchUrl, function(data)
            {
                $.each(data, function(key,value)
                {
                    // Creating a Lat and Lng coordinates
                    var latlng = new google.maps.LatLng(
                        parseFloat(value['lat']),
                        parseFloat(value['lng'])
                    );
                    // Creating a Market with coordinates from Lat and Lng
                    var marker = new google.maps.Marker({
                        position: latlng,
                        map: map,
                        title: value['name']
                    });
                    // Creating an InfoWindow with the content text: "Hello World"
                    var infoWindow = new google.maps.InfoWindow({
                        content: '<div><b>'+value['name']+'</b><br />'+value['address']+'<br /></div>'
                    });
                    // Adding a click event to the marker
                    google.maps.event.addListener(marker, 'click', function() {
                        // Calling the open method of the infoWindow
                        infoWindow.open(map, marker);
                    });

                    createSidebarEntry(marker, value['name'], value['address'], parseFloat(value['distance']));

                });
            });
        }

        function createSidebarEntry(marker, name, address, distance)
        {
            //distance.toFixed(2)
            var text = '<div><b>' + name    + '</b><br />';
                text+=      '<i>' + address + '</i><br />';
                text+=    '<pre>' + distance.toFixed() + ' miles</pre></div>';

            $(text).appendTo($('#sidebar'));

            //$('#sidebar').style.cursor = 'pointer';
            //$('#sidebar').style.marginBottom = '5px';

            google.maps.event.addDomListener($('#sidebar'), 'click', function() {
                google.maps.event.trigger(marker, 'click');
            });
            return false;
        }

    </script>

</head>
<body onload="initialize();">

<form action="SERVER::PHP_SELF" style="height:30%">

<table>
    <tr>
        <td style="width:50%;">
            <table style="height:100%;">
                <tr>
                    <th>
                        Shop Name
                    </th>
                    <td>
                        <input type="text" value="" name="shop_name"/>
                     </td>
                    <!--
                        <td>
                            Phone
                            <input type="text" value="" name="phone"/>
                        </td>
                    -->
                </tr>
                <tr>
                    <th>
                        Address
                    </th>
                    <td>
                        <input type="text" value="" name="address"/>
                    </td>
                    <!--
                        <td>
                            Address_2
                            <input type="text" value="" name="address_2"/>
                        </td>
                        <td>
                            Address_3
                            <input type="text" value="" name="address_3"/>
                        </td>

                        <td>
                            Zip
                            <input type="text" value="" name="zip"/>
                        </td>
                    -->
                </tr>
                <tr>
                    <th>
                        Shop Category:
                    </th>
                    <td>
                        <select name="shop_category">
                            <option></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>
                        Latitude  (X/Ra)
                    </th>
                    <td>
                        <input type="text" value="" name="x_coord" id="x_coord">
                    </td>
                </tr>
                <tr>
                    <th>
                        Longitude (Y/Qa)
                    </th>
                    <td>
                        <input type="text" value="" name="y_coord" id="y_coord">
                    </td>
                </tr>
                <tr>
                    <th>
                        <input type="submit" value="Add"/>
                    </th>
                </tr>
            </table>
        </td>
        <td style="width:50%;">
            <table style="height:100%;">
                <tr>
                    <th>
                        Markers (Shops) available:
                    </th>
                    <td>
                        <select>
                            <?php
                                foreach (Marker::getMarkers() as $r) {
                                    ?><option value="<?=$r->id;?>"><?=$r->name.'&nbsp;&nbsp;&nbsp;['.substr($r->address,strlen($r->address)-2,strlen($r->address)).']';?></option><?php
                                }
                            ?>
                        </select>
                            <!--Test-->
                            <!--<select>-->
                            <?php
                                $r = Marker::getMarkers(array('closest'=>500,'closest_lat'=>-74.372462,'closest_lng'=>40.649923));
                                //print_var($r);
                            ?>
                            <!--</select>-->
                    </td>
                </tr>
                <tr>
                    <th>
                        Address:
                    </th>
                    <td>
                        <input type="text" id="addressInput" value="Brighton Beach Ave"/></td>
                    </td>
                </tr>
                <tr>
                    <th>
                        Radius:
                    </th>
                    <td>
                        <select id="radiusSelect">
                            <option value="25" selected>25</option>
                            <option value="100">100</option>
                            <option value="200">200</option>
                            <option value="3200">3200</option>
                            <option value="10000">10000</option>
                        </select>
                        <b>Limit:</b>
                        <input type="text" id="limit" value="20" style="width:35px;"/>
                    </td>
                </tr>
                <tr>
                    <th></th>
                    <td><input type="button" onclick="searchLocations()" value="Search Locations"/></td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</form>


<table style="width:100%;height:70%;">
    <tr>
        <td style="width:25%">
            <div id="sidebar"    style="overflow:auto;width:100%;height:100%;"></div>
        </td>
        <td style="width:75%">
            <div id="map_canvas" style="width:100%;height:100%;"></div>
        </td>
    </tr>
</table>
    <!--
        <input style="position:absolute;z-index:1000;bottom:100px;left:400px;width:250px;"
               type="text" value="Brighton Beach" name="address" id="address" />
        <input style="position:absolute;z-index:1000;bottom:140px;left:400px;"
               type="button" value="Geocode" onclick="geoCodeAddress();" />
        <input style="position:absolute;z-index:1000;bottom:140px;left:480px;"
               type="text" value="Milk" onclick="" />
        <input style="position:absolute;z-index:1000;bottom:140px;left:600px;"
               type="button" value="Go Milk!" onclick="" />
    -->
    <!--<div id="map_canvas" style="width:100%; height:100%"></div>-->
</body>
</html>

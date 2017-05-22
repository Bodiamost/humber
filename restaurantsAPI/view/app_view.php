<?php
/**
 * Created by PhpStorm.
 * User: bodia
 * Date: 2017-04-25
 * Time: 1:20 AM
 */
?>
<!DOCTYPE html>
<html>
<head lang="en">
    <title>Search | Restaurant Locator</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/font-awesome.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <script src="js/jquery.1.11.1.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <meta charset="utf-8" />
</head>
<body>
<header class="row">
    <h1 class="pull-left col-lg-offset-2 col-md-offset-2 col-sm-offset-1 col-xs-offset-1 logo">Restaurant Locator</h1>
    <form class="pull-right" method="post" action="index.php">
        <button class="loginBtn loginBtn--exit" type="submit" name="logout">Logout</button>
    </form>
    <p class="welcome">Hello, <?php echo $userInfo['name'];?>! </p>
</header>
<div class="page-wrapper">
    <div class="centered box row">
        <p><b><i>Enter destination location:</i></b></p>
            <form id="search_form" action="app.php" method="post">
                <input id="pac-input" name="search_place"  type="text" value="<?php echo $_SESSION['searchInfo']['search_place'];?>" placeholder="Enter a location">
                <div>
                    <input type="hidden" id="latInput" name="map_lat" value="<?php echo $_SESSION['searchInfo']['map_lat'];?>" placeholder="">
                    <input type="hidden" id="lngInput" name="map_lng" value="<?php echo $_SESSION['searchInfo']['map_lng'];?>" placeholder="">
                    <input type="hidden" id="zoomInput" name="map_zoom" value="<?php echo $_SESSION['searchInfo']['map_zoom'];?>" placeholder="">
                </div>
            </form>
    <div class="row">
        <?php
            foreach ($items as $item) {
                echo "<div class='item col-lg-4 col-md-6 col-sm-6 col-xs-12'><div><a href='details.php?item=".$item['reference']."'>";
                echo '<h2>'.$item['name'].'</h2>';
                if ($item['photoref']!='')
                    echo "<img src='https://maps.googleapis.com/maps/api/place/photo?maxwidth=400&photoreference=".$item['photoref']."&key=".$google_API_KEY."' alt='Image of ".$item['name']."'>";
                else
                    echo "<span style='color: green;'> No image available</span><br/>";

                echo '</a></div><div>';
                echo $item['address']!=''?"<div>Address: ".$item['address'].'</div>':"";
                echo $item['phone']!=''?"<div>Phone: ".$item['phone'].'</div>':"";
                if($item['rating']!='')
                {
                    //echo "Rating: ".$item['rating']*2;
                    $int=intval($item['rating']*2);
                    if($int==0) echo "<span>Not rated yet</span>";
                    $i=1;
                    for($i=1; $i<$int;$i+=2)
                        echo "<i class=\"fa fa-star fa-2x\" aria-hidden=\"true\"></i>";
                    if($i==$int) echo "<i class=\"fa fa-star-half-o fa-2x\" aria-hidden=\"true\"></i>";
                }
                else echo "Not rated";

                echo '</div></div><br/>';
            }
        ?>
    </div>
        <script>
            // This example displays an address form, using the autocomplete feature
            // of the Google Places API to help users fill in the information.

            // This example requires the Places library. Include the libraries=places
            // parameter when you first load the API. For example:
            // <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&libraries=places">

            var autocomplete;

            function initAutocomplete() {
                // Create the autocomplete object, restricting the search to geographical
                // location types.
                autocomplete = new google.maps.places.Autocomplete(
                    /** @type {!HTMLInputElement} */(document.getElementById('pac-input')),
                    {types: ['geocode']});

                // When the user selects an address from the dropdown, populate the address
                // fields in the form.
                $( "#search_form" ).keypress(function (e) {
                    var key = e.which;
                    if(key == 13)  // the enter key code
                    {
                        return false;
                    }
                });
                autocomplete.addListener('place_changed', fillInAddress);
            }

            function fillInAddress() {
                // Get the place details from the autocomplete object.
                var place = autocomplete.getPlace();
                //get radius
                document.getElementById("latInput").value = place.geometry.location.lat();
                document.getElementById("lngInput").value = place.geometry.location.lng();

                var point1=new google.maps.LatLng(place.geometry.viewport.f.f,place.geometry.viewport.b.f);
                var point2=new google.maps.LatLng(place.geometry.viewport.f.b,place.geometry.viewport.b.b);

                document.getElementById("zoomInput").value = google.maps.geometry.spherical.computeDistanceBetween(point1,point2)/2;
                $( "#search_form" ).submit();
            }
        </script>
        <script async defer
                src="https://maps.googleapis.com/maps/api/js?key=<?php echo $google_API_KEY?>&libraries=geometry,places&callback=initAutocomplete">
        </script>
</body>
</html>






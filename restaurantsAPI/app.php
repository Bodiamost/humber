<?php
/**
 * Created by PhpStorm.
 * User: bodia
 * Date: 2017-04-25
 * Time: 12:56 AM
 */
require_once __DIR__.'/vendor/autoload.php';
ini_set('session.save_path',getcwd(). '/tmp'); //for my laptop
ini_set('session.gc_probability', 1);

session_start();

$google_API_KEY='AIzaSyBoRDxrM0jsGDYRQWNwis2b8NUT8jpwGZY';
$userInfo=array();
$userInfo['name']='';
$search_place='';
$items=array();

if(!isset($_SESSION['username']))//if not logined -> redirect to index
{
    session_destroy();
    header("Location: index.php");
}
else//else get info
    $userInfo['name']=$_SESSION['username'];

if(isset($_POST['map_zoom'])&&!empty($_POST['map_zoom']))//if place entered-> get info about google places nearby
{
    $search_place=$_POST['search_place'];
    $distance=($_POST['map_zoom']/1000.0) < 2.5 ? 2.5:$_POST['map_zoom']/1000.0;

    $google_places = new joshtronic\GooglePlaces($google_API_KEY);
    $google_places->location = array($_POST['map_lat'], $_POST['map_lng']);
    $google_places->rankby   = 'distance';
    $google_places->types    = 'restaurant'; // Requires keyword, name or types
    $results                 = $google_places->nearbySearch();

    foreach ($results['results'] as $place) {
        $item['reference']=$place['reference'];
        $item['id']=$place['id'];
        $item['name']=$place['name'];
        $item['photoref']=isset($place['photos'][0]['photo_reference'])? $place['photos'][0]['photo_reference']:'';

        $google_places->reference = $item['reference'];
        $details                  = $google_places->details();

        $item['rating']=isset($details['result']['rating'])?$details['result']['rating']:'';
        $item['address']=isset($details['result']['formatted_address'])?$details['result']['formatted_address']:'';
        $item['phone']=isset($details['result']['international_phone_number'])?$details['result']['international_phone_number']:'';
        array_push($items,$item);
    }

    $_SESSION['searchInfo']['search_place'] =$_POST['search_place'];
    $_SESSION['searchInfo']['map_lat'] = $_POST['map_lat'];
    $_SESSION['searchInfo']['map_lng'] = $_POST['map_lng'];
    $_SESSION['searchInfo']['map_zoom'] = $_POST['map_zoom'];
}
require_once 'view/app_view.php'; //go to a view
?>




<?php
/**
 * Created by PhpStorm.
 * User: bodia
 * Date: 2017-04-25
 * Time: 3:36 AM
 */
ini_set('session.save_path',getcwd(). '/tmp'); //for my laptop
ini_set('session.gc_probability', 1);
session_start();
if(!isset($_SESSION['username']))
{
    session_destroy();
    header("Location: index.php");
}
else
    $userInfo['name']=$_SESSION['username'];

$foursqureCliend="XK25MMSEHRHVL5OL1FVBUJ4BHN1P0USXL1UV5YS204PGOO5X";
$foursqureSecret="ZELXW4G1IO35NHBZPE3Y1CETKNHGQB0C55EYVNZ5O2GZ0DLG";
$google_API_KEY='AIzaSyBoRDxrM0jsGDYRQWNwis2b8NUT8jpwGZY';

$item=array();
$item['reference']=filter_input(INPUT_GET,'item');
require_once __DIR__.'/vendor/autoload.php';
$google_places = new joshtronic\GooglePlaces($google_API_KEY);


$google_places->reference = $item['reference'];
$details                  = $google_places->details();

$item['id']=$details['result']['id'];
$item['name']=$details['result']['name'];
$item['photoref']=isset($details['result']['photos'][0]['photo_reference'])? $details['result']['photos'][0]['photo_reference']:'';
$item['location']['lat']=$details['result']['geometry']['location']['lat'];
$item['location']['lng']=$details['result']['geometry']['location']['lng'];


$item['weekly_open']=isset($details['result']['opening_hours']['weekday_text'])? $details['result']['opening_hours']['weekday_text']:'';
$item['rating']=isset($details['result']['rating'])?$details['result']['rating']:'';
$item['address']=isset($details['result']['formatted_address'])?$details['result']['formatted_address']:'';
$item['phone']=isset($details['result']['international_phone_number'])?$details['result']['international_phone_number']:'';

$item['reviews'] =isset($details['result']['reviews'])?$details['result']['reviews']:array();

$foursquare = new FoursquareApi($foursqureCliend, $foursqureSecret);
$endpoint = "venues/search";
$searchTerms=explode(" ",$item['name']);
$params = array('ll'=>$item['location']['lat'].','.$item['location']['lng'],'radius'=>'25','query'=>$searchTerms[0]);
//$params = array('ll'=>'43.72863490941816,-79.60800619960065','radius'=>'25','categoryId'=>'4d4b7105d754a06374d81259','query'=>$searchTerms[0]);
$response = $foursquare->GetPublic($endpoint,$params, $POST=false);
$response_obj=json_decode($response);

$i=1;
while(count($response_obj->response->venues)==0&&$i<count($searchTerms))
{
    $params = array('ll'=>$item['location']['lat'].','.$item['location']['lng'],'radius'=>'25','categoryId'=>'4d4b7105d754a06374d81259','query'=>$searchTerms[$i]);
    $response = $foursquare->GetPublic($endpoint,$params, $POST=false);
    $response_obj=json_decode($response);
    $i++;
    if(count($response_obj->response->venues)!=0) break;
}

if(count($response_obj->response->venues)!=0)
{
    $venue_id=$response_obj->response->venues[0]->id;
    $endpoint = "venues/".$venue_id;
    $venue = $foursquare->GetPublic($endpoint, $POST=false);
    $venue_obj=json_decode($venue);
    $item['foursquareInfo']=$venue_obj->response->venue;
}

require_once 'view/details_view.php';
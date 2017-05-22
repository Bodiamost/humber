<?php 
ini_set('session.save_path',getcwd(). '/tmp'); //for my laptop
ini_set('session.gc_probability', 1);
session_start();
if(!isset($_SESSION['access_service']))$_SESSION['access_service']='';
$redirect_uri = 'http://bohdanmost.com/humber/restaurantsAPI/oauth2callback.php';
require_once 'vendor/autoload.php';
$client = new Google_Client();
$client->setAuthConfig('client_id.json');
$client->setAccessType("offline");        // offline access
$client->setIncludeGrantedScopes(true); // incremental auth
$client->addScope("email");  
$client->addScope("profile");

$service = new Google_Service_Oauth2($client);

if (isset($_POST['googlelogin'])) //google login
{
	$_SESSION['access_service']='google';
	header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
} 
if (isset($_POST['fblogin'])) //facebook login
{
    $_SESSION['access_service'] = 'facebook';
    $fb = new Facebook\Facebook([
        'app_id' => '1743220919342022', // Replace {app-id} with your app id
        'app_secret' => '149e47f1d6d4022186cfdba9799bd5be',
        'default_graph_version' => 'v2.2',
    ]);
    $helper = $fb->getRedirectLoginHelper();
    $permissions = ['email']; // Optional permissions
    $loginUrl = $helper->getLoginUrl($redirect_uri, $permissions);

    header('Location: ' . filter_var($loginUrl, FILTER_SANITIZE_URL));
}
if (isset($_POST['logout'])) // logout: destroy token
{
	    unset($_SESSION['access_token']);
	    session_destroy();
	    header("Refresh: 0");
}
if (isset($_SESSION['access_token']) && $_SESSION['access_token']) //Successfull login -> get info and redirect
{
    //default values
    $_SESSION['searchInfo']['search_place'] = '';
    $_SESSION['searchInfo']['map_lat'] = '';
    $_SESSION['searchInfo']['map_lng'] = '';
    $_SESSION['searchInfo']['map_zoom'] = '';

    //USer information
    if($_SESSION['access_service']=='google')
    {
        $client = new Google_Client();
        $client->setAuthConfig('client_id.json');
        $client->setAccessToken($_SESSION['access_token']);

        $service = new Google_Service_Oauth2($client);

        $user = $service->userinfo->get();
     }
    else if($_SESSION['access_service']=='facebook')
    {
        $fb = new Facebook\Facebook([
            'app_id' => '1743220919342022', // Replace {app-id} with your app id
            'app_secret' => '149e47f1d6d4022186cfdba9799bd5be',
            'default_graph_version' => 'v2.2',
        ]);

        try {
            // Returns a `Facebook\FacebookResponse` object
            $response = $fb->get('/me?fields=id,name', $_SESSION['access_token']);
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }

        $user = $response->getGraphUser();
    }

    $_SESSION['username']=$user['name'];
    header("Location: app.php");
} 

?>
<!DOCTYPE html>
<html>
  <head lang="en">
	<title>Entrance | Restaurant Locator</title>
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
  </header>
  <div class="page-wrapper">
	  	<div class="box  row centered">
	  		<h1>Welcome to restaurant locator application</h1>
	  		<form method="post" action="">
				<button class="loginBtn loginBtn--facebook" type="submit" name="fblogin">Login with Facebook</button>
				<button class="loginBtn loginBtn--google" type="submit" name="googlelogin">Login with Google</button>
			</form>
		<div>
  </body>
</html>
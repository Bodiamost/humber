<?php
require_once __DIR__.'/vendor/autoload.php';
ini_set('session.save_path',getcwd(). '/tmp'); //for my laptop
ini_set('session.gc_probability', 1);
ini_set("display_errors",1);
session_start();
$redirect_uri = 'http://bohdanmost.com/humber/restaurantsAPI/oauth2callback.php';

if($_SESSION['access_service']=='google')//google callback
{
	$client = new Google_Client();
    $guzzleClient = new \GuzzleHttp\Client(array( 'curl' => array( CURLOPT_SSL_VERIFYPEER => false, ), ));
    $client->setHttpClient($guzzleClient);
	$client->setAuthConfigFile('client_id.json');
	$client->setRedirectUri($redirect_uri);
	$client->addScope("email");  
	$client->addScope("profile");

	if (! isset($_GET['code'])) {
	  $auth_url = $client->createAuthUrl();
	  header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
	} else {
	  $client->authenticate($_GET['code']);
	  $_SESSION['access_token'] = $client->getAccessToken();
	  $redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/humber/restaurantsAPI/index.php';
	  header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
	}
}
if ($_SESSION['access_service']=='facebook') //facebook callback
{
	$fb = new Facebook\Facebook([
	  'app_id' => '1743220919342022', // Replace {app-id} with your app id
	  'app_secret' => '149e47f1d6d4022186cfdba9799bd5be',
	  'default_graph_version' => 'v2.2',
	  ]);
    $helper = $fb->getRedirectLoginHelper();

    $permissions = ['email']; // Optional permissions
    $loginUrl = $helper->getLoginUrl($redirect_uri, $permissions);

	try {
	  $accessToken = $helper->getAccessToken();
	} catch(Facebook\Exceptions\FacebookResponseException $e) {
	  // When Graph returns an error
	  echo 'Graph returned an error: ' . $e->getMessage();
	  exit;
	} catch(Facebook\Exceptions\FacebookSDKException $e) {
	  // When validation fails or other local issues
	  echo 'Facebook SDK returned an error: ' . $e->getMessage();
	  exit;
	}

	if (! isset($accessToken)) {
	  if ($helper->getError()) {
	    header('HTTP/1.0 401 Unauthorized');
	    echo "Error: " . $helper->getError() . "\n";
	    echo "Error Code: " . $helper->getErrorCode() . "\n";
	    echo "Error Reason: " . $helper->getErrorReason() . "\n";
	    echo "Error Description: " . $helper->getErrorDescription() . "\n";
	  } else {
	    header('HTTP/1.0 400 Bad Request');
	    echo 'Bad request';
	  }
	  exit;
	}

	// The OAuth 2.0 client handler helps us manage access tokens
	$oAuth2Client = $fb->getOAuth2Client();

	// Get the access token metadata from /debug_token
	$tokenMetadata = $oAuth2Client->debugToken($accessToken);

	// Validation (these will throw FacebookSDKException's when they fail)
	$tokenMetadata->validateAppId('1743220919342022'); // Replace {app-id} with your app id
	// If you know the user ID this access token belongs to, you can validate it here
	//$tokenMetadata->validateUserId('123');
	$tokenMetadata->validateExpiration();

	if (! $accessToken->isLongLived()) {
	  // Exchanges a short-lived access token for a long-lived one
	  try {
	    $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
	  } catch (Facebook\Exceptions\FacebookSDKException $e) {
	    echo "<p>Error getting long-lived access token: " . $helper->getMessage() . "</p>\n\n";
	    exit;
	  }
    }

	$_SESSION['access_token'] = (string) $accessToken;

    $redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/humber/restaurantsAPI/index.php';
    header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
}
?>
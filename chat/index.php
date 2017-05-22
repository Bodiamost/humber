<?php 
session_save_path('/home/users/web/b577/ipg.bohdanmostcom/cgi-bin/tmp');
session_start();

$redirect_uri = 'http://bohdanmost.com/humber/chat/index.php';
require_once 'vendor/autoload.php';

$client = new Google_Client();
$client->setAuthConfig('client_id.json');
$client->setAccessType("offline");        // offline access
$client->setIncludeGrantedScopes(true); // incremental auth
$client->addScope("email");  
$client->addScope("profile");
$client->setRedirectUri($redirect_uri);

$service = new Google_Service_Oauth2($client);
if (isset($_GET['code'])) // we received the positive auth callback, get the token and store it in session
{ 
    $client->authenticate($_GET['code']);
    $_SESSION['token'] = $client->getAccessToken();
    header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
  	die;
}
if (isset($_SESSION['token'])) // extract token from session and configure client
{ 
    $token = $_SESSION['token'];
    $client->setAccessToken($token);
}
if (isset($_POST['googlelogin'])) 
{
	if (!$client->getAccessToken())// auth call to google
	{ 
		
	    $authUrl = $client->createAuthUrl();
	    header("Location: ".$authUrl);
	    die;
	}
}

$doc = new DOMDocument('1.0', "utf-8");
$doc->load("channel.xml");
$members=$doc->getElementsByTagName('member');//member elements

//Get room members data:
$usersData=array();
foreach ($members as $member) {
	$id=$member->getAttribute('id');
	$fname=$member->getElementsByTagName('firstname')[0]->nodeValue;
	$lname=$member->getElementsByTagName('lastname')[0]->nodeValue;
	$email=$member->getElementsByTagName('email')[0]->nodeValue;
	$nickname=$member->getElementsByTagName('nickname')[0]->nodeValue;
	array_push($usersData,array( 'id' => $id, 'fname' => $fname,'lname' => $lname,'email' => $email,'nickname' => $nickname));
}

//Login actions
if(isset($_SESSION['token']))//login with Google
{
	$user = $service->userinfo->get();
	foreach ($usersData as $userData) {
		if($userData['email']==$user->email) //If found : use member data and redirect to chatroom
		{
			if (!isset($_SESSION['data'])) $_SESSION['data'] = $userData;
			header('Location: chat.php');
		}
	}
	if (!isset($_SESSION['data']))//If user not found : use user nickname as id(Schema changed) and redirect to chatroom
	{
		$_SESSION['data'] = array( 'id' => explode('@',$user->email)[0], 'fname' => explode(' ',$user->name)[0],'lname' => explode(' ',$user->name)[1],'email' => $user->email,'nickname' => explode('@',$user->email)[0]);
	}
	header('Location: chat.php');
}
else if(isset($_POST['loginbtn'])&&$_POST['nickname']!='')// simple login
{
	//Search for user in members
	foreach ($usersData as $userData) {
		if($userData['nickname']==$_POST['nickname']) //If found : use member data and redirect to chatroom
		{
			if (!isset($_SESSION['data'])) $_SESSION['data'] = $userData;
			header('Location: chat.php');
		}
	}
	if (!isset($_SESSION['data']))//If user not found : use user nickname as id(Schema changed) and redirect to chatroom
	{
		$_SESSION['data'] = array( 'id' => $_POST['nickname'], 'fname' => 'Not entered','lname' => 'Not entered','email' => 'Not entered','nickname' => $_POST['nickname']);
	}
	header('Location: chat.php');
}
?>
<!DOCTYPE html>
<html>
  <head lang="en">
    <title>Chat entrance</title>
    <link rel="stylesheet" type="text/css" href="style.css">
	<meta charset="utf-8" />
      <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' name='viewport' />
  </head>
  <body>
	  <div class="page-wrapper">
	  	<div class="centered">
	  		<h1>Welcome to chat application</h1>
	  		<form method="post" action="">
			  	<div>
			  		<div>
				    	<label for="nickname">Enter your nickname:</label>
				    </div>
					<input type="text" id="nickname" name="nickname" />
				</div> 
				<button class="loginBtn loginBtn--chat" type="submit" name="loginbtn">Login</button>
				<div>
				  	<div> or </div>
				  	<div>
				  	<?php 
						if(!isset($_SESSION['token'])) echo '<button class="loginBtn loginBtn--google" type="submit" name="googlelogin">
								  Login with Google</button>'; // if not logined display button to login.  
					?>
					</div>
				</div>
			</form>
		<div>
  </body>
</html>


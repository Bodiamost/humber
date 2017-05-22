<?php 
session_save_path('/home/users/web/b577/ipg.bohdanmostcom/cgi-bin/tmp');
session_start();
if(isset($_POST['exitbtn'])||!isset($_SESSION['data']))//Logout actions
{
	session_unset();
	header('Location: index.php');
	die;
}
//Loading XML
$doc = new DOMDocument('1.0', "utf-8");
$doc->preserveWhiteSpace = false;
$doc->formatOutput = true;
$doc->load("channel.xml");

$members=$doc->getElementsByTagName('members');//members conteiner element
$messages=$doc->getElementsByTagName('messages');//messages conteiner element
$mbrs=$doc->getElementsByTagName('member');//message elements
$msgs=$doc->getElementsByTagName('message');//message elements
$xpath = new DOMXPath($doc);
if(isset($_POST['delbtn']))//Delete message action (mark as 'deleted' without removing)
{
	$e=$xpath->query("/channel/messages/*[@id='".$_POST['msgid']."']")[0];
	if($e->hasAttribute('status'))	$e->removeAttribute('status');
	$e->setAttribute('status','deleted');
	if($doc->schemaValidate('channel.xsd')) //Validate changes
		{$doc->save("channel.xml");header("Refresh:0");}
	else 
		echo "Not allowed action";	
}
if(isset($_POST['joinbtn']) )//Add a member to room
{
	$el=$xpath->query("/channel/members/member/nickname[text()='".$_POST['nickname']."']")[0];//Check for unique nickname
	//validate fields
	if (is_null($el)&&filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)&&$_POST['lname']!=''&&$_POST['fname']!=''&&$_POST['nickname']!='') 
	{
		$id=$mbrs[$mbrs->length-1]->getAttribute('id')+1;
		$fname=$_POST['fname'];
		$lname=$_POST['lname'];
		$email= $_POST['email'];
		$nickname=$_POST['nickname'];
		$item= $doc->createElement("member"); // careate member item
		$e = $doc->createAttribute("id");
		$e->value =$id;
		$item->appendChild($e);
		$e = $doc->createElement("firstname", $fname);
		$item->appendChild($e);
		$e = $doc->createElement("lastname",  $lname);
		$item->appendChild($e);
		$e = $doc->createElement("email", $email);
		$item->appendChild($e);
		$e = $doc->createElement("nickname", $nickname);
		$item->appendChild($e);
		$members[0]->appendChild($item);
		$doc->getElementsByTagName('nmbofmembers')[0]->textContent=$mbrs->length;// Change number of room members
		if($doc->schemaValidate('channel.xsd')&& $mbrs->length<10)//Validate changes and check max members allowance(10) 
			{
				$doc->save("channel.xml");
				$_SESSION['data']=array( 'id' => $id, 'fname' => $fname,'lname' => $lname,'email' => $email,'nickname' => $nickname);
				header("Refresh:0");
			}
		else 
			echo "Not allowed action";
	}
	else echo "Enter valid data!";
}
if(isset($_POST['addbtn'])&&$_POST['msg']!='')//Send message actions
{
	$item= $doc->createElement("message"); //create message item
	$e = $doc->createAttribute("id");
	$e->value =$msgs[$msgs->length-1]->getAttribute('id')+1;
	$item->appendChild($e);
	$e = $doc->createAttribute("status");
	$e->value ='open';
	$item->appendChild($e);
	$e = $doc->createElement("sender", $_SESSION['data']['id']);
	$item->appendChild($e);
	$e = $doc->createElement("time", date('g:i:s A'));
	$item->appendChild($e);
	$e = $doc->createElement("date", date('d M '));
	$item->appendChild($e);
	$e = $doc->createElement("text");
	$text=$doc->createCDATASection($_POST['msg']);
	$e->appendChild($text);
	$item->appendChild($e);
	$messages[0]->appendChild($item);
	if($msgs->length>10)//Remove deleted messages after the number of all messages is greater then 10
	{
		$el=$xpath->query("/channel/messages/*[@status='deleted']")[0];
		if(!is_null($el)) $el->parentNode->removeChild($el);
	}
	if($doc->schemaValidate('channel.xsd'))//Validate changes
	{
		$doc->save("channel.xml");
		header("Refresh:0");
	}
	else 
		echo "Not allowed action";
}
?>
<!DOCTYPE html>
<html>
  <head lang="en">
    <title>Chat room</title>
	<meta charset="utf-8" />
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.0/jquery.min.js"></script>	
    <link rel="stylesheet" type="text/css" href="style.css">
      <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' name='viewport' />
      <meta http-equiv="expires" content="0">
  </head>
  <body>
  <div class="page-wrapper">
	<div class="centered">
		<div class='header'>
		<?php echo "<h1>".$doc->getElementsByTagName('roomname')[0]->nodeValue."</h1>";?>
			<form id="chatactions_form" method="post" action="">
				<?php if(!is_numeric($_SESSION['data']['id'])):?><button class="loginBtn loginBtn--chat" id="join" type="button" name="join">Join</button><?php endif; ?> <!--For not a member will be a form to join channel-->
			  	<button class="loginBtn loginBtn--chat" type="submit" name="exitbtn">Logout</button> <!--Logout-->
			</form>
		</div>
		<div id="messages" class="body chat-box">
		<?php		
		//Display messages
		foreach ($msgs as $item) {
			$senderId=$item->getElementsByTagName('sender')[0]->nodeValue;
			if($item->getAttribute('status')=='open')//Do not dislpay deleted messages
			{   
				if(is_numeric($senderId))//If user is a member  => 'sender' tag stores numeric id
				{

					if($senderId==$_SESSION['data']['id']) // Users messages  with delete option
					{
						echo "<div id='msg".$item->getAttribute('id')."' class='message' ><b><form method=\"post\" action=\"\"><button type=\"submit\" name=\"delbtn\">delete</button>@".$xpath->query("/channel/members/*[@id='".$senderId."']/nickname")[0]->nodeValue.":</b><div class='msg-content'>".$item->getElementsByTagName('text')[0]->nodeValue."</div><input type=\"hidden\" id=\"msgid\" name=\"msgid\" value=\"".$item->getAttribute('id')."\" /></form></div>";
					}
					else // Other messages without delete option
						echo "<div id='msg".$item->getAttribute('id')."' class='message' ><i>@".$xpath->query("/channel/members/*[@id='".$senderId."']/nickname")[0]->nodeValue.":</i><div class='msg-content'>".$item->getElementsByTagName('text')[0]->nodeValue."</div></div>";
				}
				else //If user is not a member  => 'sender' tag stores string nickname
				{
					if($senderId==$_SESSION['data']['id']||$senderId==$_SESSION['data']['nickname']) // Users messages  with delete option
					{
						echo "<div id='msg".$item->getAttribute('id')."' class='message' ><i><form method=\"post\" action=\"\">
							<button type=\"submit\" name=\"delbtn\">delete</button>".$senderId.":</i><div class='msg-content'>".$item->getElementsByTagName('text')[0]->nodeValue."</div><input type=\"hidden\" id=\"msgid\" name=\"msgid\" value=\"".$item->getAttribute('id')."\" /></form></div>";
					}
					else // Other messages without delete option
						echo "<div id='msg".$item->getAttribute('id')."' class='message' ><i>".$senderId.":</i> <div class='msg-content'>".$item->getElementsByTagName('text')[0]->nodeValue."</div></div>";
				}

			}
		}
		?>
		</div><!--end of #messages-->
  		<div class="footer">
			<form id="chatmsg_form" method="post" action="">
			  <div>
			  	<label class='hidden' for="msg">Text:</label>
				<textarea id="msg" name="msg" placeholder="Type your message here..."></textarea>
			  </div>
			  <button id="addbtn" class="loginBtn loginBtn--chat" type="submit" name="addbtn">Message</button>
			</form>
		</div>
		<form id="joinForm" class="hidden" method="post" action="">
			  <div>
			    <label for="fname">First name:</label>
				<input type="text" id="fname" name="fname" value="<?php echo isset($_SESSION['token'])?$_SESSION['data']['fname']:'';?>" />
			  </div> 
			  <div>
			    <label for="lname">Last name:</label>
				<input type="text" id="lname" name="lname" value="<?php echo isset($_SESSION['token'])?$_SESSION['data']['lname']:'';?>"  />
			  </div>
			  <div>
			    <label for="email">Email:</label>
				<input type="text" id="email" name="email" value="<?php echo isset($_SESSION['token'])?$_SESSION['data']['email']:'';?>"  />
			  </div>
			  <div>
			    <label for="nickname">Nickname:</label>
				<input type="text" id="nickname" name="nickname" value="<?php echo $_SESSION['data']['nickname'];?>" />
			  </div>
			  <button class="loginBtn loginBtn--chat"  type="submit" name="joinbtn">Join</button>
		</form>
  	</div>
	<script>
		$('#messages').animate({scrollTop:document.getElementById("messages").scrollHeight-document.getElementById("messages").clientHeight},0); // sroll to newest messages
	    $("textarea").keydown(function (e) { // add submiting messages with enter key 
	        if(e.which == 13) {
	            $("#addbtn").click();
	        }
	    });


        var update=setInterval(updateChat,1000);
		function updateChat(){
			// Load the xml file using ajax 
			$.ajax({
			    type: "GET",
			    url: "channel.xml?_=" + new Date().getTime(),
			    dataType: "xml",
			    success: function (xml) {

			        // Parse the xml file and get data			    
			       	var last_message_id=parseInt($('#messages').find('div.message').last().attr('id').slice(3));// get last message id on current page
                    if($('#messages').find('div.message').length>$(xml).find('message[status="open"]').length)// remove deleted messages
			       	{
			       		$(xml).find('message[status="deleted"]').each(function () {
				            var deletedId=parseInt($(this).attr('id'));
			            	if($.find("#msg"+deletedId).length>0)
			            	{
			            		$("#msg"+deletedId).remove();
			            	}

			        	});
			    	}
			        $(xml).find('message[status="open"]').each(function () { // add new messages
			            if(parseInt($(this).attr('id'))>last_message_id)
			            {
			            	var sender=$(this).find('sender').text();
				            var msgText=$(this).find('text').text();
				            if(!isNaN(sender)) {
				            	sender = '@'+$(xml).find('member[id="'+sender+'"]').find('nickname').text()+': ';
				            }
				            else {
				            	sender +=': ';
				            }
				            var msg=document.createElement('div');
				            msg.id='msg'+$(this).attr('id');
				            msg.className='message'; 
					        var msgsender=document.createElement('i');
					        var t = document.createTextNode(sender);
					        msgsender.appendChild(t);
					        msg.appendChild(msgsender);
					        var div1=document.createElement('div');
					        div1.className='msg-content';
					        t=document.createTextNode(msgText);
					        div1.appendChild(t);
					        msg.appendChild(div1);
					        $("#messages").append(msg);
			            }
			            
			        });
			        $('#messages').animate({scrollTop:document.getElementById("messages").scrollHeight-document.getElementById("messages").clientHeight},0);// sroll to newest messages
			    }
			});
		}
	</script>
	<?php if(!is_numeric($_SESSION['data']['id'])):?> <!--For not a member will be a form to join channel-->
	<script type="text/javascript">
		var el = document.getElementById("join");
		el.addEventListener("click", function(){
			var el=document.getElementById("joinForm");
			$('#messages').addClass('hidden');
			$('.footer').addClass('hidden');
			$('#join').addClass('hidden');
			$(el).removeClass('hidden');

		}, false);	
	</script>
	<?php endif; ?>
	</div>
  </body>
</html>


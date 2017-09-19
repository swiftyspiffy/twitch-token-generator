<?
$details = getRequestDetails($id);
if(count($details) == 0)
	exit(header("Location: https://twitchtokengenerator.com/"));
if($details['enabled'] == "0")
	exit(header("Location: https://twitchtokengenerator.com/"));
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>Twitch Token Generator by swiftyspiffy</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/css/bootstrap.min.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
		
	<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/js/bootstrap.min.js"></script>
	<script src="../bootstrap-checkbox.min.js"></script>
	<script src="script.js"></script>
	<link rel="stylesheet" href="../style.css">
	<link rel="icon" type="image/ico" sizes="48x48" href="../favicon-48x48.ico">
</head>
<div class="col-md-2"></div>
<div class="container col-md-8">	
	<br>
	
	<div class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title text-center">Request Details</h3>
		</div>
		<div class="panel-body">
			<span>
			Your buddy <b><? echo $details['requester_name']; ?></b> requested access to the scopes below. Please ensure that you authorize this action. Once everything looks good, click the button "Everything Looks Good!". After doing so, you will be redirected to Twitch to authorize your account. After completion, the token for the authorization will be mailed to your buddy. 
			</span>
		</div>
	</div>
	
	<div class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title text-center">Requested Scopes</h3>
		</div>
		<div class="panel-body">
			Below are the requested permissions. Each of these represent a level of access you are granting to your buddy. Please check each one to make sure you are aware of what permissions you are giving them.
			<table id="scopes" class="table">
				<thead>
					<th><h4><span class="label label-default center-block text-center">Scope Name</span></h4></th>
					<th><h4><span class="label label-default center-block text-center">Scope Description</span></h4></th>
				</thead>
				
				<?
				foreach($details['scopes'] as $scope) {
					echo '
					<tbody>
						<td class="text-center"><code>'.$scope.'</code></td>
						<td class="text-center">'.getDescription($scope).'</td>
					</tbody>
					';
				}
				?>
				
			</table>
		</div>
		<div class="panel-footer">
			<div class="row">
				<button type = "button" class = "col-md-offset-1 col-md-10 btn btn-success" onclick="processRedirect();">Everything Looks Good!</button>
			</div>
		</div>
	</div>

    <div class="row text-center">
        <span>
            <i>Website Source: <a href="https://github.com/swiftyspiffy/twitch-token-generator" target="_blank">Repo</a><br>This tool was created and is maintained by swiftyspiffy. <br>
                <a href="https://twitch.tv/swiftyspiffy" target="_blank">
                    <img src="https://twitchtokengenerator.com/img/twitch.png" width="30" height="30">
                </a>
                <a href="https://twitter.com/swiftyspiffy" target="_blank">
                    <img src="https://twitchtokengenerator.com/img/twitter.png" width="45" height="45">
                </a>
                <a href="https://github.com/swiftyspiffy" target="_blank">
                    <img src="https://twitchtokengenerator.com/img/github.png" width="30" height="30">
                </a>
            <i>
        </span>
    </div>
	<br><br>
</div>
<script>
function processRedirect() {
	// populated via db
	var id = "<? echo $id; ?>";
	var scopes = "<? echo $details["scopes_str"]; ?>";
	// twitch variables
	var client_id = "gp762nuuoqcoxypju8c569th9wz7q5";
	// populated via db
	var redirect_uri = "https://twitchtokengenerator.com#" + id;
	// redirect
	window.location = "https://api.twitch.tv/kraken/oauth2/authorize?response_type=code&client_id=" + client_id + "&scope=" + scopes +"&redirect_uri=" + redirect_uri;
}

/* --- GA START --- */
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

ga('create', 'UA-91354985-1', 'auto');
ga('send', 'pageview');
/* --- GA END --- */

/* --- Runtime PHP Generated JS Vars START -- */
var authSuccessful = <? echo (strlen($access_token) > 1 ? "true" : "false"); ?>;
/* --- Runtime PHP Generated JS Vars START -- */
</script>
</html>


<?

function getDescription($name) {
	switch($name) {
		case "user_read":
			return "Read access to non-public user information, such as email address.";
		case "user_blocks_edit":
			return "Ability to ignore or unignore on behalf of a user.";
		case "user_blocks_read":
			return "Read access to a user's list of ignored users.";
		case "user_follows_edit":
			return "Access to manage a user's followed channels.";
		case "channel_read":
			return "Read access to non-public channel information, including email address and stream key.";
		case "channel_editor":
			return "Write access to channel metadata (game, status, etc).";
		case "channel_commercial":
			return "Access to trigger commercials on channel.";
		case "channel_stream":
			return "Ability to reset a channel's stream key.";
		case "channel_subscriptions":
			return "Read access to all subscribers to your channel.";
		case "user_subscriptions":
			return "Read access to subscriptions of a user.";
		case "channel_check_subscription":
			return "Read access to check if a user is subscribed to your channel.";
		case "chat_login":
			return "Ability to log into chat and send messages.";
		case "channel_feed_read":
			return "Ability to view to a channel feed.";
		case "channel_feed_edit":
			return "Ability to add posts and reactions to a channel feed.";
		case "collections_edit":
			return "Manage a user's collections (of videos).";
		case "communities_edit":
			return "Manage a user's communities.";
		case "communities_moderate":
			return "Manage communitiy moderators.";
		case "viewing_activity_read":
			return "Turn on Viewer Heartbeat Service ability to record user data.";
			
		default:
			return "No information available. Be careful.";
	}
}

function getRequestDetails($id) {
	mysql_connect(DB_HOST, DB_USER, DB_PASS) or
		die("Could not connect: " . mysql_error());
	mysql_select_db(DB_ANONDATA);

	$ab = mysql_query("SELECT * FROM `REQUESTS`") or trigger_error(mysql_error());
	while ($row = mysql_fetch_array($ab)) {
		if(strtolower($id) == strtolower($row['unique_string'])) {
			$scopeStr = $row['scopes'];
			$scopes = array();
			if (strpos($scopeStr, '+') !== false)
				$scopes = explode("+", $scopeStr);
			else
				array_push($scopes, $scopeStr);
			return array("enabled" => $row['enabled'], "scopes_str" => $scopeStr, "scopes" => $scopes, "requester_name" => $row['requester_name'], "requester_email" => $row['requester_email']);
		}
		
	}
	return array();
}
?>

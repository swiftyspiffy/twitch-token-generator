<?
$access_token = "";
if(isset($_GET['token']))
	$access_token = $_GET['token'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>Twitch Token Generator by swiftyspiffy</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/css/bootstrap.min.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
	<link rel="stylesheet" href="style.css">
		
	<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/js/bootstrap.min.js"></script>
	<script src="../bootstrap-checkbox.min.js"></script>
	<script src="../script.js"></script>
	<link rel="icon" type="image/png" sizes="32x32" href="../favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="96x96" href="../favicon-96x96.png">
	<link rel="icon" type="image/png" sizes="16x16" href="../favicon-16x16.png">
</head>
<div class="col-md-2"></div>
<div class="container col-md-8">	
	<br>
	<div class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title text-center">Twitch Token Generator Information</h3>
		</div>
		<div class="panel-body">
			<span>This tool is used to generate tokens for use with the Twitch API and Twitch Chat! To use the tool, simply select the scopes you want and click 'Generate Token!'. You will be prompted by Twitch to authorize your account with the selected scopes. Upon authorization, your access token will be placed in the textbox that says "Token will appear here..." .</span>
		</div>
	</div>
	
	<div class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title text-center">Generated Token</h3>
		</div>
		<div class="panel-body">
			<? if (strlen($access_token) > 1): ?>
			<input type="text" class="form-control" id="token" style="text-align: center; font-size: 250%; height: 200%; color: #3a8c15;" value="<? echo $access_token; ?>" placeholder="Token will appear here..." readonly>
			<? elseif(isset($_GET['error'])): ?>
			<input type="text" class="form-control" id="token" style="text-align: center; font-size: 200%; color: #a31824;" value="ERROR: <? echo $_GET['error']; ?>" placeholder="Token will appear here..." disabled>
			<? else: ?>
			<input type="text" class="form-control" id="token" style="text-align: center; font-size: 120%;" placeholder="Token will appear here..." disabled>
			<? endif; ?>
			<span><i>As a security precaution, this tool does NOT store your access token. You will need to generate a new token if you've lost your current one.<i></span>
		</div>
	</div>
	
	<div class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title text-center">Available Token Scopes</h3>
		</div>
		<div class="panel-body">
			<table id="soundbytes" class="table">
				<thead>
					<tr>
						<th><h4><span class="label label-default center-block text-center">Add Scope?</span></h4></th>
						<th><h4><span class="label label-default center-block text-center">Scope Name</span></h4></th>
					</tr>
				</thead>
				<tbody id="available_tokens">
					<td class="text-center"><code><input id="check_user_read" type="checkbox"></code></td>
					<td class="text-center small-scope-name"><code>user_read</code></td>
				</tbody>
				<tbody id="available_tokens">
					<td class="text-center"><code><input id="check_user_blocks_edit" type="checkbox"></code></td>
					<td class="text-center small-scope-name"><code>user_blocks_edit</code></td>
				</tbody>
				<tbody id="available_tokens">
					<td class="text-center"><code><input id="check_user_blocks_read" type="checkbox"></code></td>
					<td class="text-center small-scope-name"><code>user_blocks_read</code></td>
				</tbody>
				<tbody id="available_tokens">
					<td class="text-center"><code><input id="check_user_follows_edit" type="checkbox"></code></td>
					<td class="text-center small-scope-name"><code>user_follows_edit</code></td>
				</tbody>
				<tbody id="available_tokens">
					<td class="text-center"><code><input id="check_channel_read" type="checkbox"></code></td>
					<td class="text-center small-scope-name"><code>channel_read</code></td>
				</tbody>
				<tbody id="available_tokens">
					<td class="text-center"><code><input id="check_channel_editor" type="checkbox"></code></td>
					<td class="text-center small-scope-name"><code>channel_editor</code></td>
				</tbody>
				<tbody id="available_tokens">
					<td class="text-center"><code><input id="check_channel_commercial" type="checkbox"></code></td>
					<td class="text-center small-scope-name"><code>channel_commercial</code></td>
				</tbody>
				<tbody id="available_tokens">
					<td class="text-center"><code><input id="check_channel_stream" type="checkbox"></code></td>
					<td class="text-center small-scope-name"><code>channel_stream</code></td>
				</tbody>
				<tbody id="available_tokens">
					<td class="text-center"><code><input id="check_channel_subscriptions" type="checkbox"></code></td>
					<td class="text-center small-scope-name"><code>channel_subscriptions</code></td>
				</tbody>
				<tbody id="available_tokens">
					<td class="text-center"><code><input id="check_user_subscriptions" type="checkbox"></code></td>
					<td class="text-center small-scope-name"><code>user_subscriptions</code></td>
				</tbody>
				<tbody id="available_tokens">
					<td class="text-center"><code><input id="check_channel_check_subscription" type="checkbox"></code></td>
					<td class="text-center small-scope-name"><code>channel_check_subscription</code></td>
				</tbody>
				<tbody id="available_tokens">
					<td class="text-center"><code><input id="check_chat_login" type="checkbox"></code></td>
					<td class="text-center small-scope-name"><code>chat_login</code></td>
				</tbody>
				<tbody id="available_tokens">
					<td class="text-center"><code><input id="check_channel_feed_read" type="checkbox"></code></td>
					<td class="text-center small-scope-name"><code>channel_feed_read</code></td>
				</tbody>
				<tbody id="available_tokens">
					<td class="text-center"><code><input id="check_channel_feed_edit" type="checkbox"></code></td>
					<td class="text-center small-scope-name"><code>channel_feed_edit</code></td>
				</tbody>
			</table>
			<div class="row text-center">
				<button type = "button" class = "col-md-3 btn btn-success" onclick="authenticate();">Generate Token!</button>
			</div>
			<br>
			<div class="row text-center">
				<button type = "button" class = "btn btn-danger" onclick="clearScopeSelections();">Reset Scope Selections</button>
			</div>
		</div>
	</div>
	<div class="row text-center">
		<span><i>Website Source: <a href="https://github.com/swiftyspiffy/twitch-token-generator" target="_blank">Repo</a><br>This tool was created and is maintained by swiftyspiffy. <br><a href="https://twitch.tv/swiftyspiffy" target="_blank">Twitch</a> | <a href="https://twitter.com/swiftyspiffy" target="_blank">Twitter</a> | <a href="https://github.com/swiftyspiffy" target="_blank">GitHub</a><i></span>
	</div>
	<br><br>
</div>
<script>
/* --- GA START --- */
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

ga('create', 'UA-91354985-1', 'auto');
ga('send', 'pageview');
/* --- GA END --- */
</script>
</html>

<?php
include("../config.php");
include("../twitchtv.php");
?>

<title>Twitch Token Generator by swiftyspiffy - Redirecting...</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/js/bootstrap.min.js"></script>

<br>
<div class="container">
	<div class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title text-center">Twitch Token Generator - Redirecting...</h3>
		</div>
		<div class="panel-body">
			<center>
				<span>Redirecting you to authorize on Twitch for <b> <? echo $title; ?> </b>. <br>If you are not redirected in 5 seconds, <a style="font-weight: bold; font-size: 120%;" href="<? echo $url; ?>">click here</a>.
				<br><br>
				Thanks for using TwitchTokenGenerator.com!
				</span>
			</center>
		</div>
	</div>
</div>

<script>
var url = "<? echo $url; ?>";

setInterval(function(){ window.location.href = url; }, 3000);
</script>
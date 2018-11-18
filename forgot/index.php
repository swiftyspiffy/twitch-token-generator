<!DOCTYPE html>
<html lang="en">
<head>
	<title>Twitch Token Generator by swiftyspiffy</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/css/bootstrap.min.css">
	<link rel="stylesheet" href="https://twitchtokengenerator.com/assets/style.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
	<script src="script.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/js/bootstrap.min.js"></script>
	<script src="https://twitchtokengenerator.com/assets/bootstrap-checkbox.min.js"></script>
</head>
<div class="col-md-2"></div> 
<div class="container col-md-8">	
	<br>
	<div id="top" class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title text-center">Twitch Token Generator - Forgot Your Token Details?</h3>
		</div>
		<div class="panel-body">
			<div class="row text-center">
				<span>This tool can be used to identify the username, userid, dates and scopes assigned to an oauth token. Tokens do not need to be generated on <a href="https://twitchtokengenerator.com">TwitchTokenGenerator.com</a> for this tool to work.<br>Please insert the token below:</span><br><br>
			</div>
			<div class="input-group">
				<input type="text" class="form-control" id="token" style="text-align: center; font-size: 200%;" placeholder="Please insert oauth token">
				<span class="input-group-btn">
					<button class="btn btn-success" type="button" onclick="onQuery(); return false;">Get Token Details</button>
				</span>
			</div><br>
			<hr>
			<div class="row">
				<div class="col-md-3 text-center">
					<span class="badge badge-secondary">Username</span>
				</div>
				<div class="col-md-3 text-center">
					<span class="badge badge-secondary">User ID</span>
				</div>
				<div class="col-md-3 text-center">
					<span class="badge badge-secondary">Created At</span>
				</div>
				<div class="col-md-3 text-center">
					<span class="badge badge-secondary">Updated At</span>
				</div>
			</div><br>
			<div class="row">
				<div class="col-md-3">
					<input type="text" class="form-control" id="username" style="text-align: center;" readonly="">
				</div>
				<div class="col-md-3">
					<input type="text" class="form-control" id="userid" style="text-align: center;" readonly="">
				</div>
				<div class="col-md-3">
					<input type="text" class="form-control" id="created_at" style="text-align: center;" readonly="">
				</div>
				<div class="col-md-3">
					<input type="text" class="form-control" id="updated_at" style="text-align: center;" readonly="">
				</div>
			</div>
			<div class="row">
				<table id="scopes" class="table">
					<thead>
						<tr>
							<th><h4><span class="label label-default center-block text-center">Scope Name</span></h4></th>
							<th><h4><span class="label label-default center-block text-center">Scope Description</span></h4></th>
						</tr>
					</thead>
					<tbody id="applicable_scopes">
					
					</tbody>
				</table>
			</div>
			<div class="row">
				<div class="col-md-12">
					<div class="alert alert-info text-left" role="alert"><strong>API: </strong> https://twitchtokengenerator.com/api/forgot/&lt;ACCESS_TOKEN&gt;</div>
				</div>
			</div>
		</div>
		<div class="row text-center">
			<span>
				<i>Website Source: <a href="https://github.com/swiftyspiffy/twitch-token-generator" target="_blank">Repo</a><br>This tool was created and is maintained by swiftyspiffy.</i> 
				<br>
				<a href="https://twitch.tv/swiftyspiffy" target="_blank">
					<img src="https://twitchtokengenerator.com/img/twitch.png" width="30" height="30">
				</a>
				<a href="https://twitter.com/swiftyspiffy" target="_blank">
					<img src="https://twitchtokengenerator.com/img/twitter.png" width="45" height="45">
				</a>
				<a href="https://github.com/swiftyspiffy" target="_blank">
					<img src="https://twitchtokengenerator.com/img/github.png" width="30" height="30">
				</a>
            </span>
		</div>
	</div>
</div>
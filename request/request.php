<?
$details = $dao->getRequestDetails($id);
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
				$scopes = $dao->getAllScopes();
				foreach($details['scopes'] as $scope) {
					echo '
					<tbody>
						<td class="text-center"><code>'.$scope.'</code></td>
						<td class="text-center">'.$scopes[$scope]['desc'].'</td>
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
	var client_id = "<? echo FRONTEND_CLIENT_ID; ?>";
	// populated via db
	var redirect_uri = "https://twitchtokengenerator.com#" + id;
	// redirect
	window.location = "https://id.twitch.tv/oauth2/authorize?response_type=code&client_id=" + client_id + "&force_verify=true&state=" + id + "&scope=" + scopes +"&redirect_uri=" + redirect_uri;
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

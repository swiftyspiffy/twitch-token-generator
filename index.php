<?
include("dao.php");
include("twitchtv.php");

$dao = new dao();

$access_token = "";
$id = "";
$username = "";
if(isset($_GET['code'])) {
	$data = getAccessToken($_GET['code'], "frontend");
	$access_token = $data['access'];
	$refresh_token = $data['refresh'];
	if(strlen($access_token) < 5 || strlen($refresh_token) < 5) { $dao->insertError("index.php", "", "no access token or refresh token"); }
	$username = $dao->getUsername($access_token);
	if($username == null)
	    $username = "[Not set]";
	$country = $dao->getCountry($_SERVER['REMOTE_ADDR']);    
	
	$spamRules = $dao->getSpamRules();
	$spamResult = isSpam($spamRules, $_SERVER['REMOTE_ADDR'], $_GET['scope'], $country, $username, $_SERVER['HTTP_USER_AGENT']);
	
	if(isset($_GET['scope'])) {
		$dao->logUsage($_SERVER['REMOTE_ADDR'], $_GET['scope'], $country, $username, $_SERVER['HTTP_USER_AGENT'], $spamResult);
		$udata = $dao->getUserdata($username, $access_token);
		$partner = $udata['partner'] ? "1" : "0";
		$dao->logMetadata($username, $udata['userid'], $udata['followers'], $udata['views'], $partner);
	} else {
		$dao->logUsage($_SERVER['REMOTE_ADDR'], "", $country, $username, $_SERVER['HTTP_USER_AGENT'], $spamResult);
	}
	
	if(isset($_GET['state'])) {
		exit(header("Location: https://twitchtokengenerator.com/request/".$_GET['state']."/".$access_token."/".$refresh_token));
	}
	
	if(strlen($spamResult) > 0) {
	    $access_token = genFakeToken(30);
		$refresh_token = genFakeToken(50);
	}
	
	if($username != "[Not set]") {
		$id = generateRandomString();
		$dao->insertRecaptchaListing($id, $access_token, $refresh_token, $username);
		$access_token = "Please complete the Captcha";
		$refresh_token = "Please complete the Captcha";
	}
}
	
$scopes = $dao->getScopes();
	
?>

<!DOCTYPE html>
<html lang="en">
<script>
	var scopes_set = <? echo isset($_GET['scope']) ? "true" : "false"; ?>;
	var token = "<? if (strlen($access_token) > 1) echo $access_token; ?>";
	<?
	if(isset($_GET['code'])) {
		echo 'var captchaId = "'.$id.'";';
	} else {
		echo 'var captchaId = "";';
	}
	?>
</script>
<head>
	<title>Twitch Token Generator by swiftyspiffy</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/css/bootstrap.min.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
		
	<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/js/bootstrap.min.js"></script>
	<script src="https://twitchtokengenerator.com/assets/bootstrap-checkbox.min.js"></script>
	<script src="https://twitchtokengenerator.com/assets/script.js"></script>
	<link rel="stylesheet" href="https://twitchtokengenerator.com/assets/style.css">
	<link rel="icon" type="image/ico" sizes="48x48" href="https://twitchtokengenerator.com/assets/favicon-48x48.ico">
	<script src='https://www.google.com/recaptcha/api.js'></script>
</head>
<div class="modal fade" id="welcomeModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="modal-title">Welcome to TwitchTokenGenerator.com</h5>
                    </div>
                    <div class="col-md-6 pull-right">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-body text-center">
                <span class="text-center" style="font-size: 120%;">I am here to get a...</span><br><br>
                <div class="btn-group" role="group" style="width: 100%;">
                    <button type="button" onclick="wantsBotToken();" style="width: 50%;" class="btn btn-default">
                        <img src="https://twitchtokengenerator.com/img/destructoid.png" height="110"><br>
                        <span style="font-weight: bold; font-size: 150%;">Bot Chat Token</span>
                    </button>
                    <button type="button" style="width: 50%;" data-dismiss="modal" aria-label="Close" class="btn btn-default">
                        <img src="https://twitchtokengenerator.com/img/giveplz.png" height="110"><br>
                        <span style="font-weight: bold; font-size: 150%;">Custom Scope Token</span>
                    </button>
                </div><br><br>
                <a href="#" data-dismiss="modal" aria-label="Close" style="color: #6441A4">Uhhhh what? Just take me to the site.</a>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="quickLinkModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="modal-title">Quick Link Generator</h5>
                    </div>
                    <div class="col-md-6 pull-right">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-body">
                <span class="text-center" style="font-size: 120%; padding-bottom: 3px;">You are creating a quick link for the following scope permissions:</span>
				<ul id="quick_link_permissions" style="margin-top: 10px; margin-bottom: 10px;"></ul>
				<div class="row">
					<div style="width: 100%;" class="btn-group mr-2 col-md-12 pull-left">
						<button id="quicklink_auth_auth" type = "button" style="width: 50%;" onclick="toggleQuickLinkAuth(this.id);" class = "btn btn-secondary">Authenticate Immediately</button>
						<button id="quicklink_auth_stay" type = "button" style="width: 50%;" onclick="toggleQuickLinkAuth(this.id);" class = "btn btn-primary">Stay on TwitchTokenGenerator</button>
					</div>
				</div>
            </div>
			<div class="modal-footer" style="padding-top: 12px;" >
				<div class="row" style="padding-left: 15px; padding-right: 15px;" >
					<button type="button" class="btn btn-success col-md-12 pull-left" onclick="fetchQuickLinkUrl()" id="quick_generate_link">Generate Quick Link</button>
				</div>
			</div>
        </div>
    </div>
</div>
<div class="modal fade" id="requestModal">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
		<div class="row">
			<div class="col-md-6">
				<h5 class="modal-title">Request Token From User</h5>
			</div>
			<div class="col-md-6 pull-right">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
		</div>
      </div>
      <div class="modal-body">
		<p>This system allows you to request a token from a Twitch user via a link. Using the scopes you selected, a link will be generated. You can share the link with a streamer or Twitch user. After they approve, their Twitch token will be emailed to you. Please provide your Twitch name and email address below.</p>
		<label for="my_name">My Twitch Name:</label>
		<input type="text" class="form-control" id="my_name">
		<label for="my_email">My Email (to send token to):</label>
		<input type="text" class="form-control" id="my_email">
      </div>
      <div class="modal-footer" style="padding-top: 12px;" >
		<div class="row" style="padding-left: 15px; padding-right: 15px;" >
			<button type="button" class="btn btn-success col-md-12 pull-left" onclick="fetchRequestUrl()" id="generate_link">Generate Link</button>
		</div>
        
      </div>
    </div>
  </div>
</div>
<div class="col-md-2"></div> 
<div class="container col-md-8">	
	<br>
	<div id="top" class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title text-center">Twitch Token Generator Information</h3>
		</div>
		<div class="panel-body">
			<span>This tool is used to generate tokens for use with the Twitch API and Twitch Chat! To use the tool, simply select the scopes you want and click 'Generate Token!'. You will be prompted by Twitch to authorize your account with the selected scopes. Upon authorization, your access token will be placed in the textbox that says "Token will appear here..." .</span>
		</div>
	</div>
	
	<div class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title text-center">Generated Tokens</h3>
		</div>
		<div class="panel-body">
			<? if (strlen($access_token) > 1): ?>
			<table class="table table-striped">
				<tbody>
					<tr>
						<td style="width: 20%;" class="text-center"><strong>ACCESS TOKEN</strong></td>
						<td style="width: 80%;" class="text-center">
							<div class="input-group">
								<input type="text" class="form-control" id="access" style="text-align: center; font-size: 200%; color: #009900;" value="<? echo $access_token; ?>" placeholder="Access Token will appear here..." readonly>
								<span class="input-group-btn">
									<button class="btn btn-success" type="button" onclick="copyInput(this, 'access');">Copy</button>
								</span>
							</div>
							
						</td>
					</tr>
					<tr>
						<td style="width: 20%;" class="text-center"><strong>REFRESH TOKEN</strong></td>
						<td style="width: 80%;" class="text-center">
							<div class="input-group">
								<input type="text" class="form-control" id="refresh" style="text-align: center; font-size: 200%; color: #009900;" value="<? echo $refresh_token; ?>" placeholder="Refresh Token will appear here..." readonly>
								<span class="input-group-btn">
									<button class="btn btn-success" type="button" onclick="copyInput(this, 'refresh');">Copy</button>
								</span>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
			<? elseif(isset($_GET['error'])): ?>
			<table class="table table-striped">
				<tbody>
					<tr>
						<td style="width: 20%;" class="text-center"><strong>ACCESS TOKEN</strong></td>
						<td style="width: 80%;" class="text-center">
							<div class="input-group">
								<input type="text" class="form-control" id="access" style="text-align: center; font-size: 200%; color: #a31824;" value="ERROR: <? echo $_GET['error']; ?>" placeholder="Access Token will appear here..." readonly>
								<span class="input-group-btn">
									<button class="btn btn-success" type="button" onclick="copyInput(this, 'access');">Copy</button>
								</span>
							</div>
						</td>
					</tr>
					<tr>
						<td style="width: 20%;" class="text-center"><strong>REFRESH TOKEN</strong></td>
						<td style="width: 80%;" class="text-center">
							<div class="input-group">
								<input type="text" class="form-control" id="refresh" style="text-align: center; font-size: 200%; color: #a31824;" value="ERROR: <? echo $_GET['error']; ?>" placeholder="Refresh Token will appear here..." readonly>
								<span class="input-group-btn">
									<button class="btn btn-success" type="button" onclick="copyInput(this, 'refresh');">Copy</button>
								</span>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
			<? else: ?>
			<table class="table table-striped">
				<tbody>
					<tr>
						<td style="width: 20%;" class="text-center"><strong>ACCESS TOKEN</strong></td>
						<td style="width: 80%;" class="text-center"><input type="text" class="form-control" id="access" style="text-align: center; font-size: 120%;" placeholder="Access Token will appear here..." readonly></td>
					</tr>
					<tr>
						<td style="width: 20%;" class="text-center"><strong>REFRESH TOKEN</strong></td>
						<td style="width: 80%;" class="text-center"><input type="text" class="form-control" id="refresh" style="text-align: center; font-size: 120%;" placeholder="Refresh Token will appear here..." readonly></td>
					</tr>
				</tbody>
			</table>
			<? endif; ?>
			<span class="text-center" style="width: 100%;"><i>As a security precaution, this tool does NOT store your tokens. You will need to generate new tokens if you've lost your current ones.</i></span>
		</div>
	</div>

	<div class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title text-center">Available Token Scopes</h3>
		</div>
		<div class="panel-body">
            <h3><span class="label label-primary">v5</span></h3>
			<table id="soundbytes" class="table">
				<thead>
					<tr>
						<th><h4><span class="label label-default center-block text-center">Add Scope?</span></h4></th>
						<th><h4><span class="label label-default center-block text-center">Scope Name</span></h4></th>
						<th><h4><span class="label label-default center-block text-center">Scope Description</span></h4></th>
					</tr>
				</thead>
				<?
			foreach($scopes['v5'] as $scope) {
				echo '<tbody id="available_tokens">';
					echo '<td style="width: 20%;" class="text-center"><code><input id="check_'.$scope['scope'].'" type="checkbox"></code></td>';
					echo '<td style="width: 20%;" class="text-center"><code>'.$scope['scope'].'</code></td>';
					echo '<td style="width: 60%;" class="text-center">'.$scope['desc'].'</td>';
				echo '</tbody>';
			}
				
				?>
			</table>
            <h3><span class="label label-primary">Helix</span></h3><br>
            <table id="soundbytes" class="table">
                <?
			foreach($scopes['helix'] as $scope) {
				echo '<tbody id="available_tokens">';
					echo '<td style="width: 20%;" class="text-center"><code><input id="check_helix_'.str_replace(":", "_", $scope['scope']).'" type="checkbox"></code></td>';
					echo '<td style="width: 20%;" class="text-center"><code>'.$scope['scope'].'</code></td>';
					echo '<td style="width: 60%;" class="text-center">'.$scope['desc'].'</td>';
				echo '</tbody>';
			}
		?>
            </table>
			<div class="row">
				<div class="col-md-1"></div>
				<div class="btn-group mr-2 col-md-3">
					<button type = "button" class = "btn btn-danger" onclick="clearScopeSelections();">Reset All</button>
					<button type = "button" class = "btn btn-success" onclick="selectAllScopes();">Select All</button>
				</div>
				<div class="btn-group mr-2 col-md-4">
					<button type = "button" class = "btn btn-secondary" onclick="launchQuickLinkModal();">Quick Link!</button>
					<button type = "button" class = "btn btn-primary" onclick="launchRequestModal();">Request Token!</button>
				</div>
				<button type = "button" class = "col-md-3 btn btn-success" onclick="authenticate(true);">Generate Token!</button>
				<div class="col-md-1"></div>
			</div>
		</div>
	</div>

	<div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title text-center">Refresh Access Token</h3>
        </div>
        <div class="panel-body text-center">
			<div class="row">
				<div class="col-md-12">
					<span>If you have generated an access token with TwitchTokenGenerator.com in the past, you can paste the accompanying refresh token here and perform a refresh request to generate a new access token and refresh token. This will reset the 60 day validity countdown.</span>
				</div>
			</div>
            <div class="row">
				<div class="col-md-12">
					<div class="input-group">
						<input type="text" class="form-control" id="refresh_token_refresh" placeholder="Paste refresh token here.">
						<span class="input-group-btn">
							<button class="btn btn-success" type="button" onclick="performRefreshRequest()">Refresh Access Token with Refresh Token!</button>
						</span>
					</div>
				</div>
			</div><br>
			<div class="row">
				<div class="col-md-12">
					<div class="alert alert-info text-left" role="alert"><strong>API: </strong> https://twitchtokengenerator.com/api/refresh/&lt;REFRESH_TOKEN&gt;</div>
				</div>
			</div>
        </div>
    </div>
	
	<div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title text-center">Revoke Your Access Token <span class="label label-success">NEW</span></h3>
        </div>
        <div class="panel-body text-center">
			<span>If you'd like to invalid your access token because it leaked or you want to rotate it without blocking TwitchTokenGenerator as a whole, you can revoke individual tokens here:</span>
			<div class="row">
				<div class="col-md-12">
					<div class="input-group">
						<input type="text" class="form-control" id="revoke_access_token" placeholder="Paste access token to be revoked here.">
						<span class="input-group-btn">
							<button class="btn btn-success" type="button" onclick="revokeToken()">Revoke Access Token!</button>
						</span>
					</div>
				</div>
			</div><br>
			<div class="row">
				<div class="col-md-12">
					<div class="alert alert-info text-left" role="alert"><strong>API: </strong> https://twitchtokengenerator.com/api/revoke/&lt;ACCESS_TOKEN&gt;</div>
				</div>
			</div>
        </div>
    </div>
	
	<div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title text-center">Forgot Your Token Details?</h3>
        </div>
        <div class="panel-body text-center">
            <span>If you've forgotten the username, userid, scopes or dates assigned to an oauth token (doesn't need to be from TwitchTokenGenerator.com), you can use this tool to get them: <br>
			<a target="_blank" style="font-size: 120%; font-weight: bold;" class="twitch-link" href="https://twitchtokengenerator.com/forgot">twitchtokengenerator.com/forgot</a></span>
		</div>
    </div>
	
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title text-center">TwitchTokenGenerator.com Statistics </h3>
        </div>
        <div class="panel-body text-center">
            <span>All available anonymized service statistics can be found at the following link:<br>
			<a target="_blank" style="font-size: 120%; font-weight: bold;" class="twitch-link" href="https://twitchtokengenerator.com/stats">twitchtokengenerator.com/stats</a></span>
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
<div class="modal fade-scale" id="cyborgModal" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="modal-title">You're not a robot right?</h5>
                    </div>
                </div>
            </div>
            <div class="modal-body text-center">
                <span class="text-center" style="font-size: 120%;">Prove your humanity at once!</span><br><br>
				<form id="robot_form" action="internal.php" method="post">
					<input type="hidden" id="robot_identifier" name="robot_identifier" value="<? echo $id; ?>"></input>
					<div class="g-recaptcha" data-callback="recaptchaSuccess" style="padding-left: 23%" data-sitekey="6LeaCF0UAAAAAMG7-HRJ1Oq_aneLPdQQNN0r9_no"></div>
				</form>
				<img class="text-center" src="https://twitchtokengenerator.com/img/kappa.gif"><br>
				<span id="waiting_text" class="text-center" style="font-size: 70%">waiting...</span>
            </div>
        </div>
    </div>
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

/* --- Runtime PHP Generated JS Vars START -- */
var authSuccessful = <? echo (strlen($access_token) > 1 ? "true" : "false"); ?>;
/* --- Runtime PHP Generated JS Vars END -- */
</script>
</html>

<?

function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function isSpam($rules, $ip, $scope, $country, $username, $useragent) {
    foreach($rules as $rule) {
        if(strlen($rule['ip']) > 0 && $rule['ip'] != $ip)
            return false;
        if(strlen($rule['scopes']) > 0 && $rule['scopes'] != $scope)
            return false;
        if(strlen($rule['country']) > 0 && $rule['country'] != $country)
            return false;
        if(strlen($rule['username']) > 0 && $rule['username'] != $username)
            return false;
        if(strlen($rule['useragent']) > 0 && $rule['useragent'] != $useragent)
            return false;
            
        return $rule['id'];
    }
    return "";
}

function genFakeToken($length = 10) {
    return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyz', ceil($length/strlen($x)) )),1,$length);
}

?>
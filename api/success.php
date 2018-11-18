<?php
include("../config.php");
include("../twitchtv.php");

if(!isset($_GET['code'])) {
    header('Content-Type: application/json');
    exit(json_encode(array('success' => false, 'error' => 8, 'message' => 'No code present.')));
}
if(!isset($_GET['state'])) {
    header('Content-Type: application/json');
    exit(json_encode(array('success' => false, 'error' => 9, 'message' => 'No state present.')));
}
$status;
try {
    $unique = json_decode(base64_decode($_GET['state']), true)['id'];
    $status = $dao->getAPISuccessStatus($unique);
    if(!$status['available']) {
        header('Content-Type: application/json');
        exit(json_encode(array('success' => false, 'error' => 15, 'message' => 'API flow no longer available! Create a new one!')));
    }

	$data = getAccessToken($_GET['code'], "api");
    $access_token = $data['access'];
	$refresh_token = $data['refresh'];
    $username = $dao->getUsername($access_token);
	$userid = $dao->getUserId($username, $access_token);

    $dao->updateAPIListing($unique, $access_token, $refresh_token, $username, $userid);

    $dao->logUsage($_SERVER['REMOTE_ADDR'], $status['scopes'], $dao->getCountry($_SERVER['REMOTE_ADDR']), $dao->getUsername($access_token), $_SERVER['HTTP_USER_AGENT'], "");
	$udata = $dao->getUserdata($username, $access_token);
	$partner = $udata['partner'] ? "1" : "0";
	$dao->logMetadata($username, $udata['userid'], $udata['followers'], $udata['views'], $partner);
}catch(Exception $ex) {
	exit($ex);
}
if (strlen($access_token) == 0 || strlen($unique) == 0) {
	header('Content-Type: application/json');
    exit(json_encode(array('success' => false, 'error' => 11, 'message' => 'API did not succeed, invalid code/state.('.$access_token.'|'.$unique.')')));
}

?>
<title>Twitch Token Generator by swiftyspiffy - API Success</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/js/bootstrap.min.js"></script>

<br>
<div class="container">
	<div class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title text-center">Twitch Token Generator - API Success</h3>
		</div>
		<div class="panel-body">
			<span>Thank you for using TwitchTokenGenerator! Your username and auth token has been made available to <b><? echo $status['title']; ?></b>! You may now close this window/tab.</span>
		</div>
	</div>
</div>
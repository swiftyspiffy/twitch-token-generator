<?php

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

	$data = getCredentials($_GET['code']);
    $access_token = $data['access'];
	$refresh_token = $data['refresh'];
	
    $username = $dao->getUsername($access_token);
	$userid = $dao->getUserId($username, $access_token);

    $dao->updateAPIListing($unique, $access_token, $refresh_token, $username, $userid);

    $dao->logUsage($_SERVER['REMOTE_ADDR'], $status['scopes'], $dao->getCountry($_SERVER['REMOTE_ADDR']), $dao->getUsername($access_token));
}catch(Exception $ex) {

}
?>
<title>Twitch Token Generator by swiftyspiffy - API Success</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/js/bootstrap.min.js"></script>

<? if (strlen($access_token) > 0 && strlen($unique) > 0): ?>
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

<? else:
    header('Content-Type: application/json');
    exit(json_encode(array('success' => false, 'error' => 11, 'message' => 'API did not succeed, invalid code/state.')));
endif;

function getCredentials($code) {
    $ch = curl_init("https://api.twitch.tv/kraken/" . "oauth2/token");
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    $fields = array(
        'client_id' => "zkxgn9qm9y3kzrb1p0px68qa69t3ae",
        'client_secret' => "vcoad2sha5lw6p05wcbreiiik2t09u",
        'grant_type' => 'authorization_code',
        'redirect_uri' => "https://twitchtokengenerator.com/api/success",
        'code' => $code
    );
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
    $data = curl_exec($ch);
    $response = json_decode($data, true);
    return array('access' => $response["access_token"], 'refresh' => $response['refresh_token']);
}

?>
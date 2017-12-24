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
    $status = getStatus($unique);
    if(!$status['available']) {
        header('Content-Type: application/json');
        exit(json_encode(array('success' => false, 'error' => 15, 'message' => 'API flow no longer available! Create a new one!')));
    }

	$data = getCredentials($_GET['code']);
    $access_token = $data['access'];
	$refresh_token = $data['refresh'];
	
    $username = getUsername($access_token);

    updateListing($unique, $access_token, $refresh_token, $username);
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

function getStatus($id) {
    mysql_connect(DB_HOST, DB_USER, DB_PASS) or
    die("Could not connect: " . mysql_error());
    mysql_select_db(DB_ANONDATA);

    $ab = mysql_query("SELECT * FROM `API`") or trigger_error(mysql_error());
    while ($row = mysql_fetch_array($ab)) {
        if($row['unique_string'] == $id) {
            if($row['status'] != "0") {
                return array('available' => false);
            } else {
                return array('available' => true, 'title' => $row['title'], 'scopes' => $row['scopes']);
            }
        }
    }
    return array('available' => false);
}

function getUsername($token) {
    $usernameResult = file_get_contents("https://api.twitch.tv/kraken?oauth_token=" . $token);
    $json_decoded_usernameResult = json_decode($usernameResult, true);
    return $json_decoded_usernameResult['token']['user_name'];
}

function updateListing($unique, $token, $refresh, $username) {
    mysql_connect(DB_HOST, DB_USER, DB_PASS) or
    die("Could not connect: " . mysql_error());
    mysql_select_db(DB_ANONDATA);

    // variables dont need to be validated since they're all generated internally
    $ab = mysql_query("UPDATE  `DB_ANONDATA`.`API` SET  `status` =  '1', `token` = '".$token."', `username` = '".$username."' WHERE  `api`.`unique_string` ='".$unique."';") or trigger_error(mysql_error());
}

function getCredentials($code) {
    $ch = curl_init("https://api.twitch.tv/kraken/" . "oauth2/token");
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    $fields = array(
        'client_id' => "CLIENTID",
        'client_secret' => "CLIENTSECRET",
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
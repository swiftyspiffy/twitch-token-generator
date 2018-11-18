<?
header('Content-Type: application/json');

if(count($args) == 1) {
	$resp = refresh($args[0]);
	if($resp['success']) {
		$username = $dao->getUsername($resp['access']);
		$dao->insertRefreshRequest(1, $username, $_SERVER['REMOTE_ADDR']);
		exit(json_encode(array('success' => true, 'token' => $resp['access'], 'refresh' => $resp['refresh'])));
	} else {
		$dao->insertRefreshRequest(0, "", $_SERVER['REMOTE_ADDR']);
		exit(json_encode(array('success' => false, 'error' => 22, 'message' => 'Invalid refresh token received.')));
	}
} else {
	exit(json_encode(array('success' => false, 'error' => 21, 'message' => 'No refresh token provided.')));
}


function refresh($token) {
	$ch = curl_init("https://api.twitch.tv/kraken/" . "oauth2/token");
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	$fields = array(
		'grant_type' => "refresh_token",
		'refresh_token' => urlencode($token),
		'client_id' => API_CLIENT_ID,
		'client_secret' => API_CLIENT_SECRET,
		'redirect_uri' => API_REDIRECT,
		'code' => $token
	);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
	$data = curl_exec($ch);
	$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$response = json_decode($data, true);
	if($httpcode == 200) {
		return array('success' => true, 'access' => $response['access_token'], 'refresh' => $response['refresh_token']);
	} else {
		return array('success' => false);
	}
}
?>
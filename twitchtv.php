<?
function getAccessToken($code, $type) {
	$clientId = "";
	$secret = "";
	$redirect = "";
	
	switch($type) {
		case "frontend":
			$clientId = FRONTEND_CLIENT_ID;
			$secret = FRONTEND_CLIENT_SECRET;
			$redirect = FRONTEND_REDIRECT;
			break;
		case "api":
			$clientId = API_CLIENT_ID;
			$secret = API_CLIENT_SECRET;
			$redirect = API_REDIRECT;
			break;
		default:
			exit("unknown getAccessToken type");
	}
	$curl = curl_init("https://id.twitch.tv/oauth2/token");
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_POST, 1);
	$fields = array(
		'client_id' => $clientId,
		'client_secret' => $secret,
		'grant_type' => 'authorization_code',
		'redirect_uri' => $redirect,
		'code' => $code
	);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $fields);
	$data = curl_exec($curl);
	$response = json_decode($data, true);
	return array('access' => $response['access_token'], 'refresh' => $response['refresh_token']);
}
?>